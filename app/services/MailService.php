<?php

namespace App\services;

use Illuminate\Support\Facades\Http;

class MailService
    {
    public function sendEmail($to, $subject, $body)
    {
        $response = Http::withHeaders([
            'accept'=>'application/json',
            'content-type'=>'application/json',
        ])->post('https://mail.tajerexpo.net/api/send-mail',  [
            'email' => $to,
            'title' => $subject,
            'body' => $body,
        ]);
        if ($response->successful()) {
            return true;
        }
        return false;
    }
}