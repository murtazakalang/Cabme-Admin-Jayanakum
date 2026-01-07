<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Events\PositionUpdatedEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB;
use Validator;

class PositionController extends Controller
{
    
    public function updatePosition(Request $request)
    {
        $response = array();
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|integer|exists:conducteur,id',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        if($validator->fails()){
            $response['success'] = 'failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        $id_user = $request->get('id_user');
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');

        Driver::where('id', $id_user)->update([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'modifier' =>  date('Y-m-d H:i:s'),
        ]);
        
        $driverData = Driver::find($id_user);
        
		if ($driverData->photo_path != ''){
            $driverData->photo_path = file_exists(public_path('assets/images/driver/' . $driverData->photo_path))
            ? asset('assets/images/driver/' . $driverData->photo_path)
            : asset('assets/images/placeholder_image.jpg');
        }

		$driverData->zone_id = $driverData->zone_id ? explode(',', $driverData->zone_id) : [];
        $driverData->service_type = $driverData->service_type ? explode(',', $driverData->service_type) : [];

        broadcast(new PositionUpdatedEvent($driverData));
        Log::info('PositionUpdatedEvent event call', ['driver_id' => $id_user, 'data' => $driverData]);

        $response['success'] = 'success';
        $response['error'] = null;
        $response['message'] = 'Position successfully updated';
        $response['data'] = $driverData->toArray();

        return response()->json($response);
    }
}
