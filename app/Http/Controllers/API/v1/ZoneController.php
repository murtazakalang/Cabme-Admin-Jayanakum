<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\Driver;
use Illuminate\Http\Request;
use DB;

class ZoneController extends Controller
{

    public function getData(Request $request)
    {

        $zone = Zone::where('status','yes')->get();
        
        if(count($zone) > 0){
            $response['success']= 'success';
            $response['error']= null;
            $response['message']= 'Zone successfully fetched';
            $response['data'] = $zone;
        }else {
            $response['success']= 'Failed';
            $response['error']= 'No Data Found';
            $response['message']= null;
        }

        return response()->json($response);
    }

}
