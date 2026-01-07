<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Country;
use App\Models\Driver;
use App\Models\DispatcherUser;
use App\Models\Requests;
use App\Models\ParcelOrder;
use App\Models\Vehicle;
use App\Models\DriversDocuments;
use App\Models\Brand;
use App\Models\CarModel;
use App\Models\VehicleType;
use App\Models\RentalOrder;
use App\Models\Zone;
use App\Models\SubscriptionHistory;
use App\Models\AccessToken;
use App\Models\Commission;
use App\Models\SubscriptionPlan;
use App\Models\Settings;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Image;
use App\Helpers\Helper;
use Carbon\Carbon;

class OwnersController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {

        $query = Driver::where('role', 'owner')->where('isOwner', 'true');
        
        if ($request->filled('search') && $request->filled('selected_search')) {
            $keyword = $request->input('search');
            $field   = $request->input('selected_search');
            $query->where(function ($q) use ($field, $keyword) {
                if ($field == "prenom") {
                    $q->where('prenom', 'LIKE', '%' . $keyword . '%')
                    ->orWhere(DB::raw('CONCAT(nom, " ", prenom)'), 'LIKE', '%' . $keyword . '%');
                } else {
                    $q->where($field, 'LIKE', '%' . $keyword . '%');
                }
            });
        }

        if ($request->is('owners/approved')) {
            $query->where('is_verified', 1);
        } elseif ($request->is('owners/pending')) {
            $query->where('is_verified', 0);
        }

        if ($request->filled('daterange')) {
            $dates = explode(' - ', $request->daterange);
            $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
            $endDate = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
            $query->whereBetween('creer', [$startDate, $endDate]);
        }
        if ($request->has('status_selector') && $request->status_selector != '') {
            $status = $request->input('status_selector');
            $status == 'active' ? $query->where('statut', 'yes') : $query->where('statut', 'no');
        }

        $totalRecords = $query->get();
        $totalLength = count($totalRecords);
        $perPage = $request->input('per_page', 20);
        $owners = $query->orderBy('id', 'desc')->paginate($perPage)->appends($request->all());

        $owners->map(function ($owner) {
            if (!empty($owner->email)) {
                $owner->email = Helper::shortEmail($owner->email);
            }
            if (!empty($owner->phone)) {
                $owner->phone = Helper::shortNumber($owner->phone);
            }
            return $owner;
        });
        return view("owners.index", compact('owners', 'totalLength','perPage'));
    }

    public function create()
    {

        $zones = Zone::where('status', 'yes')->get();
        $countries = Country::where('statut', 'yes')
        ->select('id', 'code', 'libelle','phone','statut')
        ->get();
        return view('owners.create')->with('zones', $zones)->with('countries', $countries);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), $rules = [
            'nom' => 'required',
            'prenom' => 'required',
            'password' => 'required',
            'phone' => 'required|unique:conducteur,phone',
            'email' => 'required|email|unique:conducteur,email',  
            'country_code' => 'required',
            'service_type' => 'required',
        ], $messages = [
            'nom.required' => trans('lang.the_first_name_field_is_required'),
            'prenom.required' => trans('lang.the_last_name_field_is_required'),
            'email.required' => trans('lang.the_email_field_is_required'),
            'email.unique' => trans('lang.the_email_field_is_should_be_unique'),
            'password.required' => trans('lang.the_password_field_is_required'),
            'phone.required' => trans('lang.the_phone_is_required'),
            'phone.unique' => trans('lang.the_phone_field_is_should_be_unique'),
            'country_code.required' => trans('lang.the_country_code_field_is_required'),
            'service_type.required' => trans('lang.please_select_service'),
        ]);

        if ($validator->fails()) {
            return redirect('owners/create')
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }
        
        $service_type = $request->input('service_type');

        $get_admin_commission = Commission::first();
        $settings = Settings::first();

        $commissionObj = ['type' => $get_admin_commission->type, 'value' => $get_admin_commission->value];
        $user = new Driver;
        $user->nom = $request->input('nom');
        $user->prenom = $request->input('prenom');
        $user->email = $request->input('email');
        $user->statut = $request->has('statut') ? 'yes' : 'no';
        $user->statut_vehicule = $request->has('statut') ? 'yes' : 'no';
        $user->online = 'no';
        $user->status_car_image = 'yes';
        $user->login_type = 'email';
        $user->device_id = $request->input('device_id');
        $user->mdp = Hash::make($request->input('password'));
        $user->country_code = '+'. $request->input('country_code');
        $user->phone = $request->input('phone');
        $user->creer = date('Y-m-d H:i:s');
        $user->modifier = date('Y-m-d H:i:s');
        $user->updated_at = date('Y-m-d H:i:s');
        $user->bank_name = $request->input('bank_name');
        $user->holder_name = $request->input('holder_name');
        $user->account_no = $request->input('account_number');
        $user->branch_name = $request->input('branch_name');
        $user->other_info = $request->input('other_information');
        $user->ifsc_code = $request->input('ifsc_code');
        $user->amount = "0";
        $user->driver_on_ride = "no";
        $user->adminCommission = $commissionObj;
        $user->service_type = $service_type ? implode(',', $service_type) : NULL;
        $user->is_verified = ($settings->owner_doc_verification == "yes") ? '0' : '1';
       
        if ($request->hasfile('photo')) {
            $file = $request->file('photo');
            $extenstion = $file->getClientOriginalExtension();
            $time = time() . '.' . $extenstion;
            $filename = 'driver_image_' . $time;
            $path = public_path('assets/images/driver/') . $filename;
            if (!file_exists(public_path('assets/images/driver/'))) {
                mkdir(public_path('assets/images/driver/'), 0777, true);
            }
            Image::make($file->getRealPath())->resize(150, 150)->save($path);
            $image = str_replace('data:image/png;base64,', '', $file);
            $image = str_replace(' ', '+', $image);
            $user->photo_path = $filename;
        }
        
        $user->role = 'owner';
        $user->isOwner = 'true';
        $user->save();

        //Create dispatcher user
        $dispatcher_user = new DispatcherUser;
        $dispatcher_user->first_name = $request->input('nom');
        $dispatcher_user->last_name = $request->input('prenom');
        $dispatcher_user->email = $request->input('email');
        $dispatcher_user->password = Hash::make($request->input('password'));
        $dispatcher_user->phone = $request->input('phone');
        $dispatcher_user->country_code = '+'. $request->input('country_code');
        $dispatcher_user->status = $request->has('statut') ? 'yes' : 'no';
        $dispatcher_user->isOwner = 'yes';
        $dispatcher_user->ownerId = $user->id;
        $dispatcher_user->created_at = date('Y-m-d H:i:s');
        $dispatcher_user->updated_at = date('Y-m-d H:i:s');
        if ($request->hasfile('photo')) {
            $file = $request->file('photo');
            $extenstion = $file->getClientOriginalExtension();
            $time = time() . '.' . $extenstion;
            $filename = 'dispatcher_user_profile' . $time;
            $path = public_path('assets/images/dispatcher_users/') . $filename;
            if (!file_exists(public_path('assets/images/dispatcher_users/'))) {
                mkdir(public_path('assets/images/dispatcher_users/'), 0777, true);
            }
            Image::make($file->getRealPath())->resize(150, 150)->save($path);
            $dispatcher_user->profile_picture_path = asset('assets/images/dispatcher_users/' . $filename);
        }
        $dispatcher_user->save();

        return redirect('owners')->with('message', trans('lang.owner_created_successfully'));
    }
    public function edit($id)
    {       
        $owner = Driver::where('id', "=", $id)->first();
        $countries = Country::where('statut', 'yes')
        ->select('id', 'code', 'libelle','phone','statut')
        ->get();

        if (!empty($owner['email'])) {
            $owner['email'] = Helper::shortEmail($owner['email']);
        }
        if (!empty($owner['phone'])) {
            $owner['phone'] = Helper::shortNumber($owner['phone']);
        }

        $currency = Currency::where('statut', 'yes')->first();

        return view('owners.edit')->with('owner', $owner)
            ->with('currency', $currency)            
            ->with('countries', $countries);
    }
    public function updateOwner(Request $request, $id)
    {

        if ($request->id > 0) {
            $image_validation = "mimes:jpeg,jpg,png";
            $doc_validation = "mimes:doc,pdf,docx,zip,txt";
        } else {
            $image_validation = "required|mimes:jpeg,jpg,png";
            $doc_validation = "required|mimes:doc,pdf,docx,zip,txt";
        }

        $validator = Validator::make($request->all(), $rules = [
            'nom' => 'required',
            'prenom' => 'required',        
         
            'commission_type' => 'required',
            'commission_value' => 'required'
        ], $messages = [
            'nom.required' => trans('lang.the_first_name_field_is_required'),
            'prenom.required' => trans('lang.the_last_name_field_is_required'),           
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }


        $nom = $request->input('nom');
        $prenom = $request->input('prenom');
        $phone = $request->input('phone');
        $device_id = $request->input('device_id');
        $status = $request->input('statut');
        $bank = $request->input('bank_name');
        $holder = $request->input('holder_name');
        $branch = $request->input('branch_name');
        $acc_no = $request->input('account_number');
        $other_info = $request->input('other_information');
        $ifsc_code = $request->input('ifsc_code');        
        $change_expiry_date = !empty($request->input('change_expiry_date')) ? Carbon::parse($request->change_expiry_date)->setTimeFromTimeString(now()->format('H:i:s')) : NULL;
        $commissionType = $request->input('commission_type');
        $commissionValue = $request->input('commission_value');
        $commissionObj = ['type' => $commissionType, 'value' => $commissionValue];
        $address = $request->input('address');
        $email = $request->input('email');
        $user = Driver::find($id);
        $prevCommission = $user->adminCommission;
        $commissionChanged = $commissionType !== $prevCommission['type'] || $commissionValue != $prevCommission['value'];
        if ($user) {
            $user->nom = $nom;
            $user->prenom = $prenom;
            $user->device_id = $device_id;
            $user->statut = $request->has('statut') ? 'yes' : 'no';
            $user->address = $address;
            $user->bank_name = $bank;
            $user->branch_name = $branch;
            $user->holder_name = $holder;
            $user->account_no = $acc_no;
            $user->other_info = $other_info;
            $user->ifsc_code = $ifsc_code;
            if ($request->hasfile('photo')) {
                $destination = public_path('assets/images/driver/' . $user->photo_path);
                if (File::exists($destination)) {
                    File::delete($destination);
                }
                $file = $request->file('photo');
                $extenstion = $file->getClientOriginalExtension();
                $time = time() . '.' . $extenstion;
                $filename = 'driver_' . $id . '.' . $extenstion;
                $path = public_path('assets/images/driver/') . $filename;
                if (!file_exists(public_path('assets/images/driver/'))) {
                    mkdir(public_path('assets/images/driver/'), 0777, true);
                }
                Image::make($file->getRealPath())->resize(150, 150)->save($path);

                $user->photo_path = $filename;
            }           
            $user->subscriptionExpiryDate = $change_expiry_date;
            $user->adminCommission = $commissionObj;
            $user->save();
        }
        if ($commissionChanged) {
            //update in all the driver of owner
            $ownersDrivers = Driver::where('ownerId', '=', $id)->get();
            foreach ($ownersDrivers as $driver) {
                $driver->adminCommission = $commissionObj;
                $driver->save();
            }
        }

        if (!empty($change_expiry_date)) {
            $historyData = SubscriptionHistory::where('user_id', $id)
                ->orderBy('created_at', 'desc')
                ->first();
            $LastHistoryId = $historyData->id;
            SubscriptionHistory::where('id', $LastHistoryId)->update([
                'expiry_date' => $change_expiry_date
            ]);
        }

        //Update dispatcher user
        $dispatcher_user = DispatcherUser::where('email',$user->email)->first();
        if ($dispatcher_user) {
            $dispatcher_user->first_name = $nom;
            $dispatcher_user->last_name = $prenom;
            $dispatcher_user->status = $request->has('statut') ? 'yes' : 'no';
            if ($request->hasfile('photo')) {
                $relativePath = str_replace(url('/') . '/', '', $dispatcher_user->profile_picture_path);
                $destination = public_path($relativePath);
                if (File::exists($destination)) {
                    File::delete($destination);
                }
                $file = $request->file('photo');
                $extenstion = $file->getClientOriginalExtension();
                $time = time() . '.' . $extenstion;
                $filename = 'dispatcher_user_profile' . $time;
                $path = public_path('assets/images/dispatcher_users/') . $filename;
                if (!file_exists(public_path('assets/images/dispatcher_users/'))) {
                    mkdir(public_path('assets/images/dispatcher_users/'), 0777, true);
                }
                Image::make($file->getRealPath())->resize(150, 150)->save($path);
                $dispatcher_user->profile_picture_path = asset('assets/images/dispatcher_users/' . $filename);
            }
            $dispatcher_user->save();
        }

        return redirect('owners')->with('message', trans('lang.owner_updated_successfully'));
    }
    public function deleteOwner($id)
    {

        if ($id != "") {

            $id = json_decode($id);


            if (is_array($id)) {

                for ($i = 0; $i < count($id); $i++) {

                    Driver::where('ownerId', $id[$i])->delete();

                    Vehicle::where('ownerId', $id[$i])->delete();

                    Transaction::where('user_id', $id[$i])->where('user_type','driver')->delete();

                    $user = Driver::find($id[$i]);
                    if (!empty($user->photo_path)) {
                        $destination = public_path('assets/images/driver/' . $user->photo_path);
                        if (File::exists($destination)) {
                            File::delete($destination);
                        }
                    }

                    $owner_docs = DriversDocuments::where('driver_id', "=", $id[$i])->get();
                    if ($owner_docs) {
                        foreach ($owner_docs as $owner_doc) {
                            if (!empty($owner_doc->document_path)) {
                                $destination = public_path('assets/images/driver/documents/' . $owner_doc->document_path);
                                if (File::exists($destination)) {
                                    File::delete($destination);
                                }
                            }
                            $owner_doc->delete();
                        }
                    }

                    AccessToken::where('user_id', $id[$i])->delete();

                    $dispatcher_user = DispatcherUser::where('email',$user->email)->first();
                    if($dispatcher_user){
                        if(!empty($dispatcher_user->profile_picture_path)){
                            $destination = public_path('assets/images/dispatcher_users/' . $dispatcher_user->profile_picture_path);
                            if (File::exists($destination)) {
                                File::delete($destination);
                            }
                        }
                        $dispatcher_user->delete();
                    }

                    $user->delete();
                }
            } else {

                Driver::where('ownerId', $id)->delete();

                Vehicle::where('ownerId', $id)->delete();

                Transaction::where('user_id', $id)->where('user_type','driver')->delete();

                AccessToken::where('user_id', $id)->delete();

                $user = Driver::find($id);
                if (!empty($user->photo_path)) {
                    $destination = public_path('assets/images/driver/' . $user->photo_path);
                    if (File::exists($destination)) {
                        File::delete($destination);
                    }
                }

                $owner_docs = DriversDocuments::where('driver_id', "=", $id)->get();
                if ($owner_docs) {
                    foreach ($owner_docs as $owner_doc) {
                        if (!empty($owner_doc->document_path)) {
                            $destination = public_path('assets/images/driver/documents/' . $owner_doc->document_path);
                            if (File::exists($destination)) {
                                File::delete($destination);
                            }
                        }
                        $owner_doc->delete();
                    }
                }

                $dispatcher_user = DispatcherUser::where('email',$user->email)->first();
                if(!empty($dispatcher_user->profile_picture_path)){
                    $destination = public_path('assets/images/dispatcher_users/' . $dispatcher_user->profile_picture_path);
                    if (File::exists($destination)) {
                        File::delete($destination);
                    }
                }
                $dispatcher_user->delete();
                
                $user->delete();
            }
        }

        return redirect()->back();
    }

    public function getModel(Request $request, $brand_id)
    {
        $id_type_vehicule = $request->get('id_type_vehicule');
        $data['model'] = CarModel::where("brand_id", $brand_id)->where('vehicle_type_id', $id_type_vehicule)->get(["name", "id"]);

        return response()->json($data);
    }

    public function getBrand(Request $request, $vehicleType_id)
    {
        $data['brand'] = Brand::where("vehicle_id", $vehicleType_id)
            ->get(["name", "id"]);

        return response()->json($data);
    }

    public function show($id)
    {
        $owner = Driver::where('id', "=", $id)->first();

        if (!empty($owner['email'])) {
            $owner['email'] = Helper::shortEmail($owner['email']);
        }
        if (!empty($owner['phone'])) {
            $owner['phone'] = Helper::shortNumber($owner['phone']);
        }

        $brand = Brand::all();
        $vehicleType = VehicleType::all();
        $model = CarModel::all();
        $drivers = Driver::where('ownerId', $id)
            ->whereNotIn('id', function ($query) {
                $query->select('id_conducteur')
                    ->from('vehicule')
                    ->whereNotNull('id_conducteur'); 
            })
            ->get();
        
        $driverIds = Driver::where('ownerId', $id)->pluck('id')->toArray();

        $vehicle = DB::table('vehicule')
            ->leftjoin('conducteur', 'conducteur.id', '=', 'vehicule.id_conducteur')
            ->leftjoin('type_vehicule', 'type_vehicule.id', '=', 'vehicule.id_type_vehicule')
            ->join('brands', 'vehicule.brand', '=', 'brands.id')
            ->join('car_model', 'vehicule.model', '=', 'car_model.id')
            ->select('vehicule.*', 'conducteur.prenom', 'conducteur.nom', 'type_vehicule.libelle as vehicle_type','brands.name as brand', 'car_model.name as model')
            ->where('vehicule.ownerId', "=", $id)->get();
        
        $currency = Currency::where('statut', 'yes')->first();
        
        $transactions = Transaction::join('payment_method', 'transactions.payment_method', '=', 'payment_method.libelle')
        ->select('transactions.*', 'payment_method.image')
        ->where('user_id', "=", $id)->orderBy('transactions.id', 'desc')->paginate(10);

        $rides = Requests::leftjoin('user_app', 'requete.id_user_app', '=', 'user_app.id')
            ->join('conducteur', 'requete.id_conducteur', '=', 'conducteur.id')
            ->join('payment_method', 'requete.id_payment_method', '=', 'payment_method.id')
            ->select('requete.id', 'requete.statut', 'requete.statut_paiement', 'requete.depart_name', 'requete.destination_name', 'requete.distance', 'requete.montant', 'requete.creer', 'conducteur.id as driver_id', 'conducteur.prenom as driverPrenom', 'conducteur.nom as driverNom', 'user_app.id as user_id', 'user_app.prenom as userPrenom', 'user_app.nom as userNom', 'payment_method.libelle', 'payment_method.image', 'requete.ride_type')
            ->whereIn('requete.id_conducteur', $driverIds)
            ->orderBy('requete.id', 'DESC');  

        $totalRides = $rides->count();
        $rides = $rides->paginate(10);

        $parcelOrders = ParcelOrder::join('user_app', 'parcel_orders.id_user_app', '=', 'user_app.id')
            ->join('conducteur', 'parcel_orders.id_conducteur', '=', 'conducteur.id')
            ->join('payment_method', 'parcel_orders.id_payment_method', '=', 'payment_method.id')
            ->select('parcel_orders.id', 'parcel_orders.status', 'parcel_orders.created_at', 'user_app.id as user_id', 'user_app.prenom as userPrenom', 'user_app.nom as userNom')
            ->whereIn('parcel_orders.id_conducteur', $driverIds)
            ->orderBy('parcel_orders.id', 'DESC');

        $totalParcelOrders = $parcelOrders->count();
        $parcelOrders = $parcelOrders->paginate(10);

        $rentalOrders = RentalOrder::join('user_app', 'rental_orders.id_user_app', '=', 'user_app.id')
            ->join('conducteur', 'rental_orders.id_conducteur', '=', 'conducteur.id')
            ->join('payment_method', 'rental_orders.id_payment_method', '=', 'payment_method.id')
            ->select('rental_orders.id', 'rental_orders.status', 'rental_orders.created_at', 'conducteur.id as driver_id', 'conducteur.prenom as driverPrenom', 'conducteur.nom as driverNom')
            ->whereIn('rental_orders.id_conducteur', $driverIds)
            ->orderBy('rental_orders.id', 'DESC');
        $totalrentalOrders = $rentalOrders->count();
        $rentalOrders = $rentalOrders->paginate(10);

        $ownerDrivers = Driver::leftJoin('vehicule', 'vehicule.id_conducteur', '=', 'conducteur.id')
        ->leftJoin('type_vehicule', 'type_vehicule.id', '=', 'vehicule.id_type_vehicule')
        ->where('conducteur.ownerId', '=', $id)
        ->select('conducteur.*', 'type_vehicule.libelle')
        ->orderBy('conducteur.id', 'desc', 'DESC')
        ->paginate(10);
        $ownerDrivers->map(function($user) {
            if(!empty($user->email)){
                $user->email = Helper::shortEmail($user->email);
            }
            if(!empty($user->phone)){
                $user->phone = Helper::shortNumber($user->phone);
            }
            return $user;
        });

        $zone_name = '';
        if (!empty($owner->zone_id)) {
            $zone_id = explode(',', $owner->zone_id);
            $zones = Zone::whereIn('id', $zone_id)->get();
            foreach ($zones as $zone) {
                $zone_name .= $zone->name . ', ';
            }
            $zone_name = rtrim($zone_name, ', ');
        }

        $history = SubscriptionHistory::where('user_id', $id)->orderBy('created_at', 'desc')->paginate(10);
        $activeSubscriptionId = null;

        $latestSubscription = SubscriptionHistory::where('user_id', $id)->where('status','active')->orderBy('created_at', 'desc')->first();
        if ($latestSubscription) {
            $activeSubscriptionId = $latestSubscription->subscriptionPlanId;
        }
        
        $activePlan = null;
        $vehicleCount = null;
        if ($activeSubscriptionId) {
            $activePlan = SubscriptionPlan::where('id', $activeSubscriptionId)->first();                   
            $vehicleCount = DB::table('vehicule')
            ->leftjoin('conducteur', 'conducteur.id', '=', 'vehicule.id_conducteur')
            ->leftjoin('type_vehicule', 'type_vehicule.id', '=', 'vehicule.id_type_vehicule')
            ->join('brands', 'vehicule.brand', '=', 'brands.id')
            ->join('car_model', 'vehicule.model', '=', 'car_model.id')
            ->select('vehicule.*', 'conducteur.prenom', 'conducteur.nom', 'type_vehicule.libelle as vehicle_type','brands.name as brand', 'car_model.name as model')
            ->where('vehicule.ownerId', "=", $id)->count();
        }
        
        $plans = SubscriptionPlan::where('isEnable', 'true')->where('plan_for', '!=','driver')->orderBy('place', 'asc')->get();

        $commissionSetting = Commission::first();
        $subscriptionSetting = Settings::first();
        $subscriptionModel = ($subscriptionSetting->subscription_model == 'true') ? true : false;
        $commissionModel = ($commissionSetting->statut == 'yes') ? true : false;

        if ($commissionSetting->type == 'Percentage') {
            $adminCommission = $commissionSetting->value . '%';
        } else {
            if ($currency->symbol_at_right == 'true') {
                $adminCommission = number_format($commissionSetting->value, $currency->decimal_digit) . $currency->symbole;
            } else {
                $adminCommission = $currency->symbole . number_format($commissionSetting->value, $currency->decimal_digit);
            }
        }

        return view('owners.show', compact('owner',  'rides', 'currency', 'transactions', 'parcelOrders', 'zone_name', 'history', 'plans', 'activeSubscriptionId', 'subscriptionModel', 'commissionModel', 'adminCommission', 'vehicleType', 'brand', 'model', 'drivers', 'vehicle', 'ownerDrivers', 'totalRides', 'totalParcelOrders', 'rentalOrders', 'totalrentalOrders','activePlan' ,'vehicleCount'));
    }
    public function createVehicle($ownerId, Request $request)
    {        
        $vehicle = new Vehicle;
        $vehicle->brand = $request->input('brand');
        $vehicle->model = $request->input('model');
        $vehicle->color = $request->input('color');
        $vehicle->numberplate = $request->input('car_number');       
        $vehicle->km = $request->input('km');
        $vehicle->milage = $request->input('milage');       
        $vehicle->car_make = $request->input('registration_year');       
        $vehicle->statut = 'yes';
        $vehicle->creer = date('Y-m-d H:i:s');
        $vehicle->modifier = date('Y-m-d H:i:s');
        $vehicle->updated_at = date('Y-m-d H:i:s');
        $vehicle->id_type_vehicule = $request->input('id_type_vehicule');
        $vehicle->passenger = $request->input('passenger');
        $vehicle->ownerId = $ownerId;
        $activePlan = SubscriptionHistory::where('user_id', $ownerId)->where('status','active')
        ->orderBy('created_at', 'desc')
        ->first();        
        if ($activePlan) {
            Helper::resetDriverSubscriptionLimit($ownerId, 'subscriptionTotalVehicle', 'dec');
        }
        $vehicle->save();
        return redirect()->back()->with('message', trans('lang.vehicle_created_successfully'));
    }
    public function editVehicle($id)
    {
        $vehicle = Vehicle::where('id', "=", $id)->first();
        $currentDriverId =  Vehicle::where('id', $id)->value('id_conducteur');
        $drivers = Driver::where('ownerId', $vehicle->ownerId)
            ->where(function ($query) use ($currentDriverId) {
                $query->whereNotIn('id', function ($subquery) use ($currentDriverId) {
                    $subquery->select('id_conducteur')
                        ->from('vehicule')
                        ->where('id_conducteur', '!=', $currentDriverId);
                })
                    ->orWhere('id', $currentDriverId);
            })
            ->get();

        return response()->json([
            'vehicle' => $vehicle,
            'drivers' => $drivers,
            'currentDriverId' => $currentDriverId
        ]);
    }
    public function updateVehicle($vehicleId, Request $request)
    {
        $vehicle = Vehicle::find($vehicleId);
        $vehicle->brand = $request->input('brand');
        $vehicle->model = $request->input('model');
        $vehicle->color = $request->input('color');
        $vehicle->numberplate = $request->input('car_number');      
        $vehicle->km = $request->input('km');
        $vehicle->milage = $request->input('milage');  
        $vehicle->car_make = $request->input('registration_year');       
        $vehicle->statut = 'yes';
        $vehicle->modifier = date('Y-m-d H:i:s');
        $vehicle->updated_at = date('Y-m-d H:i:s');
        $vehicle->id_type_vehicule = $request->input('id_type_vehicule');
        $vehicle->passenger = $request->input('passenger');
        $vehicle->save();
        return redirect()->back()->with('message', trans('lang.vehicle_updated_successfully'));
    }

    public function removeDriver($vehicleId, Request $request)
    {
        $vehicle = Vehicle::find($vehicleId);
        $driverId = $vehicle->id_conducteur;
        $driver = Driver::find($driverId);
        $driver->statut_vehicule = 'no';
        $driver->save();
        $vehicle->id_conducteur = '';
        $vehicle->save();

        return redirect()->back();
    }
    public function assignDriver(Request $request)
    {
        $vehicleId = $request->input('vehicleId');
        $driverId = $request->input('driver');
        $vehicle = Vehicle::find($vehicleId);
        $vehicle->id_conducteur = $driverId;
        $vehicle->save();
        $driver = Driver::find($driverId);
        $driver->statut_vehicule = 'yes';
        $driver->save();
        return redirect()->back();
    }
    public function toggalSwitch(Request $request)
    {
        $ischeck = $request->input('ischeck');
        $id = $request->input('id');
        $owner = Driver::find($id);

        if ($ischeck == "true") {
            $owner->statut = 'yes';
        } else {
            $owner->statut = 'no';
        }
        $owner->save();
    }
}
