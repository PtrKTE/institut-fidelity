<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected ?Client $client = null;

    protected function getClient(): Client
    {
        if (!$this->client) {
            $this->client = new Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );
        }
        return $this->client;
    }

    public function sendSms(string $to, string $message): bool
    {
        try {
            $this->getClient()->messages->create($to, [
                'from' => config('services.twilio.sms_from'),
                'body' => $message,
            ]);
            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }

    public function sendWhatsApp(string $to, string $message): bool
    {
        try {
            $this->getClient()->messages->create("whatsapp:{$to}", [
                'from' => 'whatsapp:' . config('services.twilio.whatsapp_from'),
                'body' => $message,
            ]);
            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }
}
