<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserApp;
use App\Models\Driver;
use App\Models\AdminNotification;
use App\Http\Controllers\GcmController;
use Validator;

class AdminNotificationController extends Controller
{
    public function __construct()
    {

        $this->middleware('auth');

    }

    public function index(Request $request)
    {

        $query = AdminNotification::select('*');

        $notifications = $query->orderBy('created_at', 'desc')->paginate(10);       

        if ($request->has('search') && $request->search != '' && $request->selected_search == 'title') {
            $search = $request->input('search');
            $notifications = AdminNotification::where('admin_notification.title', 'LIKE', '%'.$search.'%')
            ->orderBy('admin_notification.created_at', 'desc');
                
        } else if ($request->has('search') && $request->search != '' && $request->selected_search == 'message') {
            $search = $request->input('search');
            $notifications = AdminNotification::where('admin_notification.message', 'LIKE', '%' . $search . '%')
                ->orderBy('admin_notification.created_at','desc');
         } else {
            
            $notifications = $query->orderBy('created_at', 'desc');
        }

        $totalLength = count($notifications->get());
        $perPage = $request->input('per_page', 20);
        $notifications =  $notifications->paginate($perPage)->appends($request->all());
        return view("admin_notifications.index",compact('notifications','totalLength','perPage'));
    }

    public function create()
    {
        return view("admin_notifications.send");

    }
    
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'message' => 'required',
            'send_to' => 'required',
        ],
        [
            'send_to.required' => trans('lang.please_choose_customer_or_driver_or_both_to_send_notification'),
        ]);

        if ($validator->fails()) {
            return redirect('notification/create')->withErrors($validator)->withInput();
        }

        $title = $request->input('title');
        $message = $request->input('message');
        $send_to = $request->input('send_to');

        //Send notification to registered users
        if (in_array('customer', $send_to)) {
            GcmController::sendNotification('', array("body" => $message, "title" => $title), 'cabme_customer');
        }

        //Send notification to driver users
        if (in_array('driver', $send_to)) {
            GcmController::sendNotification('', array("body" => $message, "title" => $title), 'cabme_driver');
        }

        AdminNotification::insert(array('title' => $title, 'message' => $message, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')));

        return redirect("notification")->with('message', trans('lang.notification_successfully_sent'));

    }

    public function delete($id)
    {
        if ($id != "") {

            $id = json_decode($id);

            if (is_array($id)) {

                for ($i = 0; $i < count($id); $i++) {
                    $user = AdminNotification::find($id[$i]);
                    $user->delete();
                }
                return redirect('notification')->with('message', trans('lang.notification_successfully_deleted'));

            } else {
                $user = AdminNotification::find($id);
                $user->delete();
                return redirect('notification')->with('message', trans('lang.notification_successfully_deleted'));
            }

        }
    }

}