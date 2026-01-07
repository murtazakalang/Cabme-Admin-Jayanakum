<?php

namespace App\Http\Controllers;

use App\Models\Complaints;
use App\Models\Requests;
use App\Models\RentalOrder;
use App\Models\ParcelOrder;
use App\Models\UserApp;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComplaintsController extends Controller
{

    public function __construct()
    {

        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $selectedSearch = $request->input('selected_search');
        $status = $request->input('status');

        // Start query builder
        $query = Complaints::orderBy('created_at', 'DESC');

        // Apply search filters
        if (!empty($search) && $selectedSearch == 'title') {
            $query->where('title', 'LIKE', '%' . $search . '%');
        } elseif (!empty($search) && $selectedSearch == 'message') {
            $query->where('description', 'LIKE', '%' . $search . '%');
        } elseif (!empty($status) && $selectedSearch == 'status') {
            $query->where('status', 'LIKE', '%' . $status . '%');
        }

        // Get total before pagination
        $totalLength = $query->count();

        // Paginate results
        $perPage = $request->input('per_page', 20);
        $complaints = $query->paginate($perPage)->appends($request->all());

        foreach ($complaints as $complaint) {
            switch ($complaint->booking_type) {
                case 'ride':
                    $booking = Requests::find($complaint->booking_id);
                    break;
                case 'rental':
                    $booking = RentalOrder::find($complaint->booking_id);
                    break;
                case 'parcel':
                    $booking = ParcelOrder::find($complaint->booking_id);
                    break;
                default:
                    $booking = null;
            }
            if ($booking) {
                $complaint->user = UserApp::find($booking->id_user_app ?? null);
                $complaint->driver = Driver::find($booking->id_conducteur ?? null);
            }
        }

        return view("complaints.index", compact('complaints', 'totalLength', 'perPage'));
    }

    public function deleteComplaints($id)
    {

        if ($id != "") {

            $id = json_decode($id);

            if (is_array($id)) {

                for ($i = 0; $i < count($id); $i++) {
                    $complaint = Complaints::find($id[$i]);
                    $complaint->delete();
                }

            } else {
                $complaint = Complaints::find($id);
                $complaint->delete();
            }

        }

        return redirect()->back();
    }

    public function show($id)
    {

        $complaint = Complaints::find($id);
       
        return response()->json($complaint);
    }

    public function update(Request $request)
    {
        $id = $request->get('complaint_id');
        $status = $request->get('complaint_status');
        Complaints::where('id', $id)
                    ->update(['status' => $status]);
        return redirect('complaints')->with('message', 'trans("lang.complaint_status_update")');
    }
}