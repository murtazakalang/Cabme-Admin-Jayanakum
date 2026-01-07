<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\UserApp;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\VehicleLocation;
use App\Models\Review;
use App\Helpers\Helper;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Validator;

class DeleteUserController extends Controller
{

    public function deleteuser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'  => 'required|integer',
            'user_cat' => 'required|in:customer,driver',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'error'   => $validator->errors()->first(),
            ]);
        }

        $id = $request->get('user_id');
        $userCat = $request->get('user_cat');

        if ($userCat === 'customer') {

            $user = UserApp::find($id);
            if ($user) {

                VehicleLocation::where('id_user_app', $id)->delete();
                Review::where('user_id', $id)->delete();
                
                $imagePath = public_path('assets/images/users/' . $user->photo_path);
                if ($user->photo_path && File::exists($imagePath)) {
                    File::delete($imagePath);
                }
                $user->delete();
              
                return response()->json([
                    'success' => 'success',
                    'error'   => null,
                    'message' => 'User deleted successfully.',
                ]);
            }

            return response()->json([
                'success' => 'Failed',
                'error'   => 'User not found.',
            ]);
        }

        if ($userCat === 'driver') {
            
            $driver = Driver::find($id);

            if ($driver) {

                Vehicle::where('id_conducteur', $id)->delete();
                Review::where('driver_id', $id)->delete();
                
                $imagePath = public_path('assets/images/driver/' . $driver->photo_path);
                if ($driver->photo_path && File::exists($imagePath)) {
                    File::delete($imagePath);
                }

                //Delete all drivers of owner
                if ($driver->isOwner == 'true') {
                    Driver::where('ownerId', $driver->id)->delete();
                }
                
                $driver->delete();

                return response()->json([
                    'success' => 'success',
                    'error'   => null,
                    'message' => 'Driver deleted successfully.',
                ]);
            }

            return response()->json([
                'success' => 'Failed',
                'error'   => 'Driver not found.',
            ]);
        }

        return response()->json([
            'success' => 'Failed',
            'error'   => 'Invalid request.',
        ]);
    }
}
