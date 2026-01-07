<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\v1\GcmController;
use App\Models\Requests;
use App\Models\Driver;
use App\Models\UserApp;
use App\Models\Notification;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB;
use Validator;

class RideConfirmController extends Controller
{
    
    public function confirmRequest(Request $request)
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
        
        $rideInfo = Requests::where('id', $id_requete)->where('statut', 'new')->first();
        
        if (!empty($rideInfo)) {
        
            $userId = $rideInfo->id_user_app;
            
            if (!Helper::isDriverBookingAllowed($id_driver, 'subscriptionTotalOrders')) {
                return response()->json([
                    'success' => 'Failed',
                    'error' => 'You have reached the maximum booking limit for the current plan. Upgrade the subscription to continue accepting new bookings.'
                ]);
            }

            $driverData = Driver::where('id', $id_driver)->first();
            $driverData->driver_on_ride = 'yes';
            $driverData->save();
            
            $driver_name = $driverData->prenom . ' ' . $driverData->nom;

            $rideInfo->statut = 'confirmed';
            $rideInfo->id_conducteur = $id_driver;

            if (!empty($driverData->ownerId)) {
                $driverData = Driver::find($driverData->ownerId);
                $rideInfo->admin_commission_type = $driverData->adminCommission ? $driverData->adminCommission : null;
            }else{
                $rideInfo->admin_commission_type = $driverData->adminCommission ? $driverData->adminCommission : null;
            }
            
            $rideInfo->save();
            //Update assigned_driver_id after save due to event fired
            DB::table('requete')->where('id', $rideInfo->id)->update(['assigned_driver_id' => null]);
            
            Log::warning('Driver found. Booking confirmed.', ['booking_id' => $rideInfo->id]);

            //Reset limit
            Helper::resetDriverSubscriptionLimit($id_driver, 'subscriptionTotalOrders', 'dec');
            
            $title = "Confirmation of your ride";
            $msg = $driver_name . " is Confirmed your ride.";
            $message = array("body" => $msg, "title" => $title, "sound" => 'mySound', "tag" => "rideconfirmed");
            $fcm_token = UserApp::where('fcm_id', '!=', '')->where('id', '=', $userId)->value('fcm_id');
            if (!empty($fcm_token)) {
                GcmController::sendNotification($fcm_token, $message);
                Notification::create([
                    'titre' => $title,
                    'message' => $msg,
                    'statut' => 'yes',
                    'creer' => date('Y-m-d H:i:s'),
                    'modifier' => date('Y-m-d H:i:s'),
                    'to_id' => $userId,
                    'from_id' => $id_driver,
                    'type' => 'rideconfirmed',
                ]);
            }
            
            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'Status successfully updated';
            $response['data'] = $rideInfo->toArray();

        } else {
            
            $response['success'] = 'Failed';
            $response['error'] = 'Invalid request for confirm ride';
        }

        return response()->json($response);
    }
}
