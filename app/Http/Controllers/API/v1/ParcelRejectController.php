<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\API\v1\GcmController;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\ParcelOrder;
use App\Models\Driver;
use App\Models\UserApp;
use App\Models\Settings;
use App\Models\Currency;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB;
use Validator;

class ParcelRejectController extends Controller
{
    public function cancelRequest(Request $request){

        $response = array();
        $validator = Validator::make($request->all(), [
            'id_parcel' => 'required|integer|exists:parcel_orders,id',
            'id_user' => 'required|integer|exists:user_app,id',
        ]);
        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        $id_parcel = $request->get('id_parcel');
        $id_user = $request->get('id_user');
        $reason = $request->get('reason');

        $parcelOrder = ParcelOrder::where('id', $id_parcel)->first();
        //$parcelOrder = ParcelOrder::where('id', $id_requete)->whereNotIn('status', ['canceled', 'rejected'])->first();
        
        if (!empty($parcelOrder)) {
            
            $message = array("body" => 'Customer has cancelled the ride', "reasons" => $reason, "title" => 'Rejection of your ride', "sound" => "mySound", "tag" => "riderejected");
            $fcm_token = Driver::where('fcm_id', '!=', '')->where('id', '=', $id_user)->value('fcm_id');
            if (!empty($fcm_token)) {
                GcmController::sendNotification($fcm_token, $message);
            }
           
            $id_driver = $parcelOrder->assigned_driver_id ? $parcelOrder->assigned_driver_id : $parcelOrder->id_conducteur;
            if($id_driver){
                Driver::where('id', $id_driver)->update(['driver_on_ride' => 'no']);

                //Reset limit when customer cancel ride only if status is confirmed
                if($parcelOrder->status == "confirmed"){
                    Helper::resetDriverSubscriptionLimit($id_driver, 'subscriptionTotalOrders', 'inc');
                }
            }

            $parcelOrder->assigned_driver_id = null;
            $parcelOrder->status = 'canceled';
            $parcelOrder->save();

            //Start - Add refund in user wallet
            //Get subtotal
            $sub_total = $parcelOrder->amount;
            $totalAmount = $sub_total;

            //Calculate discount
            $discount_type = json_decode($parcelOrder->discount_type, true);
            if($discount_type){
                if($discount_type['type'] == "Percentage"){
                    $discount = ((floatval($discount_type['value']) * floatval($totalAmount)) / 100);
                }else{
                    $discount = $discount_type['value'];
                }
            }else{
                $discount = 0;
            }
            if ($discount > 0) {
                $totalAmount = floatval($sub_total) - floatval($discount);
            }
            
            $currencyData = Currency::where('statut','yes')->first();
            $paymentData = PaymentMethod::find($parcelOrder->id_payment_method);

            //Calculate tax
            $totalTaxAmount = 0;
            $tax = json_decode($parcelOrder->tax, true);
            if (!empty($tax)) {
                for ($i = 0; $i < sizeof($tax); $i++) {
                    $data = $tax[$i];
                    if ($data['type'] == "Percentage") {
                        $taxValue = (floatval($data['value']) * $totalAmount) / 100;
                    } else {
                        $taxValue = floatval($data['value']);
                    }
                    $totalTaxAmount += floatval(number_format($taxValue, $currencyData->decimal_digit));
                }
                $totalAmount = floatval($totalAmount) + $totalTaxAmount;
            }

            $userWalletAmount = UserApp::where('id', $id_user)->value('amount');
            if ($userWalletAmount != '' && $userWalletAmount != null) {
                UserApp::where('id', $id_user)->update([
                    'amount' => $userWalletAmount + $totalAmount,
                ]);
            }
            
            Transaction::create([
                'user_id' => $id_user,
                'user_type' => 'customer',
                'payment_method' => $paymentData->libelle,
                'amount' => $totalAmount,
                'is_credited' => '1',
                'booking_id' => $parcelOrder->id,
                'booking_type' => 'parcel',
                'note' => 'Parcel cancel amount credited',
                'transaction_id' => 'REFUND_'.$parcelOrder->id,
            ]);
            //End - Add refund in user wallet
            
            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'Status successfully updated';
            $response['data'] = $parcelOrder;

        }else{
            $response['success'] = 'Failed';
            $response['error'] = 'Invalid request for cancel parcel';
        }

        return response()->json($response);
    }
}
