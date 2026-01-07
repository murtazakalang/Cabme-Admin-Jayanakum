<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\API\v1\GcmController;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Requests;
use App\Models\Driver;
use App\Models\UserApp;
use App\Models\Settings;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB;
use Validator;

class RideRejectController extends Controller
{
    public function rejectedRequest(Request $request)
    {
        $response = array();
        $validator = Validator::make($request->all(), [
            'id_ride' => 'required|integer|exists:requete,id',
            'id_driver' => 'required|integer|exists:conducteur,id',
        ]);
        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        $id_requete = $request->get('id_ride');
        $id_driver = $request->get('id_driver');
        $reason = $request->get('reason');
        
        $settings = Settings::first();
        $accept_reject_time = $settings->trip_accept_reject_driver_time_sec ? $settings->trip_accept_reject_driver_time_sec : 0;

        $rideInfo = Requests::where('id', $id_requete)->whereNotIn('statut', ['canceled', 'rejected'])->first();

        if (!empty($rideInfo)) {

            $rideStatus = $rideInfo->statut;
            $lat = $rideInfo->latitude_depart;
            $long = $rideInfo->longitude_depart;
            $id_user = $rideInfo->id_user_app;
            $vehicle_type_id = $rideInfo->vehicle_type_id;
            
            // Load rejected drivers
            $rejDriverIds = json_decode($rideInfo->rejected_driver_id ?? '[]', true);
            $rejDriverIds = is_array($rejDriverIds) ? $rejDriverIds : [];
            if ($id_driver) {
                $rejDriverIds[] = $id_driver;
            }
            $rejDriverIds = array_unique($rejDriverIds);

            $driverData = Driver::where('id', $id_driver)->first();
            $driverData->driver_on_ride = 'no';
            $driverData->save();

            $driver_name = $driverData->prenom.' '.$driverData->nom;
            $title = "Rejection of your ride";
            $msg = $driver_name . " is cancelled your ride";
            $message = array("body" => $msg, "reasons" => $reason, "title" => $title, "sound" => "mySound", "tag" => "riderejected");
            $fcm_token = UserApp::where('fcm_id', '!=', '')->where('id', '=', $id_user)->value('fcm_id');
            if (!empty($fcm_token)) {
                /*GcmController::sendNotification($fcm_token, $message);*/
            }
           
            $settings = Settings::first();
            $delivery_distance = $settings->delivery_distance;
            $earthRadius = $delivery_distance == "KM" ? "6371" : "3959";
            $radius = $settings->driver_radios;
            $minimum_wallet_balance = $settings->minimum_deposit_amount ? $settings->minimum_deposit_amount : 0;
            
            $userZoneId = Helper::getUserZoneId($lat, $long);
            if (empty($userZoneId)) {
                $rideInfo->statut = 'canceled';
                $rideInfo->save();
                //Update assigned_driver_id after save due to event fired
                DB::table('requete')->where('id', $rideInfo->id)->update(['assigned_driver_id' => '']);
                Log::warning('No driver found. Booking cancelled.', ['booking_id' => $rideInfo->id]);
                
                $response['success'] = 'success';
                $response['error'] = null;
                $response['message'] = 'Status successfully updated';
                $response['data'] = $rideInfo;
                return response()->json($response);
            }

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
            ->join('zones', function ($join) use ($userZoneId) {
                $join->on(DB::raw('FIND_IN_SET(zones.id, conducteur.zone_id)'), '>', DB::raw('0'))
                    ->where('zones.status', '=', 'yes');
                if (!empty($userZoneId)) {
                    $join->where('zones.id', '=', $userZoneId);
                }
            })
            ->leftJoin('conducteur as owner', 'conducteur.ownerId', '=', 'owner.id')
            ->addBinding([$lat, $long, $lat], 'select')
            ->where('type_vehicule.id', $vehicle_type_id)
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
            ->where('conducteur.id', '!=', $id_driver)
            ->whereNotIn('conducteur.id', $rejDriverIds)
            ->whereNotIn('conducteur.id', function($query) {
                    $query->select('assigned_driver_id')
                        ->from('requete')
                        ->whereNotIn('statut',['canceled', 'completed','rejected'])
                        ->whereNotNull('assigned_driver_id')
                        ->where('assigned_driver_id', '!=', '');
                })
            ->having('distance', '<=', $radius)
            ->orderBy('distance', 'asc')
            ->first(); 
    
            if ($newDriver) {

                if(Helper::isDriverBookingAllowed($newDriver->id,'subscriptionTotalOrders')){

                    // Reassign new driver
                    $rideInfo->assigned_driver_id = $newDriver->id;
                    $rideInfo->rejected_driver_id = json_encode($rejDriverIds);
                    $rideInfo->save();

                    Notification::create([
                        'titre' => $title,
                        'message' => $msg,
                        'statut' => 'yes',
                        'creer' => date('Y-m-d H:i:s'),
                        'modifier' => date('Y-m-d H:i:s'),
                        'to_id' => $id_user,
                        'from_id' => $id_driver,
                        'type' => 'riderejected',
                    ]);
                    
                    //send notification to driver
                    $fcm_token = $newDriver->fcm_id;
                    if (!empty($fcm_token)) {
                        $message = array("body" => 'New ride', "title" => 'You have just received a request from a client', "sound" => "mySound", "tag" => "ridenewrider");
                        GcmController::sendNotification($fcm_token, $message);
                    }
                    
                }else{

                    // Reassign new driver
                    $rejDriverIds[] = $newDriver->id;
                    $rejDriverIds = array_unique($rejDriverIds);
                    $rideInfo->rejected_driver_id = json_encode($rejDriverIds);
                    $rideInfo->save();

                    // Re-dispatch job to retry after timeout
                    AssignDriverJob::dispatch($rideInfo)->delay(now()->addSeconds($accept_reject_time));

                    Log::info('AssignDriverJob triggered from reject request API and fetched driver is not able to get order due to limit', 
                        ['booking_id' => $rideInfo->id, 'rejected_driver_id' => $newDriver->id]
                    );
                }

            }else{
                
                $rideInfo->statut = 'canceled';
                $rideInfo->save();
                //Update assigned_driver_id after save due to event fired
                DB::table('requete')->where('id', $rideInfo->id)->update(['assigned_driver_id' => null]);
                Log::warning('No driver found. Booking cancelled.', ['booking_id' => $rideInfo->id]);
            }

            if ($rideStatus == 'confirmed') {
                //Reset limit
                Helper::resetDriverSubscriptionLimit($id_driver,'subscriptionTotalOrders','inc');
            }

            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'Status successfully updated';
            $response['data'] = $rideInfo;

        } else {
            $response['success'] = 'Failed';
            $response['error'] = 'Invalid request for cancel ride';
        }

        return response()->json($response);
    }

    public function cancelledRequest(Request $request){

        $response = array();
        $validator = Validator::make($request->all(), [
            'id_ride' => 'required|integer|exists:requete,id',
            'id_user' => 'required|integer|exists:user_app,id',
        ]);
        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        $id_requete = $request->get('id_ride');
        $id_user = $request->get('id_user');
        $reason = $request->get('reason');

        $rideInfo = Requests::where('id', $id_requete)->first();
        //$rideInfo = Requests::where('id', $id_requete)->whereNotIn('statut', ['canceled', 'rejected'])->first();
        
        if (!empty($rideInfo)) {
            
            $message = array("body" => 'Customer has cancelled the ride', "reasons" => $reason, "title" => 'Rejection of your ride', "sound" => "mySound", "tag" => "riderejected");
            $fcm_token = Driver::where('fcm_id', '!=', '')->where('id', '=', $id_user)->value('fcm_id');
            if (!empty($fcm_token)) {
                GcmController::sendNotification($fcm_token, $message);
            }
           
            $id_driver = $rideInfo->assigned_driver_id ? $rideInfo->assigned_driver_id : $rideInfo->id_conducteur;
            if($id_driver){
                Driver::where('id', $id_driver)->update(['driver_on_ride' => 'no']);

                //Reset limit when customer cancel ride only if status is confirmed
                if($rideInfo->statut == "confirmed"){
                    Helper::resetDriverSubscriptionLimit($id_driver, 'subscriptionTotalOrders', 'inc');
                }
            }
            
            $rideInfo->statut = 'canceled';
            $rideInfo->save();
            //Update assigned_driver_id after save due to event fired
            DB::table('requete')->where('id', $rideInfo->id)->update(['assigned_driver_id' => null]);
            
            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'Status successfully updated';
            $response['data'] = $rideInfo;

        }else{
            $response['success'] = 'Failed';
            $response['error'] = 'Invalid request for cancel ride';
        }

        return response()->json($response);
    }
}
