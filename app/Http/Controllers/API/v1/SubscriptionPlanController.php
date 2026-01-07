<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionHistory;
use App\Models\Driver;
use App\Models\Transaction;
use App\Models\Settings;
use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Validator;

class SubscriptionPlanController extends Controller
{
    
    public function getPlanList(Request $request)
    {
        $response = array();
        $validator = Validator::make($request->all(), [
            'plan_for' => 'required|in:driver,owner',
            'id_driver'  => 'required|integer|exists:conducteur,id',
         ]);
        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        $plan_for = $request->plan_for;
        $id_driver = $request->id_driver;

        $output = [];
        
        $settings = Settings::first();
        $commission = Commission::first();

        $subscriptionPlan = [];
        if($commission->statut == "yes" && $settings->subscription_model == "false"){
            $subscriptionPlan = SubscriptionPlan::where('id','1')->get();
        }else if($commission->statut == "no" && $settings->subscription_model == "true"){
            $subscriptionPlan = SubscriptionPlan::where('isEnable', '=', 'true')->where('plan_for', '=', $plan_for)->get();
        }else if($commission->statut == "yes" && $settings->subscription_model == "true"){
            $subscriptionPlan = SubscriptionPlan::where('isEnable', 'true')->where(function ($query) use ($plan_for) {
                $query->where('plan_for', $plan_for)->orWhere('plan_for','=','');
            })->get();
        }

        if (count($subscriptionPlan) > 0) {
            foreach ($subscriptionPlan as $row) {
                $row->id = (string)$row->id;
                if ($row->image != '') {
                    if (file_exists(public_path('assets/images/subscription' . '/' . $row->image))) {
                        $row->image = asset('assets/images/subscription') . '/' . $row->image;
                    } else {
                        $row->image = asset('assets/images/placeholder_image.jpg');
                    }
                }
                $row->status = $this->checkDriverPlan($row->id, $id_driver);
                $output[] = $row;
            }
            if (!empty($output)) {
                $response['success'] = 'success';
                $response['error'] = null;
                $response['message'] = 'Subscription plans fetched successfully';
                $response['data'] = $output;
            } else {
                $response['success'] = 'Failed';
                $response['error'] = 'Error while fetch data';
            }
        } else {
            $response['success'] = 'Failed';
            $response['error'] = 'No Data Found';
            $response['message'] = null;
        }
        
        return response()->json($response);
    }

    public function checkDriverPlan($planId, $driverId){
        $history = SubscriptionHistory::where('user_id', $driverId)
        ->where('subscriptionPlanId', $planId)
        ->orderByDesc('created_at')
        ->first();
        return $history && (is_null($history->expiry_date) || $history->expiry_date > now());
    }

    public function setData(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'planId' => 'required|integer|exists:subscription_plans,id',
            'driverId'   => 'required',
            'id_payment'  => 'required|integer|exists:payment_method,id',
        ]);
        if($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'code'    => 404,
                'message' => $validator->errors()->first(),
                'data'    => null,
            ]);
        }

        $planId = $request->get('planId');
        $driverId = $request->get('driverId');
        $id_payment = $request->get('id_payment');

        $subscriptionData = SubscriptionPlan::find($planId);
        $driver = Driver::find($driverId);

        if ($id_payment == '9') {

            // Check wallet only for PAID plans
            if ($subscriptionData->type === 'paid') {

                if (floatval($driver->amount) < floatval($subscriptionData->price)) {

                    $response['success'] = 'Failed';
                    $response['error'] = 'Insufficient wallet balance';
                    $response['message'] = "You don't have sufficient balance to purchase this plan";
                    return response()->json($response);
                }

                // Deduct wallet balance
                $newWalletBalance = floatval($driver->amount) - floatval($subscriptionData->price);

                Driver::where('id', $driverId)->update([
                    'amount' => $newWalletBalance
                ]);

                Transaction::create([
                    'user_id'        => $driverId,
                    'user_type'      => 'driver',
                    'payment_method'=> 'Wallet',
                    'amount'         => $subscriptionData->price,
                    'is_credited'    => '0',
                    'booking_id'     => $subscriptionData->id,
                    'booking_type'   => 'plan',
                    'note'           => 'Subscription plan amount debited',
                    'transaction_id'=> strtoupper(uniqid()),
                ]);
            }
        }

        if($planId == 1){
            $subscriptionData->vehicle_limit = '-1';
            $subscriptionData->driver_limit = '-1';
            $subscriptionTotalVehicle = '-1';
            $subscriptionTotalDriver = '-1';
        }else{
            $subscriptionTotalVehicle = $subscriptionData->vehicle_limit;
            $subscriptionTotalDriver = $subscriptionData->driver_limit;
        }

        $subscriptionPlanId = $subscriptionData->id;
        $subscriptionTotalOrders = $subscriptionData->bookingLimit;
        $expiryDay = $subscriptionData->expiryDay;
        $expiryDate = intval($expiryDay) !== -1 ? Carbon::now()->addDays($expiryDay) : null;

        Driver::where('id', $driverId)->update([
            'subscriptionPlanId' => $subscriptionPlanId,
            'subscriptionExpiryDate' => $expiryDate,
            'subscriptionTotalOrders' => $subscriptionTotalOrders,
            'subscriptionTotalVehicle' => $subscriptionTotalVehicle,
            'subscriptionTotalDriver' => $subscriptionTotalDriver,
            'subscription_plan' => $subscriptionData
        ]);
        
        // Cancel any existing active subscription histories
        SubscriptionHistory::where('user_id', $driverId)->where('status', 'active')->update(['status' => 'cancelled']);
            
        $subscriptionHistory =  SubscriptionHistory::create([
            'subscription_plan' => $subscriptionData,
            'expiry_date' => $expiryDate,
            'payment_type' => $id_payment,
            'user_id' => $driverId,
            'plan_for' => $subscriptionData->plan_for,
            'subscriptionPlanId' => $subscriptionPlanId,
            'status' => 'active',
        ]);

        if (!$subscriptionHistory) {
            $response['success'] = 'Failed';
            $response['error'] = 'Failed to create subscription history';
            $response['message'] = 'Database error';
            return response()->json($response);
        }
        
        $response['success'] = 'success';
        $response['error'] = null;
        $response['message'] = 'Subscription added successfully';
        return response()->json($response);
    }

    public function getSubscriptionHistory(Request $request)
    {
        $driverId = $request->get('driverId');
        $historyData = SubscriptionHistory::join('payment_method', 'payment_method.id', '=', 'subscription_history.payment_type')
        ->select('subscription_history.*', 'payment_method.libelle as payment_method')
        ->where('user_id', $driverId)
        ->orderByRaw("FIELD(subscription_history.status, 'active', 'cancelled', 'expired')")
        ->orderBy('created_at', 'desc')->get();
        
        $output = [];
        if (count($historyData) > 0) {
            foreach ($historyData as $row) {
                $data = $row->toArray();
                $data['id'] = (string) $row->id;
                $subscription_plan = $row->subscription_plan;
                if (!empty($subscription_plan['image'])) {
                    $imagePath = public_path('assets/images/subscription/' . $subscription_plan['image']);
                    if (file_exists($imagePath)) {
                        $subscription_plan['image'] = asset('assets/images/subscription/' . $subscription_plan['image']);
                    } else {
                        $subscription_plan['image'] = asset('assets/images/placeholder_image.jpg');
                    }
                }
                $data['subscription_plan'] = $subscription_plan;
                $data['created_at'] = $row->created_at->format('Y-m-d H:i:s');
                $output[] = $data;
            }

            if (!empty($output)) {
                $response['success'] = 'success';
                $response['error'] = null;
                $response['message'] = 'Subscription plans fetched successfully';
                $response['data'] = $output;
            } else {
                $response['success'] = 'Failed';
                $response['error'] = 'Error while fetch data';
            }

        } else {

            $response['success'] = 'Failed';
            $response['error'] = 'No Data Found';
            $response['message'] = null;
        }

        return response()->json($response);
    }
}
