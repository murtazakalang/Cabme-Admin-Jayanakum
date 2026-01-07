<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\v1\GcmController;
use App\Models\Driver;
use App\Models\UserApp;
use App\Models\Requests;
use App\Models\Notification;
use App\Models\Settings;
use Illuminate\Http\Request;
use DB;
use Validator;

class RideOnRideController extends Controller
{
    
    public function confirmRide(Request $request)
    {

        $response = array();
        $validator = Validator::make($request->all(), [
            'id_ride' => 'required|integer',
            'id_driver' => 'required|integer',
            'otp' => 'nullable|integer',
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
        $otp = $request->get('otp');
        
        if (!Requests::where('id', $id_requete)->where('statut', 'confirmed')->exists()) {
            $response['success'] = 'Failed';
            $response['code'] = 200;
            $response['message'] = 'Ride is already active or not confirmed yet.';
            $response['data'] = null;
            return response()->json($response);
        }

        $settings = Settings::first();
        if ($settings->show_ride_otp == "yes" && empty($otp)) {
            return response()->json([
                'success' => 'Failed',
                'code' => 200,
                'message' => 'OTP is required.',
                'data' => null
            ]);
        }
        if ($settings->show_ride_otp == "yes") {
            $conditions = Requests::where('id', $id_requete)->where('statut', 'confirmed')->where('otp', $otp)->exists();
        } else {
            $conditions = Requests::where('id', $id_requete)->where('statut', 'confirmed')->exists();
        }
        if (!$conditions) {
            $response['success'] = 'Failed';
            $response['code'] = 200;
            $response['message'] = 'OTP is invalid.';
            $response['data'] = null;
            return response()->json($response);
        }

        $rideInfo = Requests::where('id', $id_requete)->where('statut', 'confirmed')->first();
        $driverData = Driver::where('id', $id_driver)->first();
        
        $rideInfo->statut = 'on ride';
        $rideInfo->save();

        $id_user = $rideInfo->id_user_app;
        $fcm_token = UserApp::where('fcm_id', '!=', '')->where('id', '=', $id_user)->value('fcm_id');
        
        if (!empty($fcm_token)) {
        
            $driver_name = $driverData->prenom . ' ' . $driverData->nom;
            $title = "Beginning of your ride";
            $msg = $driver_name . " is started your ride.";
            $message = array("body" => $msg, "title" => $title, "sound" => "mySound", "tag" => "rideonride");
            GcmController::sendNotification($fcm_token, $message);

            Notification::create([
                'titre' => $title,
                'message' => $msg,
                'statut' => 'yes',
                'creer' => date('Y-m-d H:i:s'),
                'modifier' => date('Y-m-d H:i:s'),
                'to_id' => $id_user,
                'from_id' => $id_driver,
                'type' => 'rideconfirmed',
            ]); 
        }

        $response['success'] = 'success';
        $response['code'] = 200;
        $response['message'] = 'Status update successfully';
        $response['data'] = null;

        return response()->json($response);
    }
}
