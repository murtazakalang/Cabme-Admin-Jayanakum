<?php

namespace App\Observers;

use App\Models\Requests;
use App\Models\Driver;
use App\Models\DeliveryCharges;
use App\Models\Vehicle;
use App\Models\PaymentMethod;
use App\Events\RideUpdatedEvent;
use App\Events\CustomerRidesUpdatedEvent;
use App\Events\DriverRidesUpdatedEvent;
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RideObserver
{
    /**
     * Handle the Requests "created" event.
     */
    public function created(Requests $ride): void
    {
      //
    }

    /**
     * Handle the Requests "updated" event.
     */
    public function updated(Requests $ride): void
    {

        //Hide unwanted fields from response
        $ride->makeHidden([
            'trip_objective', 'trip_category', 'age_children1', 'age_children2', 'age_children3', 'user_info',
            'place', 'tip_amount', 'trajet', 'date_retour', 'heure_retour', 'statut_round','statut_course','statut_course',
            'transaction_id', 'modifier', 'id_conducteur_accepter', 'car_driver_confirmed', 'deleted_at',
            'updated_at', 'dispatcher_id', 'ownerId', 'rejected_driver_id',
        ]);

        //Set driver & user details with ride response
        if($ride->id_conducteur){
            $ride->load(['driver:id,nom,prenom,phone,latitude,longitude,review_sum,review_count,average_rating']);
            if ($ride->driver) {
                $ride->driver->image = (!empty($ride->driver->photo_path) && file_exists(public_path('assets/images/driver/' . $ride->driver->photo_path)))
                    ? asset('assets/images/driver/' . $ride->driver->photo_path)
                    : asset('assets/images/placeholder_image.jpg');
                $ride->driver->vehicle_details = Helper::getVehicleDetails($ride->id_conducteur);
            }
        }
        $ride->load(['user:id,nom,prenom,email,phone,photo_path,review_sum,review_count,average_rating']);
        if ($ride->user) {
            $ride->user->image = (!empty($ride->user->photo_path) && file_exists(public_path('assets/images/users/' . $ride->user->photo_path)))
                ? asset('assets/images/users/' . $ride->user->photo_path)
                : asset('assets/images/placeholder_image.jpg');
            unset($ride->user->photo_path);
        }
        if($ride->id_payment_method){
            $ride->payment_method = PaymentMethod::where('id', $ride->id_payment_method)->value('libelle');
        }

        $ride->discount_type = is_string($ride->discount_type) ? json_decode($ride->discount_type, true) : $ride->discount_type;
        $ride->admin_commission_type = is_string($ride->admin_commission_type) ? json_decode($ride->admin_commission_type, true) : $ride->admin_commission_type;
        $ride->tax = is_string($ride->tax) ? json_decode($ride->tax, true) : $ride->tax;
        $ride->stops = is_string($ride->stops) ? json_decode($ride->stops, true) : $ride->stops;
        
        //Trigger ride event for real time data
        $data = $ride->toArray();
        broadcast(new RideUpdatedEvent($data));
        Log::info('RideUpdatedEvent event call', ['ride_id' => $data['id'], 'data' => $data]);

        //Trigger customer ride event for real time data
        $userId = $ride->id_user_app;
        $userRides = $this->getCustomerRides($userId);
        broadcast(new CustomerRidesUpdatedEvent($userId, $userRides));
        Log::info('CustomerRidesUpdatedEvent event call', ['user_id' => $userId, 'userRides' => $userRides]);

        //Trigger driver ride event for real time data
        $driverId = $ride->assigned_driver_id ? $ride->assigned_driver_id : $ride->id_conducteur;
        $driverRides = $this->getDriverRides($driverId);
        broadcast(new DriverRidesUpdatedEvent($driverId, $driverRides));
        Log::info('DriverRidesUpdatedEvent event call', ['driver_id' => $driverId, 'driverRides' => $driverRides]);
        
        if ($ride->statut === 'canceled') {
            DB::table('requete')->where('id', $ride->id)->update(['assigned_driver_id' => null]);
            if($driverId){
                Driver::where('id', $driverId)->update(['driver_on_ride' => 'no']);
            }
        }
    }

    /**
     * Handle the Requests "deleted" event.
     */
    public function deleted(Requests $ride): void
    {
        //
    }

    /**
     * Handle the Requests "restored" event.
     */
    public function restored(Requests $ride): void
    {
        //
    }

    /**
     * Handle the Requests "force deleted" event.
     */
    public function forceDeleted(Requests $ride): void
    {
        //
    }

    public function getDriverRides($driverId, $includeCanceled = true){
        
        if (!$driverId) return []; 

        $ride = Requests::where(function ($query) use ($driverId) {
            $query->where(function ($q) use ($driverId) {
                $q->where('statut', 'new')
                ->where('assigned_driver_id', $driverId);
            })->orWhere(function ($q) use ($driverId) {
                $q->whereIn('statut', ['confirmed', 'on ride', 'completed'])
                ->where('id_conducteur', $driverId);
            });
        });
        
        // If canceled rides should be included, wrap it in an outer OR
        if ($includeCanceled) {
            $ride = $ride->orWhere(function ($q) use ($driverId) {
                $q->where('statut', 'canceled')
                ->where(function ($inner) use ($driverId) {
                    $inner->where('assigned_driver_id', $driverId)
                            ->orWhere('id_conducteur', $driverId);
                });
            });
        }

        $ride = $ride->orderBy('creer', 'desc')->first();

        if ($ride) {

            //Hide unwanted fields from response
            $ride->makeHidden([
                'trip_objective', 'trip_category', 'age_children1', 'age_children2', 'age_children3', 'user_info',
                'place', 'tip_amount', 'trajet', 'date_retour', 'heure_retour', 'statut_round', 'statut_course',
                'transaction_id', 'modifier', 'id_conducteur_accepter', 'car_driver_confirmed',
                'deleted_at', 'updated_at', 'dispatcher_id', 'ownerId', 'rejected_driver_id',
            ]);

            //Set driver & user details with ride response
            if ($ride->id_conducteur) {
                $ride->load(['driver:id,nom,prenom,phone,latitude,longitude,review_sum,review_count,average_rating']);
                if ($ride->driver) {
                    $ride->driver->image = (!empty($ride->driver->photo_path) && file_exists(public_path('assets/images/driver/' . $ride->driver->photo_path)))
                        ? asset('assets/images/driver/' . $ride->driver->photo_path)
                        : asset('assets/images/placeholder_image.jpg');
                    $ride->driver->vehicle_details = Helper::getVehicleDetails($ride->id_conducteur);
                }
            }

            $ride->load(['user:id,nom,prenom,email,phone,photo_path,review_sum,review_count,average_rating']);
            if ($ride->user) {
                $ride->user->image = (!empty($ride->user->photo_path) && file_exists(public_path('assets/images/users/' . $ride->user->photo_path)))
                    ? asset('assets/images/users/' . $ride->user->photo_path)
                    : asset('assets/images/placeholder_image.jpg');
                unset($ride->user->photo_path);
            }

            if ($ride->id_payment_method) {
                $ride->payment_method = PaymentMethod::where('id', $ride->id_payment_method)->value('libelle');
            }

            $ride->discount_type = $ride->discount_type ? json_decode($ride->discount_type, true) : null;
            $ride->admin_commission_type = $ride->admin_commission_type ? json_decode($ride->admin_commission_type, true) : null;
            $ride->tax = $ride->tax ? json_decode($ride->tax, true) : null;
            $ride->stops = $ride->stops ? json_decode($ride->stops, true) : null;

            return $ride->toArray();
        }

        return [];
    }

    public function getCustomerRides($userId){
        
        if (!$userId) return []; 

        $ride = Requests::where('id_user_app', $userId)->whereIn('statut', ['new', 'confirmed', 'on ride', 'canceled', 'completed'])->orderBy('creer', 'desc')->first();

        if ($ride) {

            //Hide unwanted fields from response
            $ride->makeHidden([
                'trip_objective', 'trip_category', 'age_children1', 'age_children2', 'age_children3', 'user_info',
                'place', 'tip_amount', 'trajet', 'date_retour', 'heure_retour', 'statut_round', 'statut_course',
                'transaction_id', 'modifier', 'id_conducteur_accepter', 'car_driver_confirmed',
                'deleted_at', 'updated_at', 'dispatcher_id', 'ownerId', 'rejected_driver_id',
            ]);

            //Set driver & user details with ride response
            if ($ride->id_conducteur) {
                $ride->load(['driver:id,nom,prenom,phone,latitude,longitude,review_sum,review_count,average_rating']);
                if ($ride->driver) {
                    $ride->driver->image = (!empty($ride->driver->photo_path) && file_exists(public_path('assets/images/driver/' . $ride->driver->photo_path)))
                        ? asset('assets/images/driver/' . $ride->driver->photo_path)
                        : asset('assets/images/placeholder_image.jpg');
                    $ride->driver->vehicle_details = Helper::getVehicleDetails($ride->id_conducteur);
                }
            }

            $ride->load(['user:id,nom,prenom,email,phone,photo_path,review_sum,review_count,average_rating']);
            if ($ride->user) {
                $ride->user->image = (!empty($ride->user->photo_path) && file_exists(public_path('assets/images/users/' . $ride->user->photo_path)))
                    ? asset('assets/images/users/' . $ride->user->photo_path)
                    : asset('assets/images/placeholder_image.jpg');
                unset($ride->user->photo_path);
            }

            if ($ride->id_payment_method) {
                $ride->payment_method = PaymentMethod::where('id', $ride->id_payment_method)->value('libelle');
            }

            $ride->discount_type = $ride->discount_type ? json_decode($ride->discount_type, true) : null;
            $ride->admin_commission_type = $ride->admin_commission_type ? json_decode($ride->admin_commission_type, true) : null;
            $ride->tax = $ride->tax ? json_decode($ride->tax, true) : null;
            $ride->stops = $ride->stops ? json_decode($ride->stops, true) : null;
            
            return $ride->toArray();
        }

        return [];
    }
}
