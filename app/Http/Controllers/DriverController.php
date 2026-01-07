<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Country;
use App\Models\Driver;
use App\Models\Requests;
use App\Models\ParcelOrder;
use App\Models\Vehicle;
use App\Models\DriversDocuments;
use App\Models\Brand;
use App\Models\CarModel;
use App\Models\VehicleType;
use App\Models\VehicleImages;
use App\Models\Zone;
use App\Models\Transaction;
use App\Models\SubscriptionHistory;
use App\Models\RentalOrder;
use App\Models\AccessToken;
use App\Models\Commission;
use App\Models\SubscriptionPlan;
use App\Models\Settings;
use App\Models\Review;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\GcmController;
use App\Helpers\Helper;
use Carbon\Carbon;
use Image;

class DriverController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {

        $query = Driver::where('conducteur.role', 'driver')
        ->where('conducteur.isOwner', 'false')
        ->whereNull('conducteur.ownerId')
        ->leftJoin('vehicule', 'conducteur.id', '=', 'vehicule.id_conducteur')
        ->leftJoin('type_vehicule', 'vehicule.id_type_vehicule', '=', 'type_vehicule.id')
        ->select(
            'conducteur.*',
            'vehicule.id as vehicle_id',
            'vehicule.id_type_vehicule',
            'type_vehicule.libelle as vehicle_type'
        );


        if ($request->is('drivers/approved')) {
            $query->where('is_verified', 1);
        } elseif ($request->is('drivers/pending')) {
            $query->where('is_verified', 0);
        }

        if ($request->filled('search') && $request->filled('selected_search')) {
            $keyword = $request->input('search');
            $field   = $request->input('selected_search');
            $query->where(function ($q) use ($field, $keyword) {
                if ($field == "prenom") {
                    $q->where('prenom', 'LIKE', '%' . $keyword . '%')
                    ->orWhere(DB::raw('CONCAT(nom, " ", prenom)'), 'LIKE', '%' . $keyword . '%');
                }elseif ($field === 'vehicleType') {            
                    $q->where('type_vehicule.libelle', 'LIKE', "%{$keyword}%");
                } else {
                    $q->where($field, 'LIKE', '%' . $keyword . '%');
                }
            });
        }

        if ($request->filled('daterange')) {
            $dates = explode(' - ', $request->daterange);
            $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
            $endDate = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
            $query->whereBetween('conducteur.creer', [$startDate, $endDate]);
        }

        if ($request->has('status_selector') && $request->status_selector != '') {
            $status = $request->input('status_selector');
            $status == 'active' ? $query->where('conducteur.statut', 'yes') : $query->where('conducteur.statut', 'no');
        }
        
        $totalRecords = $query->get();
        $totalLength = count($totalRecords);
        $perPage = $request->input('per_page', 20);
        $drivers = $query->orderBy('id', 'desc')->paginate($perPage)->appends($request->all());

        $drivers->map(function ($driver) {
            if (! empty($driver->email)) {
                $driver->email = Helper::shortEmail($driver->email);
            }
            if (! empty($driver->phone)) {
                $driver->phone = Helper::shortNumber($driver->phone);
            }
            return $driver;
        });

        $totalRide = DB::table('requete')
            ->leftjoin('user_app', 'requete.id_user_app', '=', 'user_app.id')
            ->join('conducteur', 'requete.id_conducteur', '=', 'conducteur.id')
            ->join('payment_method', 'requete.id_payment_method', '=', 'payment_method.id')
            ->select('requete.id_conducteur')
            ->orderBy('conducteur.id', 'desc')
            ->get();

        return view("drivers.index", compact('drivers', 'totalRide', 'totalLength', 'perPage'));
    }

    public function statusAproval(Request $request, $id, $type)
    {
        
        $document = DriversDocuments::find($id);
        $comment = $request->get('comment');

        $driver = Driver::find($document->driver_id);
        if ($document) {
            if ($type == 1) {
                
                $document->document_status = 'Approved';
                $document->comment = '';

                if ($driver->isOwner == "true") {
                    $admin_documents = DB::table('admin_documents')
                        ->where('type', 'owner')
                        ->where('is_enabled', 'Yes')
                        ->where('id', $document->document_id)
                        ->first();
                } else {
                    $admin_documents = DB::table('admin_documents')
                        ->where('type', 'driver')
                        ->where('is_enabled', 'Yes')
                        ->where('id', $document->document_id)
                        ->first();
                }

                if ($admin_documents && $driver && !empty($driver->fcm_id)) {
                    $message = "Your document '{$admin_documents->title}' has been approved.";
                    $title = "Document Approved";

                    GcmController::sendNotification(
                        $driver->fcm_id,
                        [
                            "body" => $message,
                            "title" => $title
                        ],
                        $title
                    );
                }
            } else {
                $document->document_status = 'Disapprove';
                $document->comment = $comment;
                $this->notifyDriver($comment, $document->driver_id);
            }
            $document->save();
        }

        // $driver = Driver::find($document->driver_id);
        if($driver->isOwner == "true"){
            $admin_documents = DB::table('admin_documents')->where('type', 'owner')->where('is_enabled', 'Yes')->get();
            $admin_documents_count = DB::table('admin_documents')->where('type', 'owner')->where('is_enabled', 'Yes')->count();
        }else{
            $admin_documents = DB::table('admin_documents')->where('type', 'driver')->where('is_enabled', 'Yes')->get();
            $admin_documents_count = DB::table('admin_documents')->where('type', 'driver')->where('is_enabled', 'Yes')->count();
        }

        $driverDocumentCount = 0;
        foreach ($admin_documents as $value) {
            $approved_documents = DriversDocuments::where('driver_id', $document->driver_id)->where('document_status', 'Approved')->where('document_id', $value->id)->get();
            if (count($approved_documents) > 0) {
                $driverDocumentCount++;
            }
        }
        
        if ($admin_documents_count == $driverDocumentCount) {
            $driver->is_verified = 1;
        } else {
            $driver->is_verified = 0;
        }
        $driver->save();

        if (!blank($comment)) {
            echo json_encode(array('success' => 'yes'));
            die;
        }

        return redirect()->back();
    }

    public function notifyDriver($comment, $id)
    {

        $tmsg = '';
        $terrormsg = '';

        $title = str_replace("'", "\'", "Disapproved of your Document");
        $msg = str_replace("'", "\'", "Admin is Disapproved your Document. Please submit again.");
        $reasons = str_replace("'", "\'", "$comment");

        $tab[] = array();
        $tab = explode("\\", $msg);

        $msg_ = "";
        for ($i = 0; $i < count($tab); $i++) {
            $msg_ = $msg_ . "" . $tab[$i];
        }

        $message = array("body" => $msg_, "reasons" => $reasons, "title" => $title, "sound" => "mySound", "tag" => "documentdisaaproved");
        $fcm_token = DB::table('conducteur')->where('fcm_id', '!=', '')->where('id', '=', $id)->value('fcm_id');
        if (!empty($fcm_token)) {
            GcmController::sendNotification($fcm_token, $message);
        }
    }

    public function statusDisaproval(Request $request, $id, $type)
    {
        $validator = Validator::make($request->all(), $rules = [
            'comment' => 'required',
        ], $messages = [
            'comment.required' => 'Add Comment for disapproval!',
        ]);


        if ($validator->fails()) {
            return redirect('/document/view/' . $id)
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }
        $comment = $request->input('comment');
        $approvalStatus = 'disapproved';

        $user = DriversDocuments::find($id);
        if ($user) {
            if ($type == 1) {
                $user->comment = $comment;
                $user->document_status = $approvalStatus;
            } elseif ($type == 2) {
                $user->comment = $comment;
                $user->document_status = $approvalStatus;
            } elseif ($type == 3) {
                $user->comment = $comment;
                $user->document_status = $approvalStatus;
            } elseif ($type == 4) {
                $user->comment = $comment;
                $user->document_status = $approvalStatus;
            }
        }
        $user->save();

        $title = str_replace("'", "\'", trans('lang.disapproved_of_your_document'));
        $msg = str_replace("'", "\'", trans('lang.admin_is_disapproved_your_document'));
        $reasons = str_replace("'", "\'", "$comment");

        $tab[] = array();
        $tab = explode("\\", $msg);
        $msg_ = "";
        for ($i = 0; $i < count($tab); $i++) {
            $msg_ = $msg_ . "" . $tab[$i];
        }

        $message = array("body" => $msg_, "reasons" => $reasons, "title" => $title, "sound" => "mySound", "tag" => "documentdisaaproved");
        $fcm_token = DB::table('conducteur')->where('fcm_id', '!=', '')->where('id', '=', DB::raw($id))->value('fcm_id');
        if (!empty($fcm_token)) {
            GcmController::sendNotification($fcm_token, $message);
        }

        return redirect()->back();
    }


    public function edit($id)
    {
        $zones = Zone::where('status', 'yes')->get();
        $driver = Driver::where('id', "=", $id)->first();
        $countries = Country::where('statut', 'yes')
        ->select('id', 'code', 'libelle','phone','statut')
        ->get();
        if (!empty($driver['email'])) {
            $driver['email'] = Helper::shortEmail($driver['email']);
        }
        if (!empty($driver['phone'])) {
            $driver['phone'] = Helper::shortNumber($driver['phone']);
        }
        // $vehicle = Vehicle::where('id_conducteur', "=", $id)->first();
        $vehicle = DB::table('vehicule')
        ->leftJoin('brands', 'vehicule.brand', '=', 'brands.id')
        ->leftJoin('car_model', 'vehicule.model', '=', 'car_model.id')
        ->leftJoin('type_vehicule', 'vehicule.id_type_vehicule', '=', 'type_vehicule.id')
        ->select('vehicule.*', 'type_vehicule.libelle as vehicle_type')
        ->where('vehicule.id_conducteur', "=", $id)
        ->first();
        // dd($vehicle);
        $owners = Driver::where('isOwner', '=', 'true')->get();
        
        $vehicleType = VehicleType::all();

        $brand = Brand::all();
        $model = [];
        if (!empty($vehicle)) {
            $model = CarModel::where('brand_id', "=", $vehicle->brand)->where('vehicle_type_id', "=", $vehicle->id_type_vehicule)->get();
        }
        $currency = Currency::where('statut', 'yes')->first();

        $vehicleImage = VehicleImages::where('id_driver', '=', $id)->first();
        $earnings = DB::select("SELECT sum(montant) as montant, count(id) as rides FROM requete WHERE statut='completed' AND id_conducteur=$id");

        $avg_rating = $driver->average_rating ? $driver->average_rating : '0.0';

        $isFleet = ( $driver->role === 'driver' && $driver->isOwner === 'false' && !empty($driver->ownerId) );

        return view('drivers.edit')->with('driver', $driver)->with('model', $model)->with('brand', $brand)
            ->with("vehicle", $vehicle)->with("earnings", $earnings)->with('vehicleType', $vehicleType)->with('currency', $currency)
            ->with('vehicleImage', $vehicleImage)
            ->with('avg_rating', $avg_rating)
            ->with('zones', $zones)
            ->with('owners',$owners)
            ->with('isFleet',$isFleet)
            ->with('countries',$countries);
    }

    public function create()
    {
        $brand = Brand::all();
        $vehicleType = VehicleType::all();
        $model = CarModel::all();
        $zones = Zone::where('status', 'yes')->get();
        $owners = Driver::where('isOwner', '=', 'true')->get();
        $countries = Country::where('statut', 'yes')
        ->select('id', 'code', 'libelle','phone','statut')
        ->get();
        return view('drivers.create')->with('brand', $brand)->with('model', $model)->with('vehicleType', $vehicleType)->with('zones', $zones)->with('owners', $owners)->with('countries', $countries);
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

    public function store(Request $request)
    {

        //For fleet driver
        if($request->has('is_under_owner')){

            $validator = Validator::make($request->all(), $rules = [
                'nom' => 'required',
                'prenom' => 'required',
                'country_code' => 'required',
                'phone' => 'required|unique:conducteur,phone',
                'email' => 'required|email|unique:conducteur,email',
                'password' => 'required',
                'zone' => 'required',
                'owner' => 'required',
                'service_type' => 'required',
            ], $messages = [
                'nom.required' => trans('lang.the_first_name_field_is_required'),
                'prenom.required' => trans('lang.the_last_name_field_is_required'),
                'email.required' => trans('lang.the_email_field_is_required'),
                'email.unique' => trans('lang.the_email_field_is_should_be_unique'),
                'password.required' => trans('lang.the_password_field_is_required'),
                'country_code.required' => trans('lang.the_country_code_field_is_required'),
                'phone.required' => trans('lang.the_phone_is_required'),
                'phone.unique' => trans('lang.the_phone_field_is_should_be_unique'),
                'owner.required' => trans('lang.please_select_an_owner_if_the_driver_is_registered_under_an_owner'),
                'service_type.required' => trans('lang.please_select_service'),
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->with(['message' => $messages])->withInput();
            }

            $ownerId = $request->input('owner');
            $owner = Driver::where('id', '=', $ownerId)->first();
            if ($owner) {
                $activeSubscriptionId = null;

                $latestSubscription = SubscriptionHistory::where('user_id', $ownerId)->where('status','active')
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($latestSubscription) {
                    $activeSubscriptionId = $latestSubscription->subscriptionPlanId;
                }

                if ($activeSubscriptionId) {
                    $activePlan = SubscriptionPlan::where('id', $activeSubscriptionId)->first();                   
                    $driversCount = Driver::where('ownerId', $ownerId)->count();

                    if ($activePlan && $driversCount >= $activePlan->driver_limit) {
                        return redirect()->back()
                        ->withErrors(['error' => trans('lang.owner_driver_limit_exceed_error')]);
                    }
                }
            }
            $commissionObj = $owner->adminCommission;
            $zone = $request->input('zone');
            $service_type = $request->input('service_type');
            
            $user = new Driver;
            $user->nom = $request->input('nom');
            $user->prenom = $request->input('prenom');
            $user->email = $request->input('email');
            $user->statut = $request->has('statut') ? 'yes' : 'no';
            $user->statut_vehicule = 'no';
            $user->online = 'no';
            $user->status_car_image = 'no';
            $user->login_type = 'email';
            $user->mdp = Hash::make($request->input('password'));
            $user->country_code = '+'. $request->input('country_code');
            $user->phone = $request->input('phone');
            $user->creer = date('Y-m-d H:i:s');
            $user->modifier = date('Y-m-d H:i:s');
            $user->amount = "0";
            $user->driver_on_ride = "no";
            $user->adminCommission= $commissionObj;
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
            $user->zone_id = $zone ? implode(',', $zone) : NULL;
            $user->service_type = $service_type ? implode(',', $service_type) : NULL;
            $user->isOwner = 'false';
            $user->role = 'driver';
            $user->ownerId = $ownerId;
            $user->is_verified = '1';
            $user->save();          

            //Reset Limit
            Helper::resetDriverSubscriptionLimit($ownerId, 'subscriptionTotalDriver', 'dec');

            return redirect('fleet-drivers')->with('message', trans('lang.driver_created_successfully'));
        }
        else{
            //For indivdual driver
            $validator = Validator::make($request->all(), $rules = [
                'nom' => 'required',
                'prenom' => 'required',
                'password' => 'required',
                'country_code' => 'required',
                'phone' => 'required|unique:conducteur,phone',
                'email' => 'required|email|unique:conducteur,email',
                'id_type_vehicule' => 'required',
                'brand' => 'required',
                'model' => 'required',
                'km' => 'required',
                'milage' => 'required',
                'car_number' => 'required',
                'color' => 'required',
                'passenger' => 'required',
                'zone' => 'required',
                'owner' => 'required_if:is_under_owner,yes',
                'service_type' => 'required',
                'registration_year' => 'required|integer|min:1980|max:' . date('Y'),
            ], $messages = [
                'nom.required' => trans('lang.the_first_name_field_is_required'),
                'prenom.required' => trans('lang.the_last_name_field_is_required'),
                'email.required' => trans('lang.the_email_field_is_required'),
                'email.unique' => trans('lang.the_email_field_is_should_be_unique'),
                'password.required' => trans('lang.the_password_field_is_required'),
                'country_code.required' => trans('lang.the_country_code_field_is_required'),
                'phone.required' => trans('lang.the_phone_is_required'),
                'phone.unique' => trans('lang.the_phone_field_is_should_be_unique'),
                'id_type_vehicule.required' => trans('lang.the_vehicle_type_field_is_required'),
                'brand.required' => trans('lang.the_brand_field_is_required'),
                'model.required' => trans('lang.the_model_field_is_required'),
                'km.required' => trans('lang.the_km_field_is_required'),
                'milage.required' => trans('lang.the_milage_field_is_required'),
                'car_number.required' => trans('lang.the_numberplate_field_is_required'),
                'color.required' => trans('lang.the_color_field_is_required'),
                'passenger.required' => trans('lang.the_number_of_passenger_field_is_required'),
                'owner.required_if' => trans('lang.please_select_an_owner_if_the_driver_is_registered_under_an_owner'),
                'service_type.required' => trans('lang.please_select_service'),
                'registration_year.required' => trans('lang.the_registration_year_field_is_required'),
                'registration_year.integer' => trans('lang.the_registration_year_must_be_a_number'),
                'registration_year.min' => trans('lang.the_registration_year_must_be_after_1980'),
                'registration_year.max' => trans('lang.the_registration_year_cannot_be_in_the_future'),
            ]);

            if ($validator->fails()) {
                return redirect('drivers/create')
                    ->withErrors($validator)->with(['message' => $messages])
                    ->withInput();
            }
        }
        
        if($request->has('is_under_owner')){
            $ownerId=$request->input('owner');
            $owner = Driver::where('id', '=', $ownerId)->first();
            $commissionObj = '';
            if (!empty($owner)) {
                $commissionObj = $owner->adminCommission;
            }
        }else{
            $get_admin_commission = Commission::first();
            $commissionObj = ['type' => $get_admin_commission->type, 'value' => $get_admin_commission->value];
        }

        $settings = Settings::first();

        $service_type = $request->input('service_type');
        $user = new Driver;
        $user->nom = $request->input('nom');
        $user->prenom = $request->input('prenom');
        $user->email = $request->input('email');
        $user->statut = $request->has('statut') ? 'yes' : 'no';
        $user->statut_vehicule = $request->has('statut') ? 'yes' : 'no';
        $user->online = 'no';
        $user->status_car_image = 'yes';
        $user->login_type = 'email';
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
        $user->service_type = $service_type ? implode(',', $service_type) : NULL;
        $user->amount = "0";
        $user->driver_on_ride = "no";
        $user->adminCommission= $commissionObj;
        $zone = $request->input('zone');

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
        $user->zone_id = $zone ? implode(',', $zone) : NULL;
        $user->isOwner = 'false';
        $user->role = 'driver';
        $user->ownerId = $request->has('is_under_owner') ? $request->input('owner') : NULL;
        $user->is_verified = ($settings->driver_doc_verification == "yes") ? '0' : '1';
        $user->save();

        $driver_id = $user->id;

        $vehicle = new Vehicle;
        $vehicle->brand = $request->input('brand');
        $vehicle->model = $request->input('model');
        $vehicle->color = $request->input('color');
        $vehicle->numberplate = $request->input('car_number');
        $vehicle->car_make = $request->input('registration_year');
        $vehicle->km = $request->input('km');
        $vehicle->milage = $request->input('milage');
        $vehicle->id_conducteur = $driver_id;
        $vehicle->statut = 'yes';
        $vehicle->creer = date('Y-m-d H:i:s');
        $vehicle->modifier = date('Y-m-d H:i:s');
        $vehicle->updated_at = date('Y-m-d H:i:s');
        $vehicle->id_type_vehicule = $request->input('id_type_vehicule');
        $vehicle->passenger = $request->input('passenger');
        $vehicle->ownerId = $request->has('is_under_owner') ? $request->input('owner') : '';
        $vehicle->save();

        // Get email template
        $emailtemplate = EmailTemplate::where('type', 'new_registration')->first();
        $emailsubject = $emailtemplate->subject;
        $emailmessage = $emailtemplate->message;
        
        $contact_us_email = Settings::select('contact_us_email')->value('contact_us_email');
        $admin_email = $contact_us_email ? $contact_us_email : 'none@none.com';
        $appName = env('APP_NAME', 'Cabme');
        $to = $request->input('email');

        $date = now()->format('d F Y');
        $emailmessage = str_replace(
            ['{AppName}', '{UserName}', '{UserEmail}', '{UserPhone}', '{UserId}', '{Date}'],
            [$appName, "{$user->nom} {$user->prenom}", $user->email, $user->phone, $user->id, $date],
            $emailtemplate->message
        );

        //Send email
        Mail::html($emailmessage, function ($message) use ($to, $admin_email, $emailsubject, $emailtemplate) {
            $message->to($to)->subject($emailsubject);
            if ($emailtemplate->send_to_admin) {
                $message->cc($admin_email);
            }
        });

        return redirect('drivers')->with('message', trans('lang.driver_created_successfully'));
    }

    public function deleteDriver($id)
    {

        if ($id != "") {

            $id = json_decode($id);


            if (is_array($id)) {

                for ($i = 0; $i < count($id); $i++) {
                    
                    $vehicle = Vehicle::where('id_conducteur', $id[$i]);
                    if ($vehicle) {
                        $vehicle->delete();                        
                    }

                    $vehicleImages = VehicleImages::where('id_driver', $id[$i]);
                    if ($vehicleImages) {
                        foreach ($vehicleImages as $vehicleImage) {
                            if (!empty($vehicleImage->image_path)) {
                                $destination = public_path('assets/images/vehicle/' . $vehicleImage->image_path);
                                if (File::exists($destination)) {
                                    File::delete($destination);
                                }
                            }
                            $vehicleImage->delete();
                        }
                    }

                    $DriverTransaction = Transaction::where('user_id', $id[$i]);
                    if ($DriverTransaction) {
                        $DriverTransaction->delete();
                    }

                    $user = Driver::find($id[$i]);
                    if ($user) {
                        if (!empty($user->photo_path)) {
                            $destination = public_path('assets/images/driver/' . $user->photo_path);
                            if (File::exists($destination)) {
                                File::delete($destination);
                            }
                        }

                        $driver_docs = DriversDocuments::where('driver_id', "=", $id[$i])->get();
                        if ($driver_docs) {
                            foreach ($driver_docs as $driver_doc) {
                                if (!empty($driver_doc->document_path)) {
                                    $destination = public_path('assets/images/driver/documents/' . $driver_doc->document_path);
                                    if (File::exists($destination)) {
                                        File::delete($destination);
                                    }
                                }
                                $driver_doc->delete();
                            }
                        }

                        $AccessToken = AccessToken::where('user_id', $id[$i]);
                        if ($AccessToken) {
                            $AccessToken->delete();
                        }

                        $isFleet = ( $user->role === 'driver' && $user->isOwner === 'false' && !empty($user->ownerId) );
                        if($isFleet){
                            $activePlan = SubscriptionHistory::where('user_id', $id[$i])->where('status','active')->orderBy('created_at', 'desc')->first();
                            if ($activePlan) {
                                Helper::resetDriverSubscriptionLimit($id[$i], 'subscriptionTotalDriver', 'inc');
                            }
                        }
                       
                        $user->delete();

                    }
                }
            } else {
                
                $vehicle = Vehicle::where('id_conducteur', $id);
                if ($vehicle) {
                    $vehicle->delete();
                }

                $vehicleImages = VehicleImages::where('id_driver', $id);
                if ($vehicleImages) {
                    foreach ($vehicleImages as $vehicleImage) {
                        if (!empty($vehicleImage->image_path)) {
                            $destination = public_path('assets/images/vehicle/' . $vehicleImage->image_path);
                            if (File::exists($destination)) {
                                File::delete($destination);
                            }
                        }
                        $vehicleImage->delete();
                    }
                }

                $DriverTransaction = Transaction::where('user_id', $id);
                if ($DriverTransaction) {
                    $DriverTransaction->delete();
                }

                $user = Driver::find($id);
                if($user){
                    if (!empty($user->photo_path)) {
                        $destination = public_path('assets/images/driver/' . $user->photo_path);
                        if (File::exists($destination)) {
                            File::delete($destination);
                        }
                    }

                    $driver_docs = DriversDocuments::where('driver_id', "=", $id)->get();
                    if ($driver_docs) {
                        foreach ($driver_docs as $driver_doc) {
                            if (!empty($driver_doc->document_path)) {
                                $destination = public_path('assets/images/driver/documents/' . $driver_doc->document_path);
                                if (File::exists($destination)) {
                                    File::delete($destination);
                                }
                            }
                            $driver_doc->delete();
                        }
                    }

                    $AccessToken = AccessToken::where('user_id', $id);
                    if ($AccessToken) {
                        $AccessToken->delete();
                    }

                    $isFleet = ( $user->role === 'driver' && $user->isOwner === 'false' && !empty($user->ownerId) );
                    if($isFleet){
                        $activePlan = SubscriptionHistory::where('user_id', $id)->where('status','active')->orderBy('created_at', 'desc')->first();
                        if ($activePlan) {
                            Helper::resetDriverSubscriptionLimit($id, 'subscriptionTotalDriver', 'inc');
                        }
                    }

                    $user->delete();
                
                }
            }
        }

        return redirect()->back();
    }

    public function updateDriver(Request $request, $id)
    {

        $user = Driver::find($id);

        if ($request->id > 0) {
            $image_validation = "mimes:jpeg,jpg,png";
            $doc_validation = "mimes:doc,pdf,docx,zip,txt";
        } else {
            $image_validation = "required|mimes:jpeg,jpg,png";
            $doc_validation = "required|mimes:doc,pdf,docx,zip,txt";
        }

        $isFleet = ( $user->role === 'driver' && $user->isOwner === 'false' && !empty($user->ownerId) );

        if($isFleet){

            $validator = Validator::make($request->all(), $rules = [
                'nom' => 'required',
                'prenom' => 'required',
                'zone' => 'required',
            ], $messages = [
                'nom.required' => trans('lang.the_first_name_field_is_required'),
                'prenom.required' => trans('lang.the_last_name_field_is_required'),
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->with(['message' => $messages])->withInput();
            }

            $nom = $request->input('nom');
            $prenom = $request->input('prenom');
            $status = $request->input('statut');
            $status = $request->has('statut') ? "yes" : "no";
            $zone = $request->input('zone');
            
             if ($user) {
                $user->nom = $nom;
                $user->prenom = $prenom;
                $user->statut = $status;
                $user->zone_id = $zone ? implode(',', $zone) : NULL;

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

                $user->save();
            }
            
            return redirect('fleet-drivers')->with('message', trans('lang.driver_updated_successfully'));

        }else{

            $validator = Validator::make($request->all(), $rules = [
                'nom' => 'required',
                'prenom' => 'required',               
                'id_type_vehicule' => 'required',
                'brand' => 'required',
                'model' => 'required',
                'km' => 'required',
                'milage' => 'required',
                'numberplate' => 'required',
                'color' => 'required',
                'passenger' => 'required',
                'zone' => 'required',
                'commission_type'=>'required',
                'commission_value'=> 'required',
                'owner' => 'required_if:is_under_owner,yes',
                'registration_year' => 'required|integer|min:1980|max:' . date('Y'),

            ], $messages = [
                'nom.required' => trans('lang.the_first_name_field_is_required'),
                'prenom.required' => trans('lang.the_last_name_field_is_required'),                
                'id_type_vehicule.required' => trans('lang.the_vehicle_type_field_is_required'),
                'brand.required' => trans('lang.the_brand_field_is_required'),
                'model.required' => trans('lang.the_model_field_is_required'),
                'km.required' => trans('lang.the_km_field_is_required'),
                'milage.required' => trans('lang.the_milage_field_is_required'),
                'numberplate.required' => trans('lang.the_numberplate_field_is_required'),
                'color.required' => trans('lang.the_color_field_is_required'),
                'passenger.required' => trans('lang.the_number_of_passenger_field_is_required'),
                'owner.required_if' => trans('lang.please_select_an_owner_if_the_driver_is_registered_under_an_owner'),
                'registration_year.required' => trans('lang.the_registration_year_field_is_required'),
                'registration_year.integer' => trans('lang.the_registration_year_must_be_a_number'),
                'registration_year.min' => trans('lang.the_registration_year_must_be_after_1980'),
                'registration_year.max' => trans('lang.the_registration_year_cannot_be_in_the_future'),
            ]);

             if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)->with(['message' => $messages])
                    ->withInput();
            }

        }

        $nom = $request->input('nom');
        $prenom = $request->input('prenom');
        $phone = $request->input('phone');
        $device_id = $request->input('device_id');
        $status = $request->input('statut');
        $id_type_vehicule = $request->input('id_type_vehicule');
        $brand = $request->input('brand');
        $model = $request->input('model');
        $color = $request->input('color');
        $km = $request->input('km');
        $milage = $request->input('milage');
        $numberplate = $request->input('numberplate');
        $passenger = $request->input('passenger');
        $bank = $request->input('bank_name');
        $holder = $request->input('holder_name');
        $branch = $request->input('branch_name');
        $acc_no = $request->input('account_number');
        $other_info = $request->input('other_information');
        $ifsc_code = $request->input('ifsc_code');
        $zone = $request->input('zone');
        $change_expiry_date = !empty($request->input('change_expiry_date')) ? Carbon::parse($request->change_expiry_date)->setTimeFromTimeString(now()->format('H:i:s')) : NULL;
        $commissionType = $request->input('commission_type');
        $commissionValue = $request->input('commission_value');
        $commissionObj = ['type' => $commissionType, 'value' => $commissionValue];
        if ($status == "on") {
            $status = "yes";
        } else {
            $status = "no";
        }

        $address = $request->input('address');
        $email = $request->input('email');
        
        if ($user) {
            $user->nom = $nom;
            $user->prenom = $prenom;
            $user->device_id = $device_id;
            $user->statut = $status;
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

                //$file->move(public_path('assets/images/driver'), $filename);
                $user->photo_path = $filename;
            }
            $user->zone_id = $zone ? implode(',', $zone) : NULL;
            $user->subscriptionExpiryDate = $change_expiry_date;
            $user->adminCommission = $commissionObj;
            $user->ownerId = $request->has('is_under_owner') ? $request->input('owner') : NULL;
            $user->save();
        }

        $vehicle_image = VehicleImages::where('id_driver', "=", $id)->first();
        if ($vehicle_image) {
            if ($request->hasfile('image_path')) {
                $destination = public_path('assets/images/vehicle/' . $vehicle_image->image_path);
                if (File::exists($destination)) {
                    File::delete($destination);
                }
                $file = $request->file('image_path');
                $extenstion = $file->getClientOriginalExtension();
                $time = time() . '.' . $extenstion;
                $filename = 'vehicle_' . $id . '.' . $extenstion;
                $compressedImage = Helper::compressFile($file->getPathName(), public_path('assets/images/vehicle') . '/' . $filename, 8);
                $vehicle_image->image_path = $filename;
                $vehicle_image->save();
            }
        } else {
            $vehicle_image = new vehicleImages;
            if ($request->hasfile('image_path')) {
                $file = $request->file('image_path');
                $extenstion = $file->getClientOriginalExtension();
                $time = time() . '.' . $extenstion;
                $filename = 'vehicle_' . $id . '.' . $extenstion;
                $compressedImage = Helper::compressFile($file->getPathName(), public_path('assets/images/vehicle') . '/' . $filename, 8);
                $vehicle_image->image_path = $filename;
                $vehicle_image->id_vehicle = $vehicle->id;
                $vehicle_image->id_driver = $id;
                $vehicle_image->creer = date('Y-m-d H:i:s');
                $vehicle_image->modifier = date('Y-m-d H:i:s');
                $vehicle_image->save();
            }
        }

        $vehicle = Vehicle::where('id_conducteur', "=", $id)->first();
        if ($vehicle) {
            $vehicle->id_type_vehicule = $id_type_vehicule;
            $vehicle->brand = $brand;
            $vehicle->model = $model;
            $vehicle->color = $color;
            $vehicle->km = $km;
            $vehicle->milage = $milage;
            $vehicle->numberplate = $numberplate;
            $vehicle->passenger = $passenger;
            $vehicle->id_type_vehicule = $request->input('id_type_vehicule');
            $vehicle->car_make = $request->input('registration_year');
            $vehicle->ownerId = $request->has('is_under_owner') ? $request->input('owner') : '';
            $vehicle->save();
        } else {
            $vehicle = new Vehicle;
            $vehicle->id_type_vehicule = $id_type_vehicule;
            $vehicle->brand = $brand;
            $vehicle->model = $model;
            $vehicle->color = $color;
            $vehicle->km = $km;
            $vehicle->milage = $milage;
            $vehicle->numberplate = $numberplate;
            $vehicle->passenger = $passenger;
            $vehicle->id_conducteur = $id;
            $vehicle->car_make = '';
            $vehicle->statut = 'yes';
            $vehicle->ownerId = $request->has('is_under_owner') ? $request->input('owner') : '';
            $vehicle->creer = date('Y-m-d H:i:s');
            $vehicle->modifier = date('Y-m-d H:i:s');
            $vehicle->updated_at = date('Y-m-d H:i:s');
            $vehicle->save();
        }

        if (!empty($change_expiry_date)) {
            $historyData = SubscriptionHistory::where('user_id', $id)->orderBy('created_at', 'desc')->first();
            $LastHistoryId = $historyData->id;
            SubscriptionHistory::where('id', $LastHistoryId)->update([
                'expiry_date' => $change_expiry_date
            ]);
        }

        return redirect('drivers')->with('message', trans('lang.driver_updated_successfully'));
    }

    public function show($id)
    {
        $driver = Driver::where('id', "=", $id)->first();

        if (!empty($driver['email'])) {
            $driver['email'] = Helper::shortEmail($driver['email']);
        }
        if (!empty($driver['phone'])) {
            $driver['phone'] = Helper::shortNumber($driver['phone']);
        }

        $vehicle = DB::table('vehicule')
        ->leftJoin('brands', 'vehicule.brand', '=', 'brands.id')
        ->leftJoin('car_model', 'vehicule.model', '=', 'car_model.id')
        ->leftJoin('type_vehicule', 'vehicule.id_type_vehicule', '=', 'type_vehicule.id')
        ->select(
            'vehicule.*',
            'brands.name as brand_name',
            'car_model.name as model_name',
            'type_vehicule.libelle as vehicle_type'
        )
        ->where('vehicule.id_conducteur', "=", $id)
        ->first();



        $currency = Currency::where('statut', 'yes')->first();
        $transactions = Transaction::join('payment_method', 'transactions.payment_method', '=', 'payment_method.libelle')
            ->select('transactions.*', 'payment_method.image')
            ->where('transactions.user_id', "=", $id)->orderBy('transactions.id', 'desc')->paginate(10);

        $rides = Requests::leftjoin('user_app', 'requete.id_user_app', '=', 'user_app.id')
            ->join('conducteur', 'requete.id_conducteur', '=', 'conducteur.id')
            ->join('payment_method', 'requete.id_payment_method', '=', 'payment_method.id')
            ->select('requete.id', 'requete.statut', 'requete.statut_paiement', 'requete.depart_name', 'requete.destination_name', 'requete.distance', 'requete.montant', 'requete.creer', 'conducteur.id as driver_id', 'conducteur.prenom as driverPrenom', 'conducteur.nom as driverNom', 'user_app.id as user_id', 'user_app.prenom as userPrenom', 'user_app.nom as userNom', 'payment_method.libelle', 'payment_method.image', 'requete.ride_type')
            ->where('requete.id_conducteur', $id)
            ->orderBy('requete.id', 'DESC');
        $totalRides = count($rides->get());
        $rides = $rides->paginate(10);

        $parcelOrders = ParcelOrder::join('user_app', 'parcel_orders.id_user_app', '=', 'user_app.id')
            ->join('conducteur', 'parcel_orders.id_conducteur', '=', 'conducteur.id')
            ->join('payment_method', 'parcel_orders.id_payment_method', '=', 'payment_method.id')
            ->select('parcel_orders.id', 'parcel_orders.status', 'parcel_orders.created_at', 'user_app.id as user_id', 'user_app.prenom as userPrenom', 'user_app.nom as userNom')
            ->where('parcel_orders.id_conducteur', $id)
            ->orderBy('parcel_orders.id', 'DESC');
        $totalParcelOrders = count($parcelOrders->get());
        $parcelOrders = $parcelOrders->paginate(10);

        $rentalOrders = RentalOrder::join('user_app', 'rental_orders.id_user_app', '=', 'user_app.id')
        ->join('conducteur', 'rental_orders.id_conducteur', '=', 'conducteur.id')
        ->join('payment_method', 'rental_orders.id_payment_method', '=', 'payment_method.id')
        ->select(
            'rental_orders.id',
            'rental_orders.status',
            'rental_orders.created_at',
            'conducteur.id as driver_id',
            'conducteur.prenom as driverPrenom',
            'conducteur.nom as driverNom',
            'user_app.prenom as userPrenom',
            'user_app.nom as userNom',
            'payment_method.libelle as paymentMethod'
        )
        ->where('rental_orders.id_conducteur', $id)
        ->orderBy('rental_orders.created_at', 'DESC');

        $totalrentalOrders = $rentalOrders->count();
        $rentalOrders = $rentalOrders->paginate(10);

        $zone_name = '';
        if ($driver->zone_id) {
            $zone_id = explode(',', $driver->zone_id);
            $zones = Zone::whereIn('id', $zone_id)->get();
            foreach ($zones as $zone) {
                $zone_name .= $zone->name . ', ';
            }
            $zone_name = rtrim($zone_name, ', ');
        }

        $history = SubscriptionHistory::where('user_id', $id)->orderBy('created_at', 'desc')->paginate(10);
        $activeSubscriptionId = null;
        $latestSubscription = SubscriptionHistory::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestSubscription) {
            $activeSubscriptionId = $latestSubscription->subscriptionPlanId;
        }
        $plans = SubscriptionPlan::where('isEnable', 'true')->where('plan_for', '!=','owner')
            ->orderBy('place', 'asc')
            ->get();

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

        $isFleet = ( $driver->role === 'driver' && $driver->isOwner === 'false' && !empty($driver->ownerId) );

        $owner = '';
        if($isFleet){
            $owner = Driver::where('id', "=", $driver->ownerId)->first();
            if (!empty($owner->email)) { 
                $owner->email = Helper::shortEmail($owner->email);
            } 
            if (!empty($owner->phone)) { 
                $owner->phone = Helper::shortNumber($owner->phone); 
            }
        }
        
        $earnings = DB::selectOne("SELECT sum(montant) as montant, count(id) as rides 
            FROM requete 
            WHERE statut='completed' AND id_conducteur = ?", [$id]);

        
        return view('drivers.show', compact('driver', 'vehicle', 'rides', 'currency', 'transactions', 'parcelOrders', 'zone_name', 'history', 'plans', 'activeSubscriptionId', 'subscriptionModel', 'commissionModel', 'adminCommission','totalRides','totalParcelOrders','totalrentalOrders','rentalOrders','isFleet','owner', 'earnings'));
    }

    public function changeStatus($id)
    {
        $driver = Driver::find($id);
        if ($driver->statut == 'no') {
            $driver->statut = 'yes';
        } else {
            $driver->statut = 'no';
        }

        $driver->save();
        return redirect()->back();
    }

    public function documentView($id)
    {
        $driver = Driver::where('id', "=", $id)->first();
        if($driver->isOwner == "true"){
            $admin_documents = DB::table('admin_documents')->where('type', 'owner')->where('is_enabled', 'Yes')->get();
        }else{
            $admin_documents = DB::table('admin_documents')->where('type', 'driver')->where('is_enabled', 'Yes')->get();
        }
        
        $admin_documents->map(function ($admin_document, $key) use ($id) {
            $driver_document = DB::table('driver_document')->where('driver_id', $id)->where('document_id', $admin_document->id)->first();
            $admin_document->driver_document = $driver_document;
            return $admin_document;
        });

        return view('drivers.viewDocument')->with('admin_documents', $admin_documents)->with('driver', $driver);
    }



    public function uploaddocument($id, $doc_id)
    {
        $document = DB::table('admin_documents')->where('is_enabled', '=', 'Yes')->get();
        return view('drivers.uploaddocument')->with('id', $id)->with('document_id', $doc_id)->with('document', $document);
    }


    public function updatedocument(Request $request, $id)
    {
        $document_id = $request->input('document_id');
        // Check if this driver already has the document
        $existingDocument = DriversDocuments::where('driver_id', $id)
            ->where('document_id', $document_id)
            ->first();

        $rules = [
            'document_path' => ($existingDocument ? 'nullable' : 'required') . '|mimes:jpeg,png,jpg',
        ];
        $messages = [
            'document_path.required' => trans('lang.the_document_field_is_required'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with(['message' => $messages])
                ->withInput();
        }

        $document_name = DB::table('admin_documents')->where('id', $document_id)->first();

         if ($existingDocument) {

            // Updating existing document
            if ($request->hasFile('document_path')) {
                $destination = public_path('assets/images/driver/documents/' . $existingDocument->document_path);

                if (File::exists($destination)) {
                    File::delete($destination);
                }

                $file = $request->file('document_path');
                $filename = str_replace(' ', '_', $document_name->title) . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('assets/images/driver/documents'), $filename);

                $existingDocument->document_path = $filename;
                $existingDocument->document_status = 'Pending';
            }

            $existingDocument->save();

        } else {

            // First time upload
            $newDocument = new DriversDocuments;

            if ($request->hasFile('document_path')) {
                $file = $request->file('document_path');
                $filename = str_replace(' ', '_', $document_name->title) . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('assets/images/driver/documents'), $filename);

                $newDocument->document_path = $filename;
                $newDocument->document_status = 'Pending';
            }

            $newDocument->driver_id = $id;
            $newDocument->document_id = $document_id;
            $newDocument->save();
        }

        return redirect()->route('driver.documentView', $id)->with('message', trans('lang.document_updated_successfully'));
    }
    
    public function toggalSwitch(Request $request)
    {
        $ischeck = $request->input('ischeck'); 
        $id = $request->input('id');
        $driver = Driver::find($id);
        if ($driver) {
            $driver->statut = $ischeck == 1 ? 'yes' : 'no';
            $driver->save();
            return response()->json(['success' => true, 'status' => $driver->statut]);
        }
        return response()->json(['success' => false], 404);
    }

    public function toggalOnlineSwitch(Request $request)
    {
        $ischeck = $request->input('ischeck'); 
        $id = $request->input('id');
        $driver = Driver::find($id);
        if ($driver) {
            $driver->online = $ischeck == 1 ? 'yes' : 'no';
            $driver->save();
            return response()->json(['success' => true, 'status' => $driver->statut]);
        }
        return response()->json(['success' => false], 404);
    }

    public function addWallet(Request $request, $id)
    {
        $driver = Driver::find($id);
        $amount = $request->amount;
        if ($amount == '' || $amount == null) {
            $amount = 0;
        }
        if ($driver) {
            $driverWallet = floatval($driver->amount) + floatval($amount);
            $driver->amount = (string) $driverWallet;
            $driver->save();
        }
        $date = date('Y-m-d H:i:s');
        $txnId = uniqid() . mt_rand(1000, 9999);
        Transaction::create([
            'amount' => $amount,
            'payment_method' => 'Wallet',
            'user_id' => $id,
            'user_type' => 'driver',
            'is_credited' => '1',
            'note' => 'Wallet TopUp',
            'transaction_id' => $txnId
        ]);
       

        $driver = Driver::find($id);
        $txnId = uniqid(0, 999);
        $email = $driver->email;
        $date = date('d F Y');

        if (!empty($email)) {

            $emailsubject = '';
            $emailmessage = '';
            $emailtemplate = EmailTemplate::select('*')->where('type', 'wallet_topup')->first();
            if (!empty($emailtemplate)) {
                $emailsubject = $emailtemplate->subject;
                $emailmessage = $emailtemplate->message;
            }
            
            $current_amount = $driver->amount;
            $currencyData = Currency::select('*')->where('statut', 'yes')->first();
            if ($currencyData->symbol_at_right == 'true') {
                $amount_init = number_format($request->get('amount'), $currencyData->decimal_digit) . $currencyData->symbole;
                $newBalance = number_format($current_amount, $currencyData->decimal_digit) . $currencyData->symbole;
            } else {
                $amount_init = $currencyData->symbole . number_format($request->get('amount'), $currencyData->decimal_digit);
                $newBalance = $currencyData->symbole . number_format($current_amount, $currencyData->decimal_digit);
            }
            
            $contact_us_email = Settings::select('contact_us_email')->value('contact_us_email');
            $admin_email = $contact_us_email ? $contact_us_email : 'none@none.com';
            $app_name = env('APP_NAME', 'Cabme');
            $to = $email;
            
            $payment_method = $request->get('payment_method');
            $transaction_id = $request->get('transaction_id');
            $date = date('d F Y', strtotime(date('Y-m-d H:i:s')));
            
            $emailmessage = str_replace('{AppName}', $app_name, $emailmessage);
            $emailmessage = str_replace('{UserName}', $driver->nom . ' ' . $driver->prenom, $emailmessage);
            $emailmessage = str_replace('{Amount}', $amount_init, $emailmessage);
            $emailmessage = str_replace('{PaymentMethod}', $payment_method ? $payment_method : 'N/A', $emailmessage);
            $emailmessage = str_replace('{TransactionId}', $transaction_id ? $transaction_id : 'N/A', $emailmessage);
            $emailmessage = str_replace('{Balance}', $newBalance, $emailmessage);
            $emailmessage = str_replace('{Date}', $date, $emailmessage);
            
            try {
                Mail::html($emailmessage, function ($message) use ($to, $admin_email, $emailsubject, $emailtemplate) {
                    $message->to($to)->subject($emailsubject);
                    if ($emailtemplate->send_to_admin) {
                        $message->cc($admin_email);
                    }
                });
            } catch (Exception $e) {
                Log::error('Wallet Transaction API: Mail Sending Failed: ' . $e->getMessage());
            }
        }

        return redirect()->back();
    }

    public function getPlanDetail(Request $request)
    {
        $planId = $request->input('planId');
        $driverId = $request->input('driverId');
        $planData = SubscriptionPlan::find($planId);
        $activePlan = SubscriptionHistory::where('user_id', $driverId)
            ->orderBy('created_at', 'desc')
            ->first();
        return response()->json(['planData' => $planData, 'activePlan' => $activePlan]);
    }

    public function subscriptionCheckout(Request $request)
    {
        $planId = $request->input('planId');
        $driverId = $request->input('driverId');

        $subscriptionData = SubscriptionPlan::find($planId);
        $driver = Driver::find($driverId);
        
        $subscriptionPlanId = $subscriptionData->id;
        $subscriptionTotalOrders = $subscriptionData->bookingLimit;
        
        if($planId == 1){
            $subscriptionData->vehicle_limit = '-1';
            $subscriptionData->driver_limit = '-1';
            $subscriptionTotalVehicle = '-1';
            $subscriptionTotalDriver = '-1';
        }else{
            $subscriptionTotalVehicle = $subscriptionData->vehicle_limit;
            $subscriptionTotalDriver = $subscriptionData->driver_limit;
        }
        
        $expiryDay = $subscriptionData->expiryDay;
        $expiryDate = intval($expiryDay) !== -1 ? Carbon::now()->addDays($expiryDay) : null;

        Driver::where('id', $driverId)->update([
            'subscriptionPlanId' => $subscriptionPlanId,
            'subscriptionExpiryDate' => $expiryDate,
            'subscriptionTotalOrders' => $subscriptionTotalOrders,
            'subscriptionTotalVehicle' => $subscriptionTotalVehicle,
            'subscriptionTotalDriver' => $subscriptionTotalDriver,
            'subscription_plan' => $subscriptionData
        ]);
        
        // Cancel any existing active subscription histories
        SubscriptionHistory::where('user_id', $driverId)->where('status', 'active')->update(['status' => 'cancelled']);
            
        SubscriptionHistory::create([
            'subscription_plan' => $subscriptionData,
            'expiry_date' => $expiryDate,
            'payment_type' => 5,
            'user_id' => $driverId,
            'plan_for' => $subscriptionData->plan_for,
            'subscriptionPlanId' => $subscriptionPlanId,
            'status' => 'active',
        ]);

        return response()->json('success');
    }
    
    public function updateLimit(Request $request, $id)
    {
        $driver = Driver::find($id);
        $data = $request->all();
        
        $bookingLimit = $data['set_booking_limit'] == 'limited' ? $data['booking_limit'] : '-1';
        $vehicleLimit = $data['set_vehicle_limit'] == 'limited' ? $data['vehicle_limit'] : '-1';
        $driverLimit = $data['set_driver_limit'] == 'limited' ? $data['driver_limit'] : '-1';

        $subscription_plan = $driver->subscription_plan;
        $subscription_plan['bookingLimit'] = $bookingLimit;
        $subscription_plan['vehicle_limit'] = $vehicleLimit;
        $subscription_plan['driver_limit'] = $driverLimit;

        Driver::where('id', $id)->update([
            'subscription_plan' => $subscription_plan,
            'subscriptionTotalOrders' => $bookingLimit,
            'subscriptionTotalVehicle' =>  $vehicleLimit,
            'subscriptionTotalDriver' => $driverLimit,
        ]);
        return redirect()->back();
    }

    public function allFleetDriver(Request $request)
    {

        $query = Driver::leftJoin('vehicule', 'vehicule.id_conducteur', '=', 'conducteur.id')
            ->leftJoin('conducteur as owner', 'conducteur.ownerId', '=', 'owner.id')
            ->leftJoin('type_vehicule', 'type_vehicule.id', '=', 'vehicule.id_type_vehicule')
            ->where('conducteur.role', '=', 'driver')
            ->whereNotNull('conducteur.ownerId')
            ->where('conducteur.ownerId', '!=', 0)
            ->select('conducteur.*', 'type_vehicule.libelle', 'owner.nom as ownerNom', 'owner.prenom as ownerPrenom');

        if ($request->filled('search') && $request->filled('selected_search')) {
            $keyword = $request->input('search');
            $field = $request->input('selected_search');
            $query->where(function ($q) use ($field, $keyword) {
                if ($field == "prenom") {
                    $q->where(function($sub) use ($keyword) {
                        $sub->where('conducteur.prenom', 'LIKE', '%' . $keyword . '%')
                            ->orWhere(DB::raw('CONCAT(conducteur.nom, " ", conducteur.prenom)'), 'LIKE', '%' . $keyword . '%')
                            ->orWhere(DB::raw('CONCAT(conducteur.prenom, " ", conducteur.nom)'), 'LIKE', '%' . $keyword . '%');
                    });
                } elseif ($field == "owner") {
                    $q->where(function($sub) use ($keyword) {
                        $sub->where('owner.prenom', 'LIKE', '%' . $keyword . '%')
                            ->orWhere(DB::raw('CONCAT(owner.nom, " ", owner.prenom)'), 'LIKE', '%' . $keyword . '%')
                            ->orWhere(DB::raw('CONCAT(owner.prenom, " ", owner.nom)'), 'LIKE', '%' . $keyword . '%');
                    });
                } elseif ($field == "vehicle_type") {
                    $q->where('type_vehicule.libelle', 'LIKE', '%' . $keyword . '%');
                }else {
                    $q->where('conducteur.' . $field, 'LIKE', '%' . $keyword . '%');
                }
            });
        }

        if ($request->filled('daterange')) {
            $dates = explode(' - ', $request->daterange);
            $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
            $endDate = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
            $query->whereBetween('conducteur.creer', [$startDate, $endDate]);
        }
        if ($request->has('status_selector') && $request->status_selector != '') {
            $status = $request->input('status_selector');
            $status == 'active' ? $query->where('conducteur.statut', 'yes') : $query->where('conducteur.statut', 'no');
        }

        $totalRecords = $query->get();
        $totalLength = count($totalRecords);
        $perPage = $request->input('per_page', 20);

        $drivers = $query->orderBy('conducteur.id', 'desc')->paginate($perPage)->appends($request->all());
        $drivers->map(function ($driver) {
            if (! empty($driver->email)) {
                $driver->email = Helper::shortEmail($driver->email);
            }
            if (! empty($driver->phone)) {
                $driver->phone = Helper::shortNumber($driver->phone);
            }
            return $driver;
        });

        $totalRide = DB::table('requete')
            ->leftjoin('user_app', 'requete.id_user_app', '=', 'user_app.id')
            ->join('conducteur', 'requete.id_conducteur', '=', 'conducteur.id')
            ->join('payment_method', 'requete.id_payment_method', '=', 'payment_method.id')
            ->select('requete.id_conducteur')
            ->orderBy('conducteur.id', 'desc')
            ->get();

        return view("fleet_drivers.index", compact('drivers', 'totalRide', 'totalLength','perPage'));
    }

    public function sendDocumentNotification(Request $request)
    {
        $driverId = $request->driver_id;
        $docTitle = $request->doc_title;

        $driver = DB::table('conducteur')->where('id', $driverId)->first();       
        if(!$driver || empty($driver->fcm_id)) {
            return response()->json(['success' => false, 'message' => 'Driver or FCM token not found']);
        }

        $message = "Your document '{$docTitle}' has been disapproved.";
        $title = "Document Disapproved";
       
        return GcmController::sendNotification($driver->fcm_id, [
            "body" => $message,
            "title" => $title
        ],$title);
    }

    public function getDriverLocation($driverId)
  	{
		$driver = Driver::find($driverId);
		$response['data'] = [ 'latitude' => $driver->latitude, 'longitude' => $driver->longitude ];
		return response()->json($response);
    }
}
