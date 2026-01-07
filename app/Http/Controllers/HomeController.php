<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Currency;
use App\Models\Driver;
use App\Models\Requests;
use App\Models\UserApp;
use App\Models\Vehicle;
use App\Models\ParcelOrder;
use App\Models\RentalOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {

        $last_month_start = now()->subMonth()->startOfMonth();
        $last_month_end = now()->subMonth()->endOfMonth();

        $currency = Currency::where('statut', 'yes')->first();
        $view = $request->input('view', 'year'); // default to 'year'
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($view === 'month') {
            $filter_start = date("Y-m-01 00:00:00", strtotime("$year-$month"));
            $filter_end = date("Y-m-t 23:59:59", strtotime("$year-$month"));
        } elseif ($view === 'year') {
            $filter_start = date("Y-01-01 00:00:00", strtotime("$year-01-01"));
            $filter_end = date("Y-12-31 23:59:59", strtotime("$year-12-31"));
        } elseif ($view === 'custom' && $startDate && $endDate) {
            $filter_start = date("Y-m-d 00:00:00", strtotime($startDate));
            $filter_end = date("Y-m-d 23:59:59", strtotime($endDate));
        } else {
            $filter_start = null;
            $filter_end = null;
        }
        $total_users_query = UserApp::query();
        if ($filter_start && $filter_end) {
            $total_users_query->whereBetween('creer', [$filter_start, $filter_end]);
        }
        $total_users = $total_users_query->count();
        $drivers_query = Driver::where('role', 'driver')->where('isOwner', 'false')->whereNull('ownerId');
        if ($filter_start && $filter_end) {
            $drivers_query->whereBetween('creer', [$filter_start, $filter_end]);
        }
        $total_drivers = $drivers_query->count();

        $owners_query = Driver::leftJoin('vehicule', 'vehicule.id_conducteur', '=', 'conducteur.id')
            ->leftJoin('type_vehicule', 'type_vehicule.id', '=', 'vehicule.id_type_vehicule')
            ->where('conducteur.role', '=', 'owner');

        if ($filter_start && $filter_end) {
            $owners_query->whereBetween('conducteur.creer', [$filter_start, $filter_end]);
        }

        $total_owners = $owners_query->count();

        // Total Fleet Drivers
        $fleet_query = Driver::leftJoin('vehicule', 'vehicule.id_conducteur', '=', 'conducteur.id')
            ->leftJoin('conducteur as owner', 'conducteur.ownerId', '=', 'owner.id')
            ->leftJoin('type_vehicule', 'type_vehicule.id', '=', 'vehicule.id_type_vehicule')
            ->where('conducteur.role', '=', 'driver')
            ->whereNotNull('conducteur.ownerId')
            ->where('conducteur.ownerId', '!=', '');

        if ($filter_start && $filter_end) {
            $fleet_query->whereBetween('conducteur.creer', [$filter_start, $filter_end]);
        }

        $total_fleet_drivers = $fleet_query->count();

        // Current totals
        $total_users = $total_users_query->count();
        $total_drivers = $drivers_query->count();
        $total_owners = $owners_query->count();
        $total_fleet_drivers = $fleet_query->count();

        $current_total = $total_users + $total_drivers + $total_owners + $total_fleet_drivers;

        // Previous period totals
        $prev_users = UserApp::whereBetween('creer', [$last_month_start, $last_month_end])->count();
        $prev_drivers = Driver::where('role', 'driver')->where('isOwner', 'false')->whereNull('ownerId')
            ->whereBetween('creer', [$last_month_start, $last_month_end])->count();
        $prev_owners = Driver::where('role', 'owner')
            ->whereBetween('creer', [$last_month_start, $last_month_end])->count();
        $prev_fleet_drivers = Driver::whereNotNull('ownerId')->where('ownerId', '!=', '')
            ->where('role', 'driver')
            ->whereBetween('creer', [$last_month_start, $last_month_end])->count();

        $previous_total = $prev_users + $prev_drivers + $prev_owners + $prev_fleet_drivers;

        // Percentage change calculation
        if ($previous_total == 0) {
            $total_users_change = $current_total > 0 ? 100 : 0;
        } else {
            $total_users_change = round((($current_total - $previous_total) / $previous_total) * 100, 2);
        }

        $total_rides_query = Requests::query();
        if ($filter_start && $filter_end) {
            $total_rides_query->whereBetween('creer', [$filter_start, $filter_end]);
        }
        $total_rides = $total_rides_query->count();

        $total_parcel_query = ParcelOrder::query();
        if ($filter_start && $filter_end) {
            $total_parcel_query->whereBetween('created_at', [$filter_start, $filter_end]);
        }
        $total_parcel = $total_parcel_query->count();

        $total_rental_query = RentalOrder::query();
        if ($filter_start && $filter_end) {
            $total_rental_query->whereBetween('created_at', [$filter_start, $filter_end]);
        }
        $total_rental = $total_rental_query->count();

        // Current totals
        $total_rides = $total_rides_query->count();
        $total_parcel = $total_parcel_query->count();
        $total_rental = $total_rental_query->count();

        $current_total = $total_rides + $total_parcel + $total_rental;

        // Previous period totals
        $prev_rides = Requests::where('statut', 'completed')
            ->whereBetween('creer', [$last_month_start, $last_month_end])
            ->count();
        $prev_parcel = ParcelOrder::where('status', 'completed')
            ->whereBetween('created_at', [$last_month_start, $last_month_end])
            ->count();
        $prev_rental = RentalOrder::where('status', 'completed')
            ->whereBetween('created_at', [$last_month_start, $last_month_end])
            ->count();

        $previous_total = $prev_rides + $prev_parcel + $prev_rental;

        if ($previous_total == 0) {
            $rides_parcels_rentals_change = $current_total > 0 ? 100 : 0;
        } else {
            $rides_parcels_rentals_change = round((($current_total - $previous_total) / $previous_total) * 100, 2);
        }

        $new_rides = Requests::where('statut', 'new')->count('id');
        $on_rides = Requests::where('statut', 'on ride')->count('id');

        $confirmed_ride_rides = Requests::where('statut', 'confirmed')
            ->whereNull('deleted_at')
            ->count();

        $confirmed_parcel_rides = ParcelOrder::
            where('status', 'confirmed')
            ->count();

        $confirmed_rental_rides = RentalOrder::
            where('status', 'confirmed')
            ->count();

        $confirmed_rides = $confirmed_ride_rides + $confirmed_parcel_rides + $confirmed_rental_rides;


        $completed_ride_rides = Requests::leftjoin('user_app', 'requete.id_user_app', '=', 'user_app.id')
            ->join('conducteur', 'requete.id_conducteur', '=', 'conducteur.id')
            ->join('payment_method', 'requete.id_payment_method', '=', 'payment_method.id')
            ->where('requete.deleted_at', '=', NULL)
            ->where('requete.statut', 'completed')->count('requete.id');

        $completed_parcel_rides = ParcelOrder::
            where('status', 'completed')->count();

        $completed_rental_rides = RentalOrder::where('status', 'completed')->count();

        $completed_rides = $completed_ride_rides + $completed_parcel_rides + $completed_rental_rides;


        $canceled_ride_rides = Requests::leftjoin('user_app', 'requete.id_user_app', '=', 'user_app.id')
            ->join('conducteur', 'requete.id_conducteur', '=', 'conducteur.id')
            ->join('payment_method', 'requete.id_payment_method', '=', 'payment_method.id')
            ->where('requete.statut', 'canceled')
            ->orwhere('requete.statut', 'rejected')
            ->where('requete.deleted_at', '=', NULL)->count('requete.id');

        $canceled_parcel_rides = ParcelOrder::leftjoin('user_app', 'parcel_orders.id_user_app', '=', 'user_app.id')
            ->join('conducteur', 'parcel_orders.id_conducteur', '=', 'conducteur.id')
            ->join('payment_method', 'parcel_orders.id_payment_method', '=', 'payment_method.id')
            ->where('parcel_orders.status', 'canceled')
            ->orwhere('parcel_orders.status', 'rejected')
            ->count('parcel_orders.id');
        $canceled_rental_rides = RentalOrder::leftjoin('user_app', 'rental_orders.id_user_app', '=', 'user_app.id')
            ->join('conducteur', 'rental_orders.id_conducteur', '=', 'conducteur.id')
            ->join('payment_method', 'rental_orders.id_payment_method', '=', 'payment_method.id')
            ->where('rental_orders.status', 'canceled')
            ->orwhere('rental_orders.status', 'rejected')
            ->count('rental_orders.id');

        $canceled_rides = $canceled_ride_rides + $canceled_parcel_rides + $canceled_rental_rides;

        $total_ride_commission_query = Requests::where('statut', 'completed');
        if ($filter_start && $filter_end) {
            $total_ride_commission_query->whereBetween('creer', [$filter_start, $filter_end]);
        }
        $total_ride_commission = $total_ride_commission_query->sum('admin_commission');

        $total_parcel_admin_commission_query = ParcelOrder::where('status', 'completed');
        if ($filter_start && $filter_end) {
            $total_parcel_admin_commission_query->whereBetween('created_at', [$filter_start, $filter_end]);
        }
        $total_parcel_admin_commission = $total_parcel_admin_commission_query->sum('admin_commission');

        $total_rental_admin_commission_query = RentalOrder::where('status', 'completed');
        if ($filter_start && $filter_end) {
            $total_rental_admin_commission_query->whereBetween('created_at', [$filter_start, $filter_end]);
        }
        $total_rental_admin_commission = $total_rental_admin_commission_query->sum('admin_commission');
        $total_admin_commission = $total_ride_commission + $total_parcel_admin_commission + $total_rental_admin_commission;

        // Previous period commissions (last month)

        // Previous month date range is already defined
        $prev_ride_commission_query = Requests::where('statut', 'completed')
            ->whereBetween('creer', [$last_month_start, $last_month_end]);
        $prev_ride_commission = $prev_ride_commission_query->sum('admin_commission');

        $prev_parcel_commission_query = ParcelOrder::where('status', 'completed')
            ->whereBetween('created_at', [$last_month_start, $last_month_end]);
        $prev_parcel_commission = $prev_parcel_commission_query->sum('admin_commission');

        $prev_rental_commission_query = RentalOrder::where('status', 'completed')
            ->whereBetween('created_at', [$last_month_start, $last_month_end]);
        $prev_rental_commission = $prev_rental_commission_query->sum('admin_commission');

        // Total previous admin commission
        $previous_admin_commission = $prev_ride_commission + $prev_parcel_commission + $prev_rental_commission;

        // Percentage change
        if ($previous_admin_commission == 0) {
            $percentage_change_admin_commission = $total_admin_commission > 0 ? 100 : 0;
        } else {
            $percentage_change_admin_commission = (($total_admin_commission - $previous_admin_commission) / $previous_admin_commission) * 100;
        }

        $day = date('w');
        $week_start = date('Y-m-d', strtotime('-' . $day . ' days'));
        $week_end = date('Y-m-d', strtotime('+' . (6 - $day) . ' days'));
        $week_start = date('Y-m-d', strtotime($week_start . ' +1 day'));
        $week_end = date('Y-m-d', strtotime($week_end . ' +1 day'));
        $commitionforweek = Commission::where('statut', 'yes')->whereBetween('creer', [$week_start, $week_end])->sum('value');

        $date_heure = date('Y-m-d');
        $date_start = date("Y-m-d", strtotime(date('Y-m-1')));
        $date_end = date("Y-m-t", strtotime($date_heure));
        $commitionformonth = Commission::where('statut', 'yes')->whereBetween('creer', [$date_start, $date_end])->sum('value');

        $drivers_query = Driver::query();
        $active_drivers_query = Driver::query();

        if ($filter_start && $filter_end) {
            $drivers_query->whereBetween('creer', [$filter_start, $filter_end]);
            $active_drivers_query->whereBetween('creer', [$filter_start, $filter_end]);
        }

        $drivers = $drivers_query->where('statut', 'no')->get();

        $active_drivers = $active_drivers_query->where('statut', 'yes')->inRandomOrder()->limit(10)->get();

        $latest_rides_query = Requests::leftJoin('user_app', 'requete.id_user_app', '=', 'user_app.id')
            ->join('conducteur', 'requete.id_conducteur', '=', 'conducteur.id')
            ->join('payment_method', 'requete.id_payment_method', '=', 'payment_method.id')
            ->select(
                'requete.id',
                'requete.statut',
                'requete.statut_paiement',
                'requete.depart_name',
                'requete.destination_name',
                'requete.distance',
                'requete.montant',
                'requete.creer',
                'conducteur.id as driver_id',
                'conducteur.prenom as driverPrenom',
                'conducteur.nom as driverNom',
                'user_app.id as user_id',
                'user_app.prenom as userPrenom',
                'user_app.nom as userNom',
                'payment_method.libelle',
                'payment_method.image'
            )
            ->where('requete.statut', 'completed');

        if (!empty($filter_start) && !empty($filter_end)) {

            $latest_rides_query->whereBetween('requete.creer', [$filter_start, $filter_end]);
        }

        $latest_rides = $latest_rides_query->inRandomOrder()->limit(10)->get();

        if ($filter_start && $filter_end) {
            $total_parcel_earnings = $this->getParcelTotalEarnings(null, $filter_start, $filter_end);
        } else {
            $total_parcel_earnings = $this->getParcelTotalEarnings();
        }

        if (!empty($filter_start) && !empty($filter_end)) {
            $total_rental_earnings = $this->getRentalTotalEarnings(null, $filter_start, $filter_end);
        } else {
            $total_rental_earnings = $this->getRentalTotalEarnings();
        }
        if (!empty($filter_start) && !empty($filter_end)) {
            $total_ride_earnings = $this->getTotalEarnings(null, $filter_start, $filter_end);
        } else {
            $total_ride_earnings = $this->getTotalEarnings();
        }

        $total_earnings = $total_parcel_earnings + $total_rental_earnings + $total_ride_earnings;

        // Current total earnings
        $total_earnings = $total_parcel_earnings + $total_rental_earnings + $total_ride_earnings;


        $prev_start = date('Y-m-01 00:00:00', strtotime('-1 month'));
        $prev_end = date('Y-m-t 23:59:59', strtotime('-1 month'));

        $prev_parcel_earnings = $this->getParcelTotalEarnings(null, $prev_start, $prev_end);
        $prev_rental_earnings = $this->getRentalTotalEarnings(null, $prev_start, $prev_end);
        $prev_ride_earnings = $this->getTotalEarnings(null, $prev_start, $prev_end);

        $previous_total_earnings = $prev_parcel_earnings + $prev_rental_earnings + $prev_ride_earnings;

        // Calculate percentage change
        if ($previous_total_earnings == 0) {
            $earnings_percentage_change = $total_earnings > 0 ? 100 : 0;
        } else {
            $earnings_percentage_change = (($total_earnings - $previous_total_earnings) / $previous_total_earnings) * 100;
        }

        $earnings_percentage_change = round($earnings_percentage_change, 2);

        $admin_commision = $currency->symbole . '0';

        $vehicles = Vehicle::leftjoin('type_vehicule', 'type_vehicule.id', '=', 'vehicule.id_type_vehicule')->where('statut', 'yes')->groupBy('brand')->inRandomOrder()->limit(10)->get();

        $rides_query = DB::table('requete')
            ->leftJoin('user_app', 'requete.id_user_app', '=', 'user_app.id')
            ->leftJoin('conducteur', 'requete.id_conducteur', '=', 'conducteur.id')
            ->leftJoin('payment_method', 'requete.id_payment_method', '=', 'payment_method.id')
            ->select(
                'requete.id',
                'requete.ride_type',
                'requete.dispatcher_id',
                'requete.user_info',
                'requete.statut',
                'requete.tip_amount',
                'requete.admin_commission',
                'requete.tax',
                'requete.discount',
                'requete.statut_paiement',
                'requete.depart_name',
                'requete.destination_name',
                'requete.distance',
                'requete.montant',
                'requete.creer',
                'requete.booking_number',
                'conducteur.id as driver_id',
                'conducteur.prenom as driverPrenom',
                'conducteur.nom as driverNom',
                'user_app.id as user_id',
                'user_app.prenom as userPrenom',
                'user_app.nom as userNom',
                'payment_method.libelle',
                'payment_method.image'
            )
            ->whereNull('requete.deleted_at');

        // Apply filter if available
        if (!empty($filter_start) && !empty($filter_end)) {
            $rides_query->whereBetween('requete.creer', [$filter_start, $filter_end]);
        }

        $rides = $rides_query->orderBy('requete.creer', 'desc')
            ->limit(10)
            ->get();

        $topDriversQuery = DB::table('requete')
            ->select(
                'conducteur.id',
                'conducteur.prenom',
                'conducteur.nom',
                'conducteur.email',
                'conducteur.phone',
                'conducteur.photo_path',
                'conducteur.is_verified',
                DB::raw('COUNT(requete.id) as total_rides')
            )
            ->join('conducteur', 'requete.id_conducteur', '=', 'conducteur.id')
            ->whereNotNull('requete.id_conducteur');

        // Apply date filter if set
        if (!empty($filter_start) && !empty($filter_end)) {
            $topDriversQuery->whereBetween('requete.creer', [$filter_start, $filter_end]);
        }

        $topDrivers = $topDriversQuery
            ->groupBy('conducteur.id', 'conducteur.prenom', 'conducteur.nom', 'conducteur.email', 'conducteur.phone', 'conducteur.photo_path', 'conducteur.is_verified')
            ->orderByDesc('total_rides')
            ->limit(10)
            ->get();

        // Shorten email and phone
        $topDrivers->map(function ($driver) {
            if (!empty($driver->email)) {
                $driver->email = Helper::shortEmail($driver->email);
            }
            if (!empty($driver->phone)) {
                $driver->phone = Helper::shortNumber($driver->phone);
            }
            return $driver;
        });

        return view('home')->with("total_users", $total_users)
            ->with("total_drivers", $total_drivers)
            ->with("vehicles", $vehicles)
            ->with("new_rides", $new_rides)
            ->with("on_rides", $on_rides)
            ->with("confirmed_rides", $confirmed_rides)
            ->with("confirmed_parcel_rides", $confirmed_parcel_rides)
            ->with("completed_rides", $completed_rides)
            ->with("completed_parcel_rides", $completed_parcel_rides)
            ->with("canceled_rides", $canceled_rides)
            ->with("canceled_parcel_rides", $canceled_parcel_rides)
            ->with("commitionforweek", $commitionforweek)
            ->with("commitionformonth", $commitionformonth)
            ->with("currency", $currency)
            ->with("drivers", $drivers)
            ->with("active_drivers", $active_drivers)
            ->with("total_earnings", $total_earnings)
            ->with("total_parcel_earnings", $total_parcel_earnings)
            ->with("rides", $rides)
            ->with("admin_commision", $admin_commision)
            ->with('total_admin_commission', $total_admin_commission)
            ->with('total_parcel_admin_commission', $total_parcel_admin_commission)
            ->with('percentage_change_admin_commission', $percentage_change_admin_commission)
            ->with('latest_rides', $latest_rides)
            ->with('topDrivers', $topDrivers)
            ->with('total_rental_earnings', $total_rental_earnings)
            ->with('total_rental_admin_commission', $total_rental_admin_commission)
            ->with('completed_rental_rides', $completed_rental_rides)
            ->with('confirmed_rental_rides', $confirmed_rental_rides)
            ->with('canceled_rental_rides', $canceled_rental_rides)
            ->with('total_ride_earnings', $total_ride_earnings)
            ->with('total_ride_commission', $total_ride_commission)
            ->with('completed_ride_rides', $completed_ride_rides)
            ->with('confirmed_ride_rides', $confirmed_ride_rides)
            ->with('canceled_ride_rides', $canceled_ride_rides)
            ->with('total_owners', $total_owners)
            ->with('total_fleet_drivers', $total_fleet_drivers)
            ->with('total_rides', $total_rides)
            ->with('total_parcel', $total_parcel)
            ->with('total_rental', $total_rental)
            ->with('rides_parcels_rentals_change', $rides_parcels_rentals_change)
            ->with('total_users_change', $total_users_change)
            ->with('earnings_percentage_change', $earnings_percentage_change)
            ->with('view', $view)
            ->with('month', $month)
            ->with('year', $year)
            ->with('start_date', $startDate)
            ->with('end_date', $endDate);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function welcome()
    {
        return view('welcome');
    }

    public function dashboard()
    {
        return view('dashboard');
    }

    public function users()
    {
        return view('users');
    }

    public function updateDriverStatus(Request $request, $id)
    {
        $driver = Driver::find($id);
        if ($driver) {
            $driver->statut = 'yes';
        }
        $driver->save();
        return redirect()->back();
    }

    public function getTotalEarnings($type = null, $date_start = null, $date_end = null)
    {
        $trip = Requests::where('statut', 'completed');
        if ($type == "today") {
            $today_start = date('Y-m-d 00:00:00');
            $today_end = date('Y-m-d 23:59:59');
            $trip->whereBetween('creer', [$today_start, $today_end]);
        }
        if (!empty($date_start) && !empty($date_end)) {
            $start = date('Y-m-d 00:00:00', strtotime($date_start));
            $end = date('Y-m-d 23:59:59', strtotime($date_end));

            $trip->whereBetween('creer', [$start, $end]);
        }
        $trip = $trip->get();

        $total_earning = 0;
        foreach ($trip as $value) {
            $totalAmount = 0;
            $totalAmount = $totalAmount + floatval($value->montant);
            $totalAmount = $totalAmount - floatval($value->discount);
            $tax = json_decode($value->tax, true);
            $totalTaxAmount = 0;

            if (!empty($tax)) {
                for ($i = 0; $i < sizeof($tax); $i++) {
                    $data = $tax[$i];
                    if ($data['type'] == "Percentage") {
                        $taxValue = (floatval($data['value']) * $totalAmount) / 100;
                    } else {
                        $taxValue = floatval($data['value']);
                    }
                    $totalTaxAmount += floatval($taxValue);
                }
                $totalAmount = floatval($totalAmount) + floatval($totalTaxAmount);
            }
            $total_earning += $totalAmount;

            if (!empty($value->tip_amount)) {
                $total_earning += floatval($value->tip_amount);
            }
        }
        return $total_earning;
    }
    public function getParcelTotalEarnings($type = null, $date_start = null, $date_end = null)
    {
        $trip = ParcelOrder::where('status', 'completed');
        if ($type == "today") {
            $today_start = date('Y-m-d 00:00:00');
            $today_end = date('Y-m-d 23:59:59');
            $trip->whereBetween('created_at', [$today_start, $today_end]);
        }
        if (!empty($date_start) && !empty($date_end)) {
            $start = date('Y-m-d 00:00:00', strtotime($date_start));
            $end = date('Y-m-d 23:59:59', strtotime($date_end));

            $trip->whereBetween('created_at', [$date_start, $date_end]);
        }

        $trip = $trip->get();

        $total_earning = 0;
        foreach ($trip as $value) {
            $totalAmount = floatval($value->amount) - floatval($value->discount);

            // Handle tax
            $tax = json_decode($value->tax, true);
            $totalTaxAmount = 0;
            if (!empty($tax)) {
                foreach ($tax as $data) {
                    $taxValue = $data['type'] === 'Percentage'
                        ? ($data['value'] * $totalAmount) / 100
                        : floatval($data['value']);
                    $totalTaxAmount += $taxValue;
                }
                $totalAmount += $totalTaxAmount;
            }

            // Add tip if exists
            if (!empty($value->tip_amount)) {
                $totalAmount += floatval($value->tip_amount);
            }

            $total_earning += $totalAmount;
        }

        return $total_earning;
    }

    public function getRentalTotalEarnings($type = null, $date_start = null, $date_end = null)
    {
        $trip = RentalOrder::where('status', 'completed');
        if ($type == "today") {
            $today_start = date('Y-m-d 00:00:00');
            $today_end = date('Y-m-d 23:59:59');
            $trip->whereBetween('created_at', [$today_start, $today_end]);
        }
        if (!empty($date_start) && !empty($date_end)) {
            $start = date('Y-m-d 00:00:00', strtotime($date_start));
            $end = date('Y-m-d 23:59:59', strtotime($date_end));

            $trip->whereBetween('created_at', [$start, $end]); // use $start and $end
        }


        $trip = $trip->get();

        $total_earning = 0;
        foreach ($trip as $value) {
            $totalAmount = 0;
            $totalAmount = $totalAmount + floatval($value->amount);
            $totalAmount = $totalAmount - floatval($value->discount);
            $tax = json_decode($value->tax, true);

            $totalTaxAmount = 0;
            if (!empty($tax)) {
                for ($i = 0; $i < sizeof($tax); $i++) {
                    $data = $tax[$i];
                    if ($data['type'] == "Percentage") {
                        $taxValue = (floatval($data['value']) * $totalAmount) / 100;
                    } else {
                        $taxValue = floatval($data['value']);
                    }
                    $totalTaxAmount += floatval($taxValue);
                }
                $totalAmount = floatval($totalAmount) + floatval($totalTaxAmount);
            }
            $total_earning += $totalAmount;
        }
        return $total_earning;
    }

    public function getSalesOverview(Request $request)
    {
        $view = $request->input('view');
        $year = $request->input('year', date('Y'));
        $month = $request->input('month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $monthlySalesRides = array_fill(1, 12, 0);
        $monthlySalesParcel = array_fill(1, 12, 0);
        $monthlySalesRental = array_fill(1, 12, 0);
        $orders = Requests::where('statut', 'completed');
        $parcelOrders = ParcelOrder::where('status', 'completed');
        $rentalOrders = RentalOrder::where('status', 'completed');
        $currency = Currency::where('statut', 'yes')->first();
        if ($view === 'year') {
            $orders->whereYear('creer', $year);
            $parcelOrders->whereYear('created_at', $year);
            $rentalOrders->whereYear('created_at', $year);
        } elseif ($view === 'month') {
            $orders->whereYear('creer', $year)->whereMonth('creer', $month);
            $parcelOrders->whereYear('created_at', $year)->whereMonth('created_at', $month);
            $rentalOrders->whereYear('created_at', $year)->whereMonth('created_at', $month);
        } elseif ($view === 'custom') {

            if ($startDate && $endDate) {
                $orders->whereBetween('creer', [
                    $startDate . ' 00:00:00',
                    $endDate . ' 23:59:59'
                ]);
                $parcelOrders->whereBetween('created_at', [
                    $startDate . ' 00:00:00',
                    $endDate . ' 23:59:59'
                ]);
                $rentalOrders->whereBetween('created_at', [
                    $startDate . ' 00:00:00',
                    $endDate . ' 23:59:59'
                ]);
            }
        }
        $orders = $orders->get();
        $parcelOrders = $parcelOrders->get();
        $rentalOrders = $rentalOrders->get();
        foreach ($orders as $order) {
            $price = floatval($order->montant) - intval($order->discount);
            $tax = json_decode($order->tax, true);
            $totalTaxAmount = 0;

            if (!empty($tax)) {
                for ($i = 0; $i < sizeof($tax); $i++) {
                    $data = $tax[$i];

                    if ($data['type'] == 'Percentage') {
                        $taxValue = (floatval($data['value']) * $price) / 100;
                    } else {
                        $taxValue = floatval($data['value']);
                    }

                    $totalTaxAmount += floatval($taxValue);
                }


                $price = floatval($price) + $totalTaxAmount;
            }
            if ($order->tip_amount) {
                $price = floatval($price) + floatval($order->tip_amount);
            }
            $price = round($price, $currency->decimal_digit);
            $monthRides = (int) date('m', strtotime($order->creer));
            $monthlySalesRides[$monthRides] += $price;
        }
        foreach ($parcelOrders as $parcel) {
            $parcelPrice = floatval($parcel->amount) - intval($parcel->discount);
            $tax = json_decode($parcel->tax, true);
            $totalTaxAmount = 0;

            if (!empty($tax)) {
                for ($i = 0; $i < sizeof($tax); $i++) {
                    $data = $tax[$i];

                    if ($data['type'] == 'Percentage') {
                        $taxValue = (floatval($data['value']) * $parcelPrice) / 100;
                    } else {
                        $taxValue = floatval($data['value']);
                    }

                    $totalTaxAmount += floatval($taxValue);
                }


                $parcelPrice = floatval($parcelPrice) + $totalTaxAmount;
            }
            if ($parcel->tip) {
                $parcelPrice = floatval($parcelPrice) + floatval($parcel->tip);
            }

            $parcelPrice = number_format($parcelPrice, $currency->decimal_digit);
            $monthParcel = (int) date('m', strtotime($parcel->created_at));
            $monthlySalesParcel[$monthParcel] += (float) $parcelPrice;
        }

        foreach ($rentalOrders as $rental) {
            $rentalPrice = floatval($rental->amount) - intval($rental->discount);
            $tax = json_decode($rental->tax, true);
            $totalTaxAmount = 0;

            if (!empty($tax)) {
                for ($i = 0; $i < sizeof($tax); $i++) {
                    $data = $tax[$i];
                    if ($data['type'] == 'Percentage') {
                        $taxValue = (floatval($data['value']) * $rentalPrice) / 100;
                    } else {
                        $taxValue = floatval($data['value']);
                    }
                    $totalTaxAmount += floatval($taxValue);
                }
                $rentalPrice = floatval($rentalPrice) + $totalTaxAmount;
            }

            if ($rental->tip) {
                $rentalPrice = floatval($rentalPrice) + floatval($rental->tip);
            }

            $rentalPrice = floatval($rentalPrice);
            $monthRental = (int) date('m', strtotime($rental->created_at));
            $monthlySalesRental[$monthRental] += round($rentalPrice, $currency->decimal_digit);
        }

        $rideData = [];
        $parcelData = [];
        $rentalData = [];

        foreach (range(1, 12) as $i) {
            $rideData["v{$i}"] = $monthlySalesRides[$i];
            $parcelData["v{$i}"] = $monthlySalesParcel[$i];
            $rentalData["v{$i}"] = $monthlySalesRental[$i];
        }

        return response()->json([
            'ride' => $rideData,
            'parcel' => $parcelData,
            'rental' => $rentalData,
        ]);
    }


    
}