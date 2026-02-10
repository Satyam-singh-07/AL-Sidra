<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserFcmToken;
use Illuminate\Http\Request;

class FcmTokensController extends Controller
{
    public function saveFcmToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'device_type' => 'nullable|string'
        ]);

        $user = auth()->user();

        $token = UserFcmToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'device_type' => $request->device_type
            ],
            [
                'token' => $request->token
            ]
        );

        return response()->json([
            'message' => 'FCM token saved successfully',
            'data' => $token
        ]);
    }
}
