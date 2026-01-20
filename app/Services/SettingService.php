<?php

namespace App\Services;

use Dipokhalder\Settings\Facades\Settings;

class SettingService
{
    public function list(): array
    {
        $array = [];
        $array = array_merge($array, Settings::group('company')->all());
        $siteSettings = Settings::group('site')->all();
        // Ensure the new fields are always present with defaults if missing
        if (!isset($siteSettings['site_free_delivery_threshold'])) {
            $siteSettings['site_free_delivery_threshold'] = '80';
        }
        if (!isset($siteSettings['site_pickup_delivery_cost'])) {
            $siteSettings['site_pickup_delivery_cost'] = '5';
        }
        $array = array_merge($array, $siteSettings);
        $array = array_merge($array, Settings::group('theme')->all());
        $array = array_merge($array, Settings::group('otp')->all());
        $array = array_merge($array, Settings::group('social_media')->all());
        $array = array_merge($array, Settings::group('notification')->all());
        return $array = array_merge($array, Settings::group('order_setup')->all());
    }
}
