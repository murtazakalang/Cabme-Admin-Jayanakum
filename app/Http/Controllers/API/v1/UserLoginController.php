<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\DriversDocuments;
use App\Models\UserApp;
use App\Models\AccessToken;
use App\Models\Vehicle;
use App\Models\Country;
use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;

class UserLoginController extends Controller
{
    
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
            'user_cat' => 'required|in:customer,driver,owner',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'error'   => $validator->errors()->first(),
            ]);
        }

        $email     = $request->email;
        $password  = $request->password;
        $userCat   = $request->user_cat;
        $accesstoken = $request->header('accesstoken');

        if ($userCat === 'customer') {
            $user = UserApp::where('email', $email)->first();
        } else {
            $user = Driver::where('email', $email)->first();
        }

        if (!$user) {
            return response()->json([
                'success' => 'Failed',
                'error'   => $userCat === 'customer' ? 'User not found' : 'Driver not found',
            ]);
        }

        if ($user->statut !== 'yes') {
            return response()->json([
                'success' => 'Failed',
                'error'   => 'Your account is not activated. Please contact the administrator.',
            ]);
        }

        if (!Hash::check($password, $user->mdp)) {
            return response()->json([
                'success' => 'Failed',
                'error'   => 'Incorrect Password.',
            ]);
        }

        $data = $user->toArray();
        unset($data['mdp']);
        $data['user_cat'] = $userCat;
        $data['accesstoken'] = $accesstoken ?: $this->adduseraccess($user->id, $userCat);

        // Add additional driver/owner flag if needed
        if ($userCat === 'driver' || $userCat === 'owner') {
            $data['is_verified'] = $user->is_verified ? 'yes' : 'no';
            $vehicle = Vehicle::where('id_conducteur', $user->id)->where('statut', 'yes')->first();
            if ($vehicle) {
                $data['brand']       = $vehicle->brand;
                $data['model']       = $vehicle->model;
                $data['color']       = $vehicle->color;
                $data['numberplate'] = $vehicle->numberplate;
            }
        }else{
            $data['role'] = "customer";
        }

        // Country
        $country = Country::where('statut', 'yes')->first();
        $data['country'] = $country ? $country->code : '';

        // Commission
        $commission = Commission::where('statut', 'yes')->first();
        if ($commission) {
            $data['admin_commission'] = [
                    'type'  => $commission->type ?? '',
                    'value' => $commission->value ?? '',
                ];
        }

        // Photo fallback
        $folder = $userCat === 'customer' ? 'users' : 'driver';
        if (!empty($user->photo_path) && file_exists(public_path("assets/images/{$folder}/{$user->photo_path}"))) {
            $data['photo_path'] = asset("assets/images/{$folder}/{$user->photo_path}");
        } else {
            $data['photo_path'] = asset('assets/images/placeholder_image.jpg');
        }

        if ($userCat != 'customer') {
            $data['zone_id'] = $data['zone_id'] ? explode(',', $data['zone_id']) : [];
            $data['service_type'] = $data['service_type'] ? explode(',', $data['service_type']) : [];
        }

        return response()->json([
            'success' => 'Success',
            'message' => 'Login Successfully.',
            'data'    => $data,
        ]);
    }

    public function logout(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'user_id'  => 'required|integer',
            'user_cat' => 'required|in:customer,driver,owner',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'error'   => $validator->errors()->first(),
            ]);
        }

        $user_id  = $request->user_id;
        $userCat  = $request->user_cat;
        
        if ($userCat === 'customer') {
            $user = UserApp::find($user_id);
        } else {
            $user = Driver::find($user_id);
        }

        if (!$user) {
            return response()->json([
                'success' => 'Failed',
                'error'   => $userCat === 'customer' ? 'User not found' : 'Driver not found',
            ]);
        }

        $user->fcm_id = null;
        $user->save();

        AccessToken::where('user_id', $user_id)->where('user_type', $userCat)->delete();

        return response()->json([
            'success' => 'Success',
            'message' => 'Logout Successfully.',
            'data'    => null,
        ]);
    }
    
    public function adduseraccess($user_id, $user_type)
    {
        $user = AccessToken::where('user_id', $user_id)->where('user_type', $user_type)->first();
        if ($user && !empty($user->accesstoken)) {
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
            if (!$user) {
                $accessget = 1;
            }
        }
        return $accessToken;
    }
}
