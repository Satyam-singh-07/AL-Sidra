<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use App\Models\User;
use App\Models\UserFcmToken;
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
     * Send push notification to ONE user
     */
    public function sendToUser(
        User $user,
        string $title,
        string $body,
        array $data = []
    ): void {
        $tokens = UserFcmToken::where('user_id', $user->id)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return;
        }

        $this->sendMulticast($tokens, $title, $body, $data);
    }

    /**
     * Send push notification to MANY users (chunked)
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
     * Core FCM multicast sender
     */
    protected function sendMulticast(
        array $tokens,
        string $title,
        string $body,
        array $data = []
    ): void {
        $message = CloudMessage::new()
            ->withNotification(
                FirebaseNotification::create($title, $body)
            ); 

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
}
