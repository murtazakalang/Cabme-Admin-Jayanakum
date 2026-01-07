<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\RentalOrder;
use App\Models\Zone;
use App\Models\Settings;
use App\Models\RentalPackage;
use App\Models\Vehicle;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Validator;
use DB;

class RentalOrdersController extends Controller
{
    
    public function getData(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'driver_id'     => 'required|integer|exists:conducteur,id',
        ]);

        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        $driver_id = $request->get('driver_id');
        $settings = Settings::first();
        $driver = Driver::find($driver_id);

        $owner = '';
        $isFleet = false;
        if(!empty($driver->ownerId) && $driver->ownerId != null){
            $isFleet = true;
            $owner = Driver::find($driver->ownerId);
        }
        
        if($isFleet && $settings->owner_doc_verification == "yes" && $owner->is_verified == "0"){
            $response['success'] = 'Failed';
            $response['error'] = 'Your document is not verified.';
            $response['message'] = null;
            return response()->json($response);
        }

        if (!$isFleet && $settings->driver_doc_verification == "yes" && $driver->is_verified == "0") {
            $response['success'] = 'Failed';
            $response['error'] = 'Your document is not verified.';
            $response['message'] = null;
            return response()->json($response);
        }

        $vehicle = Vehicle::where('id_conducteur',$driver_id)->first();
        if(!$vehicle){
            $response['success'] = 'Failed';
            $response['error'] = 'Vehicle not assigned.';
            $response['message'] = null;
            return response()->json($response);
        }

        $delivery_distance = $settings->delivery_distance;
        $earthRadius = $delivery_distance == "KM" ? "6371" : "3959";
        $radius = $settings->driver_radios;
        $minimum_wallet_balance = $settings->minimum_deposit_amount ? $settings->minimum_deposit_amount : 0;

        $driverWallet = !empty($driver->amount) && $driver->amount != null && $driver->amount != '' ? $driver->amount : 0;
        if(!$isFleet && $driverWallet < $minimum_wallet_balance){
            $response['success'] = 'Failed';
            $response['error'] = 'Minimum '.$minimum_wallet_balance.' wallet balance is required';
            $response['message'] = null;
            return response()->json($response);
        }
        
        $source_lat = $driver->latitude;
        $source_lng = $driver->longitude;
        
        $rentalOrders = RentalOrder::join('payment_method', 'payment_method.id', '=', 'rental_orders.id_payment_method')
        ->join('user_app', 'user_app.id', '=', 'rental_orders.id_user_app')
        ->leftjoin('type_vehicule', 'type_vehicule.id', '=', 'rental_orders.id_vehicle_type')
        ->select(
            'rental_orders.*',
            'payment_method.libelle as payment_method',
            'user_app.nom',
            'user_app.prenom',
            'user_app.phone as user_phone',
            'user_app.photo_path as user_photo',
            'type_vehicule.libelle as vehicle_name',
            'type_vehicule.image as vehicle_image',
        )
        ->when(!empty($source_lat) && !empty($source_lng), function ($query) use ($source_lat, $source_lng, $earthRadius, $radius) {
            $query->selectRaw("(
                    $earthRadius * acos(
                        cos(radians(?)) * cos(radians(lat_source)) *
                        cos(radians(lng_source) - radians(?)) +
                        sin(radians(?)) * sin(radians(lat_source))
                    )
                ) AS source_distance", [$source_lat, $source_lng, $source_lat])
                ->having('source_distance', '<=', $radius);
        })
        ->where('rental_orders.status', 'new')
        ->where('rental_orders.id_vehicle_type', $vehicle->id_type_vehicule)
        ->orderBy('rental_orders.created_at', 'desc')
        ->get();
        
        if (!$rentalOrders->isEmpty()) {

            //Remove driver which rejected rental order
            $rentalOrders = $rentalOrders->filter(function ($order) use ($driver_id) {
                $ids = json_decode($order->rejected_driver_id, true);
                return !is_array($ids) || !in_array((string)$driver_id, $ids);
            })->values(); 

            // Check driver zone before assign order
            $driverZoneIds = $driver->zone_id != null ? explode(',',$driver->zone_id) : [];
            $rentalOrders = $rentalOrders->filter(function ($order) use ($driverZoneIds) {
                $userZoneId = Helper::getUserZoneId($order->lat_source, $order->lng_source);
                return $userZoneId !== null && in_array($userZoneId, $driverZoneIds);
            })->values();

            $rentalOrders->map(function ($rentalOrder) {

                $rentalOrder->user_name = $rentalOrder->prenom . " " . $rentalOrder->nom;
                if ($rentalOrder->id_conducteur) {
                    $rentalOrder->load(['driver:id,nom,prenom,phone,latitude,longitude,review_sum,review_count,average_rating']);
                    if ($rentalOrder->driver) {
                        $rentalOrder->driver->image = (!empty($rentalOrder->driver->photo_path) && file_exists(public_path('assets/images/driver/' . $rentalOrder->driver->photo_path)))
                            ? asset('assets/images/driver/' . $rentalOrder->driver->photo_path)
                            : asset('assets/images/placeholder_image.jpg');
                        $rentalOrder->driver->vehicle_details = Helper::getVehicleDetails($rentalOrder->id_conducteur);
                    }
                }

                $rentalOrder->load(['user:id,nom,prenom,email,phone,photo_path,review_sum,review_count,average_rating']);
                if ($rentalOrder->user) {
                    $rentalOrder->user->image = (!empty($rentalOrder->user->photo_path) && file_exists(public_path('assets/images/users/' . $rentalOrder->user->photo_path)))
                        ? asset('assets/images/users/' . $rentalOrder->user->photo_path)
                        : asset('assets/images/placeholder_image.jpg');
                    unset($rentalOrder->user->photo_path);
                }
                
                if ($rentalOrder->vehicle_image != '' && file_exists(public_path('assets/images/type_vehicle/'.'/'.$rentalOrder->vehicle_image))) {
                    $rentalOrder->vehicle_image = asset('assets/images/type_vehicle/') . '/' . $rentalOrder->vehicle_image;
                }else{
                    $rentalOrder->vehicle_image = asset('assets/images/placeholder_image.jpg');
                } 
                
                $rentalOrder->package_details = RentalPackage::find($rentalOrder->id_rental_package);
                $rentalOrder->tax = json_decode($rentalOrder->tax, true);
                $rentalOrder->admin_commission_type = json_decode($rentalOrder->admin_commission_type,true);
                $rentalOrder->discount_type = json_decode($rentalOrder->discount_type, true);

                return $rentalOrder;
            });

            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'Rental orders successfully fetched';
            $response['data'] = $rentalOrders;

        } else {
            $response['success'] = 'Failed';
            $response['error'] = 'No Data Found';
            $response['message'] = null;
        }
        
        return response()->json($response);
    }

    public function getRecentData(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'driver_id'     => 'required|integer|exists:conducteur,id',
        ]);

        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        $driver_id = $request->get('driver_id');
        $driver = Driver::find($driver_id);
        
        $settings = Settings::select('delivery_distance','driver_radios')->first();
        $delivery_distance = $settings->delivery_distance;
        $earthRadius = $delivery_distance == "KM" ? "6371" : "3959";
        $radius = $settings->driver_radios;

        $source_lat = $driver->latitude;
        $source_lng = $driver->longitude;

        $rentalOrders = RentalOrder::join('payment_method', 'payment_method.id', '=', 'rental_orders.id_payment_method')
        ->join('user_app', 'user_app.id', '=', 'rental_orders.id_user_app')
        ->leftjoin('type_vehicule', 'type_vehicule.id', '=', 'rental_orders.id_vehicle_type')
        ->select(
            'rental_orders.*',
            'payment_method.libelle as payment_method',
            'user_app.nom',
            'user_app.prenom',
            'user_app.phone as user_phone',
            'user_app.photo_path as user_photo',
            'type_vehicule.libelle as vehicle_name',
            'type_vehicule.image as vehicle_image',
        )
        ->when(!empty($source_lat) && !empty($source_lng), function ($query) use ($source_lat, $source_lng, $earthRadius, $radius) {
            $query->selectRaw("(
                    $earthRadius * acos(
                        cos(radians(?)) * cos(radians(lat_source)) *
                        cos(radians(lng_source) - radians(?)) +
                        sin(radians(?)) * sin(radians(lat_source))
                    )
                ) AS source_distance", [$source_lat, $source_lng, $source_lat])
                ->having('source_distance', '<=', $radius);
        })
        ->whereIn('rental_orders.status', ['confirmed','on ride'])
        ->where(function ($query) use ($driver_id) {
            $query->whereNull('rental_orders.id_conducteur')
                ->orWhere('rental_orders.id_conducteur', $driver_id);
         })
        ->orderBy('rental_orders.created_at', 'desc')
        ->get();
        
        if (!$rentalOrders->isEmpty()) {

            //Remove driver which rejected rental order
            $rentalOrders = $rentalOrders->filter(function ($order) use ($driver_id) {
                $ids = json_decode($order->rejected_driver_id, true);
                return !is_array($ids) || !in_array((string)$driver_id, $ids);
            })->values(); 

            $rentalOrders->map(function ($rentalOrder) {

                $rentalOrder->user_name = $rentalOrder->prenom . " " . $rentalOrder->nom;
                if ($rentalOrder->id_conducteur) {
                    $rentalOrder->load(['driver:id,nom,prenom,phone,latitude,longitude,review_sum,review_count,average_rating']);
                    if ($rentalOrder->driver) {
                        $rentalOrder->driver->image = (!empty($rentalOrder->driver->photo_path) && file_exists(public_path('assets/images/driver/' . $rentalOrder->driver->photo_path)))
                            ? asset('assets/images/driver/' . $rentalOrder->driver->photo_path)
                            : asset('assets/images/placeholder_image.jpg');
                        $rentalOrder->driver->vehicle_details = Helper::getVehicleDetails($rentalOrder->id_conducteur);
                    }
                }

                $rentalOrder->load(['user:id,nom,prenom,email,phone,photo_path,review_sum,review_count,average_rating']);
                if ($rentalOrder->user) {
                    $rentalOrder->user->image = (!empty($rentalOrder->user->photo_path) && file_exists(public_path('assets/images/users/' . $rentalOrder->user->photo_path)))
                        ? asset('assets/images/users/' . $rentalOrder->user->photo_path)
                        : asset('assets/images/placeholder_image.jpg');
                    unset($rentalOrder->user->photo_path);
                }
                
                if ($rentalOrder->vehicle_image != '' && file_exists(public_path('assets/images/type_vehicle/'.'/'.$rentalOrder->vehicle_image))) {
                    $rentalOrder->vehicle_image = asset('assets/images/type_vehicle/') . '/' . $rentalOrder->vehicle_image;
                }else{
                    $rentalOrder->vehicle_image = asset('assets/images/placeholder_image.jpg');
                } 
                
                $rentalOrder->package_details = RentalPackage::find($rentalOrder->id_rental_package);
                $rentalOrder->tax = json_decode($rentalOrder->tax, true);
                $rentalOrder->admin_commission_type = json_decode($rentalOrder->admin_commission_type,true);
                $rentalOrder->discount_type = json_decode($rentalOrder->discount_type, true);

                return $rentalOrder;
            });

            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'rental orders successfully fetched';
            $response['data'] = $rentalOrders;

        } else {
            $response['success'] = 'Failed';
            $response['error'] = 'No Data Found';
            $response['message'] = null;
        }
        
        return response()->json($response);
    }

}
