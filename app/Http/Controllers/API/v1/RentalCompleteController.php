<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\API\v1\GcmController;
use App\Http\Controllers\Controller;
use App\Models\RentalOrder;
use App\Models\RentalPackage;
use App\Models\Notification;
use App\Models\Driver;
use App\Models\UserApp;
use App\Models\Referral;
use App\Models\Settings;
use App\Models\Commission;
use App\Models\Transaction;
use App\Models\Currency;
use App\Models\PaymentMethod;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Helpers\Helper;
use Validator;
use Carbon\Carbon;

class RentalCompleteController extends Controller
{

    public function setFinalKmRequest(Request $request)
    {

        $response = array();
        $validator = Validator::make($request->all(), [
            'id_rental' => 'required|integer|exists:rental_orders,id',
            'complete_km' => 'required|integer',
        ]);
        
        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        $now = Carbon::now();
        $end_date = $now->toDateString();
        $end_time = $now->format('H:i');

        $id_rental = $request->get('id_rental');
        $complete_km = $request->get('complete_km');
        
        $rentalOrder = RentalOrder::find($id_rental);

        //Calculate Extra Price
        $packageData = RentalPackage::find($rentalOrder->id_rental_package);
        $includedHours = $packageData->includedHours;
        $includedDistance = $packageData->includedDistance;
        $extraKmFare = $packageData->extraKmFare;
        $extraMinuteFare = $packageData->extraMinuteFare;

        //KM calculation
        $sub_total = $rentalOrder->amount;
        $current_km = $rentalOrder->current_km;
        $complete_km = $complete_km;
        $final_km = floatval($complete_km) - floatval($current_km);

        //Extra Km charge
        $extraKmCharge = 0;
        if ($final_km > $includedDistance) {
            $extraKm = $final_km - $includedDistance;
            $extraKmCharge = $extraKm * $extraKmFare;
        }

        //Extra minute calculation
        $startDateTime = Carbon::parse($rentalOrder->start_date . ' ' . $rentalOrder->start_time);
        $endDateTime = Carbon::parse($end_date . ' ' . $end_time);

        $totalDurationMinutes = $endDateTime->diffInMinutes($startDateTime);
        $includedMinutes = $includedHours * 60;

        //Extra Minute Charge
        $extraMinuteCharge = 0;
        if ($totalDurationMinutes > $includedMinutes) {
            $extraMinutes = $totalDurationMinutes - $includedMinutes;
            $extraMinuteCharge = $extraMinutes * $extraMinuteFare;
        }
        
        //Final amount
        $totalAmount = $sub_total + $extraKmCharge + $extraMinuteCharge;

        $rentalOrder->complete_km = $complete_km;
        $rentalOrder->end_date = $end_date; 
        $rentalOrder->end_time = $end_time;
        $rentalOrder->amount = $totalAmount;
        $rentalOrder->save();

        // Add referral amount in referral by
        $id_user = $rentalOrder->id_user_app;
        $settings = Settings::first();
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
                'booking_id' => $id_rental,
                'booking_type' => 'rental',
                'note' => 'Referral amount credited',
                'transaction_id' => 'REF_'.$id_rental.'_'.$id_user,
            ]);
            // Mark referral code as used
            Referral::where('user_id', $id_user)->update([
                'code_used' => 'true',
            ]);
        }

        $rental = RentalOrder::join('payment_method', 'payment_method.id', '=', 'rental_orders.id_payment_method')
            ->leftjoin('type_vehicule', 'type_vehicule.id', '=', 'rental_orders.id_vehicle_type')
            ->select('rental_orders.*', 'payment_method.libelle as payment_method','type_vehicule.libelle as vehicle_name','type_vehicule.image as vehicle_image')
            ->where('rental_orders.id', $id_rental)
            ->first();

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
        $response['message'] = 'Data saved successfully';
        $response['data'] = $rental->toArray();
        
        return response()->json($response);
    }

    public function completeRequest(Request $request)
    {
        $response = array();
        $validator = Validator::make($request->all(), [
            'id_rental' => 'required|integer|exists:rental_orders,id',
            'id_user' => 'required|integer|exists:user_app,id',
            'id_driver' => 'required|integer|exists:conducteur,id',
            'id_payment' => 'required|integer|exists:payment_method,id',
            'transaction_id' => 'required',
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
        $id_driver = $request->get('id_driver');
        $id_payment = $request->get('id_payment');
        $transaction_id = $request->get('transaction_id');
        
        $rentalOrder = RentalOrder::find($id_rental);
        if(in_array($rentalOrder->status,['new','completed'])){
            $response['success'] = 'failed';
            $response['code'] = 404;
            $response['message'] = 'Invalid request';
            $response['data'] = null;
            return response()->json($response);
        }

        try {
             $currencyData = Currency::where('statut','yes')->first();
            $currency = $currencyData->symbole ? $currencyData->symbole : '$';
            $userData = UserApp::find($id_user);
            $paymentData = PaymentMethod::find($id_payment);
            $settings = Settings::first();
            
            //Get subtotal
            $sub_total = $rentalOrder->amount;
            $totalAmount = $sub_total;

            //Calculate discount
            $discount_type = json_decode($rentalOrder->discount_type, true);
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
            
            //Calculate admin commission
            $admin_commisions = Commission::where('statut', 'yes')->first();
            $commission_amount = 0;
            if (!empty($admin_commisions)) {
                $adminCommission = json_decode($rentalOrder->admin_commission_type, true);
                if ($adminCommission['type'] == 'Percentage') {
                    $commission_amount = ((floatval($adminCommission['value']) * floatval($totalAmount)) / 100);
                } else {
                    $commission_amount = floatval($adminCommission['value']);
                }
            }            
           

            //Calculate tax
            $totalTaxAmount = 0;
            $taxHtml = '';
            $tax = json_decode($rentalOrder->tax, true);
            if (!empty($tax)) {
                for ($i = 0; $i < sizeof($tax); $i++) {
                    $data = $tax[$i];
                    if ($data['type'] == "Percentage") {
                        $taxValue = (floatval($data['value']) * $totalAmount) / 100;
                        $taxlabel = $data['libelle'];
                        $value = $data['value'] . "%";
                    } else {
                        $taxValue = floatval($data['value']);
                        $taxlabel = $data['libelle'];
                        if ($currencyData->symbol_at_right == "true") {
                            $value = number_format($data['value'], $currencyData->decimal_digit) . "" . $currency;
                        } else {
                            $value = $currency . "" . number_format($data['value'], $currencyData->decimal_digit);
                        }
                    }
                    $totalTaxAmount += $taxValue;
                    if ($currencyData->symbol_at_right == "true") {
                        $taxValueAmount = number_format($taxValue, $currencyData->decimal_digit) . "" . $currency;
                    } else {
                        $taxValueAmount = $currency . "" . number_format($taxValue, $currencyData->decimal_digit);
                    }
                    $taxHtml = $taxHtml . "<p><b>" . $taxlabel . "(" . $value . "): </b>" . $taxValueAmount . "</p>";
                }
                $totalAmount = floatval($totalAmount) + $totalTaxAmount;
            }

            if ($taxHtml == '') {
                $taxHtml = $taxHtml . "0";
            }

            //If payment is wallet
            if($id_payment == "9"){
                $userWalletAmount = UserApp::where('id', $id_user)->value('amount');
                if ($userWalletAmount != '' && $userWalletAmount != null) {
                    $userData->amount = $userWalletAmount - $totalAmount;
                    $userData->save();
                }
                Transaction::create([
                    'user_id' => $id_user,
                    'user_type' => 'customer',
                    'payment_method' => $paymentData->libelle,
                    'amount' => $totalAmount,
                    'is_credited' => '0',
                    'booking_id' => $id_rental,
                    'booking_type' => 'rental',
                    'note' => 'Rental amount debited',
                    'transaction_id' => $transaction_id,
                ]);
            }

            //Get driver based on ownership
            $ownerId = '';
            
            $driverData = Driver::find($id_driver);
            $maindriverData = $driverData;
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
                        'booking_id' => $id_rental,
                        'booking_type' => 'rental',
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
                    'booking_id' => $id_rental,
                    'booking_type' => 'rental',
                    'note' => 'Rental amount credited',
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
                    'booking_id' => $id_rental,
                    'booking_type' => 'rental',
                    'note' => 'Admin commission debited',
                    'transaction_id' => $transaction_id,
                ]);
            }

            //Save driver wallet balance based on payment method
            $driverData->amount = $driverWallet;
            $driverData->save();
            
            //Save main driver which can individual driver or owner's driver
            $maindriverData->driver_on_ride = 'no';
            $maindriverData->save();
            
            //Update ride data
            $rentalOrder->payment_status = "yes";
            $rentalOrder->id_payment_method = $id_payment;
            $rentalOrder->discount = $discount;
            $rentalOrder->transaction_id = $transaction_id;
            $rentalOrder->admin_commission = $commission_amount;
            $rentalOrder->status = 'completed';
            $rentalOrder->save();
            
            //Send notification
            $message = array("body" => "Your customer has just paid for his rental ride", "title" => "Payment of the rental ride", "sound" => "mySound", "tag" => "rentalcompleted");
            $fcm_token = $driverData->fcm_id;
            if (!empty($fcm_token)) {
                GcmController::sendNotification($fcm_token, $message);
                Notification::create([
                    'titre' => 'End of your rental ride',
                    'message' => $maindriverData->prenom." " .$maindriverData->nom . " is completed your rental ride.",
                    'statut' => 'yes',
                    'creer' => date('Y-m-d H:i:s'),
                    'modifier' => date('Y-m-d H:i:s'),
                    'to_id' => $id_user,
                    'from_id' => $id_driver,
                    'type' => 'rentalcompleted',
                ]);
            }

            //Send email
            if ($userData->email != "") {
                $emailsubject = '';
                $emailmessage = '';
                $emailtemplate = EmailTemplate::where('type', 'rental_payment_receipt')->first();
                if (!empty($emailtemplate)) {
                    $emailsubject = $emailtemplate->subject;
                    $emailmessage = $emailtemplate->message;
                }
                
                $contact_us_email = Settings::select('contact_us_email')->value('contact_us_email');
                $admin_email = $contact_us_email ? $contact_us_email : 'none@none.com';
                $app_name = env('APP_NAME', 'Cabme');
                
                $subtotal = !empty($sub_total) ? number_format($sub_total, $currencyData->decimal_digit) : 0;
                        $subtotal = $currencyData->symbol_at_right == "true" 
                            ? $subtotal . $currency 
                            : $currency . $subtotal;

                $to = $userData->email;
                $tip_amount = !empty($tip) ? number_format($tip, 2) : 0;
                $tip_amount = $currencyData->symbol_at_right == "true" ? $tip_amount.$currency : $currency.$tip_amount;
                $total = !empty($totalAmount) ? number_format($totalAmount, $currencyData->decimal_digit) : 0;
                        $total = $currencyData->symbol_at_right == "true" 
                            ? $total . $currency 
                            : $currency . $total;

                $date = date('Y-m-d H:i:s');

                $discount = !empty($discount) ? number_format($discount, $currencyData->decimal_digit) : 0;
                $discount = $currencyData->symbol_at_right == "true" ? $discount . $currency : $currency . $discount;
                
                $emailsubject = str_replace("{AppName}", $app_name, $emailsubject);
                $emailmessage = str_replace("{AppName}", $app_name, $emailmessage);
                $emailmessage = str_replace("{UserName}", $userData->prenom . " " . $userData->nom, $emailmessage);
                $emailmessage = str_replace('{Subtotal}', $subtotal, $emailmessage);
                $emailmessage = str_replace('{Discount}', $discount, $emailmessage);
                $emailmessage = str_replace('{Tax}', $taxHtml, $emailmessage);
                $emailmessage = str_replace('{Total}', $total, $emailmessage);
                $emailmessage = str_replace('{Date}', $date, $emailmessage);
                
                Mail::html($emailmessage, function ($message) use ($to, $admin_email, $emailsubject, $emailtemplate) {
                    $message->to($to)->subject($emailsubject);
                    if ($emailtemplate->send_to_admin) {
                        $message->cc($admin_email);
                    }
                });
            }

            $response['success'] = 'success';
            $response['code'] = 200;
            $response['message'] = 'Rental ride successfully completed';
            $response['data'] = null;

         } catch (Exception $e) {
            
            Log::error('Complete Rental API: Failed: ' . $e->getMessage());

            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $e->getMessage();
            $response['data'] = null;
        }
    
        return response()->json($response);
    }
}
