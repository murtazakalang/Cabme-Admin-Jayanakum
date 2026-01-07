<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\Currency;
use App\Models\Driver;
use App\Models\SubscriptionHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use File;
use Image;

class SubscriptionPlanController extends Controller
{

    public function __construct()
    {

        $this->middleware('auth');
    }

    public function index(Request $request)
    {

        $query = SubscriptionPlan::withCount('subscribers');

        if ($request->has('search') && $request->search != '') {
            $search = $request->input('search');

            if ($request->selected_search == 'name') {
                $query->where('name', 'LIKE', "%{$search}%");
            } elseif ($request->selected_search == 'price') {
                if (stripos('free', $search) !== false) {
                    $query->where('price', '0');
                }else{
                    $query->where('price', $search);
                } 
                
            }
        }
        $totalLength = count($query->get());
        $perPage = $request->input('per_page', 20);
        $subscriptionPlans = $query->paginate($perPage)->appends($request->all());

        $currency = Currency::where('statut', 'yes')->first();
        $overviewPlans = SubscriptionPlan::where('subscription_plans.id', '!=', 1)
            ->leftJoin('subscription_history', 'subscription_history.subscriptionPlanId', '=', 'subscription_plans.id')
            ->select(
                'subscription_plans.*',
                DB::raw('SUM(JSON_UNQUOTE(JSON_EXTRACT(subscription_history.subscription_plan, "$.price"))) as total_earning')
            )
            ->groupBy('subscription_plans.id')
            ->get();

        return view("subscription_plans.index", compact('subscriptionPlans', 'currency', 'overviewPlans', 'totalLength', 'perPage'));
    }



    public function create()
    {
        return view("subscription_plans.create");
    }

    public function store(Request $request)
    {
        $enabledPlans = SubscriptionPlan::where('isEnable', 'true')->where('id', '!=', 1)->count();

        $request->merge([
            'plan_points' => array_filter($request->plan_points) // Removes empty values
        ]);

        $validator = Validator::make($request->all(), $rules = [

            'planName' => 'required',
            'planPrice' => ['required_if:planType,paid',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('planType') === 'paid' && $value <= 0) {
                        $fail(__('lang.plan_price_in_positive_no'));
                    }
                }],
            'plan_validity' => [
                'required_if:plan_validity_days,limited',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('plan_validity_days') === 'limited') {
                        if ($value == 0) {
                            $fail(__('lang.expiry_day_zero'));
                        } elseif ($value < 0 && $value != -1) {
                            $fail(__('lang.expiry_day_in_positive_no'));
                        }
                    }
                }
            ],
            'description' => 'required',
            'order' => 'required',
            'image' => 'required|mimes:jpeg,jpg,png',
            'booking_limit' => ['required_if:set_booking_limit,limited',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('set_booking_limit') === 'limited' && $value <= 0) {
                        $fail(__('lang.booking_limit_in_positive_no'));

                    }
                }],
            'plan_points' => 'required|array|min:1',
            'plan_points.*' => 'required|string|min:1',
            'vehicle_limit' => [
                'required_if:plan_for,owner',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value < 0 && $value != -1) {
                        $fail(__('lang.valid_limit_no'));
                    }
                }
            ],
            'driver_limit' => [
                'required_if:plan_for,owner',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value < 0 && $value != -1) {
                        $fail(__('lang.valid_limit_no'));
                    }
                }
            ],
        ], $messages = [

            'planName.required' => __("lang.enter_plan_name"),
            'planPrice.required' => __("lang.enter_plan_price"),
            'plan_validity.required' => __("lang.please_enter_expiry"),
            'description.required' => __("lang.enter_description"),
            'order.required' => __("lang.enter_display_order"),
            'image.required' => __("lang.upload_plan_image"),
            'set_booking_limit.required' => __("lang.enter_booking_limit"),
            'plan_points.required' => __("lang.enter_plan_points")

        ]);

        if ($enabledPlans == 0 && !$request->status) {
            $validator->after(function ($validator) {
                $validator->errors()->add('status', __('lang.atleast_one_subscription_plan_should_be_active'));
            });
        }

        if ($validator->fails()) {
            return back()->withErrors($validator)->with(['message' => $messages])->withInput();
        }


        $data = $request->all();

        if ($request->hasfile('image')) {
            $file = $request->file('image');
            $extenstion = $file->getClientOriginalExtension();
            $time = time() . '.' . $extenstion;
            $filename = 'subscription_plan_' . $time;
            $path = public_path('assets/images/subscription/') . $filename;
            if (!file_exists(public_path('assets/images/subscription/'))) {
                mkdir(public_path('assets/images/subscription/'), 0777, true);
            }
            Image::make($file->getRealPath())->resize(100, 100)->save($path);
        }

        SubscriptionPlan::create([
            'name' => $data['planName'],
            'type' => $data['planType'],
            'price' => $data['planType'] == 'free' ? '0' : $data['planPrice'],
            'expiryDay' => $data['plan_validity_days'] == 'limited' ? $data['plan_validity'] : '-1',
            'description' => $data['description'],
            'place' => $data['order'],
            'isEnable' => ($request->has('status')) ? 'true' : 'false',
            'image' => $filename,
            'plan_points' => $data['plan_points'],
            'bookingLimit' => $data['set_booking_limit'] == 'limited' ? $data['booking_limit'] : '-1',
            'plan_for' => isset($data['plan_for']) ? $data['plan_for'] : '',
            'vehicle_limit' => isset($data['vehicle_limit']) ? $data['vehicle_limit'] : '',
            'driver_limit' => isset($data['driver_limit']) ? $data['driver_limit'] : '',
            'dispatcher_access' => isset($data['dispatcher_access']) ? $data['dispatcher_access'] : 'no'
        ]);

        return redirect('subscription-plans')->with('message', trans('lang.subscription_plan_added_successfully'));
    }

    public function edit($id)
    {

        $subscriptionPlan = SubscriptionPlan::find($id);
        return view("subscription_plans.edit")->with('subscriptionPlan', $subscriptionPlan);
    }



    public function update(Request $request, $id)
    {

        $enabledPlans = SubscriptionPlan::where('isEnable', 'true')->where('id', '!=', 1)->get();
        $enabledPlansCount = $enabledPlans->count();
        if ($enabledPlansCount == 1) {
            $enablePlanId = $enabledPlans->first()->id;
        }
        $request->merge([
            'plan_points' => array_filter($request->plan_points) // Removes empty values
        ]);

        $validator = Validator::make($request->all(), $rules = [

            'planName' => 'required',
            'planPrice' => [
                'required_if:planType,paid',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('planType') === 'paid' && $value <= 0) {
                        $fail(__('lang.plan_price_in_positive_no'));
                    }
                }
            ],
            'plan_validity' => [
                'required_if:plan_validity_days,limited',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('plan_validity_days') === 'limited') {
                        if ($value == 0) {
                            $fail(__('lang.expiry_day_zero'));
                        } elseif ($value < 0 && $value != -1) {
                            $fail(__('lang.expiry_day_in_positive_no'));
                        }
                    }
                }
            ],
            'description' => 'required',
            'order' => 'required',
            'image' => 'image|mimes:jpeg,jpg,png',
            'booking_limit' => [
                'required_if:set_booking_limit,limited',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('set_booking_limit') === 'limited' && $value <= 0) {
                        $fail(__('lang.booking_limit_in_positive_no'));
                        
                    }
                }
            ],
            'plan_points' => 'required|array|min:1',
            'plan_points.*' => 'required|string|min:1',
            'vehicle_limit' => [
                'required_if:plan_for,owner',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value < 0 && $value != -1) {
                        $fail(__('lang.valid_limit_no'));
                    }
                }
            ],
            'driver_limit' => [
                'required_if:plan_for,owner',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value < 0 && $value != -1) {
                        $fail(__('lang.valid_limit_no'));
                    }
                }
            ],

        ], $messages = [

            'planName.required' => __("lang.enter_plan_name"),
            'planPrice.required' => __("lang.enter_plan_price"),
            'plan_validity.required' => __("lang.please_enter_expiry"),
            'description.required' => __("lang.enter_description"),
            'order.required' => __("lang.enter_display_order"),
            'image.required' => __("lang.upload_plan_image"),
            'set_booking_limit.required' => __("lang.enter_booking_limit"),
            'plan_points.required' => __("lang.enter_plan_points")

        ]);

        if ($enabledPlansCount == 1 && $enablePlanId == $id && !$request->status) {
            $validator->after(function ($validator) {
                $validator->errors()->add('status', __('lang.atleast_one_subscription_plan_should_be_active'));
            });
        }
        if ($id != 1 && intVal($request->order) == 0) {
            $validator->after(function ($validator) {
                $validator->errors()->add('order', __('lang.commision_plan_will_be_always_at_first'));
            });
        }

        if ($validator->fails()) {
            return back()->withErrors($validator)->with(['message' => $messages])->withInput();
        }

        $plan = SubscriptionPlan::find($id);
        $filename = $plan->image;
        if ($request->hasfile('image')) {
            $destination = public_path('assets/images/subscription/' . $plan->image);

            if (File::exists($destination)) {
                File::delete($destination);
            }

            $file = $request->file('image');
            $extenstion = $file->getClientOriginalExtension();
            $filename = 'subscription_plan_' . $id . '.' . $extenstion;
            $path = public_path('assets/images/subscription/') . $filename;

            if (!file_exists(public_path('assets/images/subscription/'))) {
                mkdir(public_path('assets/images/subscription/'), 0777, true);
            }

            Image::make($file->getRealPath())->resize(100, 100)->save($path);
        }

        $data = $request->all();

        SubscriptionPlan::where('id', $id)->update([
            'name' => $data['planName'],
            'type' => $data['planType'],
            'price' => $data['planType'] == 'free' ? '0' : $data['planPrice'],
            'expiryDay' => $data['plan_validity_days'] == 'limited' ? $data['plan_validity'] : '-1',
            'description' => $data['description'],
            'place' => $data['order'],
            'isEnable' => ($request->has('status')) ? 'true' : 'false',
            'image' => $filename,
            'plan_points' => $data['plan_points'],
            'bookingLimit' => $data['set_booking_limit'] == 'limited' ? $data['booking_limit'] : '-1',
            'plan_for' => isset($data['plan_for']) ? $data['plan_for'] : '',
            'vehicle_limit' => $id == 1 ? '-1' : ($data['vehicle_limit'] ?? '0'),
            'driver_limit'  => $id == 1 ? '-1' : ($data['driver_limit'] ?? '0'),
            'dispatcher_access' => isset($data['dispatcher_access']) ? $data['dispatcher_access'] : 'no'
        ]);

        return redirect('subscription-plans')->with('message', trans('lang.subscription_plan_updated_successfully'));
    }

    public function delete($id)
    {
        if ($id != "") {
            $id = json_decode($id);
            if (is_array($id)) {
                for ($i = 0; $i < count($id); $i++) {
                    $plan = SubscriptionPlan::find($id[$i]);
                    $plan->delete();
                }
            } else {
                $plan = SubscriptionPlan::find($id);
                $plan->delete();
            }
        }
        return redirect()->back();
    }


    public function toggalSwitch(Request $request)
    {
        $enabledPlans = SubscriptionPlan::where('isEnable', 'true')->where('id', '!=', 1)->get();
        $enabledPlansCount = $enabledPlans->count();
        if ($enabledPlansCount == 1) {
            $enablePlanId = $enabledPlans->first()->id;
        }

        $ischeck = $request->input('ischeck');
        $id = $request->input('id');

        $subscriptionPlan = SubscriptionPlan::find($id);
        if ($ischeck == "true") {
            $subscriptionPlan->isEnable = 'true';
            $subscriptionPlan->save();
            return response()->json(['success' => true, 'message' => trans('lang.subscription_plan_disabled_successfully')]);
        } else {
            if ($enabledPlansCount == 1 && $enablePlanId == $id) {
                return response()->json(['success' => false, 'message' => __('lang.atleast_one_subscription_plan_should_be_active')], 400);
            } else {
                $subscriptionPlan->isEnable = 'false';
                $subscriptionPlan->save();
                return response()->json(['success' => true, 'message' => trans('lang.subscription_plan_disabled_successfully')]);
            }
        }
    }
    public function currentSubscriberList($id,Request $request)
    {
        $subscriptionPlan = SubscriptionPlan::where('id', $id)->first();
        $query = Driver::where('subscriptionPlanId', $id)->select('id','nom', 'prenom', 'subscriptionExpiryDate', 'subscriptionTotalOrders', 'subscription_plan','subscriptionTotalVehicle', 'subscriptionTotalDriver', 'role');
        if ($request->has('search') && $request->search != '') {
            $search = $request->input('search');
            if ($request->selected_search == 'driver') {
                $query->where('conducteur.prenom', 'LIKE', '%' . $search . '%')
                    ->orWhere(DB::raw('CONCAT(conducteur.nom, " ",conducteur.prenom)'), 'LIKE', '%' . $search . '%');
            } elseif ($request->selected_search == 'planName') {   
                    $query->where('subscription_plan->name', 'LIKE', "%{$search}%");
            } elseif ($request->selected_search == 'planType') {
                $query->where('subscription_plan->type', 'LIKE', "%{$search}%");
            }
        }
        $perPage = $request->input('per_page', 20);
        $currentSubscribers=$query->paginate($perPage)->appends($request->all());
        return view("subscription_plans.current_subscriber", compact('subscriptionPlan', 'currentSubscribers','perPage'));
    }
    
    public function SubscriptionHistory(Request $request)
    {
        $query = SubscriptionHistory::join('conducteur', 'conducteur.id', '=', 'subscription_history.user_id')
            ->leftjoin('subscription_plans', 'subscription_plans.id','=', 'subscription_history.subscriptionPlanId')
            ->leftjoin('payment_method', 'payment_method.id','=', 'subscription_history.payment_type')
            ->select(
                'conducteur.nom', 
                'conducteur.prenom', 
                'conducteur.isOwner', 
                'subscription_history.*', 
                'payment_method.libelle as payment_name',
                'subscription_plans.type as plan_type'
            );

        if ($request->has('search') && $request->search != '') {
            $search = $request->input('search');

            if ($request->selected_search == 'name') {
                $query->where('subscription_plans.name', 'LIKE', '%'.$search.'%');
            } elseif ($request->selected_search == 'driver') {
                $query->where(function($q) use ($search) {
                    $q->where('conducteur.prenom', 'LIKE', '%' . $search . '%')
                    ->orWhere(DB::raw('CONCAT(conducteur.nom, " ",conducteur.prenom)'), 'LIKE', '%' . $search . '%');
                });
            } elseif ($request->selected_search == 'paymentMethod') {
                $query->where('payment_method.libelle', 'LIKE', '%'.$search.'%');
            }
        }

        $totalLength = $query->count();
        $perPage = $request->input('per_page', 20);
        $history = $query->orderBy('subscription_history.created_at','desc')->paginate($perPage)->appends($request->all());

        return view("subscription_plans.history", compact('history','totalLength', 'perPage'));
    }


    public function deleteHistory($id)
    {
        if ($id != "") {
            $id = json_decode($id);
            if (is_array($id)) {
                for ($i = 0; $i < count($id); $i++) {
                    $plan = SubscriptionHistory::find($id[$i]);
                    $plan->delete();
                }
            } else {
                $plan = SubscriptionHistory::find($id);
                $plan->delete();
            }
        }
        return redirect()->back();
    }
}
