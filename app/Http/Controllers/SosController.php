<?php

namespace App\Http\Controllers;

use App\Models\Sos;
use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;

class SosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        
        $sos = Sos::join('requete', 'sos.ride_id', '=', 'requete.id')
        ->join('user_app', 'requete.id_user_app', '=', 'user_app.id')
        ->join('conducteur', 'requete.id_conducteur', '=', 'conducteur.id')
        ->select('user_app.prenom as userPreNom', 'user_app.nom as userNom', 'user_app.phone as user_phone', 'user_app.photo_path as user_photo', 'requete.latitude_depart', 'requete.longitude_depart', 'requete.destination_name')
        ->addSelect('conducteur.nom as driverNom', 'conducteur.prenom as driverPreNom', 'conducteur.phone as driver_phone', 'conducteur.photo_path as driver_photo')
        ->addSelect('requete.*', 'sos.*');

        if ($request->has('search') && $request->search != '' && $request->selected_search == 'status') {
            $search = $request->input('search');       
            $sos->where('sos.status', 'LIKE', '%' . $search . '%');
        }else if ($request->has('search') && $request->search != '' && $request->selected_search == 'ride_id') {
            $search = $request->input('search');       
            $sos->where('sos.ride_id', 'LIKE', '%' . $search . '%');
        } else if ($request->has('search') && $request->search != '' && $request->selected_search == 'user') {
            $search = $request->input('search');
            $sos->where('user_app.prenom', 'LIKE', '%' . $search . '%')
                ->orWhere(DB::raw('CONCAT(user_app.prenom, " ",user_app.nom)'), 'LIKE', '%' . $search . '%');
        } else if ($request->has('search') && $request->search != '' && $request->selected_search == 'driver') {
            $search = $request->input('search');
            $sos->where('conducteur.prenom', 'LIKE', '%' . $search . '%')
                ->orWhere(DB::raw('CONCAT(conducteur.prenom, " ",conducteur.nom)'), 'LIKE', '%' . $search . '%');
        }
        
        $totalLength = count($sos->get());
        $perPage = $request->input('per_page', 20);
        $sos = $sos->paginate($perPage)->appends($request->all());
        return view("sos.index",compact('sos','totalLength','perPage'));
    }

    public function show($id)
    {
        $sos = Sos::join('requete', 'sos.ride_id', '=', 'requete.id')
            ->join('user_app', 'requete.id_user_app', '=', 'user_app.id')
            ->join('conducteur', 'requete.id_conducteur', '=', 'conducteur.id')
            ->select('user_app.id as userID','user_app.prenom as userPreNom', 'user_app.nom as userNom', 'user_app.prenom as userFirstNom', 'user_app.phone as user_phone', 'user_app.photo_path as user_photo', 'requete.latitude_depart', 'requete.longitude_depart', 'requete.latitude_arrivee', 'requete.longitude_arrivee', 'requete.destination_name')
            ->addSelect('conducteur.id as driverID','conducteur.nom as driverNom', 'conducteur.prenom as driverPreNom', 'conducteur.phone as driver_phone', 'conducteur.photo_path as driver_photo')
            ->addSelect('requete.*', 'sos.*')
            ->where('sos.id', $id)->first();
        $row = (array)$sos;
        
        if (!empty($row['user_phone'])) { 
            $sos->user_phone = Helper::shortNumber($row['user_phone']); 
        }
        
        if (!empty($row['driver_phone'])) { 
            $sos->driver_phone = Helper::shortNumber($row['driver_phone']); 
        }

        $mapType = Settings::pluck('map_for_application')->first();
        
        return view('sos.show')->with('sos', $sos)->with('mapType', $mapType);
    }

    public function deleteSos($id)
    {
        if ($id != "") {
            $id = json_decode($id);
            if (is_array($id)) {
                for ($i = 0; $i < count($id); $i++) {
                    $user = Sos::find($id[$i]);
                    $user->delete();
                }
            } else {
                $user = Sos::find($id);
                $user->delete();
            }
        }
        return redirect()->back();
    }

    public function sosUpdate(Request $request, $id)
    {
        $sosData = Sos::find($id);
        if ($sosData) {
            $sosData->status = $request->input('order_status');
            $sosData->save();
        }
        $previous = strtok(url()->previous(), '?');
        return redirect()->to($previous . '?tab=sos');
    }
}
