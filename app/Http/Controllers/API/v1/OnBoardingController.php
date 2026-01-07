<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\OnBoard;
use Illuminate\Http\Request;

class OnBoardingController extends Controller
{
    public function __construct()
    {
        $this->limit = 20;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {
        $output = [];
        $type = $request->get('type');
        $onboard = OnBoard::where('type', '=', $type)->get();
        if (count($onboard) > 0) {
            foreach ($onboard as $row) {
                $row->id = (string) $row->id;
                if ($row->image != '') {
                    if (file_exists(public_path('assets/images/onboarding' . '/' . $row->image))) {
                        $row->image = asset('assets/images/onboarding') . '/' . $row->image;
                    } else {
                        $row->image = asset('assets/images/placeholder_image.jpg');
                    }
                }
                $output[] = $row;
            }
            if (! empty($output)) {
                $response['success'] = 'success';
                $response['error'] = null;
                $response['message'] = 'onboarding screens fetch successfully';
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
