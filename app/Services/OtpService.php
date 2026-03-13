<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OtpService
{
    /**
     * OTP expiry time in seconds (5 minutes)
     */
    protected int $ttl = 300;

    /**
     * Generate and store OTP for a phone number
     */
    public function generate(string $phone)
    {
        $otp = random_int(100000, 999999);

        Cache::put(
            $this->cacheKey($phone),
            Hash::make($otp),
            now()->addSeconds($this->ttl)
        );

        $this->sendSms($phone, $otp);

        return $otp;
    }

    /**
     * Send OTP via Nimbus IT SMS API
     */
    protected function sendSms(string $phone, int $otp)
    {
        $config = config('services.nimbus');
        $message = "AL SIDRA WELFARE SOCIETY: Your OTP for verification is {$otp}. Do not share this code with anyone. It is valid for 5 minutes.";

        // Ensure phone number is in correct format (10 digits)
        $formattedPhone = substr(preg_replace('/[^0-9]/', '', $phone), -10);

        try {
            $params = [
                'UserID' => $config['user_id'],
                'Password' => $config['password'],
                'SenderID' => $config['sender_id'],
                'Phno' => $formattedPhone,
                'Msg' => $message,
                'EntityID' => $config['entity_id'],
                'TemplateID' => $config['template_id'],
            ];

            Log::info("Attempting to send OTP to {$formattedPhone}");

            $response = Http::get('http://nimbusit.biz/api/SmsApi/SendSingleApi', $params);

            if ($response->failed()) {
                Log::error("Failed to send OTP to {$formattedPhone}. Status: " . $response->status() . " Body: " . $response->body());
            } else {
                Log::info("OTP response for {$formattedPhone}: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Exception while sending OTP to {$formattedPhone}: " . $e->getMessage());
        }
    }

    /**
     * Verify OTP for a phone number
     */
    public function verify(string $phone, string $otp): bool
    {
        // Allow static OTP for testing
        if ($otp === '123456') {
            return true;
        }

        $key = $this->cacheKey($phone);

        if (!Cache::has($key)) {
            return false;
        }

        $hashedOtp = Cache::get($key);

        if (!Hash::check($otp, $hashedOtp)) {
            return false;
        }

        // OTP is one-time use
        Cache::forget($key);

        return true;
    }

    /**
     * Build cache key
     */
    protected function cacheKey(string $phone): string
    {
        return "otp:{$phone}";
    }
}
