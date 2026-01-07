<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\API\v1\GcmController;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\RentalOrder;
use App\Models\Driver;
use App\Models\UserApp;
use App\Models\RentalPackage;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;

class RentalConfirmController extends Controller
{
    public function confirmRequest(Request $request)
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
        
        if (!Helper::isDriverBookingAllowed($id_driver, 'subscriptionTotalOrders')) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'You have reached the maximum booking limit for the current plan. Upgrade the subscription to continue accepting new bookings.'
            ]);
        }

        $driverData = Driver::find($id_driver);
        
        $rentalOrder = RentalOrder::where('id', $id_rental)->where('status', 'new')->first();

        if (!empty($rentalOrder)){
        
            $driverData->driver_on_ride = 'yes';
            $driverData->save();

            $driver_name = $driverData->prenom . ' ' . $driverData->nom;

            $now = Carbon::now();
            $rentalOrder->status = 'confirmed';
            $rentalOrder->id_conducteur = $id_driver;
            $rentalOrder->ownerId =  $driverData->ownerId;
            $rentalOrder->start_date = $now->toDateString(); 
            $rentalOrder->start_time = $now->format('H:i');
            $rentalOrder->otp = random_int(100000, 999999);

            if (!empty($driverData->ownerId)) {
                $driverData = Driver::find($driverData->ownerId);
                $rentalOrder->admin_commission_type =  $driverData->adminCommission ? $driverData->adminCommission : null;
            }else{
                $rentalOrder->admin_commission_type =  $driverData->adminCommission ? $driverData->adminCommission : null;
            }
            
            $rentalOrder->save();

            //Reset limit
            Helper::resetDriverSubscriptionLimit($id_driver, 'subscriptionTotalOrders', 'dec');

            $id_user = $rentalOrder->id_user_app;
            $title = "Confirmation of your rental order";
            $msg = $driver_name. " is Confirmed your rental order.";
            $message = array("body" => $msg, "title" => $title, "sound" => 'mySound', "tag" => "rentalconfirmed");
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
                    'type' => 'rentalconfirmed',
                ]);
            }

            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'Status successfully updated';
            $response['data'] = $rentalOrder->toArray();

            return response()->json($response);

        } else {

            $response['success'] = 'Failed';
            $response['error'] = 'Invalid request for confirm ride';
            return response()->json($response);
        }
    }
}
