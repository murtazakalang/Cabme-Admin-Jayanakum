<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaxController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if ($request->has('search') && $request->search != '' && $request->selected_search == 'libelle') {
            $search = $request->input('search');
            $taxes = Tax::where('tax.libelle', 'LIKE', '%'.$search.'%');
        } else if ($request->has('search') && $request->search != '' && $request->selected_search == 'country') {
            $search = $request->input('search');
            $taxes = Tax::where('tax.country', 'LIKE', '%' . $search . '%');
        } else {
            $taxes = Tax::query();
        }
        $totalLength = count($taxes->get());
        $perPage = $request->input('per_page', 20);
        $taxes=$taxes->paginate($perPage)->appends($request->all());
        return view("tax.index",compact('taxes','totalLength','perPage'));
    }

    public function create()
    {
        $countries = Country::all();
        return view("tax.create")->with("countries", $countries);
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(), $rules = [
            'libelle' => 'required',
            'tax' => 'required',
            'type' => 'required',
            'country' => 'required',
        ], $messages = [
                'libelle.required' => trans('lang.the_tax_label_is_required'),
                'tax.required' => trans('lang.the_tax_field_is_required'),
                'type.required' => trans('lang.the_tax_type_is_required'),
                'country.required' => trans('lang.the_country_is_required'),
            ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }
        $data = $request->all();
        $date = date('Y-m-d H:i:s');
        Tax::create([
            'libelle'=>$data['libelle'],
            'value'=>$data['tax'],
            'type'=>$data['type'],
            'country'=>$data['country'],
            'statut'=>($request->has('statut')) ? 'yes' :'no',
            'creer'=>$date
        ]);
        return redirect('tax')->with('message', trans('lang.tax_added_successfully'));

    }

    public function edit($id)
    {
        $countries = Country::all();
        $Tax = Tax::find($id);
        return view("tax.edit")->with('Tax', $Tax)->with("countries", $countries);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $rules = [
            'libelle' => 'required',
            'tax' => 'required',
            'type' => 'required',
            'country' => 'required',
        ], $messages = [
                'libelle.required' => trans('lang.the_tax_label_is_required'),
                'tax.required' => trans('lang.the_tax_field_is_required'),
                'type.required' => trans('lang.the_tax_type_is_required'),
                'country.required' => trans('lang.the_country_is_required'),
            ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }

        $name = $request->input('libelle');
        $value = $request->input('tax');
        $type = $request->input('type');
        $enabled = $request->has('statut') ? 'yes' : 'no';
        $country = $request->input('country');
        $modifier = date('Y-m-d H:i:s');
        $Tax = Tax::find($id);

        if ($Tax) {
            $Tax->libelle = $name;
            $Tax->value = $value;
            $Tax->type = $type;
            $Tax->statut = $enabled;
            $Tax->country = $country;
            $Tax->modifier = $modifier;
            $Tax->save();
        }
        return redirect('tax')->with('message', trans('lang.tax_updated_successfully'));

    }

    public function delete($id)
    {
        if ($id != "") {
            $id = json_decode($id);
            if (is_array($id)) {
                for ($i = 0; $i < count($id); $i++) {
                    $user = Tax::find($id[$i]);
                    $user->delete();
                }
            } else {
                $user = Tax::find($id);
                $user->delete();
            }
        }
        return redirect()->back();
    }

    public function changeStatus(Request $request, $id)
    {
        $Tax = Tax::find($id);
        if ($Tax->statut == 'no') {
            $Tax->statut = 'yes';
            $comm = Tax::where('id', '!=', $id)->update(['statut' => 'no']);
        } else {
            $Tax->statut = 'no';
            $comm = Tax::where('id', '!=', $id)->update(['statut' => 'yes']);
        }
        $Tax->save();
        return redirect()->back();
    }

    public function searchTax(Request $request)
    {
        if ($request->has('search') && $request->search != '' && $request->selected_search == 'Name') {
            $search = $request->input('search');
            $Tax = Tax::select('tax.*')->where('tax.libelle', 'LIKE', '%' . $search . '%')->paginate(10);

        } else if ($request->has('search') && $request->search != '' && $request->selected_search == 'Type') {
            $search = $request->input('search');
            $Tax = Tax::select('tax.*')->where('tax.type', 'LIKE', '%' . $search . '%')->paginate(10);
        } else {
            $Tax = Tax::select('tax.*')->paginate(10);
        }
        return view('tax.index')->with("Tax", $Tax);
    }

    public function toggalSwitch(Request $request)
    {
        $ischeck = $request->input('ischeck');
        $id = $request->input('id');
        $tax = Tax::find($id);

        if ($ischeck == "true") {
            $tax->statut = 'yes';
        } else {
            $tax->statut = 'no';
        }
        $tax->save();

    }
}