<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\UserApp;
use App\Models\Driver;
use App\Models\Requests;
use App\Models\Notification;
use App\Models\Settings;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Validator;

class ResertPasswordController extends Controller
{

    public function resetPasswordOtp(Request $request)
    {
        $response = [];
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'user_cat' => 'required|in:user_app,driver',
        ]);
        if($validator->fails()){
            return response()->json([
                'success' => 'Failed',
                'code'    => 404,
                'message' => $validator->errors()->first(),
                'data'    => null,
            ]);
        }

        $email = $request->get('email');
        $userCat = $request->get('user_cat');
        $otp = mt_rand(1000, 9999);
        $now = now();

        $user = null;
        if ($userCat === 'user_app') {
            $user = UserApp::where('email', $email)->first();
        } else {
            $user = Driver::where('email', $email)->first();
        }
        if (!$user) {
            return response()->json([
                'success' => 'Failed',
                'code'    => 404,
                'message' => 'Email does not exist',
                'data'    => null,
            ]);
        }

        // Update OTP fields
        $user->reset_password_otp = $otp;
        $user->reset_password_otp_modifier = $now;
        $user->save();

        // Get email template
        $emailtemplate = EmailTemplate::where('type', 'reset_password')->first();
        $emailsubject = $emailtemplate->subject;
        $emailmessage = $emailtemplate->message;
        
        $contact_us_email = Settings::select('contact_us_email')->value('contact_us_email');
        $admin_email = $contact_us_email ? $contact_us_email : 'none@none.com';
        $appName = env('APP_NAME', 'Cabme');
        $to = $email;

        $emailmessage = str_replace(
            ['{AppName}', '{UserName}', '{OTP}'],
            [$appName, "{$user->prenom} {$user->nom}", $otp],
            $emailmessage
        );

        Mail::html($emailmessage, function ($message) use ($to, $admin_email, $emailsubject, $emailtemplate) {
            $message->to($to)->subject($emailsubject);
            if ($emailtemplate->send_to_admin) {
                $message->cc($admin_email);
            }
        });

        return response()->json([
            'success' => 'success',
            'code'    => 200,
            'message' => 'OTP sent successfully',
            'data'    => null,
        ]);
    }
     
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'            => 'required|email',
            'user_cat'         => 'required|in:user_app,driver',
            'otp'              => 'required|digits:4',
            'new_password'     => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'code'    => 404,
                'message' => $validator->errors()->first(),
                'data'    => null,
            ]);
        }

        $email       = $request->email;
        $otp         = $request->otp;
        $newPassword = Hash::make($request->new_password);
        $userCat     = $request->user_cat;

        $now         = now();
        $expiryTime  = $now->subMinutes(30);

        $user = null;
        if ($userCat === 'user_app') {
            $user = UserApp::where('email', $email)->first();
        } else {
            $user = Driver::where('email', $email)->first();
        }

        if (!$user) {
            return response()->json([
                'success' => 'Failed',
                'code'    => 404,
                'message' => 'Email does not exist',
                'data'    => null,
            ]);
        }

        if ($user->reset_password_otp_modifier < $expiryTime) {
            return response()->json([
                'success' => 'Failed',
                'code'    => 400,
                'message' => 'OTP is expired',
                'data'    => null,
            ]);
        }

        if ($user->reset_password_otp != $otp) {
            return response()->json([
                'success' => 'Failed',
                'code'    => 400,
                'message' => 'OTP does not match',
                'data'    => null,
            ]);
        }

        $user->mdp = $newPassword;
        $user->modifier = $now;
        $user->save();

        // Format profile images
        $baseFolder = $userCat === 'user_app' ? 'users' : 'driver';

        if (!empty($user->photo_path)) {
            $path = public_path("assets/images/{$baseFolder}/{$user->photo_path}");
            $user->photo_path = file_exists($path)
                ? asset("assets/images/{$baseFolder}/{$user->photo_path}")
                : asset('assets/images/placeholder_image.jpg');
        } else {
            $user->photo_path = asset('assets/images/placeholder_image.jpg');
        }

        if (!empty($user->photo_nic_path)) {
            $path = public_path("assets/images/{$baseFolder}/{$user->photo_nic_path}");
            $user->photo_nic_path = file_exists($path)
                ? asset("assets/images/{$baseFolder}/{$user->photo_nic_path}")
                : asset('assets/images/placeholder_image.jpg');
        } else {
            $user->photo_nic_path = asset('assets/images/placeholder_image.jpg');
        }

        // For driver, add other documents
        if ($userCat === 'driver') {
            foreach (['photo_licence_path', 'photo_car_service_book_path', 'photo_road_worthy_path'] as $field) {
                if (!empty($user->$field)) {
                    $path = public_path("assets/images/{$baseFolder}/{$user->$field}");
                    $user->$field = file_exists($path)
                        ? asset("assets/images/{$baseFolder}/{$user->$field}")
                        : asset('assets/images/placeholder_image.jpg');
                } else {
                    $user->$field = asset('assets/images/placeholder_image.jpg');
                }
            }
        }

        return response()->json([
            'success' => 'success',
            'code'    => 200,
            'message' => 'Password saved successfully',
            'data'    => $user,
        ]);
    }
}
