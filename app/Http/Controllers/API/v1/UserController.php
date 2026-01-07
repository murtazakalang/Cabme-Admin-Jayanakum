<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\UserApp;
use App\Models\Driver;
use App\Models\DispatcherUser;
use App\Models\Currency;
use App\Models\Country;
use App\Models\Referral;
use App\Models\Commission;
use App\Models\EmailTemplate;
use App\Models\Settings;
use App\Models\AccessToken;
use App\Models\Vehicle;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Validator;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname'     => 'required|string|max:255',
            'lastname'      => 'required|string|max:255',
            'phone'         => 'required|string|max:20',
            'country_code'  => 'required|string',
            'email'         => 'required|email|max:255',
            'password'      => 'required_unless:login_type,google,apple|min:6',
            'login_type'    => 'required',
            'tonotify'      => 'nullable|string',
            'account_type'  => 'required|in:customer,driver,owner',
            'referral_code' => 'nullable|string',
            'company_name'  => 'nullable|string',
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'code'    => 404,
                'message' => $validator->errors()->first(),
                'data'    => null,
            ]);
        }

        $prenom       = $request->firstname;
        $nom          = $request->lastname;
        $phone        = $request->phone;
        $country_code = $request->country_code;
        $email        = $request->email;
        $password     = Hash::make($request->password);
        $login_type   = $request->login_type;
        $tonotify     = $request->tonotify;
        $account_type = $request->account_type;
        $referral_code = $request->referral_code;
        $companyName  = $request->company_name;
        $service_type  = $request->service_type;
        $now          = now();
        $commission   = Commission::first();
        
        // Check duplicates
        if ($account_type === 'customer') {

            if (UserApp::where('phone', $phone)->where('country_code', $country_code)->exists()) {
                return response()->json(['success' => 'Failed', 'message' => 'Phone number already exists.']);
            }
            if (UserApp::where('email', $email)->exists()) {
                return response()->json(['success' => 'Failed', 'message' => 'Email already exists.']);
            }
            // Create user
            $user = UserApp::create([
                'prenom'     => $prenom,
                'nom'        => $nom,
                'phone'      => $phone,
                'country_code' => $country_code,
                'mdp'        => $password,
                'statut'     => 'yes',
                'login_type' => $login_type,
                'tonotify'   => $tonotify,
                'creer'      => $now,
                'statut_nic' => 'no',
                'email'      => $email,
                'age'        => $request->age ?? null,
                'gender'     => $request->gender ?? null,
            ]);

            // Handle referral
            $referralBy = null;
            if (!empty($referral_code)) {
                $referralBy = Referral::where('referral_code', $referral_code)->value('user_id');
            }

            $userReferralCode = strtoupper(substr(uniqid(), rand(1, 5), 5));
            Referral::create([
                'user_id'        => $user->id,
                'referral_by_id' => $referralBy,
                'referral_code'  => $userReferralCode,
                'code_used'      => 'false',
            ]);

            // Add extra info
            $user->user_cat   = 'user_app';
            $user->accesstoken = $this->adduseraccess($user->id, 'customer');

            $currency = Currency::where('statut', 'yes')->first();
            $country  = Country::where('statut', 'yes')->first();
            
            $response = [
                'id'             => (string) $user->id,
                'firstname'      => $user->prenom,
                'lastname'       => $user->nom,
                'phone'          => $user->phone,
                'country_code'   => $user->country_code,
                'email'          => $user->email,
                'user_cat'       => $user->user_cat,
                'accesstoken'    => $user->accesstoken,
                'currency'       => $currency->symbole ?? '',
                'decimal_digit'  => $currency->decimal_digit ?? '',
                'country'        => $country->code ?? '',
                'referral_code'  => $userReferralCode,
                'referral_by'    => $referralBy,
            ];

            return response()->json([
                'success' => 'success',
                'message' => 'User registered successfully.',
                'data'    => $response,
            ]);
        }

        if (in_array($account_type, ['driver', 'owner'])) {

            if (Driver::where('phone', $phone)->where('country_code', $country_code)->exists()) {
                return response()->json(['success' => 'Failed', 'message' => 'Phone number already exists.']);
            }

            if (Driver::where('email', $email)->exists()) {
                return response()->json(['success' => 'Failed', 'message' => 'Email already exists.']);
            }

            $settings = Settings::first();
            
            $driverData = [
                'online'           => 'no',
                'prenom'           => $prenom,
                'nom'              => $nom,
                'phone'            => $phone,
                'country_code'     => $country_code,
                'mdp'              => $password,
                'statut'           => 'yes',
                'login_type'       => $login_type,
                'tonotify'         => $tonotify,
                'creer'            => $now,
                'updated_at'       => $now,
                'status_car_image' => 'no',
                'statut_vehicule'  => 'no',
                'email'            => $email,
                'address'          => '',
                'amount'           => '0',
                'driver_on_ride'   => 'no',
                'is_verified'      => '1',
                'role'             => $account_type === 'owner' ? 'owner' : 'driver',
                'isOwner'          => $account_type === 'owner' ? 'true' : 'false',
                'adminCommission'  => [
                    'type'  => $commission->type ?? '',
                    'value' => $commission->value ?? '',
                ],
                'service_type'  => $service_type
            ];
            
            if($settings->driver_doc_verification == "yes" && $account_type == 'driver'){
                $driverData['is_verified'] = '0';
            }
            if($settings->owner_doc_verification == "yes" && $account_type == 'owner'){
                $driverData['is_verified'] = '0';
            }

            $driver = Driver::create($driverData);
            $driver->accesstoken = $this->adduseraccess($driver->id, $account_type);
            $driver->user_cat    = $account_type;

            $currency = Currency::where('statut', 'yes')->first();
            $country  = Country::where('statut', 'yes')->first();
            
            $response = [
                'id'               => (string) $driver->id,
                'firstname'        => $driver->prenom,
                'lastname'         => $driver->nom,
                'phone'            => $driver->phone,
                'country_code'     => $driver->country_code,
                'email'            => $driver->email,
                'user_cat'         => $driver->user_cat,
                'accesstoken'      => $driver->accesstoken,
                'currency'         => $currency->symbole ?? '',
                'country'          => $country->code ?? '',
                'admin_commission' => [
                    'type'  => $commission->type ?? '',
                    'value' => $commission->value ?? '',
                ],
                'isOwner' => $account_type === 'owner' ? 'true' : 'false',
                'service_type' => explode(',',$driverData['service_type'])
            ];

            // Get email template
            $emailtemplate = EmailTemplate::where('type', 'new_registration')->first();
            $emailsubject = $emailtemplate->subject;
            $emailmessage = $emailtemplate->message;
            
            $contact_us_email = Settings::select('contact_us_email')->value('contact_us_email');
            $admin_email = $contact_us_email ? $contact_us_email : 'none@none.com';
            $appName = env('APP_NAME', 'Cabme');
            $to = $email;

            $date = now()->format('d F Y');
            $emailmessage = str_replace(
                ['{AppName}', '{UserName}', '{UserEmail}', '{UserPhone}', '{UserId}', '{Date}'],
                [$appName, "{$driver->nom} {$driver->prenom}", $driver->email, $driver->phone, $driver->id, $date],
                $emailtemplate->message
            );

            Mail::html($emailmessage, function ($message) use ($to, $admin_email, $emailsubject, $emailtemplate) {
                $message->to($to)->subject($emailsubject);
                if ($emailtemplate->send_to_admin) {
                    $message->cc($admin_email);
                }
            });

            if($account_type == 'owner'){
                //Create dispatcher user
                $dispatcher_user = new DispatcherUser;
                $dispatcher_user->first_name = $prenom;
                $dispatcher_user->last_name = $nom;
                $dispatcher_user->email = $email;
                $dispatcher_user->password = $password;
                $dispatcher_user->phone = $phone;
                $dispatcher_user->country_code = $country_code;
                $dispatcher_user->status = 'yes';
                $dispatcher_user->isOwner = 'yes';
                $dispatcher_user->ownerId = $driver->id;
                $dispatcher_user->created_at = date('Y-m-d H:i:s');
                $dispatcher_user->updated_at = date('Y-m-d H:i:s');
                $dispatcher_user->save();
            }
            
            return response()->json([
                'success' => 'success',
                'message' => ucfirst($account_type) . ' registered successfully.',
                'data'    => $response,
            ]);
        }

        return response()->json([
            'success' => 'Failed',
            'message' => 'Invalid account type.',
        ]);
    }

    public function addOwnerDriver(Request $request){

        $validator = Validator::make($request->all(), [
            'id_driver'     => 'nullable|integer|exists:conducteur,id',
            'firstname'     => 'required|string|max:255',
            'lastname'      => 'required|string|max:255',
            'phone'         => 'required|string|max:20',
            'country_code'  => 'required|string',
            'email'         => 'required|email|max:255',
            'password'      => 'nullable|string|min:6',
            'owner_id'      => 'required|integer',
            'service_type'  => 'required|string',
            'zoneIds'       => 'required|string',
            'vehicleId'     => 'nullable|integer|exists:vehicule,id',
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'code'    => 404,
                'message' => $validator->errors()->first(),
                'data'    => null,
            ]);
        }

        $id_driver    = $request->id_driver;
        $prenom       = $request->firstname;
        $nom          = $request->lastname;
        $phone        = $request->phone;
        $country_code = $request->country_code;
        $email        = $request->email;
        $ownerId      = $request->owner_id;
        $service_type = $request->service_type;
        $zoneIds      = $request->zoneIds;
        $vehicleId    = $request->vehicleId;
        $now          = now();
        $commission   = Commission::first();
        
        if(!empty($id_driver)){

            $driverData = [
                'prenom'           => $prenom,
                'nom'              => $nom,
                'phone'            => $phone,
                'country_code'     => $country_code,
                'modifier'         => $now,
                'email'            => $email,
                'ownerId'          => $ownerId,
                'zone_id'          => $zoneIds,
                'service_type'     => $service_type,
            ];
            if(!empty($request->password)){
                $driverData['mdp'] = Hash::make($request->password);
            }

            $driver = Driver::find($id_driver);
            if($driver){
                $driver->update($driverData);
            }
            
            if(!empty($vehicleId)){
                $vehicle = Vehicle::find($vehicleId);
                if($vehicle){
                    $vehicle->id_conducteur = $id_driver;
                    $vehicle->save();

                    $driver->statut_vehicule = 'yes';
                    $driver->save();
                }
            }else{
                $vehicle = Vehicle::where('id_conducteur',$id_driver)->first();
                if($vehicle){
                    $vehicle->id_conducteur = '';
                    $vehicle->save();

                    $driver->statut_vehicule = 'no';
                    $driver->save();
                }
            }

        }else{

            if (Driver::where('phone', $phone)->where('country_code', $country_code)->exists()) {
                return response()->json(['success' => 'Failed', 'message' => 'Phone number already exists.']);
            }

            if (Driver::where('email', $email)->exists()) {
                return response()->json(['success' => 'Failed', 'message' => 'Email already exists.']);
            }
            
            if(!empty($vehicleId)){
                $vehicle = Vehicle::find($vehicleId);
                if($vehicle && !empty($vehicle->id_conducteur)){
                    return response()->json(['success' => 'Failed', 'message' => 'Vehicle already assigned to another driver.']);
                }
            }

            $ownerData = Driver::find($ownerId);
            if ($ownerData->subscriptionTotalDriver != -1 && $ownerData->subscriptionTotalDriver <= 0) {
                return response()->json([
                    'success' => 'Failed', 
                    'message' => 'Your have reached the maximum driver create limit for the current plan, upgrade the subscription to continue.'
                ]);
            }

            $driverData = [
                'online'           => 'no',
                'prenom'           => $prenom,
                'nom'              => $nom,
                'phone'            => $phone,
                'country_code'     => $country_code,
                'mdp'              => Hash::make($request->password),
                'statut'           => 'yes',
                'login_type'       => 'email',
                'tonotify'         => 'yes',
                'creer'            => $now,
                'status_car_image' => 'no',
                'statut_vehicule'  => 'no',
                'email'            => $email,
                'address'          => '',
                'amount'           =>'0',
                'is_verified'      => '1',
                'driver_on_ride'   => 'no',
                'role'             => 'driver',
                'isOwner'          => 'false',
                'ownerId'          => $ownerId,
                'zone_id'          => $zoneIds,
                'service_type'     => $service_type,
                'adminCommission'  => [
                    'type'  => $commission->type ?? '',
                    'value' => $commission->value ?? '',
                ],
            ];

            $driver = Driver::create($driverData);

            //Reset limit
            Helper::resetDriverSubscriptionLimit($driver->id, 'subscriptionTotalDriver', 'dec');
                    
            //Assign vehicle to driver
            if(!empty($vehicleId)){
                $vehicle->id_conducteur = $driver->id;
                $vehicle->save();

                $driver->statut_vehicule = 'yes';
                $driver->save();
            }
        
            // Get email template
            $emailtemplate = EmailTemplate::where('type', 'new_registration')->first();
            $emailsubject = $emailtemplate->subject;
            $emailmessage = $emailtemplate->message;
            
            $contact_us_email = Settings::select('contact_us_email')->value('contact_us_email');
            $admin_email = $contact_us_email ? $contact_us_email : 'none@none.com';
            $appName = env('APP_NAME', 'Cabme');
            $to = $email;

            $date = now()->format('d F Y');
            $emailmessage = str_replace(
                ['{AppName}', '{UserName}', '{UserEmail}', '{UserPhone}', '{UserId}', '{Date}'],
                [$appName, "{$driver->nom} {$driver->prenom}", $driver->email, $driver->phone, $driver->id, $date],
                $emailtemplate->message
            );

            Mail::html($emailmessage, function ($message) use ($to, $admin_email, $emailsubject, $emailtemplate) {
                $message->to($to)->subject($emailsubject);
                if ($emailtemplate->send_to_admin) {
                    $message->cc($admin_email);
                }
            });
        }
        
        $currency = Currency::where('statut', 'yes')->first();
        $country  = Country::where('statut', 'yes')->first();

        $response = [
            'id'               => (string) $driver->id,
            'firstname'        => $driver->prenom,
            'lastname'         => $driver->nom,
            'phone'            => $driver->phone,
            'country_code'     => $driver->country_code,
            'email'            => $driver->email,
            'user_cat'         => 'driver',
            'accesstoken'      => $this->adduseraccess($driver->id, 'driver'),
            'currency'         => $currency->symbole ?? '',
            'country'          => $country->code ?? '',
            'admin_commission' => [
                'type'  => $commission->type ?? '',
                'value' => $commission->value ?? '',
            ],
            'isOwner' => 'false',
            'owner_id' => $ownerId,
            'zone_id'  => $driver->zone_id ? explode(',', $driver->zone_id) : [],
            'service_type'  => $driver->service_type ? explode(',', $driver->service_type) : [],
            'statut_vehicule' => $driver->statut_vehicule,
            'is_verified'     => $driver->is_verified,
        ];
        
        return response()->json([
            'success' => 'success',
            'message' => 'Driver registered successfully.',
            'data'    => $response,
        ]);
    }

    public static function url()
    {
        $actual_link = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $site_url = preg_replace('/^www\./', '', parse_url($actual_link, PHP_URL_HOST));
        if (($_SERVER['HTTPS'] && $_SERVER['HTTPS'] === 'on')) {
            return "https://" . $site_url;
        } else {
            return "http://" . $site_url;
        }
    }

    public function adduseraccess($user_id, $user_type)
    {
        $user = AccessToken::where('user_id', $user_id)->where('user_type', $user_type)->first();
        if ($user && ! empty($user->accesstoken)) {
            $token = $user->accesstoken;
        } else {
            $token = $this->getUniqAccessToken();
            AccessToken::insert(['user_id' => $user_id, 'accesstoken' => $token, 'user_type' => $user_type]);
        }
        return $token;
    }

    public function getUniqAccessToken()
    {
        $accessget = 0;
        $accessToken = '';
        while ($accessget == 0) {
            $accessToken = md5(uniqid(mt_rand(), true));
            $user = AccessToken::where('accesstoken', $accessToken)->first();
            if (! $user) {
                $accessget = 1;
            }
        }
        return $accessToken;
    }
}
