<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use DB;
use Carbon\Carbon;

class DiscountController extends Controller
{
  
  public function discountList(Request $request)
  {
      $coupon_type = $request->get('coupon_type');
      
      if(!$coupon_type){
        $response['success'] = 'Failed';
        $response['error'] = 'Coupon type missing';
        $response['message'] = null;
        return response()->json($response);
      }
    
      $today = Carbon::now();
      
      $coupons = Coupon::where('statut', '=', 'yes')->where('coupon_type', '=', $coupon_type)->where('expire_at', '>=', $today)->get();

      if (!empty($coupons)) {

          $output = [];
          foreach ($coupons as $coupon) {
            $coupon->expire_at = date('Y-m-d', strtotime($coupon->expire_at));
            $output[] = $coupon;
          }

          if (!empty($coupons)) {
            $response['success'] = 'success';
            $response['error'] = null;
            $response['message'] = 'successfully';
            $response['data'] = $output;
          } else {
            $response['success'] = 'Failed';
            $response['error'] = 'No Data Found';
            $response['message'] = null;
          }
      } else {
          $response['success'] = 'Failed';
          $response['error'] = 'Not Found';
      }

      return response()->json($response);
    }
}
