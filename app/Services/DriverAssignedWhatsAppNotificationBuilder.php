<?php

namespace App\Services;

use App\Enums\OrderType;
use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\Log;

class DriverAssignedWhatsAppNotificationBuilder
{
    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function send(): void
    {
        try {
            // Refresh order from database
            $order = $this->order->fresh();
            
            // Load driver without global scopes to avoid BranchScope filtering
            if ($order->driver_id) {
                $driver = \App\Models\Driver::withoutGlobalScopes()->find($order->driver_id);
                if ($driver) {
                    $order->setRelation('driver', $driver);
                }
            }
            
            // Check if order has a driver with WhatsApp number
            if (blank($order->driver) || blank($order->driver->whatsapp)) {
                return;
            }

            // Parse driver's WhatsApp number (normalized as +994XXXXXXXXX)
            $whatsapp = $order->driver->whatsapp;
            $parsed = $this->parseWhatsAppNumber($whatsapp);
            
            if (blank($parsed['code']) || blank($parsed['phone'])) {
                Log::warning("DriverAssignedWhatsAppNotificationBuilder: Invalid WhatsApp format for driver ID {$order->driver->id}: {$whatsapp}");
                return;
            }

            // Build message
            $message = $this->buildMessage($order);

            // Send SMS/WhatsApp (automatic sending via gateway)
            $this->sms($parsed['code'], $parsed['phone'], $message);
        } catch (Exception $e) {
            Log::info("DriverAssignedWhatsAppNotificationBuilder error: " . $e->getMessage());
        }
    }

    public function getWhatsAppLink(): ?string
    {
        try {
            // Refresh order from database
            $order = $this->order->fresh();
            
            // Load driver without global scopes to avoid BranchScope filtering
            if ($order->driver_id) {
                $driver = \App\Models\Driver::withoutGlobalScopes()->find($order->driver_id);
                if (!$driver) {
                    Log::warning("DriverAssignedWhatsAppNotificationBuilder getWhatsAppLink: Driver ID {$order->driver_id} not found for order {$order->id}");
                    return null;
                }
                $order->setRelation('driver', $driver);
            }
            
            // Check if order has a driver
            if (blank($order->driver)) {
                Log::warning("DriverAssignedWhatsAppNotificationBuilder getWhatsAppLink: Order {$order->id} has no driver. driver_id = {$order->driver_id}");
                return null;
            }
            
            // Check if driver has WhatsApp number
            $driverWhatsapp = $order->driver->whatsapp;
            
            // Debug logging
            Log::info("DriverAssignedWhatsAppNotificationBuilder getWhatsAppLink: Driver ID {$order->driver->id}, WhatsApp value: " . var_export($driverWhatsapp, true) . ", Type: " . gettype($driverWhatsapp) . ", Is blank: " . (blank($driverWhatsapp) ? 'yes' : 'no'));
            
            if (blank($driverWhatsapp)) {
                Log::warning("DriverAssignedWhatsAppNotificationBuilder getWhatsAppLink: Driver ID {$order->driver->id} (name: {$order->driver->name}) has no WhatsApp number or it is empty. WhatsApp value: " . var_export($driverWhatsapp, true));
                return null;
            }

            // Parse driver's WhatsApp number (normalized as +994XXXXXXXXX)
            $whatsapp = $order->driver->whatsapp;
            
            // Convert to string if needed
            if (!is_string($whatsapp)) {
                $whatsapp = (string) $whatsapp;
            }
            
            $parsed = $this->parseWhatsAppNumber($whatsapp);
            
            if (blank($parsed['code']) || blank($parsed['phone'])) {
                Log::warning("DriverAssignedWhatsAppNotificationBuilder getWhatsAppLink: Failed to parse WhatsApp number for driver ID {$order->driver->id}. Raw value: " . var_export($order->driver->whatsapp, true) . ", Parsed: " . json_encode($parsed));
                return null;
            }

            // Build message
            $message = $this->buildMessage($order);

            // Format phone number for WhatsApp link (country code + phone without +)
            $phoneNumber = $parsed['code'] . $parsed['phone'];

            // Create WhatsApp Web/App link with pre-filled message
            return "https://wa.me/{$phoneNumber}?text=" . urlencode($message);
        } catch (Exception $e) {
            Log::info("DriverAssignedWhatsAppNotificationBuilder getWhatsAppLink error: " . $e->getMessage());
            return null;
        }
    }

    private function parseWhatsAppNumber(string $whatsapp): array
    {
        // Normalized format: +994XXXXXXXXX
        // Handle null or non-string values
        if ($whatsapp === null || (!is_string($whatsapp) && !is_numeric($whatsapp))) {
            Log::warning("DriverAssignedWhatsAppNotificationBuilder parseWhatsAppNumber: Invalid input type: " . gettype($whatsapp) . ", value: " . var_export($whatsapp, true));
            return ['code' => '', 'phone' => ''];
        }
        
        // Convert to string if numeric
        if (is_numeric($whatsapp)) {
            $whatsapp = (string) $whatsapp;
        }
        
        $normalized = trim($whatsapp);
        if ($normalized === '') {
            return ['code' => '', 'phone' => ''];
        }

        // Remove leading +
        $digits = str_replace('+', '', $normalized);
        
        // Extract country code (Azerbaijan: 994)
        if (str_starts_with($digits, '994') && strlen($digits) > 3) {
            return [
                'code' => '994',
                'phone' => substr($digits, 3)
            ];
        }

        // Fallback: try to extract first 3 digits as country code
        if (strlen($digits) > 3) {
            return [
                'code' => substr($digits, 0, 3),
                'phone' => substr($digits, 3)
            ];
        }

        return ['code' => '', 'phone' => ''];
    }

    private function buildMessage(Order $order): string
    {
        // Determine order type
        $orderType = 'Takeaway';
        if (!blank($order->whatsapp_number)) {
            $orderType = 'Online';
        } elseif ((int) $order->order_type === OrderType::DELIVERY) {
            $orderType = 'Delivery';
        }

        // Build message parts
        $parts = [
            "You have been assigned a {$orderType} order.",
            "Order Number: {$order->order_serial_no}"
        ];

        // Add token number if available
        if (!blank($order->token)) {
            $parts[] = "Token Number: {$order->token}";
        }

        // Add client WhatsApp if available
        if (!blank($order->whatsapp_number)) {
            $parts[] = "Client WhatsApp: {$order->whatsapp_number}";
        }

        // Add location if available
        if (!blank($order->location_url)) {
            $parts[] = "Location: {$order->location_url}";
        }

        return implode("\n", $parts);
    }

    private function sms(string $code, string $phone, string $message): void
    {
        try {
            $smsManagerService = new SmsManagerService();
            $smsService = new SmsService();
            if ($smsService->gateway() && $smsManagerService->gateway($smsService->gateway())->status()) {
                $smsManagerService->gateway($smsService->gateway())->send($code, $phone, $message);
            }
        } catch (Exception $e) {
            Log::info("DriverAssignedWhatsAppNotificationBuilder SMS error: " . $e->getMessage());
        }
    }
}

