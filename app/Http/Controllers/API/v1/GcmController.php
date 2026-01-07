<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Google\Client as Google_Client;

class GcmController extends Controller
{
    public static function sendNotification($token, $messages)
    {
        if (Storage::disk('local')->has('firebase/credentials.json')) {

            $client = new Google_Client();
            $client->setAuthConfig(storage_path('app/firebase/credentials.json'));
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->refreshTokenWithAssertion();
            $client_token = $client->getAccessToken();
            $access_token = $client_token['access_token'];
            
            if (!empty($access_token) && !empty($token)) {

                $projectId = env('FIREBASE_PROJECT_ID');
                $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';
                $data = [
                    'message' => [
                        'notification' => [
                            'title' => $messages['title'],
                            'body' => $messages['body'],
                        ],
                        'data' => [
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ],
                        'token' => $token,
                    ],
                ];
                $headers = array(
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $access_token
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                $result = curl_exec($ch);
                if ($result === FALSE) {
                    die('FCM Send Error: ' . curl_error($ch));
                }
                curl_close($ch);
                $result = json_decode($result);

                Log::info('Notification Log', ['request' => $data, 'response'  => $result]);

                $response = array();
                $response['success'] = true;
                $response['message'] = 'Notification successfully sent.';
                $response['result'] = $result;

            } else {
                
                $response = array();
                $response['success'] = false;
                $response['message'] = 'Missing acccess token or fcm token to send notification.';
            }

        } else {

            $response = array();
            $response['success'] = false;
            $response['message'] = 'Firebase credentials file not found.';
        }

        return response()->json($response);
    }
}
