<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use Validator;

class BankAccountDetailsController extends Controller
{
  
    public function getData(Request $request)
    {

      $response = array();
      $validator = Validator::make($request->all(), [
          'id_driver' => 'required|integer|exists:conducteur,id',
        ]);
      if($validator->fails()){
          $response['success'] = 'Failed';
          $response['code'] = 404;
          $response['message'] = $validator->errors()->first();
          $response['data'] = null;
          return response()->json($response);
      }
      $id_driver = $request->get('id_driver');
      $bank_details = Driver::select('bank_name', 'branch_name', 'holder_name', 'account_no', 'other_info', 'ifsc_code')->where('id', $id_driver)->first();
      if(
          $bank_details->bank_name == null && $bank_details->branch_name == null && $bank_details->holder_name == null &&
          $bank_details->account_no == null && $bank_details->other_info == null && $bank_details->ifsc_code == null 
      ){
          $response['success'] = 'success';
          $response['error'] = null;
          $response['message'] = 'No bank details found';
          $response['data'] = null;
      }else{
        $response['success'] = 'success';
        $response['error'] = null;
        $response['message'] = 'Bank details fetch successfully';
        $response['data'] = $bank_details;
      }
    
      return response()->json($response);
    }

    public function register(Request $request)
    {

      $response = array();
      $validator = Validator::make($request->all(), [
          'id_driver' => 'required|integer|exists:conducteur,id',
          'bank_name' => 'required',
          'branch_name' => 'required',
          'holder_name' => 'required',
          'account_no' => 'required',
          'information' => 'required',
          'ifsc_code' => 'required',
      ]);
      if($validator->fails()){
          $response['success'] = 'Failed';
          $response['code'] = 404;
          $response['message'] = $validator->errors()->first();
          $response['data'] = null;
          return response()->json($response);
      }
      
      $id_driver = $request->get('id_driver');
      $bank_name = $request->get('bank_name');
      $branch_name = $request->get('branch_name');
      $holder_name = $request->get('holder_name');
      $account_no = $request->get('account_no');
      $other_info = $request->get('information');
      $ifsc_code = $request->get('ifsc_code');
      
      $driver = Driver::find($id_driver);
      $driver->bank_name = $bank_name;
      $driver->branch_name = $branch_name;
      $driver->holder_name = $holder_name;
      $driver->account_no = $account_no;
      $driver->other_info = $other_info;
      $driver->ifsc_code = $ifsc_code;
      $driver->modifier = now();
      $driver->save();
        
      $response['success'] = 'success';
      $response['error'] = null;
      $response['message'] = 'Bank details added successfully';
      $response['data'] = null;

      return response()->json($response);
    }
}
