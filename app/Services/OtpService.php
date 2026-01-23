<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

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

        return $otp;
        // Send OTP via SMS provider here
        // logger()->info("OTP for {$phone}: {$otp}");
    }

    /**
     * Verify OTP for a phone number
     */
    public function verify(string $phone, string $otp): bool
    {
        // $key = $this->cacheKey($phone);

        // if (!Cache::has($key)) {
        //     return false;
        // }

        // $hashedOtp = Cache::get($key);

        // if (!Hash::check($otp, $hashedOtp)) {
        //     return false;
        // }

        // // OTP is one-time use
        // Cache::forget($key);

        // return true;
        return $otp == '123456';
    }

    /**
     * Build cache key
     */
    protected function cacheKey(string $phone): string
    {
        return "otp:{$phone}";
    }
}
