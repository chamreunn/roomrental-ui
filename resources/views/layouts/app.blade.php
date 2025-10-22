<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard')</title>
    <script type="module" src="https://unpkg.com/@tabler/icons@2.41.0/dist/tabler-icons.min.js"></script>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<x-alert />

<body>
    <div class="page">
        @include('partials.sidebar')
        @include('partials.nav')

        <div class="page-wrapper">
            @include('partials.header')

            <div class="page-body">
                <div class="container-xl py-4">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
</body>

</html>
