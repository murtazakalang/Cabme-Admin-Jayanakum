<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use Validator;

class ReviewController extends Controller
{
 
    public function submitReview(Request $request){

        $response = array();
    
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'driver_id' => 'required|integer',
            'review_from' => 'required|in:driver,customer',
            'review_to' => 'required|in:driver,customer',
            'booking_id' => 'required|integer',
            'booking_type' => 'required|in:ride,parcel,rental',
            'comment' => 'required',
            'rating' => 'required',
        ]);

        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }
        
        $review = Review::updateOrCreate(
            [
                'user_id' => $request->get('user_id'),
                'driver_id' => $request->get('driver_id'),
                'review_from' => $request->get('review_from'),
                'review_to' => $request->get('review_to'),
                'booking_id' => $request->get('booking_id'),
                'booking_type' => $request->get('booking_type'),
            ],
            [
                'comment' => $request->get('comment'),
                'rating' => $request->get('rating'),
            ]
        );

        $review->rating = number_format($review->rating, 1);

        $response['success'] = 'success';
        $response['code'] = 200;
        $response['message'] = 'Review successfully submiited';
        $response['data'] = $review->toArray();

        return response()->json($response);
    }

    public function getReview(Request $request){

        $response = array();
    
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required_if:review_type,booking|nullable',
            'booking_type' => 'required|in:ride,parcel,rental',
            'review_from' => 'required|in:customer,driver',
        ]);

        if($validator->fails()){
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = $validator->errors()->first();
            $response['data'] = null;
            return response()->json($response);
        }

        $booking_type = $request->get('booking_type');
        $booking_id = $request->get('booking_id');
        $review_from = $request->get('review_from');

        $review = Review::where('review_from', $review_from)->where('booking_id', $booking_id)->where('booking_type', $booking_type)->first();
        if ($review) {
            $response['success'] = 'success';
            $response['code'] = 200;
            $response['message'] = 'Reviews successfully found';
            $response['data'] = $review;
        }else{
            $response['success'] = 'Failed';
            $response['code'] = 200;
            $response['message'] = 'No reviews found';
            $response['data'] = null;
        }

        return response()->json($response);
    }
}
