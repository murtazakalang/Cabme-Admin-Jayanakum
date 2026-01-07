<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Country;
use App\Models\DispatcherUser;
use App\Models\User;
use App\Models\Requests;
use App\Models\RentalOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Validator;
use Image;
use App\Helpers\Helper;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class DispatcherController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $sql= DB::table('dispatcher_user');
        if ($request->has('search') && $request->search != '' && $request->selected_search == 'prenom') {
            $search = $request->input('search');
            $sql->where('dispatcher_user.first_name', 'LIKE', '%'.$search.'%')
                ->orWhere(DB::raw('CONCAT(dispatcher_user.first_name, " ",dispatcher_user.last_name)'), 'LIKE', '%'.$search.'%');              
        } else if ($request->has('search') && $request->search != '' && $request->selected_search == 'phone') {
            $search = $request->input('search');
            $sql->where('dispatcher_user.phone', 'LIKE', '%'.$search.'%');          
        } else if ($request->has('search') && $request->search != '' && $request->selected_search == 'email') {
            $search = $request->input('search');
            $sql->where('dispatcher_user.email', 'LIKE', '%'.$search.'%');         
        }
        if ($request->filled('daterange')) {
            $dates = explode(' - ', $request->daterange);
            $startDate = Carbon::createFromFormat('d-m-Y', trim($dates[0]))->startOfDay();
            $endDate = Carbon::createFromFormat('d-m-Y', trim($dates[1]))->endOfDay();
            $sql->whereBetween('created_at', [$startDate, $endDate]);
        }
        if ($request->has('status_selector') && $request->status_selector != '') {
            $status = $request->input('status_selector');
            $status == 'active' ? $sql->where('status', 'yes') : $sql->where('status', 'no');
        }
        $totalRecords = $sql->get();
        $totalLength = count($totalRecords);
        $perPage = $request->input('per_page', 20);
        $users =$sql->orderBy('id','desc')->paginate($perPage)->appends($request->all());
        

         $users->map(function($user) {
            if(!empty($user->email)){
                $user->email = Helper::shortEmail($user->email);
            }
            if(!empty($user->phone)){
                $user->phone = Helper::shortNumber($user->phone);
            }
            return $user;
        });

        return view("dispatcher_user.index",compact('users', 'totalLength','perPage'));
    }

    public function createUser()
    {

        $countries = Country::where('statut', 'yes')
        ->select('id', 'code', 'libelle','phone','statut')
        ->get();
        return view("dispatcher_user.create", compact('countries'));
    }

    public function storeUser(Request $request)
    {


        if ($request->id > 0) {
            $image_validation = "mimes:jpeg,jpg,png";
            $doc_validation = "mimes:doc,pdf,docx,zip,txt";

        } else {
            $image_validation = "required|mimes:jpeg,jpg,png";
            $doc_validation = "required|mimes:doc,pdf,docx,zip,txt";

        }
        $validator = Validator::make($request->all(), $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
            'phone' => 'required|unique:dispatcher_user',
            'country_code' => 'required',
            'email' => 'required|unique:dispatcher_user',
            'profile_picture' => $image_validation,
        ], $messages = [
            'first_name.required' => trans('lang.the_first_name_field_is_required'),
            'last_name.required' => trans('lang.the_last_name_field_is_required'),
            'email.required' => trans('lang.the_email_field_is_required'),
            'email.unique' => trans('lang.the_email_is_already_taken'),
            'password.required' => trans('lang.the_password_field_is_required'),
            'confirm_password.same' => trans('lang.confirm_password_should_match_the_password'),
            'phone.required' => trans('lang.the_phone_is_required'),
            'country_code.required' => trans('lang.the_country_code_field_is_required'),
            'phone.unique' => trans('lang.the_phone_field_is_should_be_unique'),
            'profile_picture.required' => trans('lang.the_profile_image_is_required'),
        ]);

        if ($validator->fails()) {
            return redirect('dispatcher-users/create')
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }
        $user = new DispatcherUser;
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->phone = $request->input('phone');
        $user->country_code = $request->input('country_code');
        $user->status = $request->has('status') ? 'yes' : 'no';
        $user->created_at = date('Y-m-d H:i:s');
        $user->updated_at = date('Y-m-d H:i:s');

        if ($request->hasfile('profile_picture')) {
            $file = $request->file('profile_picture');
            $extenstion = $file->getClientOriginalExtension();
            $time = time() . '.' . $extenstion;
            $filename = 'dispatcher_user_profile' . $time;
            $path = public_path('assets/images/dispatcher_users/') . $filename;
            if (!file_exists(public_path('assets/images/dispatcher_users/'))) {
                mkdir(public_path('assets/images/dispatcher_users/'), 0777, true);
            }
            Image::make($file->getRealPath())->resize(150, 150)->save($path);
            $user->profile_picture_path = asset('assets/images/dispatcher_users/' . $filename);
        }

        $user->save();
       
        return redirect('dispatcher-users')->with('message', trans('lang.dispatcher_user_created_successfully'));
    }

    public function appUsers()
    {
        return view("settings.users.index");
    }

    public function editUser($id)
    {

        $user = DispatcherUser::where('id', "=", $id)->first();
         $countries = Country::where('statut', 'yes')
        ->select('id', 'code', 'libelle','phone','statut')
        ->get();


        if (!empty($user['email'])) { 
            $user['email'] = Helper::shortEmail($user['email']);
        } 
        if (!empty($user['phone'])) { 
            $user['phone'] = Helper::shortNumber($user['phone']); 
        }

        $rides = DB::select("SELECT count(id) as rides

        FROM requete WHERE statut='completed' AND id_user_app=$id");
        return view("dispatcher_user.edit")->with("user", $user)->with("rides", $rides)->with("countries", $countries);
    }

    public function userShow($id)
    {
        $user = DispatcherUser::find($id);
        if (!empty($user['email'])) { 
            $user['email'] = Helper::shortEmail($user['email']);
        } 
        if (!empty($user['phone'])) { 
            $user['phone'] = Helper::shortNumber($user['phone']); 
        }

        $currency = Currency::where('statut', 'yes')->first();

        $transactions = [];
        $rides = [];
        
        $rides = Requests::leftjoin('user_app', 'requete.id_user_app', '=', 'user_app.id')
        ->leftjoin('conducteur', 'requete.id_conducteur', '=', 'conducteur.id')
        ->join('payment_method', 'requete.id_payment_method', '=', 'payment_method.id')
        ->select('requete.*','conducteur.id as driver_id', 'conducteur.prenom as driverPrenom', 'conducteur.nom as driverNom', 'user_app.id as user_id', 'user_app.prenom as userPrenom', 'user_app.nom as userNom', 'payment_method.libelle', 'payment_method.image')
        ->where('requete.dispatcher_id', '=', $id)
        ->paginate(10);

        $rental = RentalOrder::leftjoin('user_app', 'rental_orders.id_user_app', '=', 'user_app.id')
        ->leftjoin('conducteur', 'rental_orders.id_conducteur', '=', 'conducteur.id')
        ->join('payment_method', 'rental_orders.id_payment_method', '=', 'payment_method.id')
        ->select('rental_orders.*', 'conducteur.id as driver_id', 'conducteur.prenom as driverPrenom', 'conducteur.nom as driverNom', 'user_app.id as user_id', 'user_app.prenom as userPrenom', 'user_app.nom as userNom', 'payment_method.libelle', 'payment_method.image')
        ->where('rental_orders.dispatcher_id', '=', $id)
        ->paginate(10);
        
        return view("dispatcher_user.show")->with("user", $user)->with("rides", $rides)->with("rental", $rental)->with("transactions", $transactions)->with("currency", $currency);
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
            'first_name' => 'required',
            'last_name' => 'required',          
            'profile_picture' => $image_validation,
        ], $messages = [
            'first_name.required' => trans('lang.the_first_name_field_is_required'),
            'last_name.required' => trans('lang.the_last_name_field_is_required'),           
            'profile_picture.required' => trans('lang.the_profile_image_is_required'),
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }

        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');

        $user = DispatcherUser::find($id);
        if ($user) {
            $user->first_name = $first_name;
            $user->last_name = $last_name;
            $user->status = $request->has('status') ? 'yes' : 'no';
            if ($request->hasfile('profile_picture')) {
                $relativePath = str_replace(url('/') . '/', '', $user->profile_picture_path);
                $destination = public_path($relativePath);
                if (File::exists($destination)) {
                    File::delete($destination);
                }
                $file = $request->file('profile_picture');
                $extenstion = $file->getClientOriginalExtension();
                $time = time() . '.' . $extenstion;
                $filename = 'dispatcher_user_profile' . $time;
                $path = public_path('assets/images/dispatcher_users/') . $filename;
                if (!file_exists(public_path('assets/images/dispatcher_users/'))) {
                    mkdir(public_path('assets/images/dispatcher_users/'), 0777, true);
                }
                Image::make($file->getRealPath())->resize(150, 150)->save($path);

                $user->profile_picture_path = asset('assets/images/dispatcher_users/' . $filename);
            }

            $user->save();
        }

        return redirect('dispatcher-users')->with('message', trans('lang.dispatcher_user_updated_successfully'));
    }

    public function deleteUser($id)
    {

        if ($id != "") {

            $id = json_decode($id);

            if (is_array($id)) {

                for ($i = 0; $i < count($id); $i++) {
                    $user = DispatcherUser::find($id[$i]);

                    if(!empty($user->profile_picture_path)){
                        $relativePath = str_replace(url('/') . '/', '', $user->profile_picture_path);
                        $destination = public_path($relativePath);
                        if (File::exists($destination)) {
                            File::delete($destination);
                        }
                    }

                    $user->delete();
                }

            } else {
                $user = DispatcherUser::find($id);
                if(!empty($user->profile_picture_path)){
                    $relativePath = str_replace(url('/') . '/', '', $user->profile_picture_path);
                    $destination = public_path($relativePath);
                    if (File::exists($destination)) {
                        File::delete($destination);
                    }
                }
                $user->delete();
            }

        }

        return redirect()->back();
    }

    public function userChangeStatus($id)
    {
        $user = DispatcherUser::find($id);
        if ($user->status == 'no') {
            $user->status = 'yes';
        } else {
            $user->status = 'no';
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
        $user = DispatcherUser::find($id);

        if ($ischeck == "true") {
            $user->status = 'yes';
        } else {
            $user->status = 'no';
        }
        $user->save();

    }

}
