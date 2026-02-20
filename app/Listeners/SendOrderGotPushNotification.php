<?php

namespace App\Listeners;


use App\Events\SendOrderGotPush;
use App\Services\OrderGotPushNotificationBuilder;
use Illuminate\Support\Facades\Log;

class SendOrderGotPushNotification
{
    public function handle(SendOrderGotPush $event)
    {
        try {
            $orderPushNotificationBuilderService = new OrderGotPushNotificationBuilder($event->info['order_id']);
            $orderPushNotificationBuilderService->send();
        } catch (\Throwable $e) {
            Log::error('SendOrderGotPushNotification failed', [
                'order_id' => $event->info['order_id'] ?? null,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
