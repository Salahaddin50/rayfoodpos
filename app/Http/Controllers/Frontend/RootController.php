<?php

namespace App\Http\Controllers\Frontend;


use App\Enums\Status;
use App\Models\Analytic;
use App\Models\ThemeSetting;
use App\Http\Controllers\Controller;
use Dipokhalder\Settings\Facades\Settings;
use Illuminate\Support\Facades\Cache;

class RootController extends Controller
{

    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\Contracts\View\View | \Illuminate\Contracts\Foundation\Application | \Illuminate\Http\Response
    {
        try {
            $shell = Cache::remember('frontend:root-shell', 300, function () {
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
                try {
                    $companyName = Settings::group('company')->get('company_name') ?? 'Restaurant POS';
                } catch (\Throwable $e) {
                    $companyName = 'Restaurant POS';
                }

                return [
                    'analytics' => $analytics,
                    'favicon' => $favIcon,
                    'companyName' => $companyName,
                ];
            });

            return view('master', [
                'analytics' => $shell['analytics'] ?? collect(),
                'favicon' => $shell['favicon'] ?? null,
                'companyName' => $shell['companyName'] ?? 'Restaurant POS',
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('RootController::index failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->view('master-fallback', [
                'companyName' => 'Restaurant POS',
                'pathSegment' => request()->path() ? explode('/', request()->path())[0] ?? '' : '',
            ], 200);
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
