<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\DriverDocument;
use App\Models\DriversDocuments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;


class DriverDocumentController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        if ($request->has('search') && $request->search != '') {
            $search = $request->input('search');
            $selected_search = $request->input('selected_search');
            if($selected_search == "type"){
                $document = DB::table('admin_documents')->where('admin_documents.type', 'LIKE', '%' . $search . '%');
            }else{
                $document = DB::table('admin_documents')->where('admin_documents.title', 'LIKE', '%' . $search . '%');
            }
            $totalLength = count($document->get());
            $document = $document->paginate($perPage)->appends($request->all());
        } else {
            $totalLength = count(DriverDocument::get());
            $document = DriverDocument::paginate($perPage)->appends($request->all());
        }

        return view("driver_document.index", compact('document', 'totalLength', 'perPage'));
    }

    public function create()
    {
        return view("driver_document.create");
    }

    public function storeDocument(Request $request)
    {

        $validator = Validator::make($request->all(), $rules = [
            'title' => 'required',
            'type' => 'required',
        ], $messages = [
            'title.required' => trans('lang.title_required'),
            'type.required' => trans('lang.the_document_for_field_is_required'),
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }
        $document = new DriverDocument;
        $document->title = $request->input('title');
        $document->type = $request->input('type');
        if ($request->input('status')) {
            $status = 'Yes';
            $document->is_enabled = "Yes";
        } else {
            $status = 'No';
            $document->is_enabled = "No";
        }
        $document->save();

        if ($status == 'Yes') {
            Driver::where('id', '!=', '0')->update(['is_verified' => 0]);
        }

        return redirect('driver-document')->with('message', trans('lang.document_created_successfully'));
    }

    public function edit($id)
    {

        $document = DriverDocument::where('id', "=", $id)->first();
        return view("driver_document.edit")->with("document", $document);
    }


    public function documentUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $rules = [
            'title' => 'required',
            'type' => 'required',
        ], $messages = [
            'title.required' => trans('lang.title_required'),
            'type.required' => trans('lang.the_document_for_field_is_required'),
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }

        $title = $request->input('title');
        $type = $request->input('type');

        if ($request->input('status')) {
            $status = "Yes";
        } else {
            $status = "No";
        }
        $document = DriverDocument::find($id);
        if ($document) {
            $document->title = $title;
            $document->type = $type;
            $document->is_enabled = $status;
            $document->save();
        }
        $adminDocCount = DriverDocument::where('admin_documents.is_enabled', 'Yes')->count();
        $adminDocuments = DriverDocument::where('admin_documents.is_enabled', 'Yes')->get();

        $allDrivers = Driver::all();
        foreach ($allDrivers as $drivers) {
            $driverId = $drivers->id;
            $driver = Driver::find($driverId);
            $driverDocCount = 0;
            foreach ($adminDocuments as $doc) {
                $approved_documents = DriversDocuments::where('driver_id', $driverId)->where('document_status', 'Approved')->where('document_id', $doc->id)->get();
                if (count($approved_documents) > 0) {
                    $driverDocCount++;
                }
            }
            if ($adminDocCount == $driverDocCount) {
                $driver->is_verified = 1;
            } else {
                $driver->is_verified = 0;
            }
            $driver->save();
        }
        return redirect('driver-document')->with('message', trans('lang.document_updated_successfully'));
    }

    public function deleteDocument($id)
    {

        if ($id != "") {

            $id = json_decode($id);

            if (is_array($id)) {
                for ($i = 0; $i < count($id); $i++) {
                    DriversDocuments::where('document_id', $id[$i])->delete();
                    $driverDoc = DriverDocument::find($id[$i]);
                    $driverDoc->delete();
                }
            } else {
                DriversDocuments::where('document_id', $id)->delete();
                $driverDoc = DriverDocument::find($id);
                $driverDoc->delete();
            }

            $adminDriverDocCount = DriverDocument::where('type', 'driver')->where('is_enabled', 'Yes')->count();
            $adminDriverDocuments = DriverDocument::where('type', 'driver')->where('is_enabled', 'Yes')->get();

            $adminOwnerDocCount = DriverDocument::where('type', 'owner')->where('is_enabled', 'Yes')->count();
            $adminOwnerDocuments = DriverDocument::where('type', 'owner')->where('is_enabled', 'Yes')->get();

            $allDrivers = Driver::all();

            foreach ($allDrivers as $drivers) {

                $driverId = $drivers->id;
                $driver = Driver::find($driverId);

                if($driver->isOwner){

                    $ownerDocCount = 0;
                    foreach ($adminOwnerDocuments as $doc) {
                        $approved_documents = DriversDocuments::where('driver_id', $driverId)->where('document_status', 'Approved')->where('document_id', $doc->id)->get();
                        if (count($approved_documents) > 0) {
                            $ownerDocCount++;
                        }
                    }
                    if ($adminOwnerDocCount == $ownerDocCount) {
                        $driver->is_verified = 1;
                    } else {
                        $driver->is_verified = 0;
                    }

                }else{

                    $driverDocCount = 0;
                    foreach ($adminDriverDocuments as $doc) {
                        $approved_documents = DriversDocuments::where('driver_id', $driverId)->where('document_status', 'Approved')->where('document_id', $doc->id)->get();
                        if (count($approved_documents) > 0) {
                            $driverDocCount++;
                        }
                    }
                    if ($adminDriverDocCount == $driverDocCount) {
                        $driver->is_verified = 1;
                    } else {
                        $driver->is_verified = 0;
                    }
                }

                $driver->save();
            }
        }

        return redirect()->back();
    }

    public function toggalSwitch(Request $request)
    {
        $ischeck = $request->input('ischeck');
        $id = $request->input('id');
        $document = DriverDocument::find($id);

        if ($ischeck == "true") {
            $document->is_enabled = 'Yes';
        } else {
            $document->is_enabled = 'No';
        }
        $document->save();
        $adminDocCount = DriverDocument::where('admin_documents.is_enabled', 'Yes')->count();
        $adminDocuments = DriverDocument::where('admin_documents.is_enabled', 'Yes')->get();

        $allDrivers = Driver::all();
        foreach ($allDrivers as $drivers) {
            $driverId = $drivers->id;
            $driver = Driver::find($driverId);
            $driverDocCount = 0;
            foreach ($adminDocuments as $doc) {
                $approved_documents = DriversDocuments::where('driver_id', $driverId)->where('document_status', 'Approved')->where('document_id', $doc->id)->get();
                if (count($approved_documents) > 0) {
                    $driverDocCount++;
                }
            }
            if ($adminDocCount == $driverDocCount) {
                $driver->is_verified = 1;
            } else {
                $driver->is_verified = 0;
            }
            $driver->save();
        }
    }
}
