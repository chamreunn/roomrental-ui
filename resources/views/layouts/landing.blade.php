<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Welcome')</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>

    <main class="page">
        @yield('content')
    </main>

    @include('partials.footer')
</body>

</html>
