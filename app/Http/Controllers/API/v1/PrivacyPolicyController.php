<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PrivacyPolicy;

class PrivacyPolicyController extends Controller
{
  
  public function getData(Request $request)
  {

      $privacyPolicy = PrivacyPolicy::first();

      $response['success']= 'success';
      $response['error']= null;
      $response['message']= 'successfully';
      $response['data']= $privacyPolicy;
    
      return response()->json($response);
  }
}
