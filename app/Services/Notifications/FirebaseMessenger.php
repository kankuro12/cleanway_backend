<?php

namespace App\Services\Notifications;

use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around kreait/firebase-php FCM HTTP v1 messaging.
 * No-ops (and logs) when Firebase is disabled or misconfigured, so the
 * rest of the notification pipeline never depends on a working account.
 */
class FirebaseMessenger
{
    private ?\Kreait\Firebase\Messaging\Messaging $messaging = null;

    private bool $attempted = false;

    public function configured(): bool
    {
        return config('firebase.enabled')
            && config('firebase.project_id')
            && config('firebase.credentials')
            && is_file((string) config('firebase.credentials'));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function send(string $token, string $title, string $body, array $data = []): bool
    {
        if (! $this->configured()) {
            Log::debug('FCM push skipped: Firebase not configured.');

            return false;
        }

        try {
            $message = \Kreait\Firebase\Messaging\CloudMessage::fromArray([
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
                'webpush' => [
                    'fcm_options' => [
                        'link' => (string) config('firebase.web_action_url'),
                    ],
                ],
            ]);

            $this->messaging()->send($message);

            return true;
        } catch (\Throwable $e) {
            Log::warning('FCM push failed: '.$e->getMessage());

            return false;
        }
    }

    private function messaging(): \Kreait\Firebase\Messaging\Messaging
    {
        if ($this->messaging === null && ! $this->attempted) {
            $this->attempted = true;

            try {
                $this->messaging = \Kreait\Firebase\Factory::create()
                    ->withServiceAccount((string) config('firebase.credentials'))
                    ->withProjectId((string) config('firebase.project_id'))
                    ->createMessaging();
            } catch (\Throwable $e) {
                Log::warning('FCM init failed: '.$e->getMessage());
            }
        }

        return $this->messaging;
    }
}
