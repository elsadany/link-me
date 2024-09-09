<?php

namespace App\Listeners;

use App\Events\SendFcmNotificationEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendFcmNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\SendFcmNotificationEvent  $event
     * @return void
     */
    public function handle(SendFcmNotificationEvent $event)
    {
        $notification = [
            'hello' => $event->title,
            // 'body'=>$event->message??'',
            'body' => $event->message,
            'sound' => true,
            'category'=>$event->category
        ];

        $notification = array_merge($notification, $event->extra);
        $notification=[
            'title' => $event->title,
            'body' => $event->message,
//            'data' => $event->extra
        ];
        $fcmNotification = [
            'message'=>[
            'token' => $event->users,
            'notification'=>$notification,
            'data'=>$notification,
            'category'=>$event->category
        ]];
        $result=$this->pushFCM($fcmNotification);
dd($result);
    }

    public function pushFCM($data) {
        $headers = [
            'Authorization: Bearer ' . $this->getGoogleAccessToken(),
            'Content-Type: application/json'
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/link-me-7a76b/messages:send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    private function getGoogleAccessToken(){

        $credentialsFilePath = public_path('file.json'); //replace this with your actual path and file name
        $client = new \Google_Client();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();
        return $token['access_token'];
    }
}
