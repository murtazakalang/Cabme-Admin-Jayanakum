<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\API\v1\GcmController;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\RentalOrder;
use App\Models\RentalPackage;
use App\Models\Driver;
use App\Models\UserApp;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB;
use Validator;

class RentalRejectController extends Controller
{
    public function rejectRequest(Request $request)
    {
        $response = array();
        $validator = Validator::make($request->all(), [
            'id_rental' => 'required|integer|exists:rental_orders,id',
            'id_driver' => 'required|integer|exists:conducteur,id',
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
        $reason = $request->get('reason') ? $request->get('reason') : '';
        
        $rentalOrder = RentalOrder::join('payment_method', 'payment_method.id', '=', 'rental_orders.id_payment_method')
            ->leftjoin('type_vehicule', 'type_vehicule.id', '=', 'rental_orders.id_vehicle_type')
            ->select('rental_orders.*', 'payment_method.libelle as payment_method', 'type_vehicule.libelle as vehicle_name', 'type_vehicule.image as vehicle_image')
            ->where('rental_orders.id', $id_rental)
            ->whereNotIn('rental_orders.status', ['canceled', 'rejected'])
            ->first();

        if (!empty($rentalOrder)) {

            // Load rejected drivers
            $rejDriverIds = json_decode($rentalOrder->rejected_driver_id ?? '[]', true);
            $rejDriverIds = is_array($rejDriverIds) ? $rejDriverIds : [];
            $rejDriverIds[] = $id_driver;
            $rejDriverIds = array_unique($rejDriverIds);
            
            $rentalOrder->rejected_driver_id = json_encode($rejDriverIds);
            $rentalOrder->save();

            /*$driverData = Driver::where('id', $id_driver)->first();
            $driver_name = $driverData->prenom.' '.$driverData->nom;
            $id_user = $rentalOrder->id_user_app;

            $title = "Rejection of your rental";
            $msg = $driver_name . " is rejected your rental.";
            $message = array("body" => $msg, "reasons" => $reason, "title" => $title, "sound" => "mySound", "tag" => "rentalriderejected");
            $fcm_token = UserApp::where('fcm_id', '!=', '')->where('id', '=', $id_user)->value('fcm_id');
            if (!empty($fcm_token)) {
                GcmController::sendNotification($fcm_token, $message);
            }*/
            
            //Set driver & user details with ride response
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
            $rentalOrder->discount_type = $rentalOrder->discount_type ? json_decode($rentalOrder->discount_type, true) : null;
            $rentalOrder->admin_commission_type = $rentalOrder->admin_commission_type ? json_decode($rentalOrder->admin_commission_type, true) : null;
            $rentalOrder->tax = $rentalOrder->tax ? json_decode($rentalOrder->tax, true) : null;
            
            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'Status successfully updated';
            $response['data'] = $rentalOrder->toArray();

        } else {
            $response['success'] = 'Failed';
            $response['error'] = 'Invalid request for cancel rental';
        }

        return response()->json($response);
    }

    public function cancelRequest(Request $request){

        $response = array();
        $validator = Validator::make($request->all(), [
            'id_rental' => 'required|integer|exists:rental_orders,id',
            'id_user' => 'required|integer|exists:user_app,id',
        ]);
        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        $id_rental = $request->get('id_rental');
        $id_user = $request->get('id_user');
        $reason = $request->get('reason') ? $request->get('reason') : '';

        $rentalOrder = RentalOrder::where('id', $id_rental)->first();
        
        if (!empty($rentalOrder)) {
            
            $message = array("body" => 'Customer has cancelled the ride', "reasons" => $reason, "title" => 'Rejection of your ride', "sound" => "mySound", "tag" => "rentalriderejected");
            $fcm_token = Driver::where('fcm_id', '!=', '')->where('id', '=', $id_user)->value('fcm_id');
            if (!empty($fcm_token)) {
                GcmController::sendNotification($fcm_token, $message);
            }
           
            $id_driver = $rentalOrder->id_conducteur;
            if($id_driver){
                Driver::where('id', $id_driver)->update(['driver_on_ride' => 'no']);

                //Reset limit when customer cancel ride only if status is confirmed
                if($rentalOrder->status == "confirmed"){
                    Helper::resetDriverSubscriptionLimit($id_driver, 'subscriptionTotalOrders', 'inc');
                }
            }

            $rentalOrder->status = 'canceled';
            $rentalOrder->save();
            
            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'Status successfully updated';
            $response['data'] = $rentalOrder;

        }else{
            $response['success'] = 'Failed';
            $response['error'] = 'Invalid request for cancel rental';
        }

        return response()->json($response);
    }
}
