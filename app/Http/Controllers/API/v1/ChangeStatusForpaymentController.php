<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Requests;
use App\Models\ParcelOrder;
use App\Models\RentalOrder;
use Illuminate\Http\Request;
use DB;
use Validator;

class ChangeStatusForpaymentController extends Controller
{
  
    public function ChangeBookingStatus(Request $request)
    {
      
      $response = array();
      $validator = Validator::make($request->all(), [
          'booking_type' => 'required|in:ride,parcel,rental',
          'booking_id' => 'required|integer',
          'id_payment' => 'required|integer|exists:payment_method,id',
      ]);

      if ($validator->fails()) {
          $response['success'] = 'Failed';
          $response['code'] = 404;
          $response['message'] = $validator->errors()->first();
          $response['data'] = null;
          return response()->json($response);
      }

      $booking_type = $request->booking_type;
      $booking_id = $request->booking_id;
      $id_payment = $request->id_payment;

      if($booking_type == "ride"){
          $data = Requests::find($booking_id);
      }else if($booking_type == "parcel"){
          $data = ParcelOrder::find($booking_id);
      }else if($booking_type == "rental"){
          $data = RentalOrder::find($booking_id);
      }

      if($data){

          $data->id_payment_method = $id_payment;
          $data->save();

          $response['success'] = 'success';
          $response['code'] = 200;
          $response['message'] = 'Payment method successfully changed';
          $response['data'] = null;
      }else{
          $response['success'] = 'Failed';
          $response['code'] = 404;
          $response['message'] = 'No booking found';
          $response['data'] = null;
      }

      return response()->json($response);
    }
}