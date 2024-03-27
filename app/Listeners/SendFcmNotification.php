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
            'title' => $event->title,
            // 'body'=>$event->message??'',
            'body' => $event->category,
            'sound' => true,
            'category'=>$event->category
        ];

        // $notification = array_merge($notification, $event->extra);
        $fcmNotification = [
            'registration_ids' => $event->users,
            'notification'=>$notification,
            'data'=>$event->extra,
            'category'=>$event->category
        ];
//         dd($fcmNotification);
        $result=$this->pushFCM($fcmNotification);

    }

    public function pushFCM($data) {
        $headers = [
            'Authorization: key=AAAA8APYtMw:APA91bFDOsCVJ8Cc-nNwZv1MLML9dUqyjBd7zO_OQDJ7uR7W3F1tookNvttll57qIysBFmC0ngYmeUp--VQ4EvoVeV-G_JonasEVDlkcTmbPmqxmv02XAvcZSRIuDD2gjdxxMRAZQ3Vy',
            'Content-Type: application/json'
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
