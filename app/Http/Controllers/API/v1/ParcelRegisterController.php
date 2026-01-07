<?php

namespace App\Http\Controllers\api\v1;

use App\Models\ParcelOrder;
use App\Models\Tax;
use App\Models\Coupon;
use App\Models\Driver;
use App\Models\UserApp;
use App\Models\Settings;
use App\Models\Commission;
use App\Models\Transaction;
use App\Models\Currency;
use App\Models\PaymentMethod;
use App\Models\EmailTemplate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Helpers\Helper;
use Validator;

class ParcelRegisterController extends Controller
{
    
    public function register(Request $request)
    {
        $response = array();
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|integer|exists:user_app,id',
            'lat_source' => 'required',
            'lng_source' => 'required',
            'lat_destination' => 'required',
            'lng_destination' => 'required',
            'distance' => 'required',
            'distance_unit' => 'required',
            'id_payment' => 'required|integer|exists:payment_method,id',
            'source_adrs' => 'required',
            'destination_adrs' => 'required',
            'sender_name' => 'required',
            'receiver_name' => 'required',
            'sender_phone' => 'required',
            'receiver_phone' => 'required',
            'parcel_weight' => 'required',
            'parcel_dimension' => 'required',
            'parcel_type' => 'required|integer|exists:parcel_category,id',
            'parcel_date' => 'required',
            'parcel_time' => 'required',
            'receive_date' => 'required',
            'receive_time' => 'required',
            'amount' => 'required',
            'transaction_id' => 'required',
        ]);

        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        $currencyData = Currency::where('statut','yes')->first();
        $currency = $currencyData->symbole ? $currencyData->symbole : '$';

        $id_user = $request->get('id_user');
        $lat_source = $request->get('lat_source');
        $lng_source = $request->get('lng_source');
        $lat_destination = $request->get('lat_destination');
        $lng_destination = $request->get('lng_destination');
        $distance = $request->get('distance');
        $distance_unit = $request->get('distance_unit');
        $id_payment = $request->get('id_payment');
        $source_adrs = $request->get('source_adrs');
        $destination_adrs = $request->get('destination_adrs');
        $sender_name = $request->get('sender_name');
        $receiver_name = $request->get('receiver_name');
        $sender_phone = $request->get('sender_phone');
        $receiver_phone = $request->get('receiver_phone');
        $note = $request->get('note');
        $parcel_weight = $request->get('parcel_weight');
        $parcel_dimension = $request->get('parcel_dimension');
        $parcel_image = $request->file('parcel_image');
        $parcel_type = $request->get('parcel_type');
        $parcel_date = $request->get('parcel_date');
        $parcel_time = $request->get('parcel_time');
        $receive_date = $request->get('receive_date');
        $receive_time = $request->get('receive_time');
        $amount = $request->get('amount');
        $transaction_id = $request->get('transaction_id');
        
        $filenames = [];
        if ($request->hasfile('parcel_image') && $request->file('parcel_image') != null) {
            $images = is_array($parcel_image) ? $parcel_image : [$parcel_image];
            for ($i = 0; $i < sizeof($images); $i++) {
                $extension = $images[$i]->getClientOriginalExtension();
                $time = time() . '_' . $i . '.' . $extension;
                $filename = 'parcel_' . $time;
                Helper::compressFile($images[$i]->getPathName(), public_path('images/parcel_order') . '/' . $filename, 8);
                array_push($filenames, $filename);
            }
        }

        $settings = Settings::first();
        $mapType = $settings->map_for_application;
        $google_map_api_key = $settings->google_map_api_key;

        $country = '';
        if($mapType == "Google" && !empty($google_map_api_key)){
            $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$lat_source},{$lng_source}&key={$google_map_api_key}";
            $addressResponse = file_get_contents($url);
            $data = json_decode($addressResponse, true);
            if (!empty($data['results'][0]['address_components'])) {
                foreach ($data['results'][0]['address_components'] as $component) {
                    if (in_array('country', $component['types'])) {
                        $country = $component['long_name'];
                        break;
                    }
                }
            }
        }else{
            $addressResponse = Http::withHeaders([
                'User-Agent' => env('APP_NAME','Cabme')
                ])->get("https://nominatim.openstreetmap.org/reverse",[
                'lat' => $lat_source,
                'lon' => $lng_source,
                'format' => 'json'
            ]);
            $country = optional($addressResponse->json('address'))['country'] ?? '';
        }

        if($country){
            $taxes = Tax::where('statut','yes')->where('country',$country)->get()->toArray();
        }else{
            $taxes = Tax::where('statut','yes')->get()->toArray();
        }
        
        $discount_type = '';
        if($request->has('discount_id') && !empty($request->get('discount_id'))){
            $discount = Coupon::find($request->get('discount_id'));
            $discount_type = $discount ? ['type' => $discount->type, 'value' => $discount->discount] : '';
        }
        
        $newParcelOrder = ParcelOrder::create([
            'id_user_app' => $id_user,
            'source' => $source_adrs,
            'destination' => $destination_adrs,
            'lat_source' => $lat_source,
            'lng_source' => $lng_source,
            'lat_destination' => $lat_destination,
            'lng_destination' => $lng_destination,
            'sender_name' => $sender_name,
            'sender_phone' => $sender_phone,
            'receiver_name' => $receiver_name,
            'receiver_phone' => $receiver_phone,
            'parcel_weight' => $parcel_weight,
            'parcel_dimension' => $parcel_dimension,
            'parcel_type' => $parcel_type,
            'parcel_image' => json_encode($filenames),
            'note' => $note,
            'parcel_date' => $parcel_date,
            'parcel_time' => $parcel_time,
            'receive_date' => $receive_date,
            'receive_time' => $receive_time,
            'status' => 'new',
            'payment_status' => 'no',
            'id_payment_method' => $id_payment,
            'distance' => number_format($distance, 2),
            'distance_unit' => $distance_unit,
            'amount' => $amount,
            'admin_commission_type' => null,
            'tax' => json_encode($taxes),
            'discount_type' => $discount_type ? json_encode($discount_type) : null,
            'transaction_id' => $transaction_id,
            'payment_status' => 'yes',
            'booking_number' => "#".random_int(100000, 999999),
        ]);

        $parcelOrder = ParcelOrder::join('payment_method', 'payment_method.id', '=', 'parcel_orders.id_payment_method')
            ->join('parcel_category', 'parcel_category.id', '=', 'parcel_orders.parcel_type')
            ->select('parcel_orders.*', 'payment_method.libelle as payment_method', 'parcel_category.title as parcel_type', 'parcel_category.image as parcel_type_image')
            ->where('parcel_orders.id', $newParcelOrder->id)->first();


        $userData = UserApp::find($id_user);
        $paymentData = PaymentMethod::find($id_payment);
        
        //Get subtotal
        $sub_total = $parcelOrder->amount;
        $totalAmount = $sub_total;

        //Calculate discount
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

        //Calculate tax
        $totalTaxAmount = 0;
        $taxHtml = '';
        $tax = json_decode($parcelOrder->tax, true);
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
                $totalTaxAmount += floatval(number_format($taxValue, $currencyData->decimal_digit));
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

        //If payment is wallet then debit amount from user wallet
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
                'booking_id' => $parcelOrder->id,
                'booking_type' => 'parcel',
                'note' => 'Parcel amount debited',
                'transaction_id' => $transaction_id,
            ]);
        }

        //Update ride data
        $parcelOrder->discount = $discount;
        $parcelOrder->save();

        //Send email
        if ($userData->email != "") {
            $emailsubject = '';
            $emailmessage = '';
            $emailtemplate = EmailTemplate::select('*')->where('type', 'parcel_payment_receipt')->first();
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
        
            $tip_amount = !empty($tip) ? number_format($tip, $currencyData->decimal_digit) : 0;
                        $tip_amount = $currencyData->symbol_at_right == "true" 
                            ? $tip_amount . $currency 
                            : $currency . $tip_amount;
                                      
            $total = !empty($totalAmount) ? number_format($totalAmount, $currencyData->decimal_digit) : 0;
                        $total = $currencyData->symbol_at_right == "true" 
                            ? $total . $currency 
                            : $currency . $total;


            $discount = !empty($discount) ? number_format($discount, $currencyData->decimal_digit) : 0;
            $discount = $currencyData->symbol_at_right == "true" ? $discount . $currency : $currency . $discount;
            
            $date = date('Y-m-d H:i:s');

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
        
        $parcelOrder->load(['user:id,nom,prenom,email,phone,photo_path,review_sum,review_count,average_rating']);
        if ($parcelOrder->user) {
            $parcelOrder->user->image = (!empty($parcelOrder->user->photo_path) && file_exists(public_path('assets/images/users/' . $parcelOrder->user->photo_path)))
                ? asset('assets/images/users/' . $parcelOrder->user->photo_path)
                : asset('assets/images/placeholder_image.jpg');
            unset($parcelOrder->user->photo_path);
        }

        if (!empty($parcelOrder->parcel_image)) {
            $parcelImage = json_decode($parcelOrder->parcel_image, true);
            $parcelImages = [];
            foreach ($parcelImage as $value) {
                if (file_exists(public_path("images/parcel_order/$value"))) {
                    $parcelImages[] = asset("images/parcel_order/$value");
                }
            }
            $parcelOrder->parcel_image = !empty($parcelImages) ? $parcelImages : asset('assets/images/placeholder_image.jpg');
        }

        if ($parcelOrder->parcel_type_image != '' && file_exists(public_path('assets/images/parcel_category/'.'/'.$parcelOrder->parcel_type_image))) {
            $parcelOrder->parcel_type_image = asset('assets/images/parcel_category/') . '/' . $parcelOrder->parcel_type_image;
        }else{
            $parcelOrder->parcel_type_image = asset('assets/images/placeholder_image.jpg');
        }

        $parcelOrder->admin_commission_type = json_decode($parcelOrder->admin_commission_type,true);
        $parcelOrder->tax = json_decode($parcelOrder->tax, true);
        $parcelOrder->discount_type = json_decode($parcelOrder->discount_type, true);
        
        $response['success'] = 'success';
        $response['error'] = null;
        $response['message'] = 'Parcel order successfully created';
        $response['data'] = $parcelOrder->toArray();

        //Send new booking notification to drivers
        Helper::newBookingNotification('parcel', $newParcelOrder);
        
        return response()->json($response);
    }
}
