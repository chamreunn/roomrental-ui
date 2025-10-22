<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

// 🌐 Landing Page
Route::get('/', function () {
    return view('landing.index');
})->name('home');

// 🌍 Language Switch
Route::get('lang/{lang}', function ($lang) {
    if (!in_array($lang, ['en', 'km'])) {
        abort(400);
    }

    Session::put('locale', $lang);
    App::setLocale($lang);
    return redirect()->back();
})->name('lang.switch');

// 🧑‍💻 Authentication Routes
Route::middleware('guest.session')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// 🚪 Logout (only for authenticated users)
Route::middleware('auth.session')->get('/logout', [AuthController::class, 'logout'])->name('logout');

// 🧩 Admin Routes
Route::middleware(['auth.session', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('dashboard.admin');
});

// 🧩 Manager Routes
Route::middleware(['auth.session', 'role:manager'])->group(function () {
    Route::get('/manager/dashboard', [ManagerController::class, 'index'])->name('dashboard.manager');
});

// 🧩 User Routes
Route::middleware(['auth.session', 'role:user'])->group(function () {
    Route::get('/user/dashboard', [UserController::class, 'index'])->name('dashboard.user');
});
