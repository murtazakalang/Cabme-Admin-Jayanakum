<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CouponController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $today = Carbon::now();
        if ($request->has('search') && $request->search != '') {
            $search = $request->input('search');
            $selected_search = $request->input('selected_search');
            $discounts = Coupon::when($selected_search == 'code', function ($query) use ($search) {
                $query->where('coupons.code', 'LIKE', "%{$search}%");
            })
            ->when($selected_search == 'discount', function ($query) use ($search) {
                $query->where('coupons.discount', 'LIKE', "%{$search}%");
            })
            ->when($selected_search == 'coupon_type', function ($query) use ($search) {
                $query->where('coupons.coupon_type', 'LIKE', "%{$search}%");
            })
            ->orderBy('coupons.creer', 'desc');
        } else {
            $discounts =  Coupon::orderBy('coupons.creer', 'desc');          
        }

        $totalRecords = $discounts->get();
        $totalLength = count($totalRecords);
        $perPage = $request->input('per_page', 20);
        $discounts= $discounts->paginate($perPage)->appends($request->all());

        $currency = Currency::where('statut', 'yes')->first();

        return view("coupons.index",compact('discounts', 'totalLength','currency', 'perPage'));
    }

    public function edit($id)
    {
        $discount = Coupon::where('id', "=", $id)->first();
        return view('coupons.edit')->with('discount', $discount);
    }

    public function updateDiscount(Request $request, $id)
    {

        $validator = Validator::make($request->all(), $rules = [
            'code' => 'required',
            'discount' => 'required',
            'type' => 'required',
            'expire_at' => 'required|date',
            'discription'=>'required',
            'coupon_type' => 'required',

        ], $messages = [
            'code.required' => trans('lang.code_field_is_required'),
            'discount.required' => trans('lang.the_discount_field_is_required'),
            'type.required' => trans('lang.the_discount_type_is_required'),
            'expire_at.required' => trans('lang.the_expire_date_field_is_required'),
            'discription.required' => trans('lang.the_description_field_is_required'),
            'coupon_type.required' => trans('lang.the_coupon_type_is_required'),
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }

        $code = $request->input('code');
        $discount = $request->input('discount');
        $type = $request->input('type');
        $expire_at = $request->input('expire_at');
        $description = $request->input('discription');
        $coupon_type = $request->input('coupon_type');

        $statut = $request->input('statut');
        $date = date('Y-m-d H:i:s');
        if ($statut == "on") {
            $statut = "yes";
        } else {
            $statut = "no";
        }

        $discounts = Coupon::find($id);
        if ($discounts) {
            $discounts->code = $code;
            $discounts->discount = $discount;
            $discounts->type = $type;
            $discounts->expire_at = $expire_at;
            $discounts->discription = $description;
            $discounts->coupon_type = $coupon_type;
            $discounts->statut = $statut;
            $discounts->modifier = $date;
            $discounts->save();
        }

        return redirect('coupons')->with('message', 'trans("lang.coupon_updated")');
    }

    public function create()
    {
        return view('coupons.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $rules = [
            'code' => 'required',
            'discount' => 'required',
            'type' => 'required',
            'expire_at' => 'required|date',
            'discription'=>'required',
            'coupon_type' => 'required',

        ], $messages = [
            'code.required' => trans('lang.code_field_is_required'),
            'discount.required' => trans('lang.the_discount_field_is_required'),
            'type.required' => trans('lang.the_discount_type_is_required'),
            'expire_at.required' => trans('lang.the_expire_date_field_is_required'),
            'discription.required' => trans('lang.the_description_field_is_required'),
            'coupon_type.required' => trans('lang.the_coupon_type_is_required'),
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)->with(['message' => $messages])
                ->withInput();
        }

        $code = $request->input('code');
        $discount = $request->input('discount');
        $type = $request->input('type');
        $expire_at = $request->input('expire_at');
        $description = $request->input('discription');
        $coupon_type = $request->input('coupon_type');

        $statut = $request->input('statut');
        $date = date('Y-m-d H:i:s');
        if ($statut == "on") {
            $statut = "yes";
        } else {
            $statut = "no";
        }

        $discounts = new Coupon;
        if ($discounts) {
            $discounts->code = $code;
            $discounts->discount = $discount;
            $discounts->type = $type;
            $discounts->expire_at = $expire_at;
            $discounts->discription = $description;
            $discounts->coupon_type = $coupon_type;
            $discounts->statut = $statut;
            $discounts->creer = $date;
            $discounts->modifier = $date;
            $discounts->save();
        }

        return redirect('coupons')->with('message', 'trans("lang.coupon_created")');
    }

    public function changeStatus($id)
    {
        $discount = Coupon::find($id);
        if ($discount->statut == 'no') {
            $discount->statut = 'yes';
        } else {
            $discount->statut = 'no';
        }

        $discount->save();
        return redirect()->back();
    }

    public function delete($id)
    {
        if ($id != "") {
            $id = json_decode($id);
            if (is_array($id)) {
                for ($i = 0; $i < count($id); $i++) {
                    $user = Coupon::find($id[$i]);
                    $user->delete();
                }
            } else {
                $user = Coupon::find($id);
                $user->delete();
            }
        }

        return redirect()->back();
    }

    public function toggalSwitch(Request $request){

        $ischeck = $request->input('ischeck');
        $id = $request->input('id');
        $discount = Coupon::find($id);
        if($ischeck=="true"){
          $discount->statut = 'yes';
        }else{
          $discount->statut = 'no';
        }
        $discount->save();
    }
}
