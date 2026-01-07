<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\API\v1\GcmController;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\UserApp;
use App\Models\Notification;
use App\Models\RentalOrder;
use App\Models\RentalPackage;
use App\Models\Settings;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Validator;

class RentalOnRideController extends Controller
{
    public function onrideRequest(Request $request)
    {
        $response = array();
        $validator = Validator::make($request->all(), [
            'id_rental' => 'required|integer|exists:rental_orders,id',
            'id_driver' => 'required|integer|exists:conducteur,id',
            'otp'       => 'nullable|integer',
            'current_km' => 'required',
        ]);
        
        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        $id_rental = $request->get('id_rental');
        $id_driver = $request->get('id_driver');
        $current_km = $request->get('current_km');
        $otp = $request->get('otp');

        if (!RentalOrder::where('id', $id_rental)->where('status', 'confirmed')->exists()) {
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
            $conditions = RentalOrder::where('id', $id_rental)->where('status', 'confirmed')->where('otp', $otp)->exists();
        } else {
            $conditions = RentalOrder::where('id', $id_rental)->where('status', 'confirmed')->exists();
        }
        if (!$conditions) {
            $response['success'] = 'Failed';
            $response['code'] = 200;
            $response['message'] = 'OTP is invalid.';
            $response['data'] = null;
            return response()->json($response);
        }
        
        $rentalOrder = RentalOrder::find($id_rental);
        $driverData = Driver::find($id_driver);
        
        $rentalOrder->status = 'on ride';
        $rentalOrder->current_km = $current_km;
        $rentalOrder->save();

        $id_user = $rentalOrder->id_user_app;
        $driver_name = $driverData->prenom . ' ' . $driverData->nom;
        $title = "Delivering of your Rental";
        $msg = $driver_name. " is started to deliver your rental.";
        $message = array("body" => $msg, "title" => $title, "sound" => 'mySound', "tag" => "rentalonride");
        $fcm_token = UserApp::where('fcm_id', '!=','')->where('id','=',$id_user)->value('fcm_id');
        if (! empty($fcm_token)) {
            GcmController::sendNotification($fcm_token, $message);
            Notification::create([
                'titre' => $title,
                'message' => $msg,
                'statut' => 'yes',
                'creer' => date('Y-m-d H:i:s'),
                'modifier' => date('Y-m-d H:i:s'),
                'to_id' => $id_user,
                'from_id' => $id_driver,
                'type' => 'rentalonride',
            ]);
        }

        $response['success'] = 'success';
        $response['error'] = null;
        $response['message'] = 'Status successfully updated';
        $response['data'] = null;

        return response()->json($response);
        
    }
}
