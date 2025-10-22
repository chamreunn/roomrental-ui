<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Auth\AuthController;

// Landing Page
Route::get('/', function () {
    return view('landing.index');
})->name('home');

// Language Switch
Route::get('lang/{lang}', function ($lang) {
    if (!in_array($lang, ['en', 'km'])) {
        abort(400);
    }
    Session::put('locale', $lang);
    App::setLocale($lang);
    return redirect()->back();
})->name('lang.switch');

// Auth
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// Protected Routes
Route::middleware(['auth.web'])->group(function () {
    Route::get('/dashboard', function () {
        return view('app.dashboard');
    })->name('dashboard');
});

// Logout
Route::post('/logout', function () {
    Session::flush();
    return redirect()->route('login');
})->name('logout');
