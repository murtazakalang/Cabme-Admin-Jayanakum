<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\UserApp;
use App\Models\Driver;
use App\Models\Transaction;
use App\Models\Currency;
use App\Models\EmailTemplate;
use App\Models\Settings;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use DB;
use Validator;

class AddAmountController extends Controller
{

  public function register(Request $request)
  {

      $response = array();
    
      $validator = Validator::make($request->all(), [
          'user_id' => 'required|integer',
          'user_type' => 'required',
          'amount' => 'required|integer',
          'payment_method' => 'required|exists:payment_method,id',
          'is_credited' => 'required|integer',
          'note' => 'required',
          'transaction_id' => 'required',
      ]);

      if($validator->fails()){
          $response['success'] = 'Failed';
          $response['code'] = 404;
          $response['message'] = $validator->errors()->first();
          $response['data'] = null;
          return response()->json($response);
      }

      $id_payment = $request->get('payment_method');
      $paymentData = PaymentMethod::find($id_payment);
    
      $transaction = Transaction::create([
          'user_id' => $request->get('user_id'),
          'user_type' => $request->get('user_type'),
          'payment_method' => $paymentData->libelle,
          'amount' => $request->get('amount'),
          'is_credited' => $request->get('is_credited'),
          'booking_id' => $request->get('booking_id'),
          'booking_type' => $request->get('booking_type'),
          'note' => $request->get('note'),
          'transaction_id' => $request->get('transaction_id'),
      ]);

      if($request->get('user_type') == "customer"){
          $user = UserApp::find($request->get('user_id'));
      }else{
          $user = Driver::find($request->get('user_id'));
      }
      $current_amount = $user->amount;
      if($request->get('is_credited')){
          $new_amount =  $current_amount + $request->get('amount');
      }else{
          $new_amount =  $current_amount - $request->get('amount');
      }
      $user->amount = ($new_amount <= 0) ? 0 : $new_amount;
      $user->save();

      $email = $user->email;

      if (!empty($email)) {

        $emailsubject = '';
        $emailmessage = '';
        $emailtemplate = EmailTemplate::select('*')->where('type', 'wallet_topup')->first();
        if (!empty($emailtemplate)) {
            $emailsubject = $emailtemplate->subject;
            $emailmessage = $emailtemplate->message;
        }
          
          $currencyData = Currency::select('*')->where('statut', 'yes')->first();
      
        if ($currencyData->symbol_at_right == 'true') {
            $amount_init = number_format($request->get('amount'), $currencyData->decimal_digit) . $currencyData->symbole;
            $newBalance = number_format($new_amount, $currencyData->decimal_digit) . $currencyData->symbole;
        } else {
            $amount_init = $currencyData->symbole . number_format($request->get('amount'), $currencyData->decimal_digit);
            $newBalance = $currencyData->symbole . number_format($new_amount, $currencyData->decimal_digit);
        }

          
          $contact_us_email = Settings::select('contact_us_email')->value('contact_us_email');
          $admin_email = $contact_us_email ? $contact_us_email : 'none@none.com';
          $app_name = env('APP_NAME', 'Cabme');
          $to = $email;

          $date = date('d F Y', strtotime(date('Y-m-d H:i:s')));
          $emailmessage = str_replace('{AppName}', $app_name, $emailmessage);
          $emailmessage = str_replace('{UserName}', $user->nom . ' ' . $user->prenom, $emailmessage);
          $emailmessage = str_replace('{Amount}', $amount_init, $emailmessage);
          $emailmessage = str_replace('{PaymentMethod}', $paymentData->libelle, $emailmessage);
          $emailmessage = str_replace('{TransactionId}', $request->get('transaction_id'), $emailmessage);
          $emailmessage = str_replace('{Balance}', $newBalance, $emailmessage);
          $emailmessage = str_replace('{Date}', $date, $emailmessage);
          
          try {
            Mail::html($emailmessage, function ($message) use ($to, $admin_email, $emailsubject, $emailtemplate) {
                  $message->to($to)->subject($emailsubject);
                  if ($emailtemplate->send_to_admin) {
                      $message->cc($admin_email);
                  }
            });
          } catch (Exception $e) {
            Log::error('Wallet Transaction API: Mail Sending Failed: ' . $e->getMessage());
          }
    }
       
    $response['success'] = 'success';
    $response['code'] = 200;
    $response['message'] = 'Transactionn successfully created';
    $response['data'] = $transaction->toArray();

    return response()->json($response);
  }
}
