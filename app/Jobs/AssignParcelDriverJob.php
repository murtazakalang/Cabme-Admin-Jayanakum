<?php

namespace App\Jobs;

use App\Models\Driver;
use App\Models\Settings;
use App\Helpers\Helper;
use App\Http\Controllers\API\v1\GcmController;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class AssignParcelDriverJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $booking;

    /**
     * Create a new job instance.
     */
    public function __construct(Requests $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Check for only new rides
        if ($this->booking->status !== 'new' || $this->booking->id_conducteur) {
            return;
        }

        // Get current assigned driver
        $assignedDriverId = $this->booking->assigned_driver_id;

        // Load rejected drivers
        $rejDriverIds = json_decode($this->booking->rejected_driver_id ?? '[]', true);
        $rejDriverIds = is_array($rejDriverIds) ? $rejDriverIds : [];
        
        // Add previously assigned driver to rejected list
        if ($assignedDriverId) {
            $rejDriverIds[] = $assignedDriverId;
        }
        $rejDriverIds = array_unique($rejDriverIds);
        
        //Get booking location
        $lat = $this->booking->lat_source;
        $long = $this->booking->lng_source;
        $userZoneId = Helper::getUserZoneId($lat, $long);

        //Get radius & distance map
        $settings = Settings::first();
        $delivery_distance = $settings->delivery_distance;
        $earthRadius = $delivery_distance == "KM" ? "6371" : "3959";
        $radius = $settings->driver_radios;
        $accept_reject_time = $settings->trip_accept_reject_driver_time_sec ? $settings->trip_accept_reject_driver_time_sec : 0;
        $minimum_wallet_balance = $settings->minimum_deposit_amount ? $settings->minimum_deposit_amount : 0;

        $newDriver = Driver::select(
            'conducteur.id',
            'conducteur.fcm_id',
            'conducteur.subscriptionPlanId',
            'conducteur.subscriptionExpiryDate',
            'conducteur.subscriptionTotalOrders',
            'conducteur.subscription_plan',
            'conducteur.ownerId',
            DB::raw("(
                $earthRadius * acos(
                    cos(radians(?)) *
                    cos(radians(conducteur.latitude)) *
                    cos(radians(conducteur.longitude) - radians(?)) +
                    sin(radians(?)) *
                    sin(radians(conducteur.latitude))
                )
            ) AS distance")
        )
        ->join('vehicule', 'vehicule.id_conducteur', '=', 'conducteur.id')
        ->join('type_vehicule', 'vehicule.id_type_vehicule', '=', 'type_vehicule.id')
        ->join('zones', function ($join) {
            $join->on(DB::raw('FIND_IN_SET(zones.id, conducteur.zone_id)'), '>', DB::raw('0'));
        })
        ->when($userZoneId, function($q) use ($userZoneId) {
            $q->where('zones.id', $userZoneId);
        })
        ->leftJoin('conducteur as owner', 'conducteur.ownerId', '=', 'owner.id')
        ->addBinding([$lat, $long, $lat], 'select')
        ->where('conducteur.statut', 'yes')
        ->where('conducteur.online', 'yes')
        ->where('conducteur.driver_on_ride', 'no')
        ->where(function ($q) use ($minimum_wallet_balance, $settings) {
            // Owner
            $q->where('conducteur.isOwner', 'true')
            ->when($settings->owner_doc_verification == 'yes', function($query) {
                $query->where('conducteur.is_verified', 1);
            })
            // Drivers under owner (no verification needed)
            ->orWhere(function($q1) {
                $q1->whereNotNull('conducteur.ownerId');
                    // No is_verified filter here
            })
            // Individual drivers
            ->orWhere(function ($q2) use ($minimum_wallet_balance, $settings) {
                $q2->where('conducteur.isOwner', 'false')
                    ->whereNull('conducteur.ownerId')
                    ->where('conducteur.amount', '>=', $minimum_wallet_balance)
                    ->when($settings->driver_doc_verification == 'yes', function($sub2) {
                        $sub2->where('conducteur.is_verified', 1);
                    });
            });
        })
        ->where('conducteur.id', '!=', $this->booking->assigned_driver_id)
        ->whereNotIn('conducteur.id', $rejDriverIds)
        ->whereNotIn('conducteur.id', function($query) {
                $query->select('assigned_driver_id')
                    ->from('parcel_orders')
                    ->whereNotIn('status',['canceled', 'completed','rejected'])
                    ->whereNotNull('assigned_driver_id')
                    ->where('assigned_driver_id', '!=', '');
            })
        ->having('distance', '<=', $radius)
        ->orderBy('distance', 'asc')
        ->first(); 
             
        if ($newDriver) {

            if(Helper::isDriverBookingAllowed($newDriver->id,'subscriptionTotalOrders')){

                // Reassign new driver
                $this->booking->assigned_driver_id = $newDriver->id;
                $this->booking->rejected_driver_id = json_encode($rejDriverIds);
                $this->booking->save();

                //send notification to driver
                $fcm_token = $newDriver->fcm_id;
                if (!empty($fcm_token)) {
                    $message = array("body" => 'New Parcel', "title" => 'You have just received a request from a client', "sound" => "mySound", "tag" => "parcelnewrider");
                    GcmController::sendNotification($fcm_token, $message);
                }

                // Re-dispatch job to retry after timeout
                AssignParcelDriverJob::dispatch($this->booking)->delay(now()->addSeconds($accept_reject_time));

                Log::info('AssignParcelDriverJob triggered from job', ['booking_id' => $this->booking->id, 'driver_id' => $newDriver->id]);

            }else{

                // Reassign new driver
                $rejDriverIds[] = $newDriver->id;
                $rejDriverIds = array_unique($rejDriverIds);
                $this->booking->rejected_driver_id = json_encode($rejDriverIds);
                $this->booking->save();

                // Re-dispatch job to retry after timeout
                AssignParcelDriverJob::dispatch($this->booking)->delay(now()->addSeconds($accept_reject_time));

                Log::info('AssignParcelDriverJob triggered from job and fetched driver is not able to get order due to limit', 
                    ['booking_id' => $this->booking->id, 'driver_id' => $newDriver->id]
                );
            }
            
        }else{

            //No drivers left — cancel the ride
            $this->booking->assigned_driver_id = null;
            $this->booking->status = 'canceled';
            $this->booking->save();
            Log::warning('No driver found. Parcel booking cancelled.', ['booking_id' => $this->booking->id]);
        } 
    }
}
