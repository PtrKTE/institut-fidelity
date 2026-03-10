<?php

namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public function generate(string $email, string $context = 'activation'): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'email' => $email,
            'code' => $code,
            'context' => $context,
            'created_at' => now(),
            'used' => false,
        ]);

        return $code;
    }

    public function verify(string $email, string $code, string $context = 'activation'): bool
    {
        $otp = OtpCode::where('email', $email)
            ->where('code', $code)
            ->where('context', $context)
            ->where('used', false)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->latest('created_at')
            ->first();

        if (!$otp) {
            return false;
        }

        $otp->update(['used' => true]);
        return true;
    }

    public function sendByEmail(string $email, string $code, string $customerName = ''): bool
    {
        try {
            Mail::raw(
                "Bonjour {$customerName},\n\nVotre code de vérification est : {$code}\n\nCe code expire dans 10 minutes.\n\nPrestige by ProNails",
                function ($message) use ($email) {
                    $message->to($email)
                        ->subject('Votre code de vérification - ProNails');
                }
            );
            return true;
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }
}
