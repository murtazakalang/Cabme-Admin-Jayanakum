<?php

namespace App\Http\Controllers\api\v1;

use App\Models\RentalOrder;
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
use App\Models\RentalPackage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Helpers\Helper;
use Validator;

class RentalRegisterController extends Controller
{
    
    public function register(Request $request)
    {
        $response = array();
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|integer|exists:user_app,id',
            'lat_source' => 'required',
            'lng_source' => 'required',
            'depart_name' => 'required',
            'id_payment' => 'required|integer|exists:payment_method,id',
            'id_rental_package' => 'required',
            'id_vehicle_type' => 'required',
            'amount' => 'required',
            'transaction_id' => 'required',
            'start_date' => 'required',
            'start_time' => 'required',
            'end_date' => 'required',
            'end_time' => 'required',
        ]);

        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        $id_user = $request->get('id_user');
        $lat_source = $request->get('lat_source');
        $lng_source = $request->get('lng_source');
        $depart_name = $request->get('depart_name');
        $id_payment = $request->get('id_payment');
        $id_rental_package = $request->get('id_rental_package');
        $id_vehicle_type = $request->get('id_vehicle_type');
        $amount = $request->get('amount');
        $transaction_id = $request->get('transaction_id');
        $start_date = $request->get('start_date');
        $start_time = $request->get('start_time');
        $end_date = $request->get('end_date');
        $end_time = $request->get('end_time');
        
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
        
        $newRentalOrder = RentalOrder::create([
            'id_user_app' => $id_user,
            'lat_source' => $lat_source,
            'lng_source' => $lng_source,
            'depart_name' => $depart_name,
            'id_rental_package' => $id_rental_package,
            'id_vehicle_type' => $id_vehicle_type,
            'status' => 'new',
            'payment_status' => 'no',
            'id_payment_method' => $id_payment,
            'amount' => $amount,
            'admin_commission_type' => null,
            'tax' => json_encode($taxes),
            'discount_type' => $discount_type ? json_encode($discount_type) : null,
            'transaction_id' => $transaction_id,
            'booking_number' => "#".random_int(100000, 999999),
            'start_date' => $start_date,
            'start_time' => $start_time,
            'end_date' => $end_date,
            'end_time' => $end_time,
        ]);

        $rentalOrder = RentalOrder::join('payment_method', 'payment_method.id', '=', 'rental_orders.id_payment_method')
            ->leftjoin('type_vehicule', 'type_vehicule.id', '=', 'rental_orders.id_vehicle_type')
            ->select('rental_orders.*', 'payment_method.libelle as payment_method','type_vehicule.libelle as vehicle_name','type_vehicule.image as vehicle_image')
            ->where('rental_orders.id', $newRentalOrder->id)->first();
        
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
        $rentalOrder->admin_commission_type = json_decode($rentalOrder->admin_commission_type,true);
        $rentalOrder->tax = json_decode($rentalOrder->tax, true);
        $rentalOrder->discount_type = json_decode($rentalOrder->discount_type, true);
        
        $response['success'] = 'success';
        $response['error'] = null;
        $response['message'] = 'Rental order successfully created';
        $response['data'] = $rentalOrder->toArray();

        //Send new booking notification to drivers
        Helper::newBookingNotification('rental', $newRentalOrder);

        return response()->json($response);
    }
}
