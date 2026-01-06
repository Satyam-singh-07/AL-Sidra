<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function userSignup(Request $request, OtpService $otpService)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:15',
        ]);

        // Just send OTP, do NOT create user yet
        $otp = $otpService->generate($request->phone);

        return response()->json([
            'message' => 'OTP sent successfully',
            'otp'     => $otp
        ]);
    }

    /**
     * Step 2: Verify OTP + create user + issue token
     */
    public function verifyOtp(Request $request, OtpService $otpService)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'otp'   => 'required|string',
        ]);

        if (!$otpService->verify($request->phone, $request->otp)) {
            return response()->json([
                'message' => 'Invalid or expired OTP'
            ], 422);
        }

        // Create or fetch user
        $user = User::firstOrCreate(
            ['phone' => $request->phone],
            ['name' => $request->name]
        );

        // Update name if user existed without name
        if ($user->wasRecentlyCreated === false && $user->name !== $request->name) {
            $user->update(['name' => $request->name]);
        }

        // Create Sanctum token
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => 'Signup successful',
            'token'   => $token,
            'user'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'phone' => $user->phone,
            ]
        ]);
    }
}
