<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Complaints;
use Illuminate\Http\Request;
use Validator;

class AddComplaintsController extends Controller
{
    
    public function index(Request $request)
    {
        
        $response = array();
        $validator = Validator::make($request->all(), [
            'booking_type' => 'required|in:ride,parcel,rental',
            'booking_id' => 'required|integer',
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

        $complaint = Complaints::where('booking_id', $booking_id)->where('booking_type', $booking_type)->first();

        if ($complaint) {
            $response['success'] = 'success';
            $response['code'] = 200;
            $response['message'] = 'Complaint found successfully';
            $response['data'] = $complaint->toArray();
        } else {
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = 'No complaint found for the given details';
            $response['data'] = null;
        }

        return response()->json($response);
    }

    public function register(Request $request)
    {
        $response = array();
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'booking_type' => 'required|in:ride,parcel,rental',
            'booking_id' => 'required|integer',
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
        $title = $request->title;
        $description = $request->description;

        if (Complaints::where('booking_id', $booking_id)->where('booking_type', $booking_type)->exists()){
            return response()->json([
                'success' => 'Failed',
                'error' => 'Complaint Already Submitted',
            ]);
        }

        $complaint = Complaints::create([
            'title' => $title,
            'description' => $description,
            'status' => 'initiated',
            'booking_id' => $booking_id,
            'booking_type' => $booking_type,
        ]);

        if ($complaint) {
            $response['success'] = 'success';
            $response['code'] = 200;
            $response['message'] = 'Complaint added successfully';
            $response['data'] = $complaint->toArray();
        } else {
            $response['success'] = 'Failed';
            $response['code'] = 404;
            $response['message'] = 'Failed to add complaint';
            $response['data'] = null;
        }

        return response()->json($response);
    }
}
