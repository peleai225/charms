<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    public function sendToCustomer(int $customerId, string $title, string $body, string $url = '/'): void
    {
        $subscriptions = PushSubscription::where('customer_id', $customerId)->get();
        $this->sendToSubscriptions($subscriptions, $title, $body, $url);
    }

    public function sendToAll(string $title, string $body, string $url = '/'): void
    {
        $subscriptions = PushSubscription::all();
        $this->sendToSubscriptions($subscriptions, $title, $body, $url);
    }

    protected function sendToSubscriptions($subscriptions, string $title, string $body, string $url): void
    {
        if ($subscriptions->isEmpty()) return;

        $vapidPublic = config('vapid.public_key');
        $vapidPrivate = config('vapid.private_key');
        $vapidSubject = config('app.url');

        if (!$vapidPublic || !$vapidPrivate) {
            Log::warning('Push notifications: VAPID keys not configured');
            return;
        }

        try {
            $auth = [
                'VAPID' => [
                    'subject' => $vapidSubject,
                    'publicKey' => $vapidPublic,
                    'privateKey' => $vapidPrivate,
                ],
            ];

            $webPush = new \Minishlink\WebPush\WebPush($auth);

            $payload = json_encode([
                'title' => $title,
                'body' => $body,
                'url' => $url,
                'icon' => '/favicon.ico',
            ]);

            foreach ($subscriptions as $sub) {
                $subscription = \Minishlink\WebPush\Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->public_key,
                    'authToken' => $sub->auth_token,
                ]);

                $webPush->queueNotification($subscription, $payload);
            }

            $reports = $webPush->flush();
            foreach ($reports as $report) {
                if (!$report->isSuccess()) {
                    Log::warning('Push notification failed', [
                        'endpoint' => $report->getEndpoint(),
                        'reason' => $report->getReason(),
                    ]);
                    PushSubscription::where('endpoint', $report->getEndpoint())->delete();
                }
            }
        } catch (\Throwable $e) {
            Log::error('Push notification error: ' . $e->getMessage());
        }
    }
}
