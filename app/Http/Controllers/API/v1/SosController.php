<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Sos;
use App\Models\Requests;
use App\Models\Driver;
use App\Models\UserApp;
use Illuminate\Http\Request;
use App\Events\SOSUpdatedEvent;
use Illuminate\Support\Facades\Log;
use Validator;

class SosController extends Controller
{
    
    public function storeSos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ride_id' => 'required|integer|exists:requete,id',
         ]);
        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        $ride_id = $request->ride_id;
        $rideData = Requests::with('user')->find($ride_id);

        $driverId = $rideData->id_conducteur;
        $driverData = Driver::find($driverId);

        if (!$driverData) {
            return response()->json([
                'success' => 'Failed',
                'error' => 'Driver not found for this ride',
            ]);
        }

        $latitude = $driverData->latitude;
        $longitude = $driverData->longitude;

        if (Sos::where('ride_id', $ride_id)->exists()){
            return response()->json([
                'success' => 'Failed',
                'error' => 'SOS Request Already Submitted',
            ]);
        }

        $sos = Sos::create([
            'ride_id' => $ride_id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'status' => 'initiated',
        ]);

        $rideData->sos_id = $sos->id;
        broadcast(new SOSUpdatedEvent($rideData->toArray()));
        Log::info('SOSUpdatedEvent event call', ['sos_id' => $sos->id]);

        return response()->json([
            'success' => 'success',
            'error' => null,
            'message' => 'SOS Request Submitted',
            'data' => $sos->toArray(),
        ]);
    }
}
