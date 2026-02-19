<?php

namespace App\Http\Controllers\Frontend;


use App\Enums\Status;
use App\Models\Analytic;
use App\Models\ThemeSetting;
use App\Http\Controllers\Controller;
use Dipokhalder\Settings\Facades\Settings;

class RootController extends Controller
{

    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\Contracts\View\View | \Illuminate\Contracts\Foundation\Application | \Illuminate\Http\Response
    {
        try {
            try {
                $analytics = Analytic::with('analyticSections')->where(['status' => Status::ACTIVE])->get();
            } catch (\Throwable $e) {
                $analytics = collect();
            }
            try {
                $themeFavicon = ThemeSetting::where(['key' => 'theme_favicon_logo'])->first();
                $favIcon = $themeFavicon?->faviconLogo ?? null;
            } catch (\Throwable $e) {
                $favIcon = null;
            }
            return view('master', ['analytics' => $analytics, 'favicon' => $favIcon]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('RootController::index failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            try {
                return response()->view('master-fallback', [
                    'companyName' => 'Restaurant POS',
                    'pathSegment' => request()->path() ? explode('/', request()->path())[0] ?? '' : '',
                ], 200);
            } catch (\Throwable $fallbackException) {
                \Illuminate\Support\Facades\Log::error('RootController::master-fallback failed', ['message' => $fallbackException->getMessage()]);
                return response('<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Loading</title></head><body><div id="app">Loading...</div><script>setTimeout(function(){ window.location.reload(); }, 3000);</script></body></html>', 200, ['Content-Type' => 'text/html; charset=UTF-8']);
            }
        }
    }

    public function manifest(): \Illuminate\Http\JsonResponse
    {
        try {
            $companyName = Settings::group('company')->get('company_name') ?? 'Restaurant POS';
        } catch (\Exception $e) {
            $companyName = 'Restaurant POS';
        }

        $iconBase = asset('images/theme/theme-favicon-logo.png');
        $isAdmin = request()->query('context') === 'admin';

        if ($isAdmin) {
            return response()->json([
                'name' => $companyName . ' Admin',
                'short_name' => $companyName . ' Admin',
                'description' => 'Admin Panel',
                'start_url' => '/admin/dashboard',
                'display' => 'standalone',
                'background_color' => '#ffffff',
                'theme_color' => '#696cff',
                'orientation' => 'portrait-primary',
                'icons' => [
                    ['src' => asset('favicon.ico'), 'sizes' => '48x48', 'type' => 'image/x-icon'],
                    ['src' => $iconBase, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
                    ['src' => $iconBase, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable']
                ]
            ]);
        }

        return response()->json([
            'name' => $companyName,
            'short_name' => $companyName,
            'description' => 'Restaurant POS System',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#696cff',
            'orientation' => 'portrait-primary',
            'icons' => [
                [
                    'src' => asset('favicon.ico'),
                    'sizes' => '48x48',
                    'type' => 'image/x-icon'
                ],
                [
                    'src' => $iconBase,
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => $iconBase,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ]
            ]
        ]);
    }
}
