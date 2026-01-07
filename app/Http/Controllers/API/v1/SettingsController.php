<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Settings;
use App\Models\Currency;
use App\Models\Tax;
use App\Models\Commission;

class SettingsController extends Controller
{
    public function getData(Request $request)
    {
        $settings = Settings::first();
        $settings->active_services = explode(',',$settings->active_services);

        $currency = Currency::where('statut', '=', 'yes')->first();
        $settings->currency = $currency->symbole;
        $settings->decimal_digit = $currency->decimal_digit;
        $settings->symbol_at_right = $currency->symbol_at_right;
    
        $taxes = Tax::where('statut', '=', 'yes')->get();
        $settings->tax = $taxes;

        $admin_commission = Commission::first();
        $settings->admin_commission = $admin_commission;

        $response['success'] = 'success';
        $response['error'] = null;
        $response['message'] = 'Settings successfully found';
        $response['data'] = $settings->toArray();

        return response()->json($response);
    }
}
