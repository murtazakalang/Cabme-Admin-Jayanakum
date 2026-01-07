<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\UserApp;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;

class UsermdpController extends Controller
{
    public function UpdateUsermdp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_cat' => 'required|in:user_app,driver',
            'id_user'  => 'required|integer',
            'anc_mdp'  => 'required|string',
            'new_mdp'  => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'code'    => 404,
                'message' => $validator->errors()->first(),
                'data'    => null,
            ]);
        }

        $userType = $request->user_cat;

        // Choose model based on type
        $user = $userType === 'user_app'
            ? UserApp::find($request->id_user)
            : Driver::find($request->id_user);

        if (!$user) {
            return response()->json([
                'success' => 'Failed',
                'message'   => $userType === 'user_app' ? 'User Not Found' : 'Driver Not Found',
            ]);
        }

        // Check old password
        if (!Hash::check($request->anc_mdp, $user->mdp)) {
            return response()->json([
                'success' => 'Failed',
                'message'   => 'Incorrect Password',
            ]);
        }

        // Update with Laravel Hash
        $user->mdp = Hash::make($request->new_mdp);
        $user->modifier = now();
        $user->save();

        return response()->json([
            'success' => 'success',
            'message' => 'Password changed successfully',
            'data'    => $user,
        ]);
    }
}
