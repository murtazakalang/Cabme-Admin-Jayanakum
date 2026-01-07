<?php

namespace App\Http\Controllers;

use App\Exports\GenericExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Models\Withdrawal;

class ExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function export($type, $model)
    {
        $modelOf = '';
        if ($model == 'Owner') {
            $modelOf = 'Owner';
            $model = 'Driver';
        }
        if ($model == 'FleetDriver') {
            $modelOf = 'FleetDriver';
            $model = 'Driver';
        }

        $modelClass = 'App\\Models\\' . ucfirst($model);
        if (!class_exists($modelClass) && $model !== 'Requests') {
            abort(404, 'Model not found');
        }

        $fields = match ($model) {
            'UserApp','Driver' => ['prenom','nom', 'phone', 'email', 'statut', 'creer'],
            'DispatcherUser' => ['first_name', 'last_name', 'phone', 'email', 'status', 'created_at'],
            'ParcelOrder' => ['id', 'source', 'destination', 'driver_name','user_name', 'sender_name', 'sender_phone', 'parcel_date', 'parcel_time', 'receive_date','receive_time', 'status', 'created_at'],
            'Requests' => ['id', 'depart_name', 'destination_name','driver_name','user_name', 'statut', 'creer'],
            'VehicleLocation'=>['vehicle_type','user_name', 'date_debut', 'date_fin','statut', 'creer'],
            'Withdrawal' => ['driver_name','amount','note','statut','creer'],
            default => [],
        };

        if (empty($fields)) {
            abort(400, 'Fields not defined for the selected model');
        }

        if ($model === 'Requests') {
            $data = DB::table('requete')
                ->leftJoin('user_app', 'requete.id_user_app', '=', 'user_app.id')
                ->leftJoin('conducteur', 'requete.id_conducteur', '=', 'conducteur.id')
                ->select(
                    'requete.id',
                    'requete.depart_name',
                    'requete.destination_name',
                    'requete.statut',
                    'requete.creer',
                    DB::raw("CONCAT(user_app.prenom, ' ', user_app.nom) as user_name"),
                    DB::raw("CONCAT(conducteur.prenom, ' ', conducteur.nom) as driver_name")
                )
                ->whereNull('requete.deleted_at')
                ->get();
            }  elseif ($model === 'Withdrawal') {
                $data = Withdrawal::leftJoin('conducteur', 'conducteur.id', '=', 'withdrawals.id_conducteur')
                    ->select(
                        'withdrawals.amount',
                        'withdrawals.note',
                        'withdrawals.statut',
                        'withdrawals.creer',
                        DB::raw("CONCAT(conducteur.prenom, ' ', conducteur.nom) as driver_name")
                    )
                    ->get();
            }
         else {           
            $query = $modelClass::query();

            if ($model === 'Driver' && request()->has('is_verified')) {
                $query->where('is_verified', request('is_verified'));
            }
            if ($modelOf == 'Owner') {
                $query->where('isOwner', 'true');
            }
            if ($modelOf == 'FleetDriver') {
                $query->where('ownerId', '!=', NULL)->where('ownerId', '!=', '');
            }
            if ($model == 'Driver' && $modelOf == '') {
                $query->where(function ($q) {
                    $q->whereNull('ownerId')
                    ->orWhere('ownerId', '');
                });
            }

            $relationships = match ($model) {
                'ParcelOrder' => ['user', 'driver'],
                'VehicleLocation' => ['user', 'rentalVehicleType'],
                default => [],
            };

            if (!empty($relationships)) {
                $query->with($relationships);
            }

            $data = $query->get();

            if ($model === 'ParcelOrder') {
                $data = $data->map(function ($item) {
                    $item->user_name = $item->user ? trim(($item->user->prenom ?? '') . ' ' . ($item->user->nom ?? '')) : null;
                    $item->driver_name = $item->driver ? trim(($item->driver->prenom ?? '') . ' ' . ($item->driver->nom ?? '')) : null;
                    return $item;
                });
            }

            if ($model === 'VehicleLocation') {
                $data = $data->map(function ($item) {
                    $item->user_name = $item->user ? trim(($item->user->prenom ?? '') . ' ' . ($item->user->nom ?? '')) : null;
                    $item->vehicle_type = $item->rentalVehicleType ? trim(($item->rentalVehicleType->libelle ?? '')) : null;
                    return $item;
                });
            }
        }

        $exportData = $data->map(function ($item) use ($fields) {
            $row = [];
            foreach ($fields as $field) {
                $row[$field] = $item->$field ?? '';
            }
            return $row;
        });

        if ($type === 'excel') {
            return Excel::download(new GenericExport($exportData, $fields), $model . '.xlsx');
        }

        if ($type === 'csv') {
            return Excel::download(new GenericExport($exportData, $fields), $model . '.csv');
        }

        if ($type === 'pdf') {
            $pdf = Pdf::loadView('exports.pdf', ['data' => $data, 'fields' => $fields]);
            return $pdf->download($model . '.pdf');
        }

        abort(400, 'Invalid export type');
    }


}
