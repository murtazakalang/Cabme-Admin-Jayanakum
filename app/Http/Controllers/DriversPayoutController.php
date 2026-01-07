<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Currency;
use App\Models\Withdrawal;
use App\Models\Settings;
use App\Models\Transaction;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

use Session;

class DriversPayoutController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');
  }
  
  public function index(Request $request)
  {
      $currency = Currency::where('statut', 'yes')->first();

      $withdrawal = Withdrawal::leftJoin('conducteur', 'conducteur.id', '=', 'withdrawals.id_conducteur')
          ->select('conducteur.nom', 'conducteur.prenom', 'withdrawals.*')
          ->orderBy('withdrawals.id', 'desc');

      if ($request->has('search') && $request->search != '') {
          $search = $request->input('search');
          $filter = $request->input('selected_search');

          if ($filter == 'note') {
              $withdrawal->where('withdrawals.note', 'LIKE', "%$search%");
          } elseif ($filter == 'driver') {
              $withdrawal->where(function ($q) use ($search) {
                  $q->where('conducteur.nom', 'LIKE', "%$search%")
                    ->orWhere('conducteur.prenom', 'LIKE', "%$search%");
              });
          } elseif ($filter == 'status') {
              $withdrawal->where('withdrawals.statut', 'LIKE', "%$search%");
          }
          elseif ($filter == 'payout_request_id') {
              $withdrawal->where('withdrawals.request_id', 'LIKE', "%$search%");
          }
      }

      $totalLength = $withdrawal->count();
      $perPage = $request->input('per_page', 20);

      $withdrawal = $withdrawal->paginate($perPage)->appends($request->all());

      return view("drivers_payouts.index", compact('withdrawal', 'currency', 'totalLength', 'perPage'));
  }


  public function create()
  {

    $driver = Driver::whereNull('ownerId')->get();

    return view("drivers_payouts.create")->with('driver', $driver);
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), $rules = [
      'driverId' => 'required',
      'payout' => 'required',
      'note' => 'required',

    ], $messages = [
      'driverId.required' => trans('lang.the_driver_field_is_required'),
      'payout.required' => trans('lang.the_amount_field_is_required'),
      'note.required' => trans('lang.the_note_field_is_required'),
    ]);
    if ($validator->fails()) {
      return redirect()->back()
        ->withErrors($validator)->with(['message' => $messages])
        ->withInput();
    }
    $amount = $request->input('payout');
    $driverId = $request->input('driverId');
    $driver = Driver::find($driverId);

    if ($driver->amount < 0 || $driver->amount < $amount) {
      Session::flash('msg', 'Unsufficient Balance');
      return redirect()->back();
    } else {

      $driver->amount = intval($driver->amount) - intval($amount);
      $driver->save();

      $withdrawal = new Withdrawal;
      $withdrawal->id_conducteur = $driverId;
      $withdrawal->amount = $amount;
      $withdrawal->note = $request->input('note');
      $withdrawal->statut = 'success';
      $withdrawal->creer = date('Y-m-d H:i:s');
      $withdrawal->request_id = "#".random_int(100000, 999999);
      $withdrawal->save();

      $id = DB::getPdo()->lastInsertId();

      $driver = Driver::select('email', 'nom', 'prenom')->where('id', '=', $driverId)->first();
      $date = date('d F Y');

      if (!empty($driver->email)) {
        $emailsubject = '';
        $emailmessage = '';
        $emailtemplate = DB::table('email_template')->select('*')->where('type', 'payout_approve_disapprove')->first();
        if (!empty($emailtemplate)) {
          $emailsubject = $emailtemplate->subject;
          $emailmessage = $emailtemplate->message;
        }
        $currencyData = Currency::select('*')->where('statut', 'yes')->first();
        if ($currencyData->symbol_at_right == "true") {
          $amount = number_format($amount, $currencyData->decimal_digit) . $currencyData->symbole;
        } else {
          $amount = $currencyData->symbole . number_format($amount, $currencyData->decimal_digit);
        }
        $contact_us_email = Settings::select('contact_us_email')->value('contact_us_email');
        $contact_us_email = $contact_us_email ? $contact_us_email : 'none@none.com';


        $app_name = env('APP_NAME', 'Cabme');

        $to = $driver->email;
        $emailsubject = str_replace('{RequestId}', $id, $emailsubject);
        $emailmessage = str_replace("{AppName}", $app_name, $emailmessage);
        $emailmessage = str_replace("{UserName}", $driver->nom . " " . $driver->prenom, $emailmessage);
        $emailmessage = str_replace("{Amount}", $amount, $emailmessage);
        $emailmessage = str_replace("{Status}", 'Success', $emailmessage);
        $emailmessage = str_replace('{RequestId}', $id, $emailmessage);
        $emailmessage = str_replace('{Date}', $date, $emailmessage);

        // Always set content-type when sending HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: ' . $app_name . '<' . $contact_us_email . '>' . "\r\n";
        mail($to, $emailsubject, $emailmessage, $headers);
      }

      return redirect()->route('driversPayouts.index')->with('message', trans('lang.driver_payout_created_successfully'));
    }
  }

  public function delete(Request $request, $id = null)
  {
      $ids = explode(",", $request->query('ids'));
      Withdrawal::whereIn('id', $ids)->delete();

      return redirect()->back()->with('message', trans('lang.deleted_successfully'));
  }

  public function reject(Request $request, $id)
  {
      $request->validate([
          'admin_note' => 'required|string|max:1000',
      ]);

      $withdrawal = Withdrawal::findOrFail($id);
      $withdrawal->statut = 'reject';
      $withdrawal->note = $request->admin_note;
      $withdrawal->save();

      $emailTemplate = EmailTemplate::where('type', 'payout_approve_disapprove')->first();
      if ($emailTemplate) {
          $emailSubject = str_replace("{RequestId}", $withdrawal->request_id, $emailTemplate->subject);
          $emailMessage = $emailTemplate->message;

          $contactEmail = Settings::value('contact_us_email');
          $adminEmail   = $contactEmail ?: 'none@none.com';
          $appName      = env('APP_NAME', 'Cabme');
          $date         = now()->format('d F Y');
          $currencyData = Currency::where('statut', 'yes')->first();
          if ($currencyData->symbol_at_right == "true") {
              $formattedAmount = number_format($withdrawal->amount, $currencyData->decimal_digit) . $currencyData->symbole;
          } else {
              $formattedAmount = $currencyData->symbole . number_format($withdrawal->amount, $currencyData->decimal_digit);
          }

          $driver = Driver::findOrFail($withdrawal->id_conducteur);

          // Replace placeholders
          $emailMessage = str_replace(
              ['{AppName}', '{UserName}', '{Amount}',  '{Status}', '{RequestId}', '{Date}'],
              [$appName, $driver->prenom . " " . $driver->nom, $formattedAmount, 'Rejected', $withdrawal->request_id, $date],
              $emailMessage
          );

          $to = $driver->email;

          Mail::html($emailMessage, function ($message) use ($to, $adminEmail, $emailSubject, $emailTemplate) {
              $message->to($to)->subject($emailSubject);
              if ($emailTemplate->send_to_admin) {
                  $message->cc($adminEmail);
              }
          });
      }

      return redirect()->back()->with('message', trans('lang.driver_payout_rejected_successfully'));
  }
  public function getBankDetails($id)
  {
      $driver = Driver::findOrFail($id);

      return response()->json([
          'bank_name'        => $driver->bank_name,
          'branch_name'      => $driver->branch_name,
          'holder_name'      => $driver->holder_name,
          'account_number'   => $driver->account_no,
          'other_information'=> $driver->other_info,
          'ifsc_code'=> $driver->ifsc_code,
          'amount'=> $driver->amount,
      ]);
  }

  public function accept(Request $request)
  {
      $walletBalance = $request->input('walletBalance');   
      $requestedAmount = $request->input('requestedAmount');   
      $driverId = $request->input('driverId');   
      $withdrawalId = $request->input('withdrawalId');
      $withdraw = Withdrawal::findOrFail($withdrawalId);
      $withdraw->statut = 'success'; 
      $withdraw->save();

      $driver = Driver::findOrFail($driverId);
      if ($walletBalance != '' && $walletBalance != null) {
        $driver->amount =  floatval($walletBalance) -  floatval($requestedAmount);
      }
      $driver->save();

      if (!empty($requestedAmount)) {
          Transaction::create([
              'user_id' => $driverId,
              'user_type' => 'driver',
              'payment_method' => 'Wallet',
              'amount' => $requestedAmount,
              'is_credited' => '0',
              'booking_id' => NULL,
              'booking_type' => NULL,
              'note' => 'Payout amount debited',
              'transaction_id' => strtoupper(uniqid()),
          ]);
      }


      $emailTemplate = EmailTemplate::where('type', 'payout_approve_disapprove')->first();
      if ($emailTemplate) {
          $emailSubject = str_replace("{RequestId}", $withdraw->request_id, $emailTemplate->subject);
          $emailMessage = $emailTemplate->message;

          $contactEmail = Settings::value('contact_us_email');
          $adminEmail   = $contactEmail ?: 'none@none.com';
          $appName      = env('APP_NAME', 'Cabme');
          $date         = now()->format('d F Y');
          $currencyData = Currency::where('statut', 'yes')->first();
          if ($currencyData->symbol_at_right == "true") {
              $formattedAmount = number_format($withdraw->amount, $currencyData->decimal_digit) . $currencyData->symbole;
          } else {
              $formattedAmount = $currencyData->symbole . number_format($withdraw->amount, $currencyData->decimal_digit);
          }

          $driver = Driver::findOrFail($withdraw->id_conducteur);

          // Replace placeholders
          $emailMessage = str_replace(
              ['{AppName}', '{UserName}', '{Amount}',  '{Status}', '{RequestId}', '{Date}'],
              [$appName, $driver->prenom . " " . $driver->nom, $formattedAmount, 'Accepted', $withdraw->request_id, $date],
              $emailMessage
          );

          $to = $driver->email;

          Mail::html($emailMessage, function ($message) use ($to, $adminEmail, $emailSubject, $emailTemplate) {
              $message->to($to)->subject($emailSubject);
              if ($emailTemplate->send_to_admin) {
                  $message->cc($adminEmail);
              }
          });
      }

      return redirect()->back()->with('message', trans('lang.driver_payout_accepted_successfully'));
  }




}
