<?php

namespace App\Services;


use Exception;
use App\Enums\Activity;
use App\Models\Currency;
use App\Http\Requests\SiteRequest;
use Illuminate\Support\Facades\Cache;
use Dipokhalder\EnvEditor\EnvEditor;
use Illuminate\Support\Facades\Artisan;
use App\Libraries\QueryExceptionLibrary;
use Dipokhalder\Settings\Facades\Settings;

class SiteService
{
    public $envService;

    public function __construct(EnvEditor $envEditor)
    {
        $this->envService = $envEditor;
    }

    /**
     * @throws Exception
     */
    public function list()
    {
        try {
            $settings = Settings::group('site')->all();
            // Ensure the new fields are always present (for older DBs)
            $settings['site_free_delivery_threshold'] = $settings['site_free_delivery_threshold'] ?? '80';
            $settings['site_delivery_distance_threshold_1'] = $settings['site_delivery_distance_threshold_1'] ?? '5';
            $settings['site_delivery_distance_threshold_2'] = $settings['site_delivery_distance_threshold_2'] ?? '10';
            $settings['site_delivery_cost_1'] = $settings['site_delivery_cost_1'] ?? '5';
            $settings['site_delivery_cost_2'] = $settings['site_delivery_cost_2'] ?? '8';
            $settings['site_delivery_cost_3'] = $settings['site_delivery_cost_3'] ?? '12';
            return $settings;
        } catch (Exception $exception) {
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(SiteRequest $request)
    {
        try {
            $currency = Currency::find($request->site_default_currency);
            $validated = $request->validated();
            
            // Ensure numeric values are stored as strings for consistency
            $numericFields = [
                'site_free_delivery_threshold',
                'site_delivery_distance_threshold_1',
                'site_delivery_distance_threshold_2',
                'site_delivery_cost_1',
                'site_delivery_cost_2',
                'site_delivery_cost_3'
            ];
            
            foreach ($numericFields as $field) {
                if (isset($validated[$field])) {
                    $validated[$field] = (string) $validated[$field];
                }
            }
            
            // Save all settings including the new ones
            Settings::group('site')->set($validated + ['site_default_currency_symbol' => $currency->symbol]);

            $this->envService->addData([
                'APP_DEBUG'              => $request->site_app_debug == Activity::ENABLE ? 'true' : 'false',
                'TIMEZONE'               => $request->site_default_timezone,
                'CURRENCY'               => $currency?->code,
                'CURRENCY_SYMBOL'        => $currency?->symbol,
                'CURRENCY_POSITION'      => $request->site_currency_position,
                'CURRENCY_DECIMAL_POINT' => $request->site_digit_after_decimal_point,
                'DATE_FORMAT'            => $request->site_date_format,
                'TIME_FORMAT'            => $request->site_time_format
            ]);

            if (!$this->envService->getValue('DEMO')) {
                $this->envService->addData([
                    'MIX_GOOGLE_MAP_KEY'     => $request->site_google_map_key,
                ]);
            }

            Artisan::call('optimize:clear');
            // Clear frontend settings cache
            Cache::forget('frontend:setting');

            return $this->list();
        } catch (Exception $exception) {
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }
}
