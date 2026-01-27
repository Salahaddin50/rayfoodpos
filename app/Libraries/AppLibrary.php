<?php

namespace App\Libraries;

use App\Enums\CurrencyPosition;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Dipokhalder\Settings\Facades\Settings;
use DateInterval;
use DateTime;

class AppLibrary
{
    public static function date($date, $pattern = null): string
    {
        if (!$pattern) {
            $pattern = env('DATE_FORMAT') ?: 'd-m-Y';
        }
        if (!$date) {
            return '';
        }
        return Carbon::parse($date)->format($pattern);
    }

    public static function time($time, $pattern = null): string
    {
        if (!$pattern) {
            $pattern = env('TIME_FORMAT') ?: 'h:i A';
        }
        if (!$time) {
            return '';
        }
        return Carbon::parse($time)->format($pattern);
    }

    public static function datetime($dateTime, $pattern = null): string
    {
        if (!$pattern) {
            $pattern = (env('TIME_FORMAT') ?: 'h:i A') . ', ' . (env('DATE_FORMAT') ?: 'd-m-Y');
        }
        if (!$dateTime) {
            return '';
        }
        return Carbon::parse($dateTime)->format($pattern);
    }

    public static function increaseDate($dateTime, $days, $pattern = null): string
    {
        if (!$pattern) {
            $pattern = env('DATE_FORMAT') ?: 'd-m-Y';
        }
        if (!$dateTime) {
            return '';
        }
        return Carbon::parse($dateTime)->addDays($days)->format($pattern);
    }

    public static function deliveryTime($dateTime, $pattern = null): string
    {
        if (!$pattern) {
            $pattern = env('TIME_FORMAT') ?: 'h:i A';
        }
        if (!$dateTime) {
            return '';
        }
        $explode = explode('-', $dateTime);
        if (count($explode) == 2) {
            return Carbon::parse(trim($explode[0]))->format($pattern) . ' - ' . Carbon::parse(trim($explode[1]))->format($pattern);
        }
        return '';
    }

    public static function associativeToNumericArrayBuilder($array): array
    {
        $i = 1;
        $buildArray = [];
        if (count($array)) {
            foreach ($array as $arr) {
                if (isset($arr['children'])) {
                    $children = $arr['children'];
                    unset($arr['children']);

                    $arr['parent'] = 0;
                    $buildArray[$i] = $arr;
                    $parentId = $i;
                    $i++;
                    foreach ($children as $child) {
                        $child['parent'] = $parentId;
                        $buildArray[$i] = $child;
                        $i++;
                    }
                } else {
                    $arr['parent'] = 0;
                    $buildArray[$i] = $arr;
                    $i++;
                }
            }
        }
        return $buildArray;
    }

    public static function numericToAssociativeArrayBuilder($array): array
    {
        // Build parent/children hierarchy without relying on DB row ordering.
        // The previous implementation only attached children if they appeared
        // immediately after the parent in the array, which breaks Role & Permissions UI.
        $parents = [];
        $childrenByParent = [];

        if (count($array)) {
            foreach ($array as $idx => $arr) {
                $arr['_idx'] = $idx; // keep stable order (as provided)

                // Check parent value - it might be null, 0, or "0" (string)
                $parentValue = $arr['parent'] ?? null;
                if ($parentValue === null || $parentValue === 0 || $parentValue === '0' || $parentValue === '') {
                    $parents[] = $arr;
                } else {
                    $childrenByParent[$parentValue][] = $arr;
                }
            }
        }

        usort($parents, fn ($a, $b) => ($a['_idx'] ?? 0) <=> ($b['_idx'] ?? 0));

        $buildArray = [];
        foreach ($parents as $p) {
            $id = $p['id'] ?? null;
            $children = $id && isset($childrenByParent[$id]) ? $childrenByParent[$id] : [];
            usort($children, fn ($a, $b) => ($a['_idx'] ?? 0) <=> ($b['_idx'] ?? 0));

            unset($p['_idx']);
            foreach ($children as &$c) {
                unset($c['_idx']);
            }

            if ($children) {
                $p['children'] = $children;
            }

            // Drop empty groups (same behavior as before)
            if (($p['url'] ?? null) === "#" && empty($p['children'])) {
                continue;
            }

            $buildArray[] = $p;
        }

        return $buildArray;
    }

    public static function permissionWithAccess(&$permissions, $rolePermissions): object
    {
        if ($permissions) {
            foreach ($permissions as $permission) {
                if (isset($rolePermissions[$permission->id])) {
                    $permission->access = true;
                } else {
                    $permission->access = false;
                }
            }
        }
        return $permissions;
    }

    public static function menu(&$menus, $permissions): array
    {
        if ($menus && $permissions) {
            foreach ($menus as $key => $menu) {
                if (isset($permissions[$menu['url']]) && !$permissions[$menu['url']]['access']) {
                    if ($menu['url'] != '#') {
                        unset($menus[$key]);
                    }
                }
            }
        }
        return $menus;
    }

    public static function pluck($array, $value, $key = null, $type = 'object'): array
    {
        $returnArray = [];
        if ($array) {
            foreach ($array as $item) {
                if ($key != null) {
                    if ($type == 'array') {
                        $returnArray[$item[$key]] = strtolower($value) == 'obj' ? $item : $item[$value];
                    } else {
                        $returnArray[$item[$key]] = strtolower($value) == 'obj' ? $item : $item->$value;
                    }
                } elseif ($value == 'obj') {
                    $returnArray[] = $item;
                } elseif ($type == 'array') {
                    $returnArray[] = $item[$value];
                } else {
                    $returnArray[] = $item->$value;
                }
            }
        }
        return $returnArray;
    }

    public static function username($name)
    {
        if ($name) {
            $username = strtolower(str_replace(' ', '', $name)) . rand(1, 999999);
            if (User::where(['username' => $username])->first()) {
                self::username($name);
            }
            return $username;
        }
    }

    public static function name($firstName, $lastName): string
    {
        return $firstName . ' ' . $lastName;
    }

    public static function branchChecking($branch_id): int
    {
        if (Auth::check()) {
            $branch_id = $branch_id ?? null;
            if ($branch_id === null) {
                $branch_id = Settings::group('site')->get('site_default_branch');
            } elseif ($branch_id === 0) {
                if ($branch_id === Auth::user()->branch_id) {
                    $branch_id = 0;
                } else {
                    $branch_id = Auth::user()->branch_id;
                }
            } else {
                $branch_id = Auth::user()->branch_id;
            }
        }
        return $branch_id;
    }

    public static function amountCheck($amount, $attr = 'price'): object
    {
        $response = [
            'status' => true,
            'message' => ''
        ];

        if (!is_numeric($amount)) {
            $response['status'] = false;
            $response['message'] = "This {$attr} must be integer.";
        }

        if ($amount <= 0) {
            if ($response['status'] == false) {
                return (object)$response;
            } else {
                $response['status'] = false;
                $response['message'] = "This {$attr} negative amount not allow.";
            }
        }

        $replaceValue = str_replace('.', '', $amount);
        if (strlen($replaceValue) > 12) {
            if ($response['status'] == false) {
                return (object)$response;
            } else {
                $response['status'] = false;
                $response['message'] = "This {$attr} length can't be greater than 12 digit.";
            }
        }

        if (!preg_match("/^\d{1,10}(\.\d{1,2})?$/", $amount)) {
            if ($response['status'] == false) {
                return (object)$response;
            } else {
                $response['status'] = false;
                $response['message'] = "This {$attr} amount provide invalid.";
            }
        }

        return (object)$response;
    }

    public static function currencyAmountFormat($amount): string
    {
        $currencySymbol = Settings::group('site')->get('site_default_currency_symbol', env('CURRENCY_SYMBOL', '$'));
        $currencyPosition = Settings::group('site')->get('site_currency_position', env('CURRENCY_POSITION', CurrencyPosition::LEFT));
        $decimalPoint = Settings::group('site')->get('site_digit_after_decimal_point', env('CURRENCY_DECIMAL_POINT', 2));
        
        if ($currencyPosition == CurrencyPosition::LEFT) {
            return $currencySymbol . number_format($amount, $decimalPoint, '.', '');
        }
        return number_format($amount, $decimalPoint, '.', '') . $currencySymbol;
    }

    public static function flatAmountFormat($amount): string
    {
        return number_format($amount, env('CURRENCY_DECIMAL_POINT'), '.', '');
    }

    public static function convertAmountFormat($amount): float
    {
        return (float)number_format($amount, env('CURRENCY_DECIMAL_POINT'), '.', '');
    }

    public static function fcmDataBind($request)
    {
        try {
            $cdn = public_path("firebase-cdn.txt");
            $textContent = public_path("firebase-content.txt");
            $file = public_path("firebase-messaging-sw.js");
            
            // Check if source files exist
            if (!File::exists($cdn) || !File::exists($textContent)) {
                Log::warning('FCM: Source files (firebase-cdn.txt or firebase-content.txt) not found. Service worker not updated.');
                return;
            }
            
            $content = 'let config = {
        apiKey: "' . $request->notification_fcm_api_key . '",
        authDomain: "' . $request->notification_fcm_auth_domain . '",
        projectId: "' . $request->notification_fcm_project_id . '",
        storageBucket: "' . $request->notification_fcm_storage_bucket . '",
        messagingSenderId: "' . $request->notification_fcm_messaging_sender_id . '",
        appId: "' . $request->notification_fcm_app_id . '",
        measurementId: "' . ($request->notification_fcm_measurement_id ?? '') . '",' . "\n" . ' };' . "\n";
            
            // Try to write file, but don't fail if permission denied
            try {
                File::put($file, File::get($cdn) . $content . File::get($textContent));
                Log::info('FCM: Service worker file updated successfully');
            } catch (\Exception $e) {
                Log::warning('FCM: Could not update service worker file. You may need to update it manually. Error: ' . $e->getMessage());
                // Don't throw - allow settings to be saved even if file write fails
            }
        } catch (\Exception $e) {
            Log::error('FCM: Error in fcmDataBind: ' . $e->getMessage());
            // Don't throw - allow settings to be saved
        }
    }

    public static function defaultPermission($permissions)
    {
        $defaultPermission = (object)[];
        if (count($permissions)) {
            foreach ($permissions as $permission) {
                if ($permission->access) {
                    $defaultPermission = $permission;
                    break;
                }
            }
        }
        return $defaultPermission;
    }

    public static function domain($input)
    {
        $input = trim($input, '/');
        if (!preg_match('#^http(s)?://#', $input)) {
            $input = 'http://' . $input;
        }
        $urlParts = parse_url($input);

        $link = '';
        if (isset($urlParts['port'])) {
            $link .= ':' . $urlParts['port'];
        }

        if (isset($urlParts['path'])) {
            $link .= $urlParts['path'];
        }

        return preg_replace('/^www\./', '', ($urlParts['host'] . $link));
    }

    public static function licenseApiResponse($response)
    {
        $header      = explode(';', $response->getHeader('Content-Type')[0]);
        $contentType = $header[0];
        if ($contentType == 'application/json') {
            $contents = $response->getBody()->getContents();
            $data     = json_decode($contents);
            if (json_last_error() == JSON_ERROR_NONE) {
                return $data;
            }
            return $contents;
        }

        return ['status' => false, 'message' => 'data not found'];
    }


    public static function deleteDir($dirPath): void
    {
        if (!is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    public static function reportCurrencyAmountFormat($amount): string
    {
        return number_format($amount, env('CURRENCY_DECIMAL_POINT'), '.', ',');
    }

    public static function textShortener($text, $number = 30)
    {
        if ($text && mb_strlen($text) > $number) {
            return mb_substr($text, 0, $number) . "..";
        }
        return $text;
    }

    public static function defaultMenu($menus, $defaulPermission): array
    {
        foreach ($menus as $menu) {
            if (isset($menu['url']) && $menu['url'] === $defaulPermission->url) {
                return $menu;
            }
            if (isset($menu['children']) && is_array($menu['children']) && count($menu['children']) > 0) {
                $found = self::defaultMenu($menu['children'], $defaulPermission);
                if (!empty($found)) {
                    return $found;
                }
            }
        }
        return [];
    }

    public static function deliveryTimeCheck($dateTime, $pattern = null): string
    {
        if ($dateTime) {
            [$startTime, $endTime] = explode(' - ', $dateTime);
            $currentTime = new DateTime();

            $startTimeObj = DateTime::createFromFormat('H:i', $startTime);
            $endTimeObj = DateTime::createFromFormat('H:i', $endTime);

            if ($startTimeObj && $endTimeObj) {
                $slotDuration = Settings::group('order_setup')->get('order_setup_schedule_order_slot_duration') ?? 30;
                $thirtyMinutesBefore = (clone $startTimeObj)->sub(new DateInterval('PT' . $slotDuration . 'M'));

                if ($currentTime >= $thirtyMinutesBefore && $currentTime <= $endTimeObj) {
                    return "Now";
                } else {
                    if (!$pattern) {
                        $pattern = env('TIME_FORMAT', 'h:i A');
                    }
                    $explode = explode('-', $dateTime);
                    if (count($explode) == 2) {
                        return Carbon::parse(trim($explode[0]))->format($pattern) . ' - ' . Carbon::parse(trim($explode[1]))->format($pattern);
                    }
                    return '';
                }
            }
            return '';
        }
        return '';
    }

    public static function isBetweenDate($startDate, $endDate)
    {
        if (!$startDate || !$endDate) {
            return false;
        }
        $startDate = Carbon::parse($startDate);
        $endDate   = Carbon::parse($endDate);
        return Carbon::now()->between($startDate, $endDate);
    }
}
