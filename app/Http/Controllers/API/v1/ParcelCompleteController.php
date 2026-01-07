<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\API\v1\GcmController;
use App\Http\Controllers\Controller;
use App\Models\ParcelOrder;
use App\Models\Notification;
use App\Models\Driver;
use App\Models\UserApp;
use App\Models\Referral;
use App\Models\Settings;
use App\Models\Commission;
use App\Models\Transaction;
use App\Models\Currency;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Validator;

class ParcelCompleteController extends Controller
{
    public function completeRequest(Request $request)
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
        if(in_array($parcelOrder->status,['new','completed'])){
            $response['success'] = 'failed';
            $response['code'] = 404;
            $response['message'] = 'Invalid request';
            $response['data'] = null;
            return response()->json($response);
        }

        $id_user = $parcelOrder->id_user_app;
        $transaction_id = $parcelOrder->transaction_id;
        $id_payment = $parcelOrder->id_payment_method;

        $userData = UserApp::find($id_user);
        $paymentData = PaymentMethod::find($id_payment);
        $settings = Settings::first();
        
        //Get subtotal
        $sub_total = $parcelOrder->amount;
        $totalAmount = $sub_total;

        //Calculate discount
        $discount = $parcelOrder->discount;
        if ($discount > 0) {
            $totalAmount = floatval($sub_total) - floatval($discount);
        }
        
        //Calculate admin commission
        $admin_commisions = Commission::where('statut', 'yes')->first();
        $commission_amount = 0;
        if (!empty($admin_commisions)) {
            $adminCommission = json_decode($parcelOrder->admin_commission_type, true);
            if ($adminCommission['type'] == 'Percentage') {
                $commission_amount = ((floatval($adminCommission['value']) * floatval($totalAmount)) / 100);
            } else {
                $commission_amount = floatval($adminCommission['value']);
            }
        }
        
        $currencyData = Currency::where('statut','yes')->first();
        $currency = $currencyData->symbole ? $currencyData->symbole : '$';

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

        //Get driver based on ownership
        $ownerId = '';
        $driverData = Driver::find($id_driver);
        if (!empty($driverData->ownerId)) {
            $ownerId = $driverData->ownerId;
            $driverData = Driver::find($driverData->ownerId);
        }
        $driverWallet = 0;
        if ($driverData->amount != '' && $driverData->amount != null) {
            $driverWallet = $driverData->amount;
        }

        //If payment is cash
        if($id_payment === "5"){

            $driverWallet = floatval($driverWallet) - floatval($commission_amount);

            //Driver - Add transaction for Debit admin commission
            if (!empty($commission_amount)) {
                Transaction::create([
                    'user_id' => $ownerId ? $ownerId : $id_driver,
                    'user_type' => 'driver',
                    'payment_method' => $paymentData->libelle,
                    'amount' => $commission_amount,
                    'is_credited' => '0',
                    'booking_id' => $id_parcel,
                    'booking_type' => 'parcel',
                    'note' => 'Admin commission debited',
                    'transaction_id' => $transaction_id,
                ]);
            }

        }else{

            //Add ride amount in driver wallet
            $driverWallet = floatval($driverWallet) + floatval($totalAmount);
            
            //Driver - Add transaction for ride amount credit if payment is online
            Transaction::create([
                'user_id' => $ownerId ? $ownerId : $id_driver,
                'user_type' => 'driver',
                'payment_method' => $paymentData->libelle,
                'amount' => $totalAmount,
                'is_credited' => '1',
                'booking_id' => $id_parcel,
                'booking_type' => 'parcel',
                'note' => 'Parcel amount credited',
                'transaction_id' => $transaction_id,
            ]);

            //Deduct admin comission from driver wallet
            $driverWallet = floatval($driverWallet) - floatval($commission_amount);

            //Driver - Add transaction for debit admin commission if payment is online
            Transaction::create([
                'user_id' => $ownerId ? $ownerId : $id_driver,
                'user_type' => 'driver',
                'payment_method' => $paymentData->libelle,
                'amount' => $commission_amount,
                'is_credited' => '0',
                'booking_id' => $id_parcel,
                'booking_type' => 'parcel',
                'note' => 'Admin commission debited',
                'transaction_id' => $transaction_id,
            ]);
        }

        //Save driver wallet balance based on payment method
        $driverData->amount = $driverWallet;
        $driverData->save();
        
        //Update ride data
        $parcelOrder->admin_commission = $commission_amount;
        $parcelOrder->status = 'completed';
        $parcelOrder->save();

        // Add referral amount in referral by
        $referral = Referral::where('user_id', $id_user)->where('code_used', 'false')->first();
        if ($referral && $referral->referral_by_id) {
            $userWalletAmount = UserApp::where('id', $referral->referral_by_id)->value('amount') ?? 0;
            // Add referral amount
            $newWalletAmount = $userWalletAmount + floatval($settings->referral_amount);
            // Update wallet
            UserApp::where('id', $referral->referral_by_id)->update([
                'amount' => $newWalletAmount,
            ]);
            // Create transaction history
            Transaction::create([
                'user_id' => $referral->referral_by_id,
                'user_type' => 'customer',
                'payment_method' => 'Referral',
                'amount' => $settings->referral_amount,
                'is_credited' => '1', 
                'booking_id' => $id_parcel,
                'booking_type' => 'parcel',
                'note' => 'Referral amount credited',
                'transaction_id' => 'REF_'.$id_parcel.'_'.$id_user,
            ]);
            // Mark referral code as used
            Referral::where('user_id', $id_user)->update([
                'code_used' => 'true',
            ]);
        }

        //Send notification
        $driver_name = $driverData->prenom." " .$driverData->nom;
        $title = "Parcel Delivered";
        $msg = $driver_name . " is delivered your parcel";
        $message = array("body" => $msg, "title" => $title, "sound" => "mySound", "tag" => "parcelcompleted");
        $fcm_token = UserApp::where('fcm_id', '!=', '')->where('id', '=', $id_user)->value('fcm_id');
        if (!empty($fcm_token)) {
            GcmController::sendNotification($fcm_token, $message);
            Notification::create([
                'titre' => $title,
                'message' => $msg,
                'statut' => 'yes',
                'creer' => date('Y-m-d H:i:s'),
                'modifier' => date('Y-m-d H:i:s'),
                'to_id' => $id_user,
                'from_id' => $id_driver,
                'type' => 'parcelcompleted',
            ]);
        }

        $response['success'] = 'success';
        $response['code'] = 200;
        $response['message'] = 'Parcel successfully completed';
        $response['data'] = null;

        return response()->json($response);
    }
}
