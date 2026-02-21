<?php

namespace App\Services;


use App\Enums\Role;
use App\Enums\SwitchBox;
use App\Models\FrontendOrder;
use App\Models\NotificationAlert;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

class OrderGotPushNotificationBuilder
{
    public int $orderId;
    public object $order;

    public function __construct($orderId,)
    {
        $this->orderId = $orderId;
        $this->order   = FrontendOrder::find($orderId);
    }

    public function send(): void
    {
        if (!blank($this->order)) {
            \Log::info('OrderGotPushNotification: Starting notification process', [
                'order_id' => $this->orderId,
                'branch_id' => $this->order->branch_id
            ]);
            
            $fcmWebDeviceTokenAllAdmins         = User::role(Role::ADMIN)->where(['branch_id' => 0])->whereNotNull('web_token')->get();
            $fcmWebDeviceTokenBranchAdmins      = User::role(Role::ADMIN)->where(['branch_id' => $this->order->branch_id])->whereNotNull('web_token')->get();
            $fcmWebDeviceTokenBranchManagers    = User::role(Role::BRANCH_MANAGER)->where(['branch_id' => $this->order->branch_id])->whereNotNull('web_token')->get();
            $fcmMobileDeviceTokenAllAdmins      = User::role(Role::ADMIN)->where(['branch_id' => 0])->whereNotNull('device_token')->get();
            $fcmMobileDeviceTokenBranchAdmins   = User::role(Role::ADMIN)->where(['branch_id' => $this->order->branch_id])->whereNotNull('device_token')->get();
            $fcmMobileDeviceTokenBranchManagers = User::role(Role::BRANCH_MANAGER)->where(['branch_id' => $this->order->branch_id])->whereNotNull('device_token')->get();

            $i             = 0;
            $fcmTokenArray = [];
            $addWebTokens = function ($users) use (&$fcmTokenArray, &$i) {
                foreach ($users as $u) {
                    $tokens = array_filter(array_merge(
                        !empty($u->web_token) ? [$u->web_token] : [],
                        is_array($u->web_tokens) ? $u->web_tokens : []
                    ));
                    foreach (array_unique($tokens) as $t) {
                        $fcmTokenArray[$i] = $t;
                        $i++;
                    }
                }
            };
            if (!blank($fcmWebDeviceTokenAllAdmins)) {
                $addWebTokens($fcmWebDeviceTokenAllAdmins);
            }
            if (!blank($fcmWebDeviceTokenBranchAdmins)) {
                $addWebTokens($fcmWebDeviceTokenBranchAdmins);
            }
            if (!blank($fcmWebDeviceTokenBranchManagers)) {
                $addWebTokens($fcmWebDeviceTokenBranchManagers);
            }

            if (!blank($fcmMobileDeviceTokenAllAdmins)) {
                foreach ($fcmMobileDeviceTokenAllAdmins as $fcmMobileDeviceTokenAllAdmin) {
                    $fcmTokenArray[$i] = $fcmMobileDeviceTokenAllAdmin->device_token;
                    $i++;
                }
            }

            if (!blank($fcmMobileDeviceTokenBranchAdmins)) {
                foreach ($fcmMobileDeviceTokenBranchAdmins as $fcmMobileDeviceTokenBranchAdmin) {
                    $fcmTokenArray[$i] = $fcmMobileDeviceTokenBranchAdmin->device_token;
                    $i++;
                }
            }

            if (!blank($fcmMobileDeviceTokenBranchManagers)) {
                foreach ($fcmMobileDeviceTokenBranchManagers as $fcmMobileDeviceTokenBranchManager) {
                    $fcmTokenArray[$i] = $fcmMobileDeviceTokenBranchManager->device_token;
                    $i++;
                }
            }

            \Log::info('OrderGotPushNotification: Collected tokens', [
                'order_id' => $this->orderId,
                'token_count' => count($fcmTokenArray),
                'tokens_preview' => array_map(function($t) { return substr($t, 0, 20) . '...'; }, array_slice($fcmTokenArray, 0, 3))
            ]);

            if (count($fcmTokenArray) > 0) {
                try {
                    $notificationAlert = NotificationAlert::where(['language' => 'admin_and_branch_manager_new_order_message'])->first();
                    $message = ($notificationAlert && $notificationAlert->push_notification == SwitchBox::ON && !blank($notificationAlert->push_notification_message))
                        ? $notificationAlert->push_notification_message
                        : 'New order #' . ($this->order->order_serial_no ?? $this->orderId);
                    $pushNotification = (object)[
                        'title'       => 'New Order Notification',
                        'description' => $message,
                        'order_id'   => (string) $this->orderId,
                        'url'        => '/admin/table-orders/show/' . $this->orderId,
                    ];
                    
                    \Log::info('OrderGotPushNotification: Sending notification', [
                        'order_id' => $this->orderId,
                        'title' => $pushNotification->title,
                        'message' => $pushNotification->description
                    ]);
                    
                    $firebase = new FirebaseService();
                    $firebase->sendNotification($pushNotification, $fcmTokenArray, "new-order-found");
                    
                    \Log::info('OrderGotPushNotification: Notification sent successfully', [
                        'order_id' => $this->orderId
                    ]);
                } catch (Exception $e) {
                    Log::error('OrderGotPushNotification failed', [
                        'order_id' => $this->orderId,
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            } else {
                \Log::warning('OrderGotPushNotification: No tokens found', [
                    'order_id' => $this->orderId,
                    'branch_id' => $this->order->branch_id
                ]);
            }

        }
    }
}
