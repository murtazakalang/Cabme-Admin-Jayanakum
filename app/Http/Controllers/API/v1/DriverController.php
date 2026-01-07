<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Settings;
use App\Models\Zone;
use App\Models\Vehicle;
use App\Models\Requests;
use App\Models\ParcelOrder;
use App\Models\RentalOrder;
use App\Models\Currency;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use DB;
use Validator;
use Carbon\Carbon;

class DriverController extends Controller
{
	
	public function getOwnerDriver(Request $request)
	{
		
        $validator = Validator::make($request->all(), [
            'owner_id'     => 'required|integer|exists:conducteur,id',
        ]);
        if($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'code'    => 404,
                'message' => $validator->errors()->first(),
                'data'    => null,
            ]);
        }

		$owner_id = $request->get('owner_id');

		$drivers = Driver::where('ownerId', $owner_id)->get();

		if ($drivers->isNotEmpty()) {

			$drivers->map(function($driver){
				$driver->photo_path = (!empty($driver->photo_path) && file_exists(public_path('assets/images/driver/' . $driver->photo_path)))
				? asset('assets/images/driver/' . $driver->photo_path)
				: asset('assets/images/placeholder_image.jpg');
				$driver->zone_id = $driver->zone_id ? explode(',', $driver->zone_id) : [];
            	$driver->service_type = $driver->service_type ? explode(',', $driver->service_type) : [];
				
				$vehicle = Vehicle::where('id_conducteur',$driver->id)->first();
                $driver->vehicle_id = $vehicle ? $vehicle->id : null;	
				$driver->id_type_vehicule = $vehicle ? $vehicle->id_type_vehicule : null;	
				
				return $driver;
			});
			
			return response()->json([
                'success' => 'success',
                'code'    => 200,
                'message' => 'Drivers successfully found',
                'data'    => $drivers->toArray(),
            ]);
		}else{
			return response()->json([
                'success' => 'Failed',
                'code'    => 200,
                'message' => 'No driver found',
                'data'    => null,
            ]);
		}
	}

	public function getOwnerDashboard(Request $request)
	{
		
        $validator = Validator::make($request->all(), [
            'owner_id'     => 'required|integer|exists:conducteur,id',
        ]);
        if($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'code'    => 404,
                'message' => $validator->errors()->first(),
                'data'    => null,
            ]);
        }

		$owner_id = $request->get('owner_id');

		$drivers = Driver::where('ownerId', $owner_id)->pluck('id');

		$totalRideBookings = Requests::whereIn('id_conducteur', $drivers)->where('statut', 'completed')->count();
		$totalParcelBookings = ParcelOrder::whereIn('id_conducteur', $drivers)->where('status', 'completed')->count();
		$totalRentalBookings = RentalOrder::whereIn('id_conducteur', $drivers)->where('status', 'completed')->count();
		$totalBookings = $totalRideBookings + $totalParcelBookings + $totalRentalBookings;

		$totalVehicles = Vehicle::where('ownerId', $owner_id)->count();

		$startOfWeek = Carbon::now()->startOfWeek();
		$endOfWeek = Carbon::now()->endOfWeek();   

		$totalEarnings = 0;
		$totalAdminCommision = 0;

		$rideEarnings   = 0;
		$parcelEarnings = 0;
		$rentalEarnings = 0;

		$ridesThisWeek = Requests::whereIn('id_conducteur', $drivers)
			->where('statut', 'completed')
			->get();

		foreach ($ridesThisWeek as $ride) {
			$fare = floatval($ride->montant);

			// Apply discount
			if (!empty($ride->discount)) {
				$fare -= floatval($ride->discount);
			}

			// Apply taxes
			if (!empty($ride->tax)) {
				$taxes = json_decode($ride->tax, true);
				$taxBase = $fare;
				foreach ($taxes as $tax) {
					if ($tax['type'] == 'Percentage') {
						$fare += $taxBase * floatval($tax['value']) / 100;
					} else {
						$fare += floatval($tax['value']);
					}
				}
			}

			// Add tip
			$fare += floatval($ride->tip_amount);

			// Deduct admin commission
			if (!empty($ride->admin_commission)) {
				$fare -= floatval($ride->admin_commission);
				$totalAdminCommision += floatval($ride->admin_commission);
			}

			$rideEarnings += $fare;
		}

		// Do same for parcels
		$totalEarnings        = 0;
		$totalAdminCommision  = 0;

		$rideEarnings   = 0;
		$parcelEarnings = 0;
		$rentalEarnings = 0;

		// ------------------- Rides -------------------
		$ridesThisWeek = Requests::whereIn('id_conducteur', $drivers)
			->where('statut', 'completed')
			->get();

		foreach ($ridesThisWeek as $ride) {
			$fare = floatval($ride->montant);

			// Apply discount
			if (!empty($ride->discount)) {
				$fare -= floatval($ride->discount);
			}

			// Apply taxes
			if (!empty($ride->tax)) {
				$taxes = json_decode($ride->tax, true);
				$taxBase = $fare;
				foreach ($taxes as $tax) {
					if ($tax['type'] == 'Percentage') {
						$fare += $taxBase * floatval($tax['value']) / 100;
					} else {
						$fare += floatval($tax['value']);
					}
				}
			}

			// Add tip
			$fare += floatval($ride->tip_amount);

			// Deduct admin commission
			if (!empty($ride->admin_commission)) {
				//$fare -= floatval($ride->admin_commission);
				$totalAdminCommision += floatval($ride->admin_commission);
			}

			$rideEarnings += $fare;
		}


		$parcelsThisWeek = ParcelOrder::whereIn('id_conducteur', $drivers)
			->where('status', 'completed')
			->get();

		foreach ($parcelsThisWeek as $parcel) {
			$fare = floatval($parcel->amount);

			// Apply discount
			if (!empty($parcel->discount)) {
				$fare -= floatval($parcel->discount);
			}

			// Apply taxes
			if (!empty($parcel->tax)) {
				$taxes = json_decode($parcel->tax, true);
				$taxBase = $fare;
				foreach ($taxes as $tax) {
					if ($tax['type'] == 'Percentage') {
						$fare += $taxBase * floatval($tax['value']) / 100;
					} else {
						$fare += floatval($tax['value']);
					}
				}
			}

			// Add tip
			$fare += floatval($parcel->tip);

			// Deduct admin commission
			if (!empty($parcel->admin_commission)) {
				//$fare -= floatval($parcel->admin_commission);
				$totalAdminCommision += floatval($parcel->admin_commission);
			}

			$parcelEarnings += $fare;
		}

		// Do same for rentals
		$rentalsThisWeek = RentalOrder::whereIn('id_conducteur', $drivers)
			->where('status', 'completed')
			->get();

		foreach ($rentalsThisWeek as $rental) {
			$fare = floatval($rental->amount);

			// Apply discount
			if (!empty($rental->discount)) {
				$fare -= floatval($rental->discount);
			}

			// Apply taxes
			if (!empty($rental->tax)) {
				$taxes = json_decode($rental->tax, true);
				$taxBase = $fare;
				foreach ($taxes as $tax) {
					if ($tax['type'] == 'Percentage') {
						$fare += $taxBase * floatval($tax['value']) / 100;
					} else {
						$fare += floatval($tax['value']);
					}
				}
			}

			// Deduct admin commission
			if (!empty($rental->admin_commission)) {
				//$fare -= floatval($rental->admin_commission);
				$totalAdminCommision += floatval($rental->admin_commission);
			}

			$rentalEarnings += $fare;
		}
		
		$totalEarnings = $rideEarnings + $parcelEarnings + $rentalEarnings;

		$currency = Currency::where('statut', 'yes')->first();
		if($currency->symbol_at_right == 'true'){
			$formattedTotalEarnings = number_format( $totalEarnings, $currency->decimal_digit ).$currency->symbole;
			$formattedTotalAdminCommission = number_format( $totalAdminCommision, $currency->decimal_digit ).$currency->symbole;
		}else{
			$formattedTotalEarnings = $currency->symbole.number_format( $totalEarnings, $currency->decimal_digit );
			$formattedTotalAdminCommission = $currency->symbole.number_format( $totalAdminCommision, $currency->decimal_digit );
		}
		
		$data = [
			'total_drivers' => (string) $drivers->count(),
			'total_vehicles' => (string) $totalVehicles,
			'total_bookings' => (string) $totalBookings,
			'total_earnings' => $formattedTotalEarnings,
			'total_admin_commission' => $formattedTotalAdminCommission,
		];
	
		return response()->json([
			'success' => 'success',
			'code'    => 200,
			'message' => 'Owner dashbaord data successfully found',
			'data'    => $data,
		]);
	}

	public function getDriverDetail(Request $request)
	{
		
        $validator = Validator::make($request->all(), [
            'id_driver'     => 'required|integer|exists:conducteur,id',
        ]);
		
        if($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'code'    => 404,
                'message' => $validator->errors()->first(),
                'data'    => null,
            ]);
        }

		$id_driver = $request->id_driver;

		$driverData = Driver::find($id_driver);
        
		if ($driverData->photo_path != ''){
            $driverData->photo_path = file_exists(public_path('assets/images/driver/' . $driverData->photo_path))
            ? asset('assets/images/driver/' . $driverData->photo_path)
            : asset('assets/images/placeholder_image.jpg');
        }

		$driverData->zone_id = $driverData->zone_id ? explode(',', $driverData->zone_id) : [];
        $driverData->service_type = $driverData->service_type ? explode(',', $driverData->service_type) : [];

		return response()->json([
			'success' => 'success',
			'code'    => 200,
			'message' => 'Driver data successfully found',
			'data'    => $driverData->toArray(),
		]);
	}

	public function deleteOwnerDriver(Request $request)
  	{

		$validator = Validator::make($request->all(), [
			'id_driver'     => 'required|integer|exists:conducteur,id',
		]);
		if($validator->fails()) {
			return response()->json([
				'success' => 'Failed',
				'code'    => 404,
				'message' => $validator->errors()->first(),
				'data'    => null,
			]);
		}

		$id_driver = $request->get('id_driver');
		
		$driver = Driver::find($id_driver);
		
		//Reset limit
		Helper::resetDriverSubscriptionLimit($driver->id, 'subscriptionTotalDriver', 'inc');

		//Remove driver from vehicle
		$vehicle = Vehicle::where('id_conducteur', $driver->id)->first();
		if ($vehicle) {
			$vehicle->id_conducteur = null;
			$vehicle->save();
		}

		$driver->delete();
		
		$response['success'] = 'success';
		$response['error'] = null;
		$response['message'] = 'Driver removed successfully';
		$response['data'] = '';
		
		return response()->json($response);
    }
}
