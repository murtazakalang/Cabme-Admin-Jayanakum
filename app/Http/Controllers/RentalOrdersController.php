<?php

namespace App\Http\Controllers;

use App\Models\RentalOrder;
use App\Models\Currency;
use App\Models\Driver;
use App\Models\Review;
use App\Models\Complaints;
use App\Models\Commission;
use App\Models\Settings;
use App\Models\VehicleType;
use App\Models\RentalPackage;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;
use File;
use Image;
use Carbon\Carbon;

class RentalOrdersController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {

        $query = RentalOrder::with(['user', 'driver', 'vehicleType', 'rentalPackage', 'paymentMethod']);

        if ($request->selected_search == 'userName' && $request->has('search') && $request->search != '') {

            $search = $request->input('search');
            $query->whereHas('user', function($q) use ($search) {
                $q->where('prenom', 'LIKE', "%{$search}%")
                ->orWhere('nom', 'LIKE', "%{$search}%")
                ->orWhereRaw('CONCAT(prenom, " ", nom) LIKE ?', ["%{$search}%"]);
            });

        } elseif ($request->selected_search == 'driverName' && $request->has('search') && $request->search != '') {

            $search = $request->input('search');
            $query->whereHas('driver', function($q) use ($search) {
                $q->where('prenom', 'LIKE', "%{$search}%")
                ->orWhere('nom', 'LIKE', "%{$search}%")
                ->orWhereRaw('CONCAT(prenom, " ", nom) LIKE ?', ["%{$search}%"]);
            });

        } else if ($request->selected_search == 'type' && $request->has('ride_type') && $request->ride_type != '') {
            
            $search = $request->input('ride_type');
            if ($search == "dispatcher") {
                $query->where('rental_orders.ride_type', 'dispatcher');
            } else if ($search == "customer") {
                $query->where('rental_orders.ride_type', NULL);
            }

        } else if ($request->selected_search == 'status' && $request->has('ride_status') && $request->ride_status != '') {

            $search = $request->input('ride_status');
            $query->where('rental_orders.status', 'LIKE', '%' . $search . '%');

        } else if ($request->selected_search == 'orderId' && $request->has('search') && $request->search != '') {

            $search = $request->input('search');
            $query->where('rental_orders.id', '=', $search);

        } else if ($request->selected_search == 'orderNumber' && $request->has('search') && $request->search != '') {

            $search = $request->input('search');
            $query->where('rental_orders.booking_number', 'LIKE', '%' . $search . '%');
        } 
        
        if ($request->filled('daterange')) {
            $dates = explode(' - ', $request->daterange);
            $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
            $endDate = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
            $query->whereBetween('rental_orders.created_at', [$startDate, $endDate]);
        }
        
        if ($request->has('status_selector') && $request->status_selector != '') {
            $status = $request->input('status_selector');
            $query->where('rental_orders.status', 'LIKE', '%' . $status . '%');
        }
       
        $totalLength = count($query->get());
        $perPage = $request->input('per_page', 20);
        $bookings = $query->orderBy('created_at', 'desc')->paginate($perPage)->appends($request->all());

        $currency = Currency::where('statut', 'yes')->first();
       
        $completed_rental_rides = RentalOrder::where('status', 'completed')->count();
        $canceled_rental_rides = RentalOrder::leftjoin('user_app', 'rental_orders.id_user_app', '=', 'user_app.id')
            ->join('conducteur', 'rental_orders.id_conducteur', '=', 'conducteur.id')
            ->join('payment_method', 'rental_orders.id_payment_method', '=', 'payment_method.id')
            ->where('rental_orders.status', 'canceled')
            ->orwhere('rental_orders.status', 'rejected')
            ->count('rental_orders.id');
        $totalNewRides = RentalOrder::where('status', 'new')->count();
        $totalOnRides = RentalOrder::where('status', 'onride')->count();
        $totalRides = RentalOrder::count();

        $bookings = $this->getFinalBookings($bookings, $currency);
        
        return view("rental_orders.index", compact('bookings', 'currency', 'totalLength','perPage','completed_rental_rides','canceled_rental_rides','totalNewRides','totalOnRides','totalRides'));
    }
    
    public function getFinalBookings($bookings, $currency){

        $bookings->getCollection()->transform(function ($booking) use ($currency) {

            //Calculate Extra Price
            $packageData = RentalPackage::find($booking->id_rental_package);
            $includedHours = $packageData->includedHours;
            $includedDistance = $packageData->includedDistance;
            $extraKmFare = $packageData->extraKmFare;
            $extraMinuteFare = $packageData->extraMinuteFare;

            //KM calculation
            $sub_total = $packageData->baseFare;
            $current_km = $booking->current_km;
            $complete_km = $booking->complete_km;
            $final_km = floatval($complete_km) - floatval($current_km);

            //Extra Km charge
            $extraKmCharge = 0;
            if ($final_km > $includedDistance) {
                $extraKm = $final_km - $includedDistance;
                $extraKmCharge = $extraKm * $extraKmFare;
            }
            
            //Extra minute calculation
            $startDateTime = Carbon::parse($booking->start_date . ' ' . $booking->start_time);
            $endDateTime = Carbon::parse($booking->end_date . ' ' . $booking->end_time);

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

            // Apply discount if exists
            $discount = $booking->discount;
            if ($discount) {
                $totalAmount -= $discount;
            }
            // Calculate tax
            $taxes = json_decode($booking->tax, true);
            $totalTaxAmount = 0;
            if (!empty($taxes)) {
                foreach ($taxes as $data) {
                    if ($data['type'] == 'Percentage') {
                        $taxValue = ($data['value'] * $totalAmount) / 100;
                    } else {
                        $taxValue = floatval($data['value']);
                    }
                    $totalTaxAmount += floatval($taxValue);
                }
                $totalAmount += $totalTaxAmount;
            }
            // Add a new attribute to booking
            $booking->total_price = $totalAmount;

            return $booking;
        });

        return $bookings;
    }

    public function update(Request $request, $id)
    {

        $rides = RentalOrder::find($id);
        $prevoiusStatus = $rides->status;
        $driverId = $rides->id_conducteur;
        $driverData = Driver::where('id', $driverId)->first();
        if (!empty($driverData->ownerId)) {
            $driverData = Driver::where('id', $driverData->ownerId)->first();
        }
        $setting = Settings::first();
        $subscriptionModel = $setting->subscription_model;
        $commissionData = Commission::first();
        $commissionModel = $commissionData->statut;
       
        if ($subscriptionModel == 'true' || $commissionModel == 'yes') {
            if ($request->input('order_status') == 'confirmed') {
                
                if ($driverData && $driverData->subscriptionTotalOrders !== null && $driverData->subscriptionTotalOrders !== '') {

                    if (intval($driverData->subscriptionTotalOrders) != -1) {

                        if (intval($driverData->subscriptionTotalOrders) != -1 && intval($driverData->subscriptionTotalOrders) <= 0) {
                            return redirect()->back()->with('message', __('lang.upgrade_plan_limit_err'));
                        } else {
                            $remaningRides = intval($driverData->subscriptionTotalOrders) - 1;
                            Driver::where('id', $driverData->id)
                                ->update(['subscriptionTotalOrders' => $remaningRides]);
                        }
                    }
                }
            }
        }

        if ($prevoiusStatus == 'confirmed' && ($request->input('order_status') == 'canceled' || $request->input('order_status') == 'rejected')) {
            if ($subscriptionModel == 'true' || $commissionModel == 'yes') {
                if ($driverData && $driverData->subscriptionTotalOrders != '' && $driverData->subscriptionTotalOrders != null && intval($driverData->subscriptionTotalOrders != '-1')) {
                    $subscriptionTotalOrders = intval($driverData->subscriptionTotalOrders) + 1;
                    Driver::where('id', $driverData->id)->update(['subscriptionTotalOrders' => $subscriptionTotalOrders]);
                }
            }
        }
        if ($request->input('order_status') == 'on ride') {
            $driverData = Driver::where('id', $driverId)->first();
            if ($driverData->driver_on_ride == 'yes') {
                return redirect()->back()->with('message', __('lang.one_ride_at_a_time_allow'));
            } else {
                Driver::where('id', $driverId)->update(['driver_on_ride' => 'yes']);
            }
        }
        if ($request->input('order_status') == 'completed') {
            Driver::where('id', $driverId)->update(['driver_on_ride' => 'no']);
        }
        if ($rides) {
            $rides->status = $request->input('order_status');
            $rides->save();
        }
        return redirect()->back()->with('message', __('lang.status_updated_successfully'));
    }

    public function show($id)
    {
        $currency = Currency::where('statut', 'yes')->first();
        $ride = RentalOrder::leftJoin('user_app', 'rental_orders.id_user_app', '=', 'user_app.id')
            ->leftJoin('conducteur', 'rental_orders.id_conducteur', '=', 'conducteur.id')
            ->leftJoin('conducteur as owner', 'owner.id', '=', 'rental_orders.ownerId')
            ->leftJoin('payment_method', 'rental_orders.id_payment_method', '=', 'payment_method.id')
            ->leftJoin('rental_packages', 'rental_orders.id_rental_package', '=', 'rental_packages.id')
            ->select('rental_orders.*', 'rental_orders.depart_name')
            ->addSelect(
                'conducteur.prenom as driverPrenom',
                'conducteur.nom as driverNom',
                'conducteur.phone as driver_phone',
                'conducteur.email as driver_email',
                'conducteur.photo_path as driver_photo'
            )
            ->addSelect(
                'user_app.prenom as userPrenom',
                'user_app.nom as userNom',
                'user_app.phone as user_phone',
                'user_app.email as user_email',
                'user_app.photo_path'
            )
            ->addSelect('payment_method.libelle', 'payment_method.image')
            ->addSelect(
                'owner.prenom as ownerPrenom',
                'owner.nom as ownerNom',
                'owner.phone as owner_phone',
                'owner.email as owner_email',
                'owner.photo_path as owner_photo'
            )
            ->addSelect(
                'rental_packages.title as package_title',
                'rental_packages.description as package_description',
                'rental_packages.image as package_image',
                'rental_packages.baseFare as package_baseFare',
                'rental_packages.includedHours as package_includedHours',
                'rental_packages.includedDistance as package_includedDistance',
                'rental_packages.extraKmFare as package_extraKmFare',
                'rental_packages.extraMinuteFare as package_extraMinuteFare'
            )
            ->where('rental_orders.id', $id)
            ->first();

        if (!$ride) {
            abort(404, 'Rental order not found');
        }
      
        if (!empty($ride->driver_phone)) $ride->driver_phone = Helper::shortNumber($ride->driver_phone);
        if (!empty($ride->driver_email)) $ride->driver_email = Helper::shortEmail($ride->driver_email);

        if (!empty($ride->user_phone)) $ride->user_phone = Helper::shortNumber($ride->user_phone);
        if (!empty($ride->user_email)) $ride->user_email = Helper::shortEmail($ride->user_email);

        
        $startDateTime = \Carbon\Carbon::parse($ride->start_date . ' ' . $ride->start_time);
        $endDateTime   = \Carbon\Carbon::parse($ride->end_date . ' ' . $ride->end_time);

        $totalMinutes = $endDateTime->diffInMinutes($startDateTime);
        $totalHours   = floor($totalMinutes / 60); 
        $remainingMinutes = $totalMinutes % 60;

        // Package data
        $baseFare          = floatval($ride->package_baseFare);
        $includedHours     = intval($ride->package_includedHours);
        $includedDistance  = floatval($ride->package_includedDistance);
        $extraKmFare       = floatval($ride->package_extraKmFare);
        $extraMinuteFare   = floatval($ride->package_extraMinuteFare);

        // Distance calculation
        $startKm = floatval($ride->current_km); 
        $endKm   = floatval($ride->complete_km); 
        $totalDistance = $endKm - $startKm;

        // Base fare
        $calculatedAmount = $baseFare;

        // Extra time
        $extraMinutes = max(0, $totalMinutes - ($includedHours * 60));
        $extraHours   = floor($extraMinutes / 60);
        $leftoverExtraMinutes = $extraMinutes % 60;

        $extraMinuteCost = $extraMinutes * $extraMinuteFare;
        $calculatedAmount += $extraMinuteCost;

        // Extra distance
        $extraKm = max(0, $totalDistance - $includedDistance);
        $extraKmCost = $extraKm * $extraKmFare;
        $calculatedAmount += $extraKmCost;

        // Save calculated values
        $ride->base_fare            = $baseFare;
        $ride->total_distance       = $totalDistance;
        $ride->total_hours_display  = "{$totalHours}h {$remainingMinutes}m";
        $ride->included_hours       = $includedHours;
        $ride->extra_hours          = $extraHours;
        $ride->extra_minutes        = $leftoverExtraMinutes;
        $ride->extra_minutes_cost   = $extraMinuteCost;
        $ride->included_distance    = $includedDistance;
        $ride->extra_km             = $extraKm;
        $ride->extra_km_cost        = $extraKmCost;

        // Apply discount, tax, and tip
        $totalAmount = $calculatedAmount - floatval($ride->discount);

        // Tax
        $totalTaxAmount = 0;
        $taxHtml = '';
        if (!empty($ride->tax)) {
            $tax = json_decode($ride->tax, true);
            foreach ($tax as $data) {
                $taxValue = ($data['type'] == "Percentage")
                    ? (floatval($data['value']) * $totalAmount) / 100
                    : floatval($data['value']);

                $totalTaxAmount += $taxValue;
                $taxLabel = $data['libelle'];
                $valueDisplay = ($data['type'] == "Percentage")
                    ? $data['value'] . "%"
                    : ($currency->symbol_at_right == "true"
                        ? number_format($data['value'], $currency->decimal_digit) . $currency->symbole
                        : $currency->symbole . number_format($data['value'], $currency->decimal_digit));

                $taxValueAmount = ($currency->symbol_at_right == "true")
                    ? number_format($taxValue, $currency->decimal_digit) . $currency->symbole
                    : $currency->symbole . number_format($taxValue, $currency->decimal_digit);

                $taxHtml .= "<tr><td>{$taxLabel} ({$valueDisplay})</td>
                            <td style='color:green'>+{$taxValueAmount}</td></tr>";
            }
            $totalAmount += $totalTaxAmount;
        }

        $totalAmount += floatval($ride->tip);

        $customer_review = Review::where('booking_id', $id)
            ->where('booking_type', 'rental')
            ->where('review_from', 'customer')
            ->get();

        $driver_review = Review::where('booking_id', $id)
            ->where('booking_type', 'rental')
            ->where('review_from', 'driver')
            ->get();

        $complaints = Complaints::select('title', 'description')
            ->where('booking_id', $id)
            ->where('booking_type', 'rental')
            ->get();

        $driverRating = '0.0 (0)';
        if (!empty($ride->id_conducteur)) {
            $driver = Driver::find($ride->id_conducteur);
            if ($driver && $driver->review_count) {
                $driverRating = $driver->average_rating . ' (' . $driver->review_count . ')';
            }
        }

        $mapType = Settings::pluck('map_for_application')->first();
        
        return view("rental_orders.show", compact(
            'ride', 'currency', 'customer_review', 'complaints',
            'taxHtml', 'totalAmount', 'driverRating', 'mapType', 'driver_review'
        ));
    }

    public function delete($id)
    {
        if ($id != "") {
            $id = json_decode($id);
            if (is_array($id)) {
                for ($i = 0; $i < count($id); $i++) {
                    $complaint = Complaints::where('booking_id', $id[$i])->where('booking_type', 'rental');
                    if ($complaint) {
                        $complaint->delete();
                    }
                   
                    $ride = RentalOrder::find($id[$i]);
                    $ride->delete();
                    Review::where('booking_id', $id[$i])->where('booking_type', 'rental')->delete();
                    Transaction::where('booking_id', $id[$i])->where('booking_type', 'rental')->delete();
                }
            } else {
                $complaint = Complaints::where('booking_id', $id)->where('booking_type', 'rental');
                if ($complaint) {
                    $complaint->delete();
                }
              
                $ride = RentalOrder::find($id);
                $ride->delete();
                Review::where('booking_id', $id)->where('booking_type', 'rental')->delete();
                Transaction::where('booking_id', $id)->where('booking_type', 'rental')->delete();
            }
        }
        return redirect()->back();
    }
    
}
