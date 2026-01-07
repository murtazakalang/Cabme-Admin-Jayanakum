<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;
use App\Models\UserApp;
use App\Models\Driver;
use App\Models\DispatcherUser;
use Validator;

class UserProfileUpdateController extends Controller
{
    public function update(Request $request)
    {
        $response = array();
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|integer',
            'user_type' => 'required|in:driver,customer',
         ]);
        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }
        
        $id_user = $request->get('id_user');
        $user_type = $request->get('user_type');

        if ($user_type === 'driver') {
            $user = Driver::find($id_user);
            $folder_type = "driver";
            $file_type = "Driver";
        } else {
            $user = UserApp::find($id_user);
            $folder_type = "users";
            $file_type = "User";
        }
        if (!$user) {
            $response['success'] = 'Failed';
            $response['error'] = "User not found";
            return response()->json($response);
        }

        $phone = $request->get('phone');
        $country_code = $request->get('country_code');
        $email = $request->get('email');
        $mdp = $request->get('mdp');
        $prenom = $request->get('prenom');
        $nom = $request->get('nom');
        $image = $request->file('image');
        
        // Check for duplicate email
        $checkEmail = $user->where('email', $email)
            ->where('id', '!=', $id_user)
            ->exists();

        if ($checkEmail) {
            $response['success'] = 'Failed';
            $response['error'] = "Email already exists";
            return response()->json($response);
        }

        // Check for duplicate phone
        $checkPhone = $user->where('phone', $phone)->where('country_code', $country_code)
            ->where('id', '!=', $id_user)
            ->exists();

        if ($checkPhone) {
            $response['success'] = 'Failed';
            $response['error'] = "Phone already exists";
            return response()->json($response);
        }

        // Process image if provided
        if ($request->has('image')) {
            // Delete old photo
            if ($user->photo_path) {
                $oldPath = public_path('assets/images/'.$folder_type.'/' . $user->photo_path);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }
            // Save new photo
            $ext = $image->getClientOriginalExtension();
            $filename = $file_type.'_photo_' . time() . '.' . $ext;
            $path = public_path('assets/images/'.$folder_type.'/') . $filename;
            Image::make($image->getRealPath())->resize(150, 150)->save($path);
            $user->photo_path = $filename;

            if($user->isOwner == "true"){
                $dispatcher_user = DispatcherUser::where('email',$user->email)->first();
                $relativePath = str_replace(url('/') . '/', '', $dispatcher_user->profile_picture_path);
                $destination = public_path($relativePath);
                if (File::exists($destination)) {
                    File::delete($destination);
                }
                $file = $request->file('image');
                $extenstion = $file->getClientOriginalExtension();
                $time = time() . '.' . $extenstion;
                $filename = 'dispatcher_user_profile' . $time;
                $path = public_path('assets/images/dispatcher_users/') . $filename;
                if (!file_exists(public_path('assets/images/dispatcher_users/'))) {
                    mkdir(public_path('assets/images/dispatcher_users/'), 0777, true);
                }
                Image::make($file->getRealPath())->resize(150, 150)->save($path);
                
                $dispatcher_user->profile_picture_path = asset('assets/images/dispatcher_users/' . $filename);
                $dispatcher_user->save();
            }
        }

        if (!empty($mdp)) {
            $user->mdp = md5($mdp); 
        }

        $user->nom = $nom;
        $user->prenom = $prenom;
        $user->email = $email;
        $user->phone = $phone;
        $user->country_code = $country_code;
        $user->modifier = now();
        $user->save();

        $row = $user->toArray();
        if ($user->photo_path && File::exists(public_path('assets/images/'.$folder_type.'/' . $user->photo_path))) {
            $row['photo_path'] = asset('assets/images/'.$folder_type.'/' . $user->photo_path);
        } else {
            /*$row['photo_path'] = asset('assets/images/placeholder_image.jpg');*/
            $row['photo_path'] = null;
        }
        if (!empty($user->photo_nic_path) && File::exists(public_path('assets/images/'.$folder_type.'/' . $user->photo_nic_path))) {
            $row['photo_nic_path'] = asset('assets/images/'.$folder_type.'/' . $user->photo_nic_path);
        } else {
            /*$row['photo_nic_path'] = asset('assets/images/placeholder_image.jpg');*/
            $row['photo_nic_path'] = null;
        }

        if ($user_type === 'driver') {
            $row['zone_id'] = $row['zone_id'] ? explode(',',$row['zone_id']) : [];
            $row['service_type'] = $row['service_type'] ? explode(',',$row['service_type']) : [];
        }

        $response['success'] = 'success';
        $response['code'] = 200;
        $response['message'] = "Successfully updated";
        $response['data'] = $row;
        
        return response()->json($response);
    }
}
