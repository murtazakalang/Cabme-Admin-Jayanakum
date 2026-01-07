<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\ParcelOrder;
use App\Models\Zone;
use App\Models\Settings;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Validator;

class ParcelSearchController extends Controller
{
    
    public function getData(Request $request)
    {

        $response = array();
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|integer|exists:conducteur,id',
        ]);
        
        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        $source_lat = $request->get('source_lat');
        $source_lng = $request->get('source_lng');
        $destination_lat = $request->get('destination_lat');
        $destination_lng = $request->get('destination_lng');
        $date = $request->get('date');
        $source_city = $request->get('source_city');
        $driver_id = $request->get('driver_id');

        Log::info('Search Parcel Request Data', ['request' => $request->all()]);

        $settings = Settings::first();
        $driver = Driver::find($driver_id);

        $isInZone = Helper::driverInZone($driver_id, $source_lat, $source_lng);
        if (!$isInZone) {
            return response()->json([
                'success' => 'Failed',
                'error'   => 'Driver is not available in the selected zone.',
                'message' => null,
            ]);
        }

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
        
        if ((!empty($date)) || (!empty($source_lat) && !empty($source_lng)) || (!empty($destination_lat) && !empty($destination_lng))) {

            $parcelOrders = ParcelOrder::join('payment_method', 'payment_method.id', '=', 'parcel_orders.id_payment_method')
            ->join('user_app', 'user_app.id', '=', 'parcel_orders.id_user_app')
            ->join('parcel_category', 'parcel_category.id', '=', 'parcel_orders.parcel_type')
            ->select(
                'parcel_orders.*',
                'payment_method.libelle as payment_method',
                'parcel_category.title as parcel_type',
                'parcel_category.image as parcel_type_image',
                'user_app.nom',
                'user_app.prenom',
                'user_app.phone as user_phone',
                'user_app.photo_path as user_photo'
            )
            ->when(!empty($date), function ($query) use ($date) {
                $query->where('parcel_date', $date);
            })
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
            ->when(!empty($destination_lat) && !empty($destination_lng), function ($query) use ($destination_lat, $destination_lng, $earthRadius, $radius) {
                $query->selectRaw("(
                        $earthRadius * acos(
                            cos(radians(?)) * cos(radians(lat_destination)) *
                            cos(radians(lng_destination) - radians(?)) +
                            sin(radians(?)) * sin(radians(lat_destination))
                        )
                    ) AS destination_distance", [$destination_lat, $destination_lng, $destination_lat])
                    ->having('destination_distance', '<=', $radius);
            })
            ->when(!empty($source_city), function ($query) use ($source_city) {
                $query->orWhere('source', 'LIKE', "%$source_city%");
            })
            ->where('parcel_orders.status', 'new')
            ->orderBy('parcel_orders.created_at', 'desc')
            ->get();
            
            if (!$parcelOrders->isEmpty()) {

                $parcelOrders->map(function ($parcelOrder) {

                    $parcelOrder->user_name = $parcelOrder->prenom . " " . $parcelOrder->nom;
                    if ($parcelOrder->parcel_image != '') {
                        $parcelImage = json_decode($parcelOrder->parcel_image, true);
                        $parcelImages = [];
                        foreach ($parcelImage as $value) {
                            if (file_exists(public_path('images/parcel_order/' . '/' . $value))) {
                                $parcelImages[] = asset('images/parcel_order/') . '/' . $value;
                            }
                        }
                        $parcelOrder->parcel_image = !empty($parcelImages) ? $parcelImages : [];
                    }
                    
                    if ($parcelOrder->parcel_type_image != '' && file_exists(public_path('assets/images/parcel_category/'.'/'.$parcelOrder->parcel_type_image))) {
                        $parcelOrder->parcel_type_image = asset('assets/images/parcel_category/') . '/' . $parcelOrder->parcel_type_image;
                    }else{
                        $parcelOrder->parcel_type_image = asset('assets/images/placeholder_image.jpg');
                    }

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
                   
                    $parcelOrder->tax = json_decode($parcelOrder->tax, true);
                    $parcelOrder->admin_commission_type = json_decode($parcelOrder->admin_commission_type,true);
                    $parcelOrder->discount_type = json_decode($parcelOrder->discount_type, true);

                    return $parcelOrder;
                });

                $response['success'] = 'success';
                $response['error'] = null;
                $response['message'] = 'Parcel orders successfully found';
                $response['data'] = $parcelOrders;

            } else {
                $response['success'] = 'Failed';
                $response['error'] = 'No parcel bookings found. Please adjust your search criteria.';
                $response['message'] = null;
            }
            
        } else {
            $response['success'] = 'Failed';
            $response['error'] = 'Some required field is missing to search parcel bookings.';
        }
        
        return response()->json($response);
    }

    public function is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y){
		$i = $j = $c = $point = 0;
		for ($i = 0, $j = $points_polygon ; $i < $points_polygon; $j = $i++) {
			$point = $i;
			if( $point == $points_polygon )
				$point = 0;
			if ( (($vertices_y[$point]  >  $latitude_y != ($vertices_y[$j] > $latitude_y)) && ($longitude_x < ($vertices_x[$j] - $vertices_x[$point]) * ($latitude_y - $vertices_y[$point]) / ($vertices_y[$j] - $vertices_y[$point]) + $vertices_x[$point]) ) )
				$c = !$c;
		}
		return $c;
	}
}
