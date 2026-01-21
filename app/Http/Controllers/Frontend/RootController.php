<?php

namespace App\Http\Controllers\Frontend;


use App\Enums\Status;
use App\Models\Analytic;
use App\Models\ThemeSetting;
use App\Http\Controllers\Controller;
use Dipokhalder\Settings\Facades\Settings;

class RootController extends Controller
{

    public function index(): \Illuminate\Contracts\View\Factory | \Illuminate\Contracts\View\View | \Illuminate\Contracts\Foundation\Application
    {
        $analytics =  Analytic::with('analyticSections')->where(['status' => Status::ACTIVE])->get();
        $themeFavicon = ThemeSetting::where(['key' => 'theme_favicon_logo'])->first();
        $favIcon = $themeFavicon?->faviconLogo ?? null;
        return view('master', ['analytics' => $analytics, 'favicon' => $favIcon]);
    }

    public function manifest(): \Illuminate\Http\JsonResponse
    {
        try {
            $companyName = Settings::group('company')->get('company_name') ?? 'Restaurant POS';
        } catch (\Exception $e) {
            $companyName = 'Restaurant POS';
        }

        // Always use local assets for PWA icons to ensure they're accessible
        $iconBase = asset('images/theme/theme-favicon-logo.png');

        return response()->json([
            'name' => $companyName,
            'short_name' => $companyName,
            'description' => 'Restaurant POS System',
            'id' => '/',
            'start_url' => '/',
            'scope' => '/',
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
                    'purpose' => 'any'
                ],
                [
                    'src' => $iconBase,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any'
                ],
                [
                    'src' => $iconBase,
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'maskable'
                ],
                [
                    'src' => $iconBase,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable'
                ]
            ]
        ]);
    }
}
