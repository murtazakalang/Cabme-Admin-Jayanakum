<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CurrencyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {

        if ($request->has('search') && $request->search != '' && $request->selected_search == 'libelle') {
            $search = $request->input('search');
            $currencies = Currency::where('libelle', 'LIKE', '%'.$search.'%');
        } else if ($request->has('search') && $request->search != '' && $request->selected_search == 'symbole') {
            $search = $request->input('search');
            $currencies = Currency::where('symbole', 'LIKE', '%'.$search.'%');
        } else {
            $currencies = Currency::query();
        }

        $totalLength = count($currencies->get());
        $perPage = $request->input('per_page', 20);
        $currencies = $currencies->paginate($perPage)->appends($request->all());
        return view("currency.index",compact('currencies', 'totalLength','perPage'));
    }

    public function createCurrency()
    {
    	return view("currency.create");
    }

    public function currencyEdit(Request $request, $id)
    {
		$currency = Currency::where('id', $id)->first();
        return view("currency.edit", compact('currency'));
    }

    public function update($id, Request $request)
    {
        $name = $request->input('libelle');
        $symbol = $request->input('symbol');
        $status = $request->input('statut');
        $decimal = $request->input('decimal_digit');
        $symbol_at_right = $request->has('symbol_at_right')?"true":"false";

        if ($status == "on") {
            Currency::where('statut', "yes")->update(array('statut' => "no"));
            $status = "yes";
        } else {
            $status = "no";
        }

        $currencies = Currency::find($id);

        if ($currencies) {
            $currencies->libelle = $name;
            $currencies->symbole = $symbol;
            $currencies->statut = $status;
            $currencies->symbol_at_right = $symbol_at_right;
            $currencies->decimal_digit = $decimal;
            $currencies->modifier = date('Y-m-d H:i:s');

            $currencies->save();
        }

        return redirect('currency')->with('message', trans('lang.currency_updated'));
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), $rules = [
            'libelle' => 'required',
            'symbol' => 'required',


        ], $messages = [
            'libelle.required' => trans('lang.the_name_field_is_required'),
            'symbol.required' => trans('lang.the_symbol_field_is_required'),


        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }


        $name = $request->input('libelle');
        $symbol = $request->input('symbol');
        $status = $request->input('statut');
        $decimal = $request->input('decimal_digit');
        $symbol_at_right = $request->has('symbol_at_right') ? "true" : "false";

        if ($status == "yes") {
            Currency::where('statut', "yes")->update(array('statut' => "no"));
            $status = "yes";
        }
        else{
          $status='no';
        }

        $currencies = new Currency;

        if ($currencies) {
            $currencies->libelle = $name;
            $currencies->symbole = $symbol;
            $currencies->statut = $status;
            $currencies->decimal_digit = $decimal;
            $currencies->symbol_at_right = $symbol_at_right;
            $currencies->modifier = date('Y-m-d H:i:s');

            $currencies->save();
        }


        return redirect('currency')->with('message', trans('lang.currency_created'));
    }

    public function delete($id){
      if ($id != "") {

           $id = json_decode($id);

           if (is_array($id)) {

               for ($i = 0; $i < count($id); $i++) {
                   $user = Currency::find($id[$i]);
                   $user->delete();
               }

           } else {
               $user = Currency::find($id);
               $user->delete();
           }

       }

       return redirect()->back();
    }

    public function changeStatus($id)
    {
        $currencies = Currency::find($id);
        if ($currencies->statut == 'no') {
            Currency::where('statut', "yes")->update(array('statut' => "no"));
            $currencies->statut = 'yes';
        } else {
            $currencies->statut = 'no';
        }

        $currencies->save();
        return redirect()->back();

    }

    public function toggalSwitch(Request $request)
    {
        $ischeck = $request->input('ischeck');
        $id = $request->input('id');
        $currencies = Currency::find($id);
		
		$response = array();
        if($currencies->statut == 'yes')
        {
            
            $messages = trans('lang.you_can_not_disable_all_currencies');
            $response['error'] = $messages;
    
        }else{
	        if ($ischeck == "true") {
	
	            Currency::where('statut', "yes")->update(array('statut' => "no"));
	
	            $currencies->statut = 'yes';
	        } else {
	            $currencies->statut = 'no';
	        }
        	$currencies->save();
    	}
        
        return response()->json($response);
    }

}
