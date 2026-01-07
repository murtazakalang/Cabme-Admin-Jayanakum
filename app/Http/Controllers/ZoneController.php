<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use App\Models\Settings;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\MultiPolygon;
use App\Http\Controllers\API\v1\GcmController;

class ZoneController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        if ($request->has('search') && $request->search != '' && $request->selected_search == 'name') {
            $search = $request->input('search');
            $zones = Zone::where('zones.name', 'LIKE', '%' . $search . '%');
            $totalRecords = $zones->get();
            $zones = $zones->paginate($perPage)->appends($request->all());
        } else {
            $totalRecords = Zone::get();
            $zones = Zone::paginate($perPage)->appends($request->all());
        }
        $totalLength = count($totalRecords);
        return view("zone.index", compact("zones", 'totalLength', 'perPage'));
    }

    public function create()
    {
        $settings = Settings::first();
        $lat_long = $this->getDefaultLatLong();
        $mapType = $settings->map_for_application;
        $googleMapKey = $settings->google_map_api_key;
        return view("zone.create")->with("settings", $settings)->with("lat_long", $lat_long)->with("mapType", $mapType)->with("googleMapKey", $googleMapKey);
    }

    public function edit(Request $request, $id)
    {
        $zone = Zone::find($id);
        $settings = Settings::first();
        $mapType = $settings->map_for_application;
        $googleMapKey = $settings->google_map_api_key;
        
        $lat_long = $this->getDefaultLatLong();
        $area = $zone->area->toArray();
        $coordinates = [];
        foreach ($area['coordinates'] as $key => $data) {
            foreach ($data as $k => $v) {
                $coordinates[$key][] = array('lat' => $v[1], 'lng' => $v[0]);
            }
        }
        $default_lat = $coordinates[0][0]['lat'];
        $default_lng = $coordinates[0][0]['lng'];
        return view("zone.edit")
            ->with("zone", $zone)
            ->with("settings", $settings)
            ->with("lat_long", $lat_long)
            ->with("coordinates", json_encode($coordinates))
            ->with("default_lat", $default_lat)
            ->with("default_lng", $default_lng)
            ->with("mapType", $mapType)
            ->with("googleMapKey", $googleMapKey);
    }
    
    public function delete($id)
    {
        if ($id != "") {
            $id = json_decode($id);
            if (is_array($id)) {
                for ($i = 0; $i < count($id); $i++) {
                    $zone = Zone::find($id[$i]);
                    $zone->delete();
                }
            } else {
                $zone = Zone::find($id);
                $zone->delete();
            }
        }
        return redirect()->back();
    }

    public function update($id, Request $request)
    {
        $validator = Validator::make($request->all(), $rules = [
            'name' => 'required',
            'coordinates' => 'required',
        ], $messages = [
            'name.required' => trans('lang.the_name_field_is_required'),
            'coordinates.required' => trans('lang.please_select_your_zone_from_map'),
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->with(['message' => $messages])->withInput();
        }
        $name = $request->input('name');
        $status = $request->input('status') ? 'yes' : 'no';
        $coordinatesJson = $request->input('coordinates');
        $coordinates = json_decode($coordinatesJson, true); // Convert JSON to array
        if (json_last_error() !== JSON_ERROR_NONE) {
            return redirect()->back()->withErrors(['coordinates' => trans('lang.invalid_JSON_format')])->withInput();
        }
        if (isset($coordinates[0]) && is_array($coordinates[0]) && isset($coordinates[0][0])) {
            $coordinates = $coordinates[0]; // Flatten the array
        }
        if (!is_array($coordinates) || count($coordinates) < 3) {
            return redirect()->back()->withErrors(['coordinates' => trans('lang.invalid_coordinate_format')])->withInput();
        }
        $points = [];
        foreach ($coordinates as $coordinate) {
            $longitude = $coordinate['lon'] ?? $coordinate['lng'] ?? null;
            if (!isset($coordinate['lat']) || !isset($longitude)) {
                return redirect()->back()->withErrors(['coordinates' => trans('lang.missing_lat_long_in_coordinate')])->withInput();
            }
            if (!is_numeric($coordinate['lat']) || !is_numeric($longitude)) {
                return redirect()->back()->withErrors(['coordinates' => trans('lang.invalid_coordinate_values')])->withInput();
            }
            // Convert to Laravel Point format
            $points[] = new Point(round(floatval($coordinate['lat']), 6), round(floatval($longitude), 6));
        }
        $points[] = $points[0];
        $zone = Zone::find($id);
        if (!$zone) {
            return redirect()->back()->withErrors(['zone' => trans('lang.zone_not_found')])->withInput();
        }
        $zone->name = $name;
        $zone->status = $status;
        $zone->latitude = $points[0]->latitude;
        $zone->longitude = $points[0]->longitude;
        $zone->area = new Polygon([
            new LineString($points),
        ]);
        $zone->update();
        return redirect('zone')->with('message', trans('lang.zone_updated_successfully'));
    }

    public function store(Request $request)    {
        
        $validator = Validator::make($request->all(), $rules = [
            'name' => 'required|unique:zones,name',
            'coordinates' => 'required',
        ], $messages = [
            'name.required' => trans('lang.the_name_field_is_required'),
            'coordinates.required' => trans('lang.please_select_your_zone_from_map'),
            'name.unique' => trans('lang.zone_name_already_exists'),
        ]);
       
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $name = $request->input('name');
        $status = $request->input('status') ? 'yes' : 'no';
        /* $coordinates = json_decode($request->input('coordinates'));
        $points = array();
        foreach ($coordinates[0] as $coordinate) {
            $points[] = new Point($coordinate->lat, $coordinate->lng);
        } */
        $coordinates = json_decode($request->input('coordinates'));

        $points = [];

        if (isset($coordinates[0]) && is_array($coordinates[0])) {          
            foreach ($coordinates[0] as $coordinate) {
                $points[] = new Point($coordinate->lat, $coordinate->lng);
            }
        } else {           
            foreach ($coordinates as $coordinate) {
                $points[] = new Point($coordinate->lat, $coordinate->lng);
            }
        }
        array_push($points, $points[0]);
        $zone = Zone::create([
            'name' => $name,
            'status' => $status,
            'latitude' => $points[0]->latitude,
            'longitude' => $points[0]->longitude,
            'area' =>  new Polygon([
                new LineString($points),
            ])
        ]);
        return redirect('zone')->with('message', trans('lang.zone_created_successfully'));
    }

    public function toggalSwitch(Request $request)
    {
        $ischeck = $request->input('ischeck');
        $id = $request->input('id');
        $zone = Zone::find($id);
        if ($ischeck == "true") {
            $zone->status = 'yes';
        } else {
            $zone->status = 'no';
            // Remove zone id from drivers
            $drivers = Driver::whereRaw("FIND_IN_SET(?, zone_id)", [$id])->get();
            foreach ($drivers as $driver) {
                $zones = explode(',', $driver->zone_id);
                $zones = array_filter($zones, fn($z) => $z != $id);
                $driver->zone_id = implode(',', $zones);
                $driver->save();

                //send notification to driver when zone is disabled
                $fcm_token = $driver->fcm_id;
                if (!empty($fcm_token)) {
                    $message = array("body" => 'For some reason '.$zone->name.' zone is disabled', "title" => 'Zone Disabled', "sound" => "mySound");
                    GcmController::sendNotification($fcm_token, $message);
                }
            }
        }

        $zone->save();
    }
    
    public function getDefaultLatLong()
    {
        $sql = Settings::select('settings.contact_us_address as address', 'settings.google_map_api_key as apikey')->first();
        $address = $sql->address;
        $apiKey = $sql->apikey;
        if (!empty($address) && !empty($apiKey)) {
            $geo = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=' . $apiKey);
            $geo = json_decode($geo, true);
            $latlong = array();
            if (isset($geo['status']) && $geo['status'] == 'OK') {
                $latitude = $geo['results'][0]['geometry']['location']['lat'];
                $longitude = $geo['results'][0]['geometry']['location']['lng'];
                $latlong = array('lat' => $latitude, 'lng' => $longitude);
            }
        } else {
            $latlong = array();
        }
        return $latlong;
    }
}
