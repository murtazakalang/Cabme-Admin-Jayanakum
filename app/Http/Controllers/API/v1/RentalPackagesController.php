<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\RentalPackage;
use Illuminate\Http\Request;
use Validator;

class RentalPackagesController extends Controller
{
    
    public function getList(Request $request)
    {

        $vehicleTypeId = $request->get('vehicleTypeId');

        if(!empty($vehicleTypeId)){
            $packages = RentalPackage::where('vehicleTypeId', $vehicleTypeId)->where('published','true')->orderBy('ordering','asc')->get();
        }else{
            $packages = RentalPackage::where('published','true')->orderBy('ordering','asc')->get();
        }

        if ($packages->isEmpty()) {
            $response['success'] = 'Failed';
            $response['error'] = 'No Data Found';
            $response['message'] = null;
            return response()->json($response);
        }

        $packages->map(function ($package) {
            if ($package->image && file_exists(public_path('assets/images/rental-packages/' . $package->image))) {
                $package->image =  asset('assets/images/rental-packages/' . $package->image);
            }else{
                $package->image = asset('assets/images/placeholder_image.jpg');
            }
            $package->vehicleTypeName = $package->vehicleType->libelle;
            unset($package->vehicleType);
        });

        $response['success'] = 'success';
        $response['error'] = null;
        $response['message'] = 'Rental packages fetched successfully.';
        $response['data'] = $packages;

        return response()->json($response);
    }
}
