<?php

namespace App\Http\Controllers;

use App\Models\CarModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\VehicleType;
use Validator;

class CarModelController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {

        $search = $request->input('search');
        $selected_search = $request->input('selected_search');

        $query = CarModel::query()
        ->join('brands', 'car_model.brand_id', '=', 'brands.id')
        ->join('type_vehicule', 'car_model.vehicle_type_id', '=', 'type_vehicule.id')
        ->select('car_model.*');

        if (!empty($search) && !empty($selected_search)) {
            $query->where(function ($q) use ($search, $selected_search) {
                if ($selected_search === 'type') {
                    $q->where('car_model.name', 'LIKE', "%$search%");
                } elseif ($selected_search === 'brand') {
                    $q->where('brands.name', 'LIKE', "%$search%");
                } elseif ($selected_search === 'vehicle') {
                    $q->where('type_vehicule.libelle', 'LIKE', "%$search%");
                }
            });
        }
        $totalLength = $query->count();
        $perPage = $request->input('per_page', 20);
        $carModel = $query->paginate($perPage)->appends($request->all());
           
        $brand=DB::table('brands')->select('*')->get();
        $vehicleType = VehicleType::all();
        return view("car_model.index",compact('carModel', 'brand', 'vehicleType','totalLength','perPage'));
    }

    public function create()
    {
        $brand=DB::table('brands')->select('*')->get();
        $vehicleType = VehicleType::all();
        return view("car_model.create")->with('brand',$brand)->with('vehicleType',$vehicleType);
    }

    public function storecarmodel(Request $request)
    {

        $validator = Validator::make($request->all(), $rules = [
            'name' => 'required',
            'brand' => 'required',
            'vehicle_id'=> 'required',

        ], $messages = [
            'name.required' => trans('lang.the_name_field_is_required'),
            'brand.required' => trans('lang.the_brand_field_is_required'),
            'vehicle_id.required' => trans('lang.the_vehicle_type_field_is_required'),
        ]);

        if ($validator->fails()) {
            return redirect('car-model/create')
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }
        $carModel = new CarModel;
        $carModel->name = $request->input('name');
        $carModel->brand_id = $request->input('brand');
        $carModel->vehicle_type_id = $request->input('vehicle_id');
        $carModel->status = $request->input('status') ? 'yes' : 'no';


        $carModel->created_at = date('Y-m-d H:i:s');
        $carModel->modifier = date('Y-m-d H:i:s');
        $carModel->updated_at = date('Y-m-d H:i:s');

        $carModel->save();

        return redirect('car-model')->with('message', trans('lang.carmodel_created'));

    }


    public function edit($id)
    {
        $carModel = CarModel::where('id', "=", $id)->first();
        $brand=DB::table('brands')->select('*')->get();
        $vehicleType = VehicleType::all();

        return view("car_model.edit")->with("carModel", $carModel)->with("brand", $brand)->with('vehicleType', $vehicleType);
    }

    public function UpdateCarModel(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $rules = [
            'name' => 'required',
            'brand_name' => 'required',
            'vehicle_id'=> 'required',

        ], $messages = [
            'name.required' => trans('lang.the_name_field_is_required'),
            'brand_name.required' => trans('lang.the_brand_field_is_required'),
            'vehicle_id.required' =>trans('lang.the_vehicle_type_field_is_required'),
        ]);

        if ($validator->fails()) {
            return redirect('users/create')
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }

        $name = $request->input('name');
        $brand = $request->input('brand_name');
        $status = $request->input('status') ? 'yes' : 'no';
        $vehicle_type = $request->input('vehicle_id');

        $carModel = CarModel::find($id);
        if ($carModel) {
            $carModel->name = $name;
            $carModel->brand_id = $brand;
            $carModel->status = $status;
            $carModel->vehicle_type_id = $vehicle_type;
            $carModel->updated_at = date('Y-m-d H:i:s');

            $carModel->save();
        }

        return redirect('car-model')->with('message', trans('lang.carmodel_updated'));
    }

  
    public function deleteCarModel(Request $request)
    {
        $ids = $request->get('ids');

        if ($ids) {
            $ids = explode(',', $ids); 

            CarModel::whereIn('id', $ids)->delete();
        }

        return redirect()->back();
    }


    public function changeStatus($id)
    {
        $carModel = CarModel::find($id);
        if ($carModel->status == 'no') {
            $carModel->status = 'yes';
        } else {
            $carModel->status = 'no';
        }

        $carModel->save();
        return redirect()->back();

    }

    public function toggalSwitch(Request $request){
            $ischeck=$request->input('ischeck');
            $id=$request->input('id');
            $carModel = CarModel::find($id);

            if($ischeck=="true"){
              $carModel->status = 'yes';
            }else{
              $carModel->status = 'no';
            }
              $carModel->save();
    }
}
