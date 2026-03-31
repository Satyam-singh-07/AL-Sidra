<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use App\Models\User;
use App\Models\UserFcmToken;
use App\Models\Notification;
use App\Notifications\AppNotification;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $this->messaging = (new Factory)
            ->withServiceAccount(storage_path('app/public/firebase/firebase.json'))
            ->createMessaging();
    }

    /**
     * Send push notification to ONE user and store in database
     */
    public function sendToUser(
        User $user,
        string $title,
        string $body,
        array $data = []
    ): void {
        // Custom save to database (Integer ID)
        Notification::create([
            'type' => AppNotification::class,
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
            'data' => [
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ],
        ]);

        $tokens = UserFcmToken::where('user_id', $user->id)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return;
        }

        $this->sendMulticast($tokens, $title, $body, $data);
    }

    /**
     * Send push notification to MANY users (chunked) and store in database
     */
    public function sendToUsers(
        iterable $users,
        string $title,
        string $body,
        array $data = []
    ): void {
        foreach ($users as $user) {
            $this->sendToUser($user, $title, $body, $data);
        }
    }

    /**
     * Send push notification to ALL users (chunked) and store in database
     */
    public function sendToAll(
        string $title,
        string $body,
        array $data = []
    ): int {
        $users = User::all();
        foreach ($users as $user) {
            Notification::create([
                'type' => AppNotification::class,
                'notifiable_id' => $user->id,
                'notifiable_type' => get_class($user),
                'data' => [
                    'title' => $title,
                    'body' => $body,
                    'data' => $data,
                ],
            ]);
        }

        $tokens = UserFcmToken::pluck('token')->unique()->toArray();
        $totalSent = 0;

        if (empty($tokens)) {
            return 0;
        }

        // FCM multicast limit is 500 tokens per request
        $chunks = array_chunk($tokens, 500);

        foreach ($chunks as $chunk) {
            $this->sendMulticast($chunk, $title, $body, $data);
            $totalSent += count($chunk);
        }

        return $totalSent;
    }

    /**
     * Core FCM multicast sender
     */
    protected function sendMulticast(
        array $tokens,
        string $title,
        string $body,
        array $data = []
    ): void {
        $payload = array_merge($data, [
            'title' => $title,
            'body'  => $body,
        ]);

        $message = CloudMessage::new()
            ->withData(array_map('strval', $payload)); // FCM requires strings

        try {
            $report = $this->messaging->sendMulticast($message, $tokens);

            if ($report->hasFailures()) {
                foreach ($report->failures()->getItems() as $failure) {
                    $invalidToken = $failure->target()->value();

                    UserFcmToken::where('fcm_token', $invalidToken)->delete();

                    Log::warning('Invalid FCM token removed', [
                        'token' => $invalidToken,
                        'error' => $failure->error()->getMessage(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('FCM multicast failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendToToken(
        string $token,
        string $title,
        string $body,
        array $data = []
    ): void {
        $payload = array_merge($data, [
            'title' => $title,
            'body'  => $body,
        ]);

        $message = CloudMessage::withTarget('token', $token)
            ->withData(array_map('strval', $payload));

        try {
            $this->messaging->send($message);

            Log::info("Notification sent to token successfully", [
                'token' => $token,
            ]);
        } catch (\Throwable $e) {

            Log::error("FCM sendToToken failed", [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
