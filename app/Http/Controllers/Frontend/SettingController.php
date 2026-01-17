<?php

namespace App\Http\Controllers\Frontend;


use App\Http\Resources\SettingResource;
use App\Services\SettingService;
use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    private SettingService $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function index() : \Illuminate\Http\Response | SettingResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $start = microtime(true);
            $cacheKey = 'frontend:setting';
            $cacheHit = Cache::has($cacheKey);

            $payload = Cache::remember($cacheKey, 300, function () {
                return (new SettingResource($this->settingService->list()))
                    ->response()
                    ->getData(true);
            });

            $durMs = (microtime(true) - $start) * 1000;
            return response()
                ->json($payload)
                ->header('Cache-Control', 'public, max-age=300')
                ->header('Server-Timing', 'app;dur=' . round($durMs, 2) . ', cache;desc=' . ($cacheHit ? 'hit' : 'miss'));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
