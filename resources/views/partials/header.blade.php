<header class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
    <div class="container-xl">
        <a class="navbar-brand" href="#">
            <i data-icon="layout-dashboard" class="me-2"></i>
            Dashboard
        </a>

        <div class="navbar-nav flex-row order-md-last">
            <a href="#" class="nav-link px-3" data-bs-toggle="theme">
                <i data-icon="moon"></i>
            </a>
            <a href="{{ route('logout') }}" class="nav-link px-3 text-danger">
                <i data-icon="logout"></i> Logout
            </a>
        </div>
    </div>
</header>
