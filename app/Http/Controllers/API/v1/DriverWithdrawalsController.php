<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Settings;
use App\Models\EmailTemplate;
use App\Models\Withdrawal;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Validator;

class DriverWithdrawalsController extends Controller
{
  
    public function Withdrawals(Request $request)
    {
        $response = [];

        // Validation
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|integer|exists:conducteur,id',
            'amount'    => 'required',
            'note'      => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 'Failed',
                'code'    => 404,
                'message' => $validator->errors()->first(),
                'data'    => null,
            ]);
        }

        $driver_id = $request->get('driver_id');
        $amount    = $request->get('amount');
        $note      = $request->get('note');

        $setting           = Settings::first();
        $minWithdrawAmount = $setting->minimum_withdrawal_amount;

        $driver        = Driver::find($driver_id);
        $driverBalance = $driver->amount;

        if ($driverBalance >= $minWithdrawAmount) {

            if ($driverBalance >= $amount) {

                // Create withdrawal request
                $withdrawal = Withdrawal::create([
                    'id_conducteur' => $driver_id,
                    'amount'        => $amount,
                    'note'          => $note,
                    'statut'        => 'pending',
                    'creer'         => now(),
                    'modifier'      => now(),
                    'request_id'    => "#".random_int(100000, 999999),
                ]);

                $row = [
                    'withdrawals_status' => $withdrawal->statut,
                    'withdrawals_amount' => $withdrawal->amount,
                ];

                $response = [
                    'success' => 'success',
                    'error'   => null,
                    'message' => 'Amount withdrawal request created successfully',
                    'data'    => $row,
                ];

                // Currency formatting
                $currencyData = Currency::where('statut', 'yes')->first();
                if ($currencyData->symbol_at_right == "true") {
                    $formattedAmount = number_format($withdrawal->amount, $currencyData->decimal_digit) . $currencyData->symbole;
                } else {
                    $formattedAmount = $currencyData->symbole . number_format($withdrawal->amount, $currencyData->decimal_digit);
                }

                // Email template
                $emailTemplate = EmailTemplate::where('type', 'payout_request')->first();
                if ($emailTemplate) {
                    $emailSubject = str_replace("{PayoutRequestId}", $withdrawal->request_id, $emailTemplate->subject);
                    $emailMessage = $emailTemplate->message;

                    $contactEmail = Settings::value('contact_us_email');
                    $adminEmail   = $contactEmail ?: 'none@none.com';
                    $appName      = env('APP_NAME', 'Cabme');
                    $date         = now()->format('d F Y');

                    // Replace placeholders
                    $emailMessage = str_replace(
                        ['{AppName}', '{UserName}', '{Amount}', '{UserContactInfo}', '{UserId}', '{PayoutRequestId}', '{Date}'],
                        [$appName, $driver->prenom . " " . $driver->nom, $formattedAmount, $driver->phone, $driver->id, $withdrawal->request_id, $date],
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
            } else {
                $response = [
                    'success' => 'Failed',
                    'error'   => 'Insufficient balance',
                ];
            }
        } else {
            $response = [
                'success' => 'Failed',
                'error'   => 'Insufficient minimum wallet balance to withdraw',
            ];
        }

        return response()->json($response);
    }

}