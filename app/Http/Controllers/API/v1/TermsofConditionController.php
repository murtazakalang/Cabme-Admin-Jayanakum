<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\TearmsCondition;
use Illuminate\Http\Request;

class TermsofConditionController extends Controller
{
  public function getData(Request $request)
  {

      $termcondition = TearmsCondition::first();

      $response['success']= 'success';
      $response['error']= null;
      $response['message']= 'successfully';
      $response['data']= $termcondition;
    
      return response()->json($response);

  }

}
