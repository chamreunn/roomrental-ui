<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ page_title() }}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js','resources/js/settings.js'])
</head>

<x-alert />

<body class="d-flex flex-column">

    @include('partials.nav_auth')

    <div class="row g-0 flex-fill">
        @yield('content')
    </div>

    @stack('scripts')

</body>

</html>
