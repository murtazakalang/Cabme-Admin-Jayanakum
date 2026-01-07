<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\API\v1\GcmController;
use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\UserApp;
use App\Models\Notification;
use App\Models\ParcelOrder;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Validator;

class ParcelOnRideController extends Controller
{
    public function onrideRequest(Request $request)
    {
        $response = array();
        $validator = Validator::make($request->all(), [
            'id_parcel' => 'required|integer|exists:parcel_orders,id',
            'id_driver' => 'required|integer|exists:conducteur,id',
        ]);
        
        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        $id_parcel = $request->get('id_parcel');
        $id_driver = $request->get('id_driver');
        
        $parcelOrder = ParcelOrder::find($id_parcel);
        $driverData = Driver::find($id_driver);
        
        /*if (!empty($driverData->ownerId)) {
            $driverData = Driver::find($driverData->ownerId);
        }*/
        
        $parcelOrder->status = 'on ride';
        $parcelOrder->save();

        $id_user = $parcelOrder->id_user_app;
        $driver_name = $driverData->prenom . ' ' . $driverData->nom;
        $title = "Delivering of your Parcel";
        $msg = $driver_name. " is started to deliver your parcel.";
        $message = array("body" => $msg, "title" => $title, "sound" => 'mySound', "tag" => "parcelonride");
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
                'type' => 'parcelonride',
            ]);
        }

        //Get latest data
        $parcelOrder = ParcelOrder::join('payment_method', 'payment_method.id', '=', 'parcel_orders.id_payment_method')
            ->join('parcel_category', 'parcel_category.id', '=', 'parcel_orders.parcel_type')
            ->select('parcel_orders.*', 'payment_method.libelle as payment_method', 'parcel_category.title as parcel_type', 'parcel_category.image as parcel_type_image')
            ->where('parcel_orders.id', $id_parcel)
            ->first();

        if ($parcelOrder->id_conducteur) {
            $parcelOrder->load(['driver:id,nom,prenom,phone,latitude,longitude']);
            if ($parcelOrder->driver) {
                $parcelOrder->driver->image = (!empty($parcelOrder->driver->photo_path) && file_exists(public_path('assets/images/driver/' . $parcelOrder->driver->photo_path)))
                    ? asset('assets/images/driver/' . $parcelOrder->driver->photo_path)
                    : asset('assets/images/placeholder_image.jpg');
                $parcelOrder->driver->vehicle_details = Helper::getVehicleDetails($parcelOrder->id_conducteur);
            }
        }
        $parcelOrder->load(['user:id,nom,prenom,email,phone,photo_path']);
        if ($parcelOrder->user) {
            $parcelOrder->user->image = (!empty($parcelOrder->user->photo_path) && file_exists(public_path('assets/images/users/' . $parcelOrder->user->photo_path)))
                ? asset('assets/images/users/' . $parcelOrder->user->photo_path)
                : asset('assets/images/placeholder_image.jpg');
            unset($parcelOrder->user->photo_path);
        }

        if (!empty($parcelOrder->parcel_image)) {
            $parcelImage = json_decode($parcelOrder->parcel_image, true);
            $image_user = [];
            foreach ($parcelImage as $value) {
                $path = public_path("images/parcel_order/$value");
                if (file_exists($path)) {
                    $image_user[] = asset("images/parcel_order/$value");
                }
            }
            $parcelOrder->parcel_image = !empty($image_user) ? $image_user : asset('assets/images/placeholder_image.jpg');
        }

        $parcelOrder->admin_commission_type = json_decode($parcelOrder->admin_commission_type,true);
        $parcelOrder->tax = json_decode($parcelOrder->tax, true);
        $parcelOrder->discount_type = json_decode($parcelOrder->discount_type, true);

        $response['success'] = 'success';
        $response['error'] = null;
        $response['message'] = 'Status successfully updated';
        $response['data'] = $parcelOrder->toArray();

        return response()->json($response);
        
    }
}
