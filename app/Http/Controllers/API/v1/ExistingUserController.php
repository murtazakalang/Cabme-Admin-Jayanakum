<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\UserApp;
use Illuminate\Http\Request;

class ExistingUserController extends Controller
{
	public function getData(Request $request)
	{
		$phone        = $request->get('phone');
		$user_cat     = $request->get('user_cat');   // 'customer' or 'driver'
		$email        = $request->get('email');
		$login_type   = $request->get('login_type'); // phoneNumber, email, google, apple
		$country_code = $request->get('country_code');

		// Validation
		if ($login_type == 'phoneNumber' && empty($phone)) {
			return response()->json([
				'success' => 'Failed',
				'error'   => 'Phone number required'
			]);
		}
		if ($login_type != 'phoneNumber' && empty($email)) {
			return response()->json([
				'success' => 'Failed',
				'error'   => 'Email is required'
			]);
		}

		// Which model to check first based on user_cat
		$model    = ($user_cat == 'driver') ? Driver::class : UserApp::class;
		$altModel = ($user_cat == 'driver') ? UserApp::class : Driver::class;

		/**
		 * if login_type != phoneNumber
		 * We need to check if the email exists AND what login_type is stored
		 */
		if ($login_type != 'phoneNumber') {

			// Find user by email in both tables
			$existingUser = $model::where('email', $email)->first() 
						?: $altModel::where('email', $email)->first();

			if ($existingUser) {
				// If that user originally registered with phoneNumber
				if ($existingUser->login_type == 'phoneNumber') {
					return response()->json([
						'success' => 'Failed',
						'error'   => 'This user already registered with phone number',
						'message'  => ''
					]);
				}

				// Otherwise valid user exists with google or email
				return response()->json([
					'success' => 'success',
					'message' => 'User exist',
					'error'   => null,
					'data'    => true
				]);
			}

			// Email not found anywhere
			return response()->json([
				'success' => 'success',
				'message' => 'User not exist',
				'error'   => null,
				'data'    => false
			]);
		}

		/**
		 * if login_type = phoneNumber
		 * 
		 */
		$conditions = ['phone' => $phone, 'country_code' => $country_code];

		// Check in main role
		if ($model::where($conditions)->exists()) {
			return response()->json([
				'success' => 'success',
				'message' => 'User exist',
				'error'   => null,
				'data'    => true
			]);
		}

		// Check phone in opposite role table
		if ($altModel::where($conditions)->exists()) {
			return response()->json([
				'success' => 'Failed',
				'error'   => 'User already exist, please try with different number',
				'data'    => false
			]);
		}

		// completely new phone user
		return response()->json([
			'success' => 'success',
			'message' => 'User not exist',
			'error'   => null,
			'data'    => false
		]);
	}
}

