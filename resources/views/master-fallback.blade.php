<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#696cff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ $companyName ?? 'Restaurant POS' }}">
    @php $isAdminPwa = ($pathSegment ?? '') === 'admin'; @endphp
    <link rel="manifest" href="{{ url('/manifest.json') }}{{ $isAdminPwa ? '?context=admin' : '' }}">
    <link rel="stylesheet" href="{{ asset('themes/default/fonts/fontawesome/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/default/fonts/lab/lab.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/default/fonts/typography/public/public.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/default/fonts/typography/rubik/rubik.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/default/css/custom.css') }}">
    <title>{{ $companyName ?? 'Restaurant POS' }}</title>
    <link rel="icon" type="image" href="">
</head>

<body>
    <div id="app">
        <default-component />
    </div>

    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('build/assets/app.css') }}">
        <script type="module" src="{{ asset('build/assets/app.js') }}"></script>
    @endif

    <script>
        const APP_URL = "{{ config('app.url', '') }}";
        const APP_KEY = "{{ config('app.key', '') }}";
        const GOOGLE_TOKEN = "{{ env('VITE_GOOGLE_MAP_KEY', '') }}";
        const APP_DEMO = "{{ env('VITE_DEMO', '') }}";
    </script>

    <script src="{{ asset('themes/default/js/drawer.js') }}" defer></script>
    <script src="{{ asset('themes/default/js/modal.js') }}" defer></script>
    <script src="{{ asset('themes/default/js/customScript.js') }}" defer></script>
    <script src="{{ asset('themes/default/js/tabs.js') }}" defer></script>
    <script src="{{ asset('themes/default/js/dropdown.js') }}" defer></script>
    <script src="{{ asset('themes/default/js/apexcharts/apexcharts.min.js') }}" defer></script>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                var pathSegment = window.location.pathname.split('/')[1] || '';
                var isAdmin = pathSegment === 'admin';
                var swUrl = isAdmin ? '/sw-admin.js' : '/sw.js';
                var options = isAdmin ? { scope: '/admin/' } : {};
                navigator.serviceWorker.register(swUrl, options).catch(function() {});
            });
        }
    </script>
</body>

</html>
