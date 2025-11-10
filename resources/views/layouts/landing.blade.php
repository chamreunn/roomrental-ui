<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Welcome')</title>

    <!-- Vite -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js', 'resources/js/settings.js'])
</head>

<body class="body-marketing body-gradient">

    <main class="page">
        @include('partials.nav_landing')

        @yield('content')
    </main>

    @include('partials.footer')
</body>

</html>
