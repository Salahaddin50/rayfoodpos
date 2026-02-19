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
        } catch (\Throwable $e) {
            $companyName = 'Restaurant POS';
        }

        try {
            $iconBase = asset('images/theme/theme-favicon-logo.png');
            $faviconSrc = asset('favicon.ico');
        } catch (\Throwable $e) {
            $iconBase = url('/images/theme/theme-favicon-logo.png');
            $faviconSrc = url('/favicon.ico');
        }

        $isAdmin = request()->query('context') === 'admin';
        $icons = [
            ['src' => $faviconSrc, 'sizes' => '48x48', 'type' => 'image/x-icon'],
            ['src' => $iconBase, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ['src' => $iconBase, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
        ];

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
                'icons' => $icons,
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
            'icons' => $icons,
        ]);
    }
}
