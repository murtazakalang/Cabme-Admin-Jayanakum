<?php

namespace App\Http\Controllers;

use App\Models\RentalPackage;
use App\Models\Currency;
use App\Models\Driver;
use App\Models\SubscriptionHistory;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use File;
use Image;

class RentalPackageController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = RentalPackage::with('vehicleType');

        if ($request->has('search') && $request->search != '') {
            $search = $request->input('search');
            $selected = $request->selected_search;

            if ($selected == 'title') {
                $query->where('title', 'LIKE', "%{$search}%");
            }

            if ($selected == 'farePrice') {              
                if (is_numeric($search)) {
                    $query->where('baseFare', $search);
                } else {
                    $query->where('baseFare', 'LIKE', "%{$search}%");
                }
            }

            if ($selected == 'vehicleType') {
                $query->whereHas('vehicleType', function ($q) use ($search) {
                    $q->where('libelle', 'LIKE', "%{$search}%");
                });
            }
        }

        $totalLength = $query->count(); 
        $perPage = $request->input('per_page', 20);
        $packages = $query->paginate($perPage)->appends($request->all());

        $currency = Currency::where('statut', 'yes')->first();

        return view("rental_packages.index", compact('packages', 'currency', 'totalLength', 'perPage'));
    }


    public function create()
    {
        $vehicleType = VehicleType::select('id','libelle')->where('status', 'yes')->get();

        return view("rental_packages.create", compact('vehicleType'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'ordering' => 'required|numeric|min:1',
            'baseFare' => 'required|numeric|min:1',
            'includedHours' => 'required|numeric|min:1',
            'includedDistance' => 'required|numeric|min:1',
            'extraKmFare' => 'required|numeric|min:1',
            'extraMinuteFare' => 'required|numeric|min:1',
            'vehicleTypeId' => 'required',
        ],[
            'ordering.min' => trans('lang.ordering_must_be_greater_than_zero'),
            'baseFare.min' => trans('lang.base_fare_must_be_greater_than_zero'),
            'includedHours.min' => trans('lang.included_hours_must_be_greater_than_zero'),
            'includedDistance.min' => trans('lang.included_distance_must_be_greater_than_zero'),
            'extraKmFare.min' => trans('lang.extra_km_fare_must_be_zero_or_greater'),
            'extraMinuteFare.min' => trans('lang.extra_minute_fare_must_be_zero_or_greater'),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->all();

        RentalPackage::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'published' => ($request->has('published')) ? "true" : "false",
            'ordering' => $data['ordering'],
            'baseFare' => $data['baseFare'],
            'includedHours' => $data['includedHours'],
            'includedDistance' => $data['includedDistance'],
            'extraKmFare' => $data['extraKmFare'],
            'extraMinuteFare' => $data['extraMinuteFare'],
            'vehicleTypeId' => $data['vehicleTypeId'],
        ]);

        return redirect('rental-packages')->with('message', trans('lang.rental_package_created_successfully'));
    }

    public function edit($id)
    {
        $package = RentalPackage::find($id);
        
        $vehicleType = VehicleType::select('id','libelle')->where('status', 'yes')->get();
        
        return view("rental_packages.edit",compact('package', 'vehicleType'));
   }

    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'ordering' => 'required|numeric|min:1',
            'baseFare' => 'required|numeric|min:1',
            'includedHours' => 'required|numeric|min:1',
            'includedDistance' => 'required|numeric|min:1',
            'extraKmFare' => 'required|numeric|min:1',
            'extraMinuteFare' => 'required|numeric|min:1',
            'vehicleTypeId' => 'required',
        ],[
            'ordering.min' => trans('lang.ordering_must_be_greater_than_zero'),
            'baseFare.min' => trans('lang.base_fare_must_be_greater_than_zero'),
            'includedHours.min' => trans('lang.included_hours_must_be_greater_than_zero'),
            'includedDistance.min' => trans('lang.included_distance_must_be_greater_than_zero'),
            'extraKmFare.min' => trans('lang.extra_km_fare_must_be_zero_or_greater'),
            'extraMinuteFare.min' => trans('lang.extra_minute_fare_must_be_zero_or_greater'),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->all();

        RentalPackage::where('id', $id)->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'published' => ($request->has('published')) ? "true" : "false",
            'ordering' => $data['ordering'],
            'baseFare' => $data['baseFare'],
            'includedHours' => $data['includedHours'],
            'includedDistance' => $data['includedDistance'],
            'extraKmFare' => $data['extraKmFare'],
            'extraMinuteFare' => $data['extraMinuteFare'],
            'vehicleTypeId' => $data['vehicleTypeId'],
        ]);

        return redirect('rental-packages')->with('message', trans('lang.rental_package_updated_successfully'));
    }

    public function delete($id)
    {
        if ($id != "") {
            $id = json_decode($id);
            if (is_array($id)) {
                for ($i = 0; $i < count($id); $i++) {
                    $plan = RentalPackage::find($id[$i]);
                    $destination = public_path('assets/images/rental-packages/' . $plan->image);
                    if (File::exists($destination)) {
                        File::delete($destination);
                    }
                    $plan->delete();
                }
            } else {
                $plan = RentalPackage::find($id);
                $destination = public_path('assets/images/rental-packages/' . $plan->image);
                if (File::exists($destination)) {
                    File::delete($destination);
                }
                $plan->delete();
            }
        }
        return redirect()->back();
    }


    public function toggalSwitch(Request $request)
    {
        $ischeck = $request->input('ischeck');
        $id = $request->input('id');

        $package = RentalPackage::find($id);
        if ($ischeck == "true") {
            $package->published = 'true';
            $package->save();
            return response()->json(['success' => true, 'message' => trans('lang.subscription_plan_disabled_successfully')]);
        } else {
            if ($enabledPlansCount == 1 && $enablePlanId == $id) {
                return response()->json(['success' => false, 'message' => __('lang.atleast_one_subscription_plan_should_be_active')], 400);
            } else {
                $package->published = 'false';
                $package->save();
                return response()->json(['success' => true, 'message' => trans('lang.subscription_plan_disabled_successfully')]);
            }
        }
    }

}
