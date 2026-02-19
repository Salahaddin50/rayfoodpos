<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- REQUIRED META TAGS -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#696cff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    @php
        try {
            $companyName = \Dipokhalder\Settings\Facades\Settings::group('company')->get('company_name') ?? 'Restaurant POS';
        } catch (\Throwable $e) {
            $companyName = 'Restaurant POS';
        }
        try {
            $pathSegment = is_string(request()->path()) ? (explode('/', request()->path())[0] ?? '') : '';
        } catch (\Throwable $e) {
            $pathSegment = '';
        }
        $isAdminPwa = $pathSegment === 'admin';
    @endphp
    <meta name="apple-mobile-web-app-title" content="{{ $companyName }}">


    <!-- PWA MANIFEST (admin vs main app by URL path) -->
    <link rel="manifest" href="{{ route('manifest') }}{{ $isAdminPwa ? '?context=admin' : '' }}">

    <!-- FONTS -->
    <link rel="stylesheet" href="{{ asset('themes/default/fonts/fontawesome/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/default/fonts/lab/lab.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/default/fonts/typography/public/public.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/default/fonts/typography/rubik/rubik.css') }}">

    <!-- CUSTOM STYLE -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/default/css/custom.css') }}">
    
    <!-- PAGE TITLE -->

    <title>{{ $companyName }}</title>

    <!-- FAV ICON -->
    <link rel="icon" type="image" href="{{ $favicon }}">


    @if (!blank($analytics))
        @foreach ($analytics as $analytic)
            @php $sections = $analytic->analyticSections ?? collect(); @endphp
            @if (!blank($sections))
                @foreach ($sections as $section)
                    @if (isset($section->section) && $section->section == \App\Enums\AnalyticSection::HEAD)
                        {!! $section->data ?? '' !!}
                    @endif
                @endforeach
            @endif
        @endforeach
    @endif
</head>

<body>
    @if (!blank($analytics))
        @foreach ($analytics as $analytic)
            @php $sections = $analytic->analyticSections ?? collect(); @endphp
            @if (!blank($sections))
                @foreach ($sections as $section)
                    @if (isset($section->section) && $section->section == \App\Enums\AnalyticSection::BODY)
                        {!! $section->data ?? '' !!}
                    @endif
                @endforeach
            @endif
        @endforeach
    @endif

    <div id="app">
        <default-component />
    </div>

    @if (!blank($analytics))
        @foreach ($analytics as $analytic)
            @php $sections = $analytic->analyticSections ?? collect(); @endphp
            @if (!blank($sections))
                @foreach ($sections as $section)
                    @if (isset($section->section) && $section->section == \App\Enums\AnalyticSection::FOOTER)
                        {!! $section->data ?? '' !!}
                    @endif
                @endforeach
            @endif
        @endforeach
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        const APP_URL = "{{ env('VITE_HOST') }}";
        const APP_KEY = "{{ env('VITE_API_KEY') }}";
        const GOOGLE_TOKEN = "{{ env('VITE_GOOGLE_MAP_KEY') }}";
        const APP_DEMO = "{{ env('VITE_DEMO') }}";
    </script>

    <script src="{{ asset('themes/default/js/drawer.js') }}" defer></script>
    <script src="{{ asset('themes/default/js/modal.js') }}" defer></script>
    <script src="{{ asset('themes/default/js/customScript.js') }}" defer></script>
    <script src="{{ asset('themes/default/js/tabs.js') }}" defer></script>
    <script src="{{ asset('themes/default/js/dropdown.js') }}" defer></script>
    <script src="{{ asset('themes/default/js/apexcharts/apexcharts.min.js') }}" defer></script>

    <!-- PWA Install Prompt -->
    <div id="pwa-install-prompt" class="pwa-install-prompt" style="display: none;">
        <div class="pwa-install-prompt-content">
            <div class="pwa-install-prompt-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="currentColor"/>
                    <path d="M2 17L12 22L22 17V12L12 17L2 12V17Z" fill="currentColor"/>
                </svg>
            </div>
            <div class="pwa-install-prompt-text">
                <div class="pwa-install-prompt-title">{{ $isAdminPwa ? 'Install Admin Panel' : 'Install App' }}</div>
                <div class="pwa-install-prompt-description">{{ $isAdminPwa ? 'Add the admin panel to your home screen' : 'Install our app for a better experience' }}</div>
            </div>
            <div class="pwa-install-prompt-actions">
                <button id="pwa-install-btn" class="pwa-install-btn">Install</button>
                <button id="pwa-dismiss-btn" class="pwa-dismiss-btn">×</button>
            </div>
        </div>
    </div>

    <!-- PWA Service Worker Registration (admin scope vs app scope) -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                var pathSegment = window.location.pathname.split('/')[1] || '';
                var isAdmin = pathSegment === 'admin';
                var swUrl = isAdmin ? '/sw-admin.js' : '/sw.js';
                var options = isAdmin ? { scope: '/admin/' } : {};
                navigator.serviceWorker.register(swUrl, options)
                    .then(function(registration) {
                        console.log('ServiceWorker registration successful', isAdmin ? '(admin)' : '');
                    })
                    .catch(function(err) {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }
    </script>

    <!-- PWA Install Prompt Script -->
    <script>
        (function() {
            let deferredPrompt;
            const installPrompt = document.getElementById('pwa-install-prompt');
            const installBtn = document.getElementById('pwa-install-btn');
            const dismissBtn = document.getElementById('pwa-dismiss-btn');
            const STORAGE_KEY = 'pwa-install-dismissed';
            const DISMISS_DURATION = 7 * 24 * 60 * 60 * 1000; // 7 days

            // Check if user already dismissed the prompt
            function shouldShowPrompt() {
                const dismissed = localStorage.getItem(STORAGE_KEY);
                if (!dismissed) return true;
                
                const dismissedTime = parseInt(dismissed, 10);
                const now = Date.now();
                return (now - dismissedTime) > DISMISS_DURATION;
            }

            // Show the install prompt
            function showInstallPrompt() {
                if (installPrompt && shouldShowPrompt()) {
                    installPrompt.style.display = 'flex';
                    setTimeout(() => {
                        installPrompt.classList.add('show');
                    }, 100);
                }
            }

            // Hide the install prompt
            function hideInstallPrompt() {
                if (installPrompt) {
                    installPrompt.classList.remove('show');
                    setTimeout(() => {
                        installPrompt.style.display = 'none';
                    }, 300);
                }
            }

            // Store dismissal in localStorage
            function dismissPrompt() {
                localStorage.setItem(STORAGE_KEY, Date.now().toString());
                hideInstallPrompt();
            }

            // Listen for beforeinstallprompt event
            window.addEventListener('beforeinstallprompt', (e) => {
                // Prevent the default browser install prompt
                e.preventDefault();
                // Store the event for later use
                deferredPrompt = e;
                // Show our custom prompt
                showInstallPrompt();
            });

            // Handle install button click
            if (installBtn) {
                installBtn.addEventListener('click', async () => {
                    if (!deferredPrompt) {
                        hideInstallPrompt();
                        return;
                    }

                    // Show the native install prompt
                    deferredPrompt.prompt();
                    
                    // Wait for user response
                    const { outcome } = await deferredPrompt.userChoice;
                    
                    if (outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                        localStorage.removeItem(STORAGE_KEY); // Clear dismissal if installed
                    } else {
                        console.log('User dismissed the install prompt');
                    }
                    
                    // Clear the deferredPrompt
                    deferredPrompt = null;
                    hideInstallPrompt();
                });
            }

            // Handle dismiss button click
            if (dismissBtn) {
                dismissBtn.addEventListener('click', () => {
                    dismissPrompt();
                });
            }

            // Check if app is already installed
            window.addEventListener('appinstalled', () => {
                console.log('PWA was installed');
                deferredPrompt = null;
                hideInstallPrompt();
                localStorage.removeItem(STORAGE_KEY);
            });

            // Check on page load if app is already installed
            if (window.matchMedia('(display-mode: standalone)').matches) {
                // App is already installed
                console.log('App is already installed');
            }
        })();
    </script>
</body>

</html>
