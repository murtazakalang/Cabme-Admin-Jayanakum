<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Google\Client as Google_Client;

class GcmController extends Controller
{
	public static function sendNotification($token, $messages, $topic='')
    {

        if(Storage::disk('local')->has('firebase/credentials.json')){

            $client= new Google_Client();
            $client->setAuthConfig(storage_path('app/firebase/credentials.json'));
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->refreshTokenWithAssertion();
            $client_token = $client->getAccessToken();
            $access_token = $client_token['access_token'];
            
            if(!empty($access_token)){

                $projectId = env('FIREBASE_PROJECT_ID');
                $url = 'https://fcm.googleapis.com/v1/projects/'.$projectId.'/messages:send';

                $data = [
                    'message' => [
                        'notification' => [
                            'title' => $messages['title'],
                            'body' => $messages['body'],
                        ]
                    ],
                ];

                if(!empty($topic) && empty($token)){
                    $data['message']['topic'] = $topic; 
                }else{
                    $data['message']['token'] = $token; 
                }

                $headers = array(
                    'Content-Type: application/json',
                    'Authorization: Bearer '.$access_token
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
                if ($result === false) {
                    $error = curl_error($ch);
                    Log::error('FCM Send Error', [
                        'url' => $url,
                        'headers' => $headers,
                        'data' => $data,
                        'error' => $error,
                    ]);
                    die('FCM Send Error: ' . curl_error($ch));
                } else {
                    $resultDecoded = json_decode($result, true);
                    Log::info('FCM Response', [
                        'url' => $url,
                        'headers' => $headers,
                        'data' => $data,
                        'response' => $resultDecoded,
                    ]);
                }

                curl_close($ch);
                $result=json_decode($result);

                $response = array();
                $response['success'] = true;
                $response['message'] = trans('lang.notification_successfully_sent');
                $response['result'] = $result;

            }else{
                $response = array();
                $response['success'] = false;
                $response['message'] = trans('lang.missing_access_token_to_send_notification');
            }

        }else{
            $response = array();
            $response['success'] = false;
            $response['message'] = trans('lang.firebase_credentials_file_not_found');
        }
       
        return response()->json($response);
    }
}