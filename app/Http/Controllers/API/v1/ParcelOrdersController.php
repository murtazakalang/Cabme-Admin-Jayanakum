<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\ParcelOrder;
use App\Models\UserApp;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use DB;
use Validator;

class ParcelOrdersController extends Controller
{
    
    public function getDriverParcel(Request $request)
    {

        $response = array();
        $validator = Validator::make($request->all(), [
            'id_driver' => 'required|integer|exists:conducteur,id',
        ]);
        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }
        $id_driver = $request->get('id_driver');

        $parcels = ParcelOrder::join('payment_method', 'payment_method.id', '=', 'parcel_orders.id_payment_method')
            ->join('parcel_category', 'parcel_category.id', '=', 'parcel_orders.parcel_type')
            ->select('parcel_orders.*', 'payment_method.libelle as payment_method', 'parcel_category.title as parcel_type', 'parcel_category.image as parcel_type_image')
            ->where('parcel_orders.id_conducteur', $id_driver)
            ->whereIn('parcel_orders.status', ['confirmed', 'on ride'])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = [];
        if ($parcels->isNotEmpty()){
            $parcels->map(function ($parcel) {
                $item = $this->getItemFormat($parcel);
                return $item;
            });
            $data = $parcels->toArray();
        }

        if($data){
            $response['success'] = 'success';
            $response['code'] = 200;
            $response['message'] = 'Booking data found successfully';
            $response['data'] = $data;
        }else{
            $response['success'] = 'Failed';
            $response['code'] = 200;
            $response['message'] = 'No data found';
            $response['data'] = null;
        }
        
        return response()->json($response);
    }

    public function getItemFormat($item){

        if($item->id_conducteur){
            $item->load(['driver:id,nom,prenom,phone,latitude,longitude,review_sum,review_count,average_rating']);
            if ($item->driver) {
                if (!empty($item->driver->photo_path) && file_exists(public_path('assets/images/driver'.'/'.$item->driver->photo_path))) {
                    $item->driver->image = asset('assets/images/driver').'/'.$item->driver->photo_path;
                }else{
                    $item->driver->image = asset('assets/images/placeholder_image.jpg');
                }
                $item->driver->vehicle_details = Helper::getVehicleDetails($item->id_conducteur);
            }
            $item->load(['user:id,nom,prenom,email,phone,photo_path,review_sum,review_count,average_rating']);
            if ($item->user) {
                if (!empty($item->user->photo_path) && file_exists(public_path('assets/images/users'.'/'.$item->user->photo_path))) {
                    $item->user->image = asset('assets/images/users').'/'.$item->user->photo_path;
                }else{
                    $item->user->image = asset('assets/images/placeholder_image.jpg');
                }
            }
        }

        $parcel_images = [];    
        if($item->parcel_image){
            $images = json_decode($item->parcel_image, true);
            foreach($images as $image){
                $parcel_images[] = asset('images/parcel_order/'.$image); 
            }
        }
        $item->parcel_image = $parcel_images;

        if ($item->parcel_type_image != '' && file_exists(public_path('assets/images/parcel_category/'.'/'.$item->parcel_type_image))) {
            $item->parcel_type_image = asset('assets/images/parcel_category/') . '/' . $item->parcel_type_image;
        }else{
            $item->parcel_type_image = asset('assets/images/placeholder_image.jpg');
        }
        
        $item->discount_type = $item->discount_type ? json_decode($item->discount_type, true) : null;
        $item->admin_commission_type = $item->admin_commission_type ? json_decode($item->admin_commission_type, true) : null;
        $item->tax = $item->tax ? json_decode($item->tax, true) : null;
        
        return $item;
    }

    public function getUserParcel(Request $request)
    {

        $response = array();
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|integer|exists:user_app,id',
        ]);
        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }
        $id_user = $request->get('id_user');

        $parcels = ParcelOrder::join('payment_method', 'payment_method.id', '=', 'parcel_orders.id_payment_method')
            ->join('parcel_category', 'parcel_category.id', '=', 'parcel_orders.parcel_type')
            ->select('parcel_orders.*', 'payment_method.libelle as payment_method', 'parcel_category.title as parcel_type', 'parcel_category.image as parcel_type_image')
            ->where('parcel_orders.id_user_app', $id_user)
            ->orderBy('created_at', 'desc')
            ->get();

        $data = [];
        if ($parcels->isNotEmpty()){
            $parcels->map(function ($parcel) {
                $item = $this->getItemFormat($parcel);
                return $item;
            });
            $data = $parcels->toArray();
        }

        if($data){
            $response['success'] = 'success';
            $response['code'] = 200;
            $response['message'] = 'Booking data found successfully';
            $response['data'] = $data;
        }else{
            $response['success'] = 'Failed';
            $response['code'] = 200;
            $response['message'] = 'No data found';
            $response['data'] = null;
        }
        
        return response()->json($response);
    }

    public function getParcelDetail(Request $request)
    {
        $response = array();
        $validator = Validator::make($request->all(), [
            'id_parcel' => 'required|integer|exists:parcel_orders,id',
        ]);
        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        $id_parcel = $request->get('id_parcel');

        $parcelOrder = ParcelOrder::join('payment_method', 'payment_method.id', '=', 'parcel_orders.id_payment_method')
            ->join('parcel_category', 'parcel_category.id', '=', 'parcel_orders.parcel_type')
            ->select('parcel_orders.*', 'payment_method.libelle as payment_method', 'parcel_category.title as parcel_type', 'parcel_category.image as parcel_type_image')
            ->where('parcel_orders.id', $id_parcel)
            ->first();

        if ($parcelOrder) {

            //Set driver & user details with ride response
            if ($parcelOrder->id_conducteur) {
                $parcelOrder->load(['driver:id,nom,prenom,phone,latitude,longitude,review_sum,review_count,average_rating']);
                if ($parcelOrder->driver) {
                    $parcelOrder->driver->image = (!empty($parcelOrder->driver->photo_path) && file_exists(public_path('assets/images/driver/' . $parcelOrder->driver->photo_path)))
                        ? asset('assets/images/driver/' . $parcelOrder->driver->photo_path)
                        : asset('assets/images/placeholder_image.jpg');
                    $parcelOrder->driver->vehicle_details = Helper::getVehicleDetails($parcelOrder->id_conducteur);
                }
            }

            $parcelOrder->load(['user:id,nom,prenom,email,phone,photo_path,review_sum,review_count,average_rating']);
            if ($parcelOrder->user) {
                $parcelOrder->user->image = (!empty($parcelOrder->user->photo_path) && file_exists(public_path('assets/images/users/' . $parcelOrder->user->photo_path)))
                    ? asset('assets/images/users/' . $parcelOrder->user->photo_path)
                    : asset('assets/images/placeholder_image.jpg');
                unset($parcelOrder->user->photo_path);
            }

            $parcel_images = [];    
            if($parcelOrder->parcel_image){
                $images = json_decode($parcelOrder->parcel_image, true);
                foreach($images as $image){
                    $parcel_images[] = asset('images/parcel_order/'.$image); 
                }
            }
            $parcelOrder->parcel_image = $parcel_images;

            if ($parcelOrder->parcel_type_image != '' && file_exists(public_path('assets/images/parcel_category/'.'/'.$parcelOrder->parcel_type_image))) {
                $parcelOrder->parcel_type_image = asset('assets/images/parcel_category/') . '/' . $parcelOrder->parcel_type_image;
            }else{
                $parcelOrder->parcel_type_image = asset('assets/images/placeholder_image.jpg');
            }    
            
            $parcelOrder->discount_type = $parcelOrder->discount_type ? json_decode($parcelOrder->discount_type, true) : null;
            $parcelOrder->admin_commission_type = $parcelOrder->admin_commission_type ? json_decode($parcelOrder->admin_commission_type, true) : null;
            $parcelOrder->tax = $parcelOrder->tax ? json_decode($parcelOrder->tax, true) : null;
            
            $response['success'] = 'success';
            $response['code'] = 200;
            $response['message'] = 'Booking data found successfully';
            $response['data'] = $parcelOrder->toArray();
            return response()->json($response);

        }else{

            $response['success'] = 'Failed';
            $response['code'] = 200;
            $response['message'] = 'No data found';
            $response['data'] = null;
            return response()->json($response);
        }
        
    }
}
