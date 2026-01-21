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
        // Delivery rules are branch-specific now; no site-level defaults here.
        $array = array_merge($array, $siteSettings);
        $array = array_merge($array, Settings::group('theme')->all());
        $array = array_merge($array, Settings::group('otp')->all());
        $array = array_merge($array, Settings::group('social_media')->all());
        $array = array_merge($array, Settings::group('notification')->all());
        return $array = array_merge($array, Settings::group('order_setup')->all());
    }
}
