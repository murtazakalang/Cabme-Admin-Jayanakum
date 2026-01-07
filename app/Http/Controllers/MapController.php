<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Requests;
use App\Models\ParcelOrder;
use App\Models\RentalOrder;
use App\Models\UserApp;
use App\Models\Settings;
use Illuminate\Http\Request;

class MapController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {

        $lat_long = $this->getDefaultLatLong();
        $mapType = Settings::pluck('map_for_application')->first();

        $busyRides = collect();
        $normalRides = Requests::whereIn('statut', ['on ride', 'confirmed'])->get()
        ->map(function ($ride) {
            return [
                'driver_id'  => $ride->id_conducteur,
                'ride_type'  => 'Ride',
                'ride_id'    => $ride->id,
                'ride_status'     => $ride->statut,
                'depart_name' => $ride->depart_name ?? null,
                'destination_name'  => $ride->destination_name ?? null,
            ];
        });
        $busyRides = $busyRides->merge($normalRides);

        $parcelRides = ParcelOrder::whereIn('status', ['on ride', 'confirmed'])
        ->get()
        ->map(function ($ride) {
            return [
                'driver_id'  => $ride->id_conducteur,
                'ride_type'  => 'Parcel',
                'ride_id'    => $ride->id,
                'ride_status'  => $ride->status,
                'depart_name'     => $ride->source ?? null,
                'destination_name'    => $ride->destination ?? null,
            ];
        });
        $busyRides = $busyRides->merge($parcelRides);

        $rentalRides = RentalOrder::whereIn('status', ['on ride', 'confirmed'])
            ->get()
            ->map(function ($ride) {
                return [
                    'driver_id'  => $ride->id_conducteur,
                    'ride_type'  => 'Rental',
                    'ride_id'    => $ride->id,
                    'status'     => $ride->status,
                    'depart_name' => $ride->depart_name ?? null,
                    'destination_name' => '-',
                ];
            });
        $busyRides = $busyRides->merge($rentalRides);

        $busyDrivers = $busyRides->pluck('driver_id')->unique()->toArray();

        $drivers = Driver::select(
                'conducteur.*',
                'vehicule.numberplate as car_number',
                'vehicule.car_make',
                'brands.name as brand_name',
                'car_model.name as car_model'
            )
            ->join('vehicule', 'vehicule.id_conducteur', '=', 'conducteur.id')
            ->join('type_vehicule', 'type_vehicule.id', '=', 'vehicule.id_type_vehicule')
            ->leftJoin('brands', 'vehicule.brand', '=', 'brands.id')
            ->leftJoin('car_model', 'vehicule.model', '=', 'car_model.id')
            ->where('conducteur.latitude','!=',null)
            ->where('conducteur.longitude','!=',null)
            ->get();

        $driver_data = [];
        foreach ($drivers as $driver) {
            $data = [
                'driver_id'        => $driver->id,
                'driver_name'      => $driver->prenom . ' ' . $driver->nom,
                'driver_mobile'    => $driver->phone,
                'vehicle_brand'    => $driver->brand_name,
                'vehicle_number'   => $driver->car_number,
                'vehicle_model'    => $driver->car_model,
                'vehicle_make'     => $driver->car_make,
                'driver_latitude'  => $driver->latitude,
                'driver_longitude' => $driver->longitude,
                'flag'             => in_array($driver->id, $busyDrivers) || $driver->driver_on_ride === 'yes' ? 'on_ride' : 'available',
            ];

            if (in_array($driver->id, $busyDrivers)) {
                $rideInfo = $busyRides->firstWhere('driver_id', $driver->id);
                $data['ride_details'] = $rideInfo;
            }
            
            $driver_data[] = $data;
        }

        return view('map.index',compact('lat_long', 'mapType','driver_data'));
    }

    
    public function getDefaultLatLong(){
        $sql= Settings::select('settings.contact_us_address as address','settings.google_map_api_key as apikey')->first();
        $address = $sql->address;
        $apiKey = $sql->apikey;
        if(!empty($address) && !empty($apiKey)){
            $geo=file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($address).'&sensor=false&key='.$apiKey);
            $geo = json_decode($geo, true);
            $latlong = array();
            if (isset($geo['status']) && $geo['status'] == 'OK') {
                $latitude = $geo['results'][0]['geometry']['location']['lat'];
                $longitude = $geo['results'][0]['geometry']['location']['lng'];
                $latlong = array('lat'=> $latitude ,'lng'=>$longitude);
            }
        }else{
            $latlong = array();
        }
        return $latlong;
    }
}

