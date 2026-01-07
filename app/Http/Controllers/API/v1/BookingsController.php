<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\API\v1\GcmController;
use App\Http\Controllers\Controller;
use App\Models\Requests;
use App\Models\ParcelOrder;
use App\Models\RentalOrder;
use App\Models\Settings;
use App\Models\Commission;
use App\Models\UserApp;
use App\Models\Driver;
use App\Models\Tax;
use App\Models\Vehicle;
use App\Models\PaymentMethod;
use App\Models\Note;
use App\Models\UserNote;
use App\Models\DeliveryCharges;
use App\Models\Complaints;
use App\Models\RentalPackage;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Carbon\Carbon;
use Validator;

class BookingsController extends Controller
{

    public function getUserRecentRide(Request $request){

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
        
        $userId = $request->get('id_user');

        $ride = Requests::where('id_user_app', $userId)->whereIn('statut', ['new', 'confirmed', 'on ride'])->orderBy('creer', 'desc')->first();

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

            $response['success'] = 'success';
            $response['code'] = 200;
            $response['message'] = 'Booking data found successfully';
            $response['data'] = $ride->toArray();
            return response()->json($response);

        }else{

            $response['success'] = 'Failed';
            $response['code'] = 200;
            $response['message'] = 'No data found';
            $response['data'] = null;
            return response()->json($response);
        }
    }

    public function getDriverRecentRide(Request $request){
        
        $response = array();
        $validator = Validator::make($request->all(), [
            'id_driver' => 'required|integer|exists:conducteur,id',
         ]);
        if($validator->fails()){
            $response['success'] = 'failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        $driverId = $request->get('id_driver');

        $ride = Requests::where(function ($query) use ($driverId) {
            $query->where(function ($q) use ($driverId) {
                $q->where('statut', 'new')
                ->where('assigned_driver_id', $driverId);
            })->orWhere(function ($q) use ($driverId) {
                $q->whereIn('statut', ['confirmed', 'on ride'])
                ->where('id_conducteur', $driverId);
            });
        });
        
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

            $response['success'] = 'success';
            $response['code'] = 200;
            $response['message'] = 'Booking data found successfully';
            $response['data'] = $ride->toArray();
            return response()->json($response);
        }

        $response['success'] = 'Failed';
        $response['code'] = 200;
        $response['message'] = 'No data found';
        $response['data'] = null;
        return response()->json($response);
    }
    
    public function getItemFormat($item, $type){

        if($type == "ride"){
            $item->makeHidden([
                'trip_objective', 'trip_category', 'age_children1', 'age_children2', 'age_children3', 'user_info',
                'place', 'tip_amount', 'trajet', 'date_retour', 'heure_retour', 'statut_round','statut_course','statut_course',
                'transaction_id', 'modifier', 'id_conducteur_accepter', 'car_driver_confirmed', 'deleted_at',
                'updated_at', 'dispatcher_id', 'ownerId', 'rejected_driver_id',
            ]);
        }
        
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
        }

         $item->load(['user:id,nom,prenom,email,phone,photo_path,review_sum,review_count,average_rating']);
        if ($item->user) {
            if (!empty($item->user->photo_path) && file_exists(public_path('assets/images/users'.'/'.$item->user->photo_path))) {
                $item->user->image = asset('assets/images/users').'/'.$item->user->photo_path;
            }else{
                $item->user->image = asset('assets/images/placeholder_image.jpg');
            }
        }

        if($type == "parcel"){
            
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
        }
        
        $item->discount_type = $item->discount_type ? json_decode($item->discount_type, true) : null;
        $item->admin_commission_type = $item->admin_commission_type ? json_decode($item->admin_commission_type, true) : null;
        $item->tax = $item->tax ? json_decode($item->tax, true) : null;
        
        $item->complaint = false;
        $complaint = Complaints::where('booking_id',$item->id)->where('booking_type',$type)->first();
        if($complaint){
            $item->complaint = true;
            $item->complaint_detail = $complaint->toArray();
        }
        
        if($type == "ride"){
            $item->stops = $item->stops ? json_decode($item->stops, true) : null;
        }
        
        return $item;
    }

    public function getBookingList(Request $request){

        $response = array();
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'user_type' => 'required|in:customer,driver',
            'booking_type' => 'required|in:ride,parcel,rental',
         ]);

        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        $user_id = $request->get('user_id');
        $booking_type = $request->get('booking_type');
        $user_type = $request->get('user_type');
        $user_field = $user_type == "customer" ? "id_user_app" : "id_conducteur";

        $userIds = [$user_id];
        //If driver is owner then get all drivers of owner
        if ($user_type === "driver") {
            $driverData = Driver::find($user_id);
            if (!empty($driverData) && $driverData->isOwner == 'true') {
                $userIds = Driver::where('ownerId', $driverData->id)->orWhere('id', $driverData->id)->pluck('id')->toArray();
            }
        }
        
        $data = [];
        
        if($booking_type == "ride"){
            
            $rides = Requests::join('payment_method', 'payment_method.id', '=', 'requete.id_payment_method')
            ->select('requete.*', 'payment_method.libelle as payment_method')
            ->whereIn($user_field, $userIds)
            ->orderBy('creer', 'desc')
            ->get();
            if ($rides->isNotEmpty()){
                $rides->map(function ($ride) {
                    $item = $this->getItemFormat($ride, 'ride');
                    return $item;
                });
                $data = $rides->toArray();
            }

        }else if($booking_type == "parcel"){
            
            $parcels = ParcelOrder::join('payment_method', 'payment_method.id', '=', 'parcel_orders.id_payment_method')
            ->join('parcel_category', 'parcel_category.id', '=', 'parcel_orders.parcel_type')
            ->select('parcel_orders.*', 'payment_method.libelle as payment_method', 'parcel_category.title as parcel_type', 'parcel_category.image as parcel_type_image')
            ->whereIn($user_field, $userIds)
            ->orderBy('created_at', 'desc')
            ->get();

            if ($parcels->isNotEmpty()){
                $parcels->map(function ($parcel) {
                    $item = $this->getItemFormat($parcel, 'parcel');
                    return $item;
                });
                $data = $parcels->toArray();
            }
        }else if($booking_type == "rental"){
            
            $rentals = RentalOrder::join('payment_method', 'payment_method.id', '=', 'rental_orders.id_payment_method')
            ->leftjoin('type_vehicule', 'type_vehicule.id', '=', 'rental_orders.id_vehicle_type')
            ->select('rental_orders.*', 'payment_method.libelle as payment_method','type_vehicule.libelle as vehicle_name','type_vehicule.image as vehicle_image')
            ->whereIn($user_field, $userIds)
            ->orderBy('created_at', 'desc')
            ->get();

            if ($rentals->isNotEmpty()){
                $rentals->map(function ($rental) {
                    $item = $this->getItemFormat($rental, 'rental');
                    if ($rental->vehicle_image != '' && file_exists(public_path('assets/images/type_vehicle/'.'/'.$rental->vehicle_image))) {
                        $rental->vehicle_image = asset('assets/images/type_vehicle/') . '/' . $rental->vehicle_image;
                    }else{
                        $rental->vehicle_image = asset('assets/images/placeholder_image.jpg');
                    } 
                    $rental->package_details = RentalPackage::find($rental->id_rental_package);
                    return $item;
                });
                $data = $rentals->toArray();
            }
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
    
    public function getBookingDetails(Request $request){

        $response = array();
        $validator = Validator::make($request->all(), [
            'id_ride' => 'required|integer|exists:requete,id',
         ]);

        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }
        
        $ride = Requests::find($request->get('id_ride'));

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

            $response['success'] = 'success';
            $response['code'] = 200;
            $response['message'] = 'Booking data found successfully';
            $response['data'] = $ride->toArray();
            return response()->json($response);
        }

        $response['success'] = 'Failed';
        $response['code'] = 200;
        $response['message'] = 'No data found';
        $response['data'] = null;
        return response()->json($response);
    }

    public function getRentalBookingDetails(Request $request){

        $response = array();
        $validator = Validator::make($request->all(), [
            'id_rental' => 'required|integer|exists:rental_orders,id',
         ]);

        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        $id_rental = $request->id_rental;
        
        $rental = RentalOrder::join('payment_method', 'payment_method.id', '=', 'rental_orders.id_payment_method')
            ->leftjoin('type_vehicule', 'type_vehicule.id', '=', 'rental_orders.id_vehicle_type')
            ->select('rental_orders.*', 'payment_method.libelle as payment_method','type_vehicule.libelle as vehicle_name','type_vehicule.image as vehicle_image')
            ->where('rental_orders.id', $id_rental)
            ->first();

        if ($rental) {

            if ($rental->id_conducteur) {
                $rental->load(['driver:id,nom,prenom,phone,latitude,longitude,review_sum,review_count,average_rating']);
                if ($rental->driver) {
                    $rental->driver->image = (!empty($rental->driver->photo_path) && file_exists(public_path('assets/images/driver/' . $rental->driver->photo_path)))
                        ? asset('assets/images/driver/' . $rental->driver->photo_path)
                        : asset('assets/images/placeholder_image.jpg');
                    $rental->driver->vehicle_details = Helper::getVehicleDetails($rental->id_conducteur);
                }
            }

            $rental->load(['user:id,nom,prenom,email,phone,photo_path,review_sum,review_count,average_rating']);
            if ($rental->user) {
                $rental->user->image = (!empty($rental->user->photo_path) && file_exists(public_path('assets/images/users/' . $rental->user->photo_path)))
                    ? asset('assets/images/users/' . $rental->user->photo_path)
                    : asset('assets/images/placeholder_image.jpg');
                unset($rental->user->photo_path);
            }

            if ($rental->id_payment_method) {
                $rental->payment_method = PaymentMethod::where('id', $rental->id_payment_method)->value('libelle');
            }

            if ($rental->vehicle_image != '' && file_exists(public_path('assets/images/type_vehicle/'.'/'.$rental->vehicle_image))) {
                $rental->vehicle_image = asset('assets/images/type_vehicle/') . '/' . $rental->vehicle_image;
            }else{
                $rental->vehicle_image = asset('assets/images/placeholder_image.jpg');
            } 
            $rental->package_details = RentalPackage::find($rental->id_rental_package);
            
            $rental->discount_type = $rental->discount_type ? json_decode($rental->discount_type, true) : null;
            $rental->admin_commission_type = $rental->admin_commission_type ? json_decode($rental->admin_commission_type, true) : null;
            $rental->tax = $rental->tax ? json_decode($rental->tax, true) : null;
            
            $response['success'] = 'success';
            $response['code'] = 200;
            $response['message'] = 'Booking data found successfully';
            $response['data'] = $rental->toArray();
            return response()->json($response);
        }

        $response['success'] = 'Failed';
        $response['code'] = 200;
        $response['message'] = 'No data found';
        $response['data'] = null;
        return response()->json($response);
    }
}
