<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Validator;
use Carbon\Carbon;

class WalletHistoryController extends Controller
{
    public function getWalletHistory(Request $request)
    {
        $response = array();
    
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'user_type' => 'required',
        ]);

        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }
        
        $user_type = $request->get('user_type');
        $user_id = $request->get('user_id');

        $transactions = Transaction::where('user_type',$user_type)->where('user_id',$user_id)
        ->orderBy('created_at', 'desc')->orderBy('id', 'desc')
        ->get();

        if($transactions){

            $response['success'] = 'success';
            $response['code'] = 200;
            $response['message'] = 'Found wallet history';
            
            $response['data'] = $transactions->toArray();
        }else{
            $response['success'] = 'Failed';
            $response['code'] = 200;
            $response['message'] = 'No result found';
            $response['data'] = null;
        }

        return response()->json($response);
    }
}
