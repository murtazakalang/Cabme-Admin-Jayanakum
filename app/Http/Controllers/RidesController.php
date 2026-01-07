<?php

namespace App\Http\Controllers;

use App\Models\Complaints;
use App\Models\UserApp;
use App\Models\Currency;
use App\Models\Requests;
use App\Models\Driver;
use App\Models\Settings;
use App\Models\Commission;
use App\Models\Zone;
use App\Models\Review;
use App\Models\Sos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;
use Carbon\Carbon;

class RidesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, $id = null)
    {
        $sql = DB::table('requete')
            ->leftjoin('user_app', 'requete.id_user_app', '=', 'user_app.id')
            ->leftjoin('conducteur', 'requete.id_conducteur', '=', 'conducteur.id')
            ->leftjoin('payment_method', 'requete.id_payment_method', '=', 'payment_method.id')
            ->select('requete.id', 'requete.ride_type', 'requete.dispatcher_id', 'requete.user_info', 'requete.statut', 'requete.tip_amount', 'requete.admin_commission', 'requete.tax', 'requete.discount', 'requete.statut_paiement', 'requete.depart_name', 'requete.destination_name', 'requete.distance', 'requete.montant', 'requete.creer', 'requete.booking_number', 'conducteur.id as driver_id', 'conducteur.prenom as driverPrenom', 'conducteur.nom as driverNom', 'user_app.id as user_id', 'user_app.prenom as userPrenom', 'user_app.nom as userNom', 'payment_method.libelle', 'payment_method.image')
            ->where('requete.deleted_at', '=', NULL);

        if ($id != '' || $id != null) {
            $sql->where('requete.id_conducteur', '=', $id);
        }

        if ($request->has('datepicker_from') && $request->datepicker_from != '' && $request->has('datepicker_to') && $request->datepicker_to != '') {

            $fromDate = $request->input('datepicker_from');
            $toDate = $request->input('datepicker_to');
            $sql->whereDate('requete.creer', '>=', $fromDate)
                ->whereDate('requete.creer', '<=', $toDate);

        } else if ($request->has('datepicker_from') && $request->datepicker_from != '') {

            $fromDate = $request->input('datepicker_from');
            $sql->whereDate('requete.creer', '>=', $fromDate)
                ->where('requete.deleted_at', '=', NULL);

        } else if ($request->has('datepicker_to') && $request->datepicker_to != '') {

            $toDate = $request->input('datepicker_to');
            $sql->whereDate('requete.creer', '<=', $toDate);

        } else if ($request->selected_search == 'userName' && $request->has('search') && $request->search != '') {
            $search = $request->input('search');
            $sql->where(function($q) use ($search) {
                $q->where('user_app.prenom', 'LIKE', '%' . $search . '%')
                ->orWhere('user_app.nom', 'LIKE', '%' . $search . '%')
                ->orWhere(DB::raw('CONCAT(user_app.prenom, " ", user_app.nom)'), 'LIKE', '%' . $search . '%');
            });
        }else if ($request->selected_search == 'driverName' && $request->has('search') && $request->search != '') {
            
            $search = $request->input('search');
             $sql->where(function($q) use ($search) {
                $q->where('conducteur.prenom', 'LIKE', '%' . $search . '%')
                ->orWhere('conducteur.nom', 'LIKE', '%' . $search . '%')
                ->orWhere(DB::raw('CONCAT(conducteur.prenom, " ", conducteur.nom)'), 'LIKE', '%' . $search . '%');
            });

        } else if ($request->selected_search == 'type' && $request->has('ride_type') && $request->ride_type != '') {

            $search = $request->input('ride_type');
            if ($search == "dispatcher") {
                $sql->where('requete.ride_type', 'dispatcher');
            } else if ($search == "customer") {
                $sql->where('requete.ride_type', NULL);
            }
        }else if ($request->selected_search == 'rideId' && $request->has('search') && $request->search != '') {

            $search = $request->input('search');
            $sql->where('requete.id', $search); // ride id is exact match

        } else if ($request->selected_search == 'orderId' && $request->has('search') && $request->search != '') {

            $search = $request->input('search');
            $sql->where('requete.booking_number', 'LIKE', '%' . $search . '%');
        }

        if ($request->filled('daterange')) {
            $dates = explode(' - ', $request->daterange);
            $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
            $endDate = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
            $sql->whereBetween('requete.creer', [$startDate, $endDate]);
        }

        if ($request->has('status_selector') && $request->status_selector != '') {
            $status = $request->input('status_selector');
            $sql->where('requete.statut', 'LIKE', '%' . $status . '%');
        }
        
        $totalRides = $sql->count();      
        
        $totalNewRides = (clone $sql)->where(function($q) {
            $q->where('requete.statut', 'new');
        })->count();
        $totalOnRides = (clone $sql)->where(function($q) {
            $q->where('requete.statut', 'on ride');
        })->count();
        $totalCompletedRides = (clone $sql)->where(function($q) {
            $q->where('requete.statut', 'completed');
        })->count();
        $totalCancelledRides = (clone $sql)->where(function($q) {
            $q->where('requete.statut', 'canceled')
            ->orWhere('requete.statut', 'rejected');
        })->count();
        $perPage = $request->input('per_page', 20);
        $rides = $sql->orderBy('requete.id', 'desc')->paginate($perPage)->appends($request->all());

        $currency = Currency::where('statut', 'yes')->first();

        return view("rides.index", compact('rides', 'currency', 'totalRides', 'id', 'totalNewRides', 'totalOnRides', 'totalCompletedRides','totalCancelledRides','perPage'));
    }

    public function filterRides(Request $request)
    {
        $page = $request->input('pageName');
        $fromDate = $request->input('datepicker-from');
        $toDate = $request->input('datepicker-to');
        if ($page == "allpage") {
            $rides = DB::table('requete')
                ->leftjoin('user_app', 'requete.id_user_app', '=', 'user_app.id')
                ->join('conducteur', 'requete.id_conducteur', '=', 'conducteur.id')
                ->join('payment_method', 'requete.id_payment_method', '=', 'payment_method.id')
                ->select('requete.id', 'requete.statut', 'requete.statut_paiement', 'requete.depart_name', 'requete.destination_name', 'requete.distance', 'requete.montant', 'requete.creer', 'conducteur.prenom as driverPrenom', 'conducteur.nom as driverNom', 'user_app.prenom as userPrenom', 'user_app.nom as userNom', 'payment_method.libelle')
                ->orderBy('requete.id', 'DESC')
                ->whereDate('requete.creer', '>=', $fromDate)
                ->paginate(10);
            return view("rides.all")->with("rides", $rides);
        } else {
        }
    }

    public function deleteRide($id)
    {
        if ($id != "") {
            $id = json_decode($id);
            if (is_array($id)) {
                for ($i = 0; $i < count($id); $i++) {
                    $complaint = Complaints::where('booking_id', $id[$i])->where('booking_type', 'ride');
                    if ($complaint) {
                        $complaint->delete();
                    }
                   
                    $ride = Requests::find($id[$i]);
                    $ride->delete();
                    Review::where('booking_id', $id[$i])->where('booking_type', 'ride')->delete();
                    Sos::where('ride_id', $id[$i])->delete();
                }
            } else {
                $complaint = Complaints::where('booking_id', $id)->where('booking_type', 'ride');
                if ($complaint) {
                    $complaint->delete();
                }
              
                $ride = Requests::find($id);
                $ride->delete();
                Review::where('booking_id', $id)->where('booking_type', 'ride')->delete();
                Sos::where('ride_id', $id)->delete();
            }
        }
        return redirect()->back();
    }

    public function show($id)
    {
        $currency = Currency::where('statut', 'yes')->first();
        $ride = Requests::leftjoin('user_app', 'requete.id_user_app', '=', 'user_app.id')
            ->leftjoin('conducteur', 'requete.id_conducteur', '=', 'conducteur.id')
            ->leftjoin('conducteur as owner', 'owner.id', '=', 'requete.ownerId')
            ->join('payment_method', 'requete.id_payment_method', '=', 'payment_method.id')
            ->leftjoin('vehicule', 'requete.id_conducteur', '=', 'vehicule.id_conducteur')
            ->leftjoin('brands', 'vehicule.brand', '=', 'brands.id')
            ->leftjoin('car_model', 'vehicule.model', '=', 'car_model.id')
            ->leftjoin('type_vehicule', 'requete.vehicle_type_id', '=', 'type_vehicule.id')
            ->select('requete.*')
            ->addSelect('conducteur.prenom as driverPrenom', 'conducteur.nom as driverNom', 'conducteur.phone as driver_phone', 'conducteur.email as driver_email', 'conducteur.photo_path as driver_photo')
            ->addSelect(
                'owner.prenom as ownerPrenom',
                'owner.nom as ownerNom',
                'owner.phone as owner_phone',
                'owner.email as owner_email',
                'owner.photo_path as owner_photo'
            )
            ->addSelect('user_app.prenom as userPrenom', 'user_app.nom as userNom', 'user_app.phone as user_phone', 'user_app.email as user_email', 'user_app.photo_path')
            ->addSelect('payment_method.libelle', 'payment_method.image')
            ->addSelect('vehicule.brand', 'vehicule.model', 'vehicule.car_make', 'vehicule.numberplate', 'brands.name as brand', 'car_model.name as model','vehicule.color')
            ->addSelect('type_vehicule.libelle as vehicle_type')
            ->where('requete.id', $id)
            ->first();
        if (!empty($ride['user_email'])) {
            $ride['user_email'] = Helper::shortEmail($ride['user_email']);
        }
        if (!empty($ride['user_phone'])) {
            $ride['user_phone'] = Helper::shortNumber($ride['user_phone']);
        }
        if (!empty($ride['user_info']) && $ride['user_info'] != "null") {
            $ride['user_info'] = json_encode(tap(json_decode($ride['user_info'], true), function (&$info) {
                $info['email'] = $info['email'] ? Helper::shortEmail($info['email']) : $info['email'];
                $info['phone'] = $info['phone'] ? Helper::shortNumber($info['phone']) : $info['phone'];
            }));
        }
        if (!empty($ride['driver_email'])) {
            $ride['driver_email'] = Helper::shortEmail($ride['driver_email']);
        }
        if (!empty($ride['driver_phone'])) {
            $ride['driver_phone'] = Helper::shortNumber($ride['driver_phone']);
        }
        $montant = $ride->montant;
        $tax = json_decode($ride->tax, true);
        $discount = $ride->discount;
        $tip = $ride->tip_amount;
        $totalAmount = floatval($montant) - floatval($discount);
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
        
        $customer_review = Review::where('booking_id', $id)->where('booking_type', 'ride')->where('review_from', 'customer')->get();
        $driver_review = Review::where('booking_id', $id)->where('booking_type', 'ride')->where('review_from', 'driver')->get();
        $complaints = Complaints::select('title', 'description')->where('booking_id', $id)->where('booking_type', 'ride')->get();
        
        $driverRating = '0.0 (0)';
        $ride->zone_name = '';
        if ($ride->id_conducteur) {
            $zone_name = '';
            $driver = Driver::find($ride->id_conducteur);
            if($driver){
                $driverRating = $driver->review_count ? $driver->average_rating.' ('.$driver->review_count.')' : '0.0 (0)';
                $zone_id = explode(',', $driver->zone_id);
                $zones = Zone::whereIn('id', $zone_id)->get();
                foreach ($zones as $zone) {
                    $zone_name .= $zone->name . ', ';
                }
            }
            $ride->zone_name = rtrim($zone_name, ', ');
        }

        $user = UserApp::find($ride->id_user_app);
        $userRating = $user ? ( $user->review_count ? $user->average_rating.' ('.$user->review_count.')' : '0.0 (0)' ) : '0.0 (0)';
        
        $mapType = Settings::pluck('map_for_application')->first();
        
        return view("rides.show")->with("ride", $ride)->with("currency", $currency)
            ->with("customer_review", $customer_review)
            ->with("driver_review", $driver_review)
            ->with("complaints", $complaints)
            ->with('taxHtml', $taxHtml)
            ->with('totalAmount', $totalAmount)
            ->with('driverRating', $driverRating)
            ->with('userRating', $userRating)
            ->with('mapType', $mapType);
    }

    public function updateRide(Request $request, $id)
    {
        $rides = Requests::find($id);
        if (!$rides) {
            return redirect()->back()->with('message', trans('lang.ride_not_found'));
        }

        $prevoiusStatus = $rides->statut;
        $driverId = $rides->id_conducteur;

        $driverData = Driver::where('id', $driverId)->first();
        if ($driverData && !empty($driverData->ownerId)) {
            $ownerData = Driver::where('id', $driverData->ownerId)->first();
            if ($ownerData) {
                $driverData = $ownerData;
            }
        }

        if (!$driverData) {
            return redirect()->back()->with('message', trans('lang.driver_not_found'));
        }

        $setting = Settings::first();
        $subscriptionModel = $setting->subscription_model ?? 'false';
        $commissionData = Commission::first();
        $commissionModel = $commissionData->statut ?? 'no';

        if ($subscriptionModel == 'true' || $commissionModel == 'yes') {
            if ($request->input('order_status') == 'confirmed') {
                if ($driverData->subscriptionTotalOrders !== '' 
                    && $driverData->subscriptionTotalOrders !== null 
                    && intval($driverData->subscriptionTotalOrders) != -1) {
                    
                    if (intval($driverData->subscriptionTotalOrders) != -1 && intval($driverData->subscriptionTotalOrders) <= 0) {
                        return redirect()->back()->with('message', __('lang.upgrade_plan_limit_err'));
                    } else {
                        $remaningRides = intval($driverData->subscriptionTotalOrders) - 1;
                        Driver::where('id', $driverId)->update(['subscriptionTotalOrders' => $remaningRides]);
                    }
                }
            }
        }

        if ($prevoiusStatus == 'confirmed' && in_array($request->input('order_status'), ['canceled', 'rejected'])) {
            if ($subscriptionModel == 'true' || $commissionModel == 'yes') {
                if ($driverData->subscriptionTotalOrders !== '' 
                    && $driverData->subscriptionTotalOrders !== null 
                    && intval($driverData->subscriptionTotalOrders) != -1) {
                    
                    $subscriptionTotalOrders = intval($driverData->subscriptionTotalOrders) + 1;
                    Driver::where('id', $driverId)->update(['subscriptionTotalOrders' => $subscriptionTotalOrders]);
                }
            }
        }

        if ($request->input('order_status') == 'on ride') {
            if ($driverData->driver_on_ride == 'yes') {
                return redirect()->back()->with('message', __('lang.one_ride_at_a_time_allow'));
            } else {
                Driver::where('id', $driverId)->update(['driver_on_ride' => 'yes']);
            }
        }

        if ($request->input('order_status') == 'completed') {
            Driver::where('id', $driverId)->update(['driver_on_ride' => 'no']);
        }

        $rides->statut = $request->input('order_status');
        $rides->save();

        return redirect()->back()->with('message', trans('lang.ride_updated_successfully'));
    }

}
