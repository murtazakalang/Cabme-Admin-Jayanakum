<?php

namespace App\Http\Controllers;

use App\Models\DeliveryCharges;
use App\Models\RentalVehicleType;
use App\Models\Settings;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Helper;

class VehicleController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        if ($request->has('search') && $request->search != '' && $request->selected_search == 'libelle') {

            $search = $request->input('search');
            $types = DB::table('type_vehicule')
                ->where('type_vehicule.libelle', 'LIKE', '%' . $search . '%')
                ->where('type_vehicule.deleted_at', '=', NULL);
            $totalLength = count($types->get()); 
            $types = $types->paginate($perPage)->appends($request->all());


        } elseif ($request->has('search') && $request->search != '' && $request->selected_search == 'prix') {

            $search = $request->input('search');
            $types = DB::table('type_vehicule')
                ->where('type_vehicule.prix', 'LIKE', '%' . $search . '%')
                ->where('type_vehicule.deleted_at', '=', NULL);
            $totalLength = count($types->get());    
            $types = $types->paginate($perPage)->appends($request->all());
                


        } else {
            $totalLength = count(VehicleType::get());
            $types = VehicleType::paginate($perPage)->appends($request->all());

        }

        return view("vehicle.index",compact('types','totalLength','perPage'));
    }

    public function create()
    {
        $vehicle = VehicleType::all();
        $Settings = Settings::all();

        foreach ($Settings as $data)
            $delivery_distance = $data->delivery_distance;

        return view('vehicle.create', compact('vehicle'))->with('delivery_distance', $delivery_distance);
    }

    public function store(Request $request)
    {
        if ($request->id > 0) {
            $image_validation = "mimes:jpeg,jpg,png";
        } else {
            $image_validation = "required|mimes:jpeg,jpg,png";
        }

        $validator = Validator::make($request->all(), $rules = [
            'libelle' => 'required',
            'image' => $image_validation,
            'delivery_charge_per_km'=>'required',
            'minimum_delivery_charge'=>'required',
            'minimum_delivery_charge_within_km'=>'required',

        ], $messages = [
            'libelle.required' => trans('lang.the_vehicle_type_field_is_required'),
            'image.required' => trans('lang.the_image_field_is_required'),
            'delivery_charge_per_km.required'=>trans('lang.delivery_charges_per_miles_is_required'),
            'minimum_delivery_charge.required' => trans('lang.minimum_delivery_charges_is_required'),
            'minimum_delivery_charge_within_km.required'=>trans('lang.minimum_delivery_charges_within_miles_is_required'),


        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }


        $vehicle = new VehicleType;
        $vehicle->libelle = $request->input('libelle');
        $vehicle->status = !empty($request->input('status')) ? 'Yes' : 'No';

        if ($request->hasfile('image')) {
            $file = $request->file('image');
            $extenstion = $file->getClientOriginalExtension();
            $time = time() . '.' . $extenstion;
            $filename = 'image_vehicleType' . $time;
            $selectedfilename = 'selected_image_vehicleType' . $time;
            /*$file->move(public_path('assets/images/type_vehicle'), $filename);*/
            $compressedImage = Helper::compressFile($file->getPathName(), public_path('assets/images/type_vehicle').'/'.$filename, 8);
            $vehicle->image = $filename;
        }
        $vehicle->creer = date('Y-m-d H:i:s');
        $vehicle->modifier = date('Y-m-d H:i:s');
        $vehicle->updated_at = date('Y-m-d H:i:s');
        $vehicle->save();
        $vedicleType_id = $vehicle->id;

        $delivery = new DeliveryCharges;
        $delivery->delivery_charges_per_km = $request->input('delivery_charge_per_km');
        $delivery->minimum_delivery_charges = $request->input('minimum_delivery_charge');
        $delivery->minimum_delivery_charges_within_km = $request->input('minimum_delivery_charge_within_km');
        $delivery->id_vehicle_type = $vedicleType_id;
        $delivery->created = date('Y-m-d H:i:s');
        $delivery->modifier = date('Y-m-d H:i:s');
        $delivery->save();

        return redirect('vehicle-type')->with('message', trans('lang.vehicle_type_created_successfully'));
    }

    public function edit($id)
    {

        $type = VehicleType::find($id);
       
        $delivery_charges = DeliveryCharges::where('id_vehicle_type', $id)->first();
        $Settings = Settings::all();

        foreach ($Settings as $data)
            $delivery_distance = $data->delivery_distance;

        return view("vehicle.edit")->with("type", $type)->with('delivery_charges', $delivery_charges)->with('delivery_distance', $delivery_distance);
    }

    public function update(Request $request, $id)
    {

        if ($request->id > 0) {
            $image_validation = "mimes:jpeg,jpg,png";
        } else {
            $image_validation = "required|mimes:jpeg,jpg,png";

        }

        $validator = Validator::make($request->all(), $rules = [
            'libelle' => 'required',
            'image' => $image_validation,
            'delivery_charge_per_km'=>'required',
            'minimum_delivery_charge'=>'required',
            'minimum_delivery_charge_within_km'=>'required',


        ], $messages = [
            'libelle.required' => trans('lang.the_vehicle_type_field_is_required'),
            'image.required' => trans('lang.the_image_field_is_required'),
            'delivery_charge_per_km.required'=>trans('lang.delivery_charges_per_miles_is_required'),
            'minimum_delivery_charge.required' => trans('lang.minimum_delivery_charges_is_required'),
            'minimum_delivery_charge_within_km.required'=> trans('lang.minimum_delivery_charges_within_miles_is_required'),


        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }
        $Libelle = $request->input('libelle');
        $status = !empty($request->input('status')) ? 'Yes' : 'No';
        $modifier = $request->updated_at = date('Y-m-d H:i:s');
        $updated_at = $request->updated_at = date('Y-m-d H:i:s');

        $vehicle = VehicleType::find($id);
        if ($vehicle) {
            $vehicle->Libelle = $Libelle;
            $vehicle->status = $status;
            $vehicle->modifier = $modifier;
            $vehicle->updated_at = $updated_at;
            if ($request->hasfile('image')) {
                $destination = public_path('assets/images/type_vehicle/' . $vehicle->image);
                if (File::exists($destination)) {
                    File::delete($destination);
                }
                $file = $request->file('image');
                $extenstion = $file->getClientOriginalExtension();
                $time = time() . '.' . $extenstion;
                $filename = 'image_vehicleType' . $time;
                $selectedfilename = 'selected_image_vehicleType' . $time;
                /*$file->move(public_path('assets/images/type_vehicle'), $filename);*/
                $compressedImage = Helper::compressFile($file->getPathName(), public_path('assets/images/type_vehicle').'/'.$filename, 8);
                $vehicle->selected_image = $selectedfilename;
                $vehicle->image = $filename;
            }
            $vehicle->save();

            $delivery_charge_per_km = $request->input('delivery_charge_per_km');
            $minimum_delivery_charge = $request->input('minimum_delivery_charge');
            $minimum_delivery_charge_within_km = $request->input('minimum_delivery_charge_within_km');
            $delivery = DeliveryCharges::where('id_vehicle_type', $id)->first();
            if ($delivery) {
                $delivery->delivery_charges_per_km = $delivery_charge_per_km;
                $delivery->minimum_delivery_charges = $minimum_delivery_charge;
                $delivery->minimum_delivery_charges_within_km = $minimum_delivery_charge_within_km;
                $delivery->modifier = date('Y-m-d H:i:s');

            } else {
                $delivery = new DeliveryCharges;
                $delivery->delivery_charges_per_km = $delivery_charge_per_km;
                $delivery->minimum_delivery_charges = $minimum_delivery_charge;
                $delivery->minimum_delivery_charges_within_km = $minimum_delivery_charge_within_km;
                $delivery->id_vehicle_type = $id;
                $delivery->created = date('Y-m-d H:i:s');
                $delivery->modifier = date('Y-m-d H:i:s');

            }
            $delivery->save();
            return redirect('vehicle-type')->with('message', trans('lang.vehicle_type_updated_successfully'));
        }
    }

    public function delete($id)
    {
        if ($id != "") {
            $id = json_decode($id);
            if (is_array($id)) {
                for ($i = 0; $i < count($id); $i++) {
                    $user = VehicleType::find($id[$i]);
                   
                    $destination = public_path('assets/images/type_vehicle/' . $user->image);
                    if (File::exists($destination)) {
                        File::delete($destination);
                    }
                    $user->delete();
                    $DeliveryCharges = DeliveryCharges::where('id_vehicle_type',$id[$i]);
                    if($DeliveryCharges){
                        $DeliveryCharges->delete();
                    }
                }
            } else {

                $user = VehicleType::find($id);
                $destination = public_path('assets/images/type_vehicle/' . $user->image);
                if (File::exists($destination)) {
                    File::delete($destination);
                }
                $user->delete();
                $DeliveryCharges = DeliveryCharges::where('id_vehicle_type',$id);
                if($DeliveryCharges){
                    $DeliveryCharges->delete();
                }
            }

        }
        return redirect()->back();
    }

    public function toggalSwitch(Request $request)
    {
        $ischeck = $request->input('ischeck');
        $id = $request->input('id');
        $vehicle = VehicleType::find($id);

        if ($ischeck == "true") {
            $vehicle->status = 'Yes';
        } else {
            $vehicle->status = 'No';
        }
        $vehicle->save();

    }

}
