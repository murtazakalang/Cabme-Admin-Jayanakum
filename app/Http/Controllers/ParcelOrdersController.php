<?php

namespace App\Http\Controllers;
use App\Models\Complaints;
use App\Models\Currency;
use App\Models\ParcelOrder;
use App\Models\Settings;
use App\Models\Commission;
use App\Models\Driver;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Helpers\Helper;
use Carbon\Carbon;

class ParcelOrdersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, $id = null)
    {
        $currency = Currency::where('statut', 'yes')->first();
        $sql = ParcelOrder::leftjoin('user_app', 'parcel_orders.id_user_app', '=', 'user_app.id')
            ->leftjoin('conducteur', 'parcel_orders.id_conducteur', '=', 'conducteur.id')
            ->select('parcel_orders.id', 'parcel_orders.ride_type', 'parcel_orders.booking_number', 'parcel_orders.status', 'parcel_orders.admin_commission', 'parcel_orders.tax', 'parcel_orders.tip', 'parcel_orders.discount', 'parcel_orders.payment_status', 'parcel_orders.distance', 'parcel_orders.amount', 'parcel_orders.created_at', 'conducteur.id as driver_id', 'conducteur.prenom as driverPrenom', 'conducteur.nom as driverNom', 'user_app.id as user_id', 'user_app.prenom as userPrenom', 'user_app.nom as userNom');
        if ($id != '' || $id != null) {
            $sql->where('parcel_orders.id_conducteur', '=', $id);
        }
        $totalRides = $sql->count();
        
        $totalNewRides = (clone $sql)->where('parcel_orders.status', 'new')->count();
        $totalOnRides = (clone $sql)->where('parcel_orders.status', 'onride')->count();

        $totalCompletedRides = (clone $sql)->where('parcel_orders.status', 'completed')->count();
        
        if ($request->selected_search == 'userName' && $request->has('search') && $request->search != '') {

            $search = $request->input('search');
            $sql->where('user_app.prenom', 'LIKE', '%' . $search . '%');
            $sql->orwhere('user_app.nom', 'LIKE', '%' . $search . '%');
            $sql->orWhere(DB::raw('CONCAT(user_app.prenom, " ",user_app.nom)'), 'LIKE', '%' . $search . '%');

        } else if ($request->selected_search == 'driverName' && $request->has('search') && $request->search != '') {

            $search = $request->input('search');
            $sql->where('conducteur.prenom', 'LIKE', '%' . $search . '%');
            $sql->orwhere('conducteur.nom', 'LIKE', '%' . $search . '%');
            $sql->orWhere(DB::raw('CONCAT(conducteur.prenom, " ",conducteur.nom)'), 'LIKE', '%' . $search . '%');

        } else if ($request->selected_search == 'type' && $request->has('ride_type') && $request->ride_type != '') {
            
            $search = $request->input('ride_type');
            if ($search == "dispatcher") {
                $sql->where('parcel_orders.ride_type', 'dispatcher');
            } else if ($search == "customer") {
                $sql->where('parcel_orders.ride_type', NULL);
            }

        } else if ($request->selected_search == 'status' && $request->has('ride_status') && $request->ride_status != '') {

            $search = $request->input('ride_status');
            $sql->where('parcel_orders.status', 'LIKE', '%' . $search . '%');

        } else if ($request->selected_search == 'orderId' && $request->has('search') && $request->search != '') {

            $search = $request->input('search');
            $sql->where('parcel_orders.id', '=', $search);

        } else if ($request->selected_search == 'orderNumber' && $request->has('search') && $request->search != '') {

            $search = $request->input('search');
            $sql->where('parcel_orders.booking_number', 'LIKE', '%' . $search . '%');
        } 

        if ($request->filled('daterange')) {
            $dates = explode(' - ', $request->daterange);
            $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
            $endDate = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
            $sql->whereBetween('parcel_orders.created_at', [$startDate, $endDate]);
        }
        if ($request->has('status_selector') && $request->status_selector != '') {
            $status = $request->input('status_selector');
            $sql->where('parcel_orders.status', 'LIKE', '%' . $status . '%');
        }
        $totalLength = count($sql->get());
        $perPage = $request->input('per_page', 20);
        $rides = $sql->orderBy('parcel_orders.id', 'desc')->paginate($perPage)->appends($request->all());
        return view("parcel_order.index", compact('rides', 'currency', 'id', 'totalRides', 'totalNewRides', 'totalOnRides', 'totalCompletedRides', 'totalLength', 'perPage'));
    }

    public function deleteRide($id)
    {
        if ($id != "") {
            $id = json_decode($id);
            if (is_array($id)) {
                for ($i = 0; $i < count($id); $i++) {
                    $complaint = Complaints::where('booking_id', $id[$i])->where('booking_type', 'parcel');
                    if ($complaint) {
                        $complaint->delete();
                    }
                    
                    $ride = ParcelOrder::find($id[$i]);
                    if ($ride->parcel_image != '') {
                        $parcelImages = json_decode($ride->parcel_image, true);
                        if (count($parcelImages) > 0) {
                            foreach ($parcelImages as $parcelImage) {
                                $destination = public_path('images/parcel_order/' . $parcelImage);
                                if (File::exists($destination)) {
                                    File::delete($destination);
                                }
                            }
                        }
                    }
                    
                    $ride->delete();
                    Review::where('booking_id', $id[$i])->where('booking_type', 'parcel')->delete();
                }
            } else {
                $complaint = Complaints::where('booking_id', $id)->where('booking_type', 'parcel');
                if ($complaint) {
                    $complaint->delete();
                }
                
                $ride = ParcelOrder::find($id);
                if ($ride->parcel_image != '') {
                    $parcelImages = json_decode($ride->parcel_image, true);
                    if (count($parcelImages) > 0) {
                        foreach ($parcelImages as $parcelImage) {
                            $destination = public_path('images/parcel_order/' . $parcelImage);
                            if (File::exists($destination)) {
                                File::delete($destination);
                            }
                        }
                    }
                }

                $ride->delete();
                Review::where('booking_id', $id)->where('booking_type', 'parcel')->delete();
            }
        }
        return redirect()->back();
    }

    public function show($id)
    {
        $currency = Currency::where('statut', 'yes')->first();
        $ride = ParcelOrder::leftjoin('user_app', 'parcel_orders.id_user_app', '=', 'user_app.id')
            ->leftjoin('conducteur', 'parcel_orders.id_conducteur', '=', 'conducteur.id')
            ->leftjoin('conducteur as owner', 'owner.id', '=', 'parcel_orders.ownerId')
            ->join('payment_method', 'parcel_orders.id_payment_method', '=', 'payment_method.id')
            ->leftjoin('parcel_category', 'parcel_orders.parcel_type', '=', 'parcel_category.id')
            ->select('parcel_orders.*')
            ->addSelect('conducteur.prenom as driverPrenom', 'conducteur.nom as driverNom', 'conducteur.phone as driver_phone', 'conducteur.email as driver_email', 'conducteur.photo_path as driver_photo')
            ->addSelect('user_app.prenom as userPrenom', 'user_app.nom as userNom', 'user_app.phone as user_phone', 'user_app.email as user_email', 'user_app.photo_path')
            ->addSelect('payment_method.libelle', 'payment_method.image')
            ->addSelect(
                'owner.prenom as ownerPrenom',
                'owner.nom as ownerNom',
                'owner.phone as owner_phone',
                'owner.email as owner_email',
                'owner.photo_path as owner_photo'
            )
            ->addSelect('parcel_category.title')
            ->where('parcel_orders.id', $id)->first();
        $row = $ride->toArray();
        if (!empty($row['sender_phone'])) {
            $ride->sender_phone = Helper::shortNumber($row['sender_phone']);
        }
        if (!empty($row['driver_phone'])) {
            $ride->driver_phone = Helper::shortNumber($row['driver_phone']);
        }
        if (!empty($row['driver_email'])) {
            $ride->driver_email = Helper::shortEmail($row['driver_email']);
        }
        if (!empty($row['receiver_phone'])) {
            $ride->receiver_phone = Helper::shortNumber($row['receiver_phone']);
        }
        $parcel_image = [];
        if ($ride->parcel_image != '') {
            $parcelImage = json_decode($ride->parcel_image, true);
            foreach ($parcelImage as $value) {
                if (file_exists(public_path('images/parcel_order/' . '/' . $value))) {
                    $image = asset('images/parcel_order/') . '/' . $value;
                    array_push($parcel_image, $image);
                }
            }
        }
        $amount = $ride->amount;
        $tax = json_decode($ride->tax, true);
        $discount = $ride->discount;
        $tip = $ride->tip;
        $totalAmount = floatval($amount) - floatval($discount);
        $totalTaxAmount = 0;
        $taxHtml = '';
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
                    if ($currency->symbol_at_right == "true") {
                        $value = number_format($data['value'], $currency->decimal_digit) . "" . $currency->symbole;
                    } else {
                        $value = $currency->symbole . "" . number_format($data['value'], $currency->decimal_digit);
                    }
                }
                $totalTaxAmount += $taxValue;
                if ($currency->symbol_at_right == "true") {
                    $taxValueAmount = number_format($taxValue, $currency->decimal_digit) . "" . $currency->symbole;
                } else {
                    $taxValueAmount = $currency->symbole . "" . number_format($taxValue, $currency->decimal_digit);
                }
                $taxHtml = $taxHtml . "<tr><td class='label'>" . $taxlabel . "(" . $value . ")</td><td><span style='color:green'>+" . $taxValueAmount . "<span></td></tr>";
            }
            $totalAmount = floatval($totalAmount) + floatval($totalTaxAmount);
        }
        $totalAmount = floatval($totalAmount) + floatval($tip);
        
        $customer_review = Review::where('booking_id', $id)->where('booking_type', 'parcel')->where('review_from', 'customer')->get();
        $driver_review = Review::where('booking_id', $id)->where('booking_type', 'parcel')->where('review_from', 'driver')->get();
        
        $complaints = Complaints::select('title', 'description')->where('booking_type', 'parcel')->where('booking_id', $id)->get();
        
        $driverRating = '0.0 (0)';
        if (!empty($ride->id_conducteur)) {
            $driver = Driver::find($ride->id_conducteur);
            $driverRating = $driver ? ($driver->review_count ? $driver->average_rating.' ('.$driver->review_count.')' : '0.0 (0)' ) : '0.0 (0)';
        }
        
        $mapType = Settings::pluck('map_for_application')->first();
        
        return view("parcel_order.show")->with("ride", $ride)->with("currency", $currency)
            ->with("customer_review", $customer_review)
            ->with("complaints", $complaints)
            ->with('taxHtml', $taxHtml)
            ->with('totalAmount', $totalAmount)
            ->with('driverRating', $driverRating)
            ->with('parcel_image', $parcel_image)
            ->with('mapType', $mapType);
    }
    
    public function updateRide(Request $request, $id)
    {
        $rides = ParcelOrder::find($id);
        if (!$rides) {
            return redirect()->back()->with('error', trans('lang.ride_not_found'));
        }
        $prevoiusStatus = $rides->status;
        $driverId = $rides->id_conducteur;
        $driverData = Driver::where('id', $driverId)->first();
        if (!empty($driverData->ownerId)) {
            $driverData = Driver::where('id', $driverData->ownerId)->first();
        }
        
        if (!$driverData) {
          
            return redirect()->back()->with('error', trans('lang.driver_not_found'));
        }
        $setting = Settings::first();
        $subscriptionModel = $setting->subscription_model;
        $commissionData = Commission::first();
        $commissionModel = $commissionData->statut;
        if ($subscriptionModel == 'true' || $commissionModel == 'yes') {
            if ($request->input('order_status') == 'confirmed') {
                if ($driverData->subscriptionTotalOrders != '' && $driverData->subscriptionTotalOrders != null && intval($driverData->subscriptionTotalOrders) != '-1') {
                    if (intval($driverData->subscriptionTotalOrders) != -1 && intval($driverData->subscriptionTotalOrders) <= 0) {
                        return redirect()->back()->with('message', __('lang.upgrade_plan_limit_err'));
                    } else {
                        $remaningRides = intval($driverData->subscriptionTotalOrders) - 1;
                        Driver::where('id', $driverData->id)->update(['subscriptionTotalOrders' => $remaningRides]);
                    }
                }
            }
        }
        if ($prevoiusStatus == 'confirmed' && ($request->input('order_status') == 'canceled' || $request->input('order_status') == 'rejected')) {
            if ($subscriptionModel == 'true' || $commissionModel == 'yes') {
                if ($driverData->subscriptionTotalOrders != '' && $driverData->subscriptionTotalOrders != null && intval($driverData->subscriptionTotalOrders != '-1')) {
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
}
