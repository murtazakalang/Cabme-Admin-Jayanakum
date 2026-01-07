<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Commission;
use App\Models\Driver;
use App\Models\VehicleType;
use App\Models\RentalVehicleType;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use Validator;
use DB;

class VehicleController extends Controller
{

  /*Register Vehicle */

  public function register(Request $request)
  {

      $brand = $request->get('brand');
      $model = $request->get('model');
      $color = $request->get('color');
      $numberplate = $request->get('carregistration');
      $passenger = $request->get('passenger');
      $id_driver = $request->get('id_driver');
      $id_categorie_vehicle = $request->get('id_categorie_vehicle');
      $date_heure = date('Y-m-d H:i:s');
      $car_make = $request->get('car_make');
      $milage = $request->get('milage');
      $km = $request->get('km_driven');
      $zone_id = $request->get('zone_id');

      $get_admin_commission = Commission::first();
      $commissionObj = ['type' => $get_admin_commission->type, 'value' => $get_admin_commission->value];
      $commission = json_encode($commissionObj);
      $chkdriver = Driver::where('id', $id_driver)->first();

      if (!empty($chkdriver)) {

          $chkid = Vehicle::where('id_conducteur', $id_driver)->first();
          $ownerId = '';
          if (! empty($chkdriver->$ownerId)) {
            $ownerId = $chkdriver->ownerId;
          }

          if (!empty($chkid)) {

              $row = $chkid->toArray();
              $id_vehicule = $row['id'];
              $updatedata = DB::update('update vehicule set brand = ?,model = ?,passenger = ?,color = ?,numberplate = ?,modifier = ?,id_type_vehicule = ?,car_make = ?,km = ?,milage = ?,ownerId= ?  where id = ?', [$brand, $model, $passenger, $color, $numberplate, $date_heure, $id_categorie_vehicle, $car_make, $km, $milage, $ownerId, $id_vehicule]);

              if (!empty($updatedata)) {
                $response['success'] = 'Success';
                $response['error'] = null;
                $response['message'] = 'Vehicle updated successfully';
                $get_vehicule = Vehicle::where('id', $id_vehicule)->first();
                $row = $get_vehicule->toArray();
                $response['data'] = $row;

              } else {

                $response['success'] = 'Failed';
                $response['error'] = 'Error while updating';
              }

              $updatedata = DB::update('update conducteur set statut_vehicule = ?, zone_id = ?,adminCommission= ? where id = ?', ['yes', $zone_id, $commission, $id_driver]);

          } else {

              DB::insert("insert into vehicule(passenger,brand,model,color,numberplate,id_conducteur,statut,creer,updated_at,id_type_vehicule,car_make,milage,km,ownerId)
                values('" . $passenger . "','" . $brand . "','" . $model . "','" . $color . "','" . $numberplate . "','" . $id_driver . "','yes','" . $date_heure . "','" . $date_heure . "','" . $id_categorie_vehicle . "','" . $car_make . "','" . $milage . "','" . $km . "','" . $ownerId . "')");
              $id = DB::getPdo()->lastInsertId();

              if ($id > 0) {

                $response['success'] = 'success';
                $response['error'] = null;
                $response['message'] = 'Vehicle Added successfully';
                $get_vehicule = Vehicle::where('id', $id)->first();
                $row = $get_vehicule->toArray();
                $response['data'] = $row;
              } else {
                $response['success'] = 'Failed';
                $response['error'] = 'Error while Add data';
              }

              $updatedata = DB::update('update conducteur set statut_vehicule = ?, zone_id = ?,adminCommission= ? where id = ?', ['yes', $zone_id, $commission, $id_driver]);
          }

      } else {
        $response['success'] = 'Failed';
        $response['error'] = 'Driver Not Found';
      }

      return response()->json($response);
  }

  public function ownerVehicleRegister(Request $request)
  {
      $validator = Validator::make($request->all(), [
          'id_categorie_vehicle' => 'required|exists:type_vehicule,id',
          'brand'                => 'required|string|max:255',
          'model'                => 'required|string|max:255',
          'color'                => 'required|string|max:255',
          'carregistration'          => 'required|string|max:255',
          'car_make'             => 'nullable|string|max:255',
          'milage'               => 'nullable|numeric',
          'km_driven'            => 'nullable|numeric',
          'passenger'            => 'required|integer|min:1',
          'owner_id'             => 'required|integer|exists:conducteur,id',
          'id_vehicle'           => 'nullable|integer|exists:vehicule,id',
      ]);

      if ($validator->fails()) {
          return response()->json([
              'success' => 'Failed',
              'error'   => $validator->errors()->first()
          ]);
      }
      
      $data = $validator->validated();

      $commission = Commission::first();
      $commissionJson = json_encode([
          'type'  => $commission->type ?? '',
          'value' => $commission->value ?? ''
      ]);

      $driver = Driver::find($data['owner_id']);
      if (!$driver) {
          return response()->json([
              'success' => 'Failed',
              'error'   => 'Driver Not Found'
          ]);
      }

      if($request->id_vehicle){

          $vehicle = Vehicle::find($request->id_vehicle);

          $vehicle->update([
              'brand'             => $data['brand'],
              'model'             => $data['model'],
              'passenger'         => $data['passenger'],
              'color'             => $data['color'],
              'numberplate'       => $data['carregistration'],
              'modifier'          => now(),
              'id_type_vehicule'  => $data['id_categorie_vehicle'],
              'car_make'          => $data['car_make'],
              'km'                => $data['km_driven'],
              'milage'            => $data['milage'],
              'ownerId'           => $data['owner_id'],
          ]);

          return response()->json([
              'success' => 'Success',
              'error'   => null,
              'message' => 'Vehicle updated successfully',
              'data'    => $vehicle
          ]);

      }else{

          if ($driver->subscriptionTotalVehicle != -1 && $driver->subscriptionTotalVehicle <= 0) {
              return response()->json([
                'success' => 'Failed', 
                'message' => 'Your have reached the maximum vehicle create limit for the current plan, upgrade the subscription to continue.'
              ]);
          }
            
          $vehicle = Vehicle::create([
              'passenger'          => $data['passenger'],
              'brand'              => $data['brand'],
              'model'              => $data['model'],
              'color'              => $data['color'],
              'numberplate'        => $data['carregistration'],
              'id_conducteur'      => null,
              'statut'             => 'yes',
              'creer'              => now(),
              'modifier'           => now(),
              'id_type_vehicule'   => $data['id_categorie_vehicle'],
              'car_make'           => $data['car_make'],
              'milage'             => $data['milage'],
              'km'                 => $data['km_driven'],
              'ownerId'           =>  $data['owner_id'],
          ]);

          $driver->update([
            'statut_vehicule' => 'yes'
          ]);

          //Reset limit
          Helper::resetDriverSubscriptionLimit($driver->id, 'subscriptionTotalVehicle', 'dec');

          return response()->json([
              'success' => 'Success',
              'error'   => null,
              'message' => 'Vehicle added successfully',
              'data'    => $vehicle
          ]);
      }
  }

  public function getOwnerVehicle(Request $request){

        $validator = Validator::make($request->all(), [
            'owner_id'             => 'required|integer|exists:conducteur,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'error'   => $validator->errors()->first()
            ]);
        }

        $vehicles = Vehicle::join('type_vehicule', 'type_vehicule.id', '=', 'vehicule.id_type_vehicule')
        ->join('brands', 'vehicule.brand', '=', 'brands.id')
        ->join('car_model', 'vehicule.model', '=', 'car_model.id')
        ->select('vehicule.*', 'type_vehicule.libelle as vehicle_name', 'type_vehicule.image as vehicle_image', 'brands.name as brand', 'car_model.name as model')
        ->where('vehicule.ownerId',$request->owner_id)->get();

        if($vehicles->isNOtEmpty()){

            $vehicles->map(function ($vehicle) {
                if (!empty($vehicle->vehicle_image) && file_exists(public_path('assets/images/type_vehicle'.'/'.$vehicle->vehicle_image))) {
                    $vehicle->vehicle_image = asset('assets/images/type_vehicle').'/'.$vehicle->vehicle_image;
                }else{
                    $vehicle->vehicle_image = asset('assets/images/placeholder_image.jpg');
                }
                return $vehicle;
            });
            
            return response()->json([
              'success' => 'Success',
              'error'   => null,
              'message' => 'Vehicle fetch successfully',
              'data'    => $vehicles->toArray()
            ]);

        }else{
          
            return response()->json([
              'success' => 'Failed',
              'error'   => null,
              'message' => 'No vehicle found',
              'data'    => null
            ]);
        }
  }


  /*get Vehicle category*/

  public function getVehicleCategoryData(Request $request)

  {

    $sql = VehicleType::select('*')

      ->where('status', '=', 'Yes')

      ->where('deleted_at', '=', null)

      ->get();



    $output = array();

    foreach ($sql as $row) {

      if (file_exists(public_path('assets/images/type_vehicle' . '/' . $row->image)) && !empty($row->image)) {

        $image_path = asset('assets/images/type_vehicle') . '/' . $row->image;
      } else {

        $image_path  =  asset('assets/images/placeholder_image.jpg');
      }

      if (file_exists(public_path('assets/images/type_vehicle' . '/' . $row->selected_image)) && !empty($row->selected_image)) {

        $selected_image_path = asset('assets/images/type_vehicle') . '/' . $row->selected_image;
      } else {

        $selected_image_path  =  asset('assets/images/placeholder_image.jpg');
      }

      $row->image = $image_path;

      $row->selected_image_path = $selected_image_path;

      $get_commission = Commission::select('*')->where('type', '=', 'fixed')->get();



      foreach ($get_commission as $row_commission) {

        $row->statut_commission = $row_commission->statut;

        $row->commission = $row_commission->value;

        $row->type = $row_commission->type;
      }


      $get_commission_perc = Commission::select('*')->where('type', '=', 'percentage')->get();
      

      foreach ($get_commission_perc as $row_commission_perc) {

        $row->statut_commission_perc = $row_commission_perc->statut;

        $row->commission_perc = $row_commission_perc->value;

        $row->type_perc = $row_commission_perc->type;
      }



      //Delivery Charges

      $get_delivery_chagres = DB::table('delivery_charges')

        ->select('*')

        ->where('id_vehicle_type', '=', $row->id)

        ->get();



      foreach ($get_delivery_chagres as $row_delivery_chagres) {

        $row->delivery_charges = $row_delivery_chagres->delivery_charges_per_km;

        $row->minimum_delivery_charges = $row_delivery_chagres->minimum_delivery_charges;

        $row->minimum_delivery_charges_within = $row_delivery_chagres->minimum_delivery_charges_within_km;
      }



      $output[] = $row;
    }

    if (!empty($sql)) {

      $response['success'] = 'Success';

      $response['error'] = null;

      $response['message'] = 'Successfully fetch data';

      $response['data'] = $output;
    } else {

      $response['success'] = 'Failed';

      $response['error'] = 'Failed To Fetch Data';
    }

    return response()->json($response);
  }

  public function removeDriverVehicle(Request $request)
  {

      $validator = Validator::make($request->all(), [
          'vehicleId'       => 'required',
      ]);
      if($validator->fails()) {
          return response()->json([
              'success' => 'Failed',
              'code'    => 404,
              'message' => $validator->errors()->first(),
              'data'    => null,
          ]);
      }

      $vehicleId = $request->get('vehicleId');
      
      $vehicle = Vehicle::find($vehicleId);
      if (!$vehicle) {
          return response()->json([
              'success' => 'Failed',
              'code'    => 404,
              'message' => 'Vehicle not found.',
              'data'    => null,
          ], 404);
      }

      if ($vehicle->ownerId) {
          $driver = Driver::find($vehicle->ownerId);
          if ($driver) {
              Helper::resetDriverSubscriptionLimit($driver->id, 'subscriptionTotalVehicle', 'inc');
          }
      }
      
      $vehicle->delete();

      $response['success'] = 'success';
      $response['error'] = null;
      $response['message'] = 'Vehicle removed successfully';
      $response['data'] = '';
      
      return response()->json($response);
    }
}
