<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Country;
use App\Models\ParcelOrder;
use App\Models\RentalOrder;
use App\Models\Referral;
use App\Models\Requests;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserApp;
use App\Models\VehicleLocation;
use App\Models\AccessToken;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use File;
use Image;
use Validator;
use App\Helpers\Helper;
use Carbon\Carbon;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $sql = UserApp::where('user_app.deleted_at', '=', NULL);

        if ($request->has('search') && $request->search != '' && $request->selected_search == 'prenom') {
            $search = $request->input('search');       
                $sql->where('user_app.prenom', 'LIKE', '%' . $search . '%')
                ->orWhere(DB::raw('CONCAT(user_app.prenom, " ",user_app.nom)'), 'LIKE', '%' . $search . '%');
        } else if ($request->has('search') && $request->search != '' && $request->selected_search == 'phone') {
            $search = $request->input('search');
            $sql->where('user_app.phone', 'LIKE', '%'.$search.'%');
        } else if ($request->has('search') && $request->search != '' && $request->selected_search == 'email') {
            $search = $request->input('search');
            $sql->where('user_app.email', 'LIKE', '%'.$search.'%');
        }
        
        if ($request->filled('daterange')) {
            $dates = explode(' - ', $request->daterange);
            $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
            $endDate = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
            $sql->whereBetween('creer', [$startDate, $endDate]);
        }

        if($request->has('status_selector') && $request->status_selector!=''){
            $status= $request->input('status_selector');
            $status == 'active' ? $sql->where('statut', 'yes') : $sql->where('statut', 'no');
        }
        
        $totalRecords = $sql->get();
        $totalLength = count($totalRecords);
        $perPage = $request->input('per_page', 20);
        $users = $sql->orderBy('user_app.id', 'desc')->paginate($perPage)->appends($request->all());
        
        $users->map(function($user) {
            if(!empty($user->email)){
                $user->email = Helper::shortEmail($user->email);
            }
            if(!empty($user->phone)){
                $user->phone = Helper::shortNumber($user->phone);
            }
            return $user;
        });
        
        return view("users.index",compact('users','totalLength','perPage'));
    }

    public function create()
    {
        $countries = Country::where('statut', 'yes')
        ->select('id', 'code', 'libelle','phone','statut')
        ->get();
        return view("users.create", compact('countries'));
    }

    public function storeuser(Request $request)
    {

        $validator = Validator::make($request->all(), $rules = [
            'nom' => 'required',
            'prenom' => 'required',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
            'country_code' => 'required',
            'phone' => 'required|unique:user_app',
            'email' => 'required|unique:user_app',
           
        ], $messages = [
            'nom.required' => trans('lang.the_first_name_field_is_required'),
            'prenom.required' => trans('lang.the_last_name_field_is_required'),
            'email.required' => trans('lang.the_email_field_is_required'),
            'email.unique' => trans('lang.the_email_is_already_taken'),
            'password.required' => trans('lang.the_password_field_is_required'),
            'confirm_password.same' => trans('lang.confirm_password_should_match_the_password'),
            'country_code.required' => trans('lang.the_country_code_field_is_required'),
            'phone.required' => trans('lang.the_phone_is_required'),
            'phone.unique' => trans('lang.the_phone_field_is_should_be_unique'),
        ]);

        if ($validator->fails()) {
            return redirect('users/create')
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }
        $user = new UserApp;
        $user->nom = $request->input('nom');
        $user->prenom = $request->input('prenom');
        $user->email = $request->input('email');
        $user->country_code = '+'. $request->input('country_code');
        $user->mdp = Hash::make($request->input('password'));
        $user->login_type = 'email';
        $user->phone = $request->input('phone');
        $user->statut = $request->has('statut') ? 'yes' : 'no';
        $user->photo = '';
        $user->photo_nic = '';
        $user->creer = date('Y-m-d H:i:s');
        $user->modifier = date('Y-m-d H:i:s');
        $user->updated_at = date('Y-m-d H:i:s');

        if ($request->hasfile('photo')) {
            $file = $request->file('photo');
            $extenstion = $file->getClientOriginalExtension();
            $time = time() . '.' . $extenstion;
            $filename = 'user_image' . $time;
            $path = public_path('assets/images/users/') . $filename;
            if (!file_exists(public_path('assets/images/users/'))) {
                mkdir(public_path('assets/images/users/'), 0777, true);
            }
            Image::make($file->getRealPath())->resize(100, 100)->save($path);
            
            $image = str_replace('data:image/png;base64,', '', $file);
            $image = str_replace(' ', '+', $image);
            $user->photo_path = $filename;
        }
        $user->save();

        $referral = new Referral;
        $referral->user_id = $user->id;
        $referral->referral_code = Str::random(5);
        $referral->code_used = "false";
        $referral->created_at = date('Y-m-d H:i:s');
        $referral->save();

        return redirect('users')->with('message', trans('lang.user_created_successfully'));
    }

    public function appUsers()
    {
        return view("users.index");
    }

    public function edit($id)
    {
        $user = UserApp::where('id', "=", $id)->first();
          
        $rides = DB::select("SELECT count(id) as rides FROM requete WHERE statut='completed' AND id_user_app=$id");

        $countries = Country::where('statut', 'yes')
        ->select('id', 'code', 'libelle','phone','statut')
        ->get();

        if (!empty($user['email'])) { 
            $user['email'] = Helper::shortEmail($user['email']);
        } 
        if (!empty($user['phone'])) { 
            $user['phone'] = Helper::shortNumber($user['phone']); 
        }

        return view("users.edit")->with("user", $user)->with("rides", $rides)->with("countries",$countries);
    }

    public function show($id)
    {

        $user = UserApp::where('id', "=", $id)->first();

        if (!empty($user['email'])) { 
            $user['email'] = Helper::shortEmail($user['email']);
        } 
        if (!empty($user['phone'])) { 
            $user['phone'] = Helper::shortNumber($user['phone']); 
        }

        $currency = Currency::where('statut', 'yes')->first();

        $transactions = Transaction::join('payment_method', 'transactions.payment_method', '=', 'payment_method.libelle')
            ->select('transactions.*', 'payment_method.image')
            ->where('user_id', "=", $id)->orderBy('transactions.id', 'desc')->paginate(10);

        $rides = Requests::join('user_app', 'requete.id_user_app', '=', 'user_app.id')
            ->leftjoin('conducteur', 'requete.id_conducteur', '=', 'conducteur.id')
            ->leftjoin('payment_method', 'requete.id_payment_method', '=', 'payment_method.id')
            ->select('requete.id', 'requete.statut', 'requete.statut_paiement', 'requete.depart_name', 'requete.destination_name', 'requete.distance', 'requete.montant', 'requete.creer', 'conducteur.id as driver_id', 'conducteur.prenom as driverPrenom', 'conducteur.nom as driverNom', 'user_app.id as user_id', 'user_app.prenom as userPrenom', 'user_app.nom as userNom', 'payment_method.libelle', 'payment_method.image')
            ->where('requete.id_user_app', $id)
            ->orderBy('requete.id', 'DESC');
        $totalRides = count($rides->get());
        $rides = $rides->paginate(10);

        $parcelOrders = ParcelOrder::join('user_app', 'parcel_orders.id_user_app', '=', 'user_app.id')
            ->leftjoin('conducteur', 'parcel_orders.id_conducteur', '=', 'conducteur.id')
            ->leftjoin('payment_method', 'parcel_orders.id_payment_method', '=', 'payment_method.id')
            ->select('parcel_orders.id', 'parcel_orders.status', 'parcel_orders.created_at', 'conducteur.id as driver_id', 'conducteur.prenom as driverPrenom', 'conducteur.nom as driverNom')
            ->where('parcel_orders.id_user_app', $id)
            ->orderBy('parcel_orders.id', 'DESC');
        $totalParcelOrders = count($parcelOrders->get());
        $parcelOrders = $parcelOrders->paginate(10);


        $rentalOrders = RentalOrder::join('user_app', 'rental_orders.id_user_app', '=', 'user_app.id')
            ->leftjoin('conducteur', 'rental_orders.id_conducteur', '=', 'conducteur.id')
            ->leftjoin('payment_method', 'rental_orders.id_payment_method', '=', 'payment_method.id')
            ->select('rental_orders.id', 'rental_orders.status', 'rental_orders.created_at', 'conducteur.id as driver_id', 'conducteur.prenom as driverPrenom', 'conducteur.nom as driverNom')
            ->where('rental_orders.id_user_app', $id)
            ->orderBy('rental_orders.id', 'DESC');
        $totalrentalOrders = count($rentalOrders->get());
        $rentalOrders = $rentalOrders->paginate(10);

        return view("users.show",compact('user', 'rides', 'transactions', 'currency', 'parcelOrders','totalRides','totalParcelOrders','rentalOrders','totalrentalOrders'));
    }

    public function userUpdate(Request $request, $id)
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
      
        $device_id = $request->input('device_id');

       
        if ($request->input('statut')) {
            $status = "yes";
        } else {
            $status = "no";
        }
        


        $user = UserApp::find($id);
        if ($user) {
            $user->nom = $nom;
            $user->prenom = $prenom;            
            $user->device_id = $device_id;
            $user->statut = $request->has('statut') ? 'yes' : 'no';
            
            if ($request->hasfile('photo')) {

                $destination = public_path('assets/images/users/' . $user->photo_path);
                if (File::exists($destination)) {
                    File::delete($destination);
                }
                $file = $request->file('photo');
                $extenstion = $file->getClientOriginalExtension();
                $time = time() . '.' . $extenstion;
                $filename = 'user_' . $id . '.' . $extenstion;
                $path = public_path('assets/images/users/') . $filename;
                if (!file_exists(public_path('assets/images/users/'))) {
                    mkdir(public_path('assets/images/users/'), 0777, true);
                }
                Image::make($file->getRealPath())->resize(100, 100)->save($path);
                
                $user->photo_path = $filename;
            }
            $user->save();
        }

        return redirect('users')->with('message', trans('lang.user_updated_successfully'));
    }

    public function deleteUser($id)
    {

        if ($id != "") {

            $id = json_decode($id);


            if (is_array($id)) {

                for ($i = 0; $i < count($id); $i++) {
                    $rides = Requests::where('id_user_app', $id[$i]);
                    if ($rides) {
                        $rides->delete();
                    }
                    $parcels = ParcelOrder::where('id_user_app', $id[$i]);
                    if ($parcels) {
                        $parcels->delete();
                    }

                    $vehicle_location = VehicleLocation::where('id_user_app', $id[$i]);
                    if ($vehicle_location) {
                        $vehicle_location->delete();
                    }

                    $Transaction = Transaction::where('user_id', $id[$i]);
                    if ($Transaction) {
                        $Transaction->delete();
                    }

                    $Referral = Referral::where('user_id', $id[$i]);
                    if ($Referral) {
                        $Referral->delete();
                    }

                    $user = UserApp::find($id[$i]);
                    $destination = public_path('assets/images/users/' . $user->photo_path);
                    if (File::exists($destination)) {
                        File::delete($destination);
                    }
                    
                    $AccessToken = AccessToken::where('user_id', $id[$i]);
                    if ($AccessToken) {
                        $AccessToken->delete();
                    }

                    $user->delete();
                }

            } else {

                $rides = Requests::where('id_user_app', $id);
                if ($rides) {
                    $rides->delete();
                }
                $parcels = ParcelOrder::where('id_user_app', $id);
                if ($parcels) {
                    $parcels->delete();
                }

                $vehicle_location = VehicleLocation::where('id_user_app', $id);
                if ($vehicle_location) {
                    $vehicle_location->delete();
                }

                $Transaction = Transaction::where('user_id', $id);
                if ($Transaction) {
                    $Transaction->delete();
                }

                $Referral = Referral::where('user_id', $id);
                if ($Referral) {
                    $Referral->delete();
                }

                $user = UserApp::find($id);
                $destination = public_path('assets/images/users/' . $user->photo_path);
                if (File::exists($destination)) {
                    File::delete($destination);
                }

                $AccessToken = AccessToken::where('user_id', $id);
                if ($AccessToken) {
                    $AccessToken->delete();
                }
                
                $user->delete();
            }

        }

        return redirect()->back();
    }

    public function addWallet(Request $request, $id)
    {
        $user = UserApp::find($id);
        $amount = $request->amount;
        if ($amount == '' || $amount == null) {
            $amount = 0;
        }
        if ($user) {
            $userWallet = floatval($user->amount) + floatval($amount);
            $user->amount = (string)$userWallet;
            $user->save();
        }
        $date = date('Y-m-d H:i:s');
        $txnId = uniqid() . mt_rand(1000, 9999);
        Transaction::create([
            'amount' => $amount,
            'payment_method' => 'Wallet',
            'user_id' => $id,
            'user_type'=>'customer',
            'is_credited' => '1',
            'note'=>'Wallet TopUp',
            'transaction_id'=> $txnId
        ]);
        $user = UserApp::find($id);
        $email = $user->email;
        $date = date('d F Y');

        if (!empty($email)) {

            $emailsubject = '';
            $emailmessage = '';
            $emailtemplate = DB::table('email_template')->select('*')->where('type', 'wallet_topup')->first();
            if (!empty($emailtemplate)) {
                $emailsubject = $emailtemplate->subject;
                $emailmessage = $emailtemplate->message;
                $send_to_admin = $emailtemplate->send_to_admin;
            }
            $currencyData = Currency::select('*')->where('statut', 'yes')->first();
            
            if ($currencyData->symbol_at_right == "true") {
                $amount = number_format($amount, $currencyData->decimal_digit) . $currencyData->symbole;
                $newBalance = number_format($user['amount'], $currencyData->decimal_digit) . $currencyData->symbole;
            } else {
                $amount = $currencyData->symbole . number_format($amount, $currencyData->decimal_digit);
                $newBalance = $currencyData->symbole . number_format($user['amount'], $currencyData->decimal_digit);
            }

            $contact_us_email = Settings::select('contact_us_email')->value('contact_us_email');
            $contact_us_email = $contact_us_email ? $contact_us_email : 'none@none.com';
            $app_name = env('APP_NAME', 'Cabme');
            if ($send_to_admin == "true") {
                $to = $email . "," . $contact_us_email;

            } else {
                $to = $email;
            }

            $emailmessage = str_replace("{AppName}", $app_name, $emailmessage);
            $emailmessage = str_replace("{UserName}", $user['nom'] . " " . $user['prenom'], $emailmessage);
            $emailmessage = str_replace("{Amount}", $amount, $emailmessage);
            $emailmessage = str_replace("{PaymentMethod}", 'Wallet', $emailmessage);
            $emailmessage = str_replace('{TransactionId}', $txnId, $emailmessage);
            $emailmessage = str_replace('{Balance}', $newBalance, $emailmessage);
            $emailmessage = str_replace('{Date}', $date, $emailmessage);

            // Always set content-type when sending HTML email
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: ' . $app_name . '<' . $contact_us_email . '>' . "\r\n";
            mail($to, $emailsubject, $emailmessage, $headers);
        }

        return redirect('users/show/' . $id);
    }

    public function profile()
    {
        $user = Auth::user();
        return view('users.profile', compact(['user']));
    }


    public function changeStatus($id)
    {
        $user = UserApp::find($id);
        if ($user->statut == 'no') {
            $user->statut = 'yes';
        } else {
            $user->statut = 'no';
        }
        $user->save();
        return redirect()->back();

    }


    public function update(Request $request, $id)
    {
        $name = $request->input('name');
        $password = $request->input('password');
        $old_password = $request->input('old_password');
        $email = $request->input('email');
        
        if ($password == '') {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:255',
                'email' => 'required|email'
            ]);
        } else {
            $user = Auth::user();
            if (password_verify($old_password, $user->password)) {
                $validator = Validator::make($request->all(), [
                    'name' => 'required|max:255',
                    'password' => 'required|min:8',
                    'confirm_password' => 'required|same:password',
                    'email' => 'required|email'
                ]);

            } else {
                return Redirect()->back()->with(['message' => trans('lang.please_enter_correct_old_password')]);
            }

        }

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return Redirect()->back()->with(['message' => $error]);
        }

        $user = User::find($id);
        if ($user) {
            $user->name = $name;
            $user->email = $email;
            if ($password != '') {
                $user->password = Hash::make($password);
            }
            $user->save();
        }

        return redirect()->back();
    }

    public function toggalSwitch(Request $request)
    {
        $ischeck = $request->input('ischeck');
        $id = $request->input('id');
        $user = UserApp::find($id);

        if ($ischeck == "true") {
            $user->statut = 'yes';
        } else {
            $user->statut = 'no';
        }
        $user->save();

    }

}
