<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomtypeController;
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

// 🧩 Admin&Manager Routes
Route::middleware(['auth.session', 'role:admin,manager'])->group(function () {
    // for account usable
    Route::get('/accounts', [AccountController::class, 'index'])->name('account.index');
    Route::get('/accounts/create', [AccountController::class, 'create'])->name('account.create');
    Route::post('/accounts/store', [AccountController::class, 'store'])->name('account.store');
    Route::get('/accounts/show/{id}', [AccountController::class, 'show'])->name('account.show');
    Route::patch('accounts/{id}', [AccountController::class, 'update'])->name('account.update');
    // for locations
    Route::get('/locations', [LocationController::class, 'index'])->name('location.index');
    Route::get('/locations/create', [LocationController::class, 'create'])->name('location.create');
    Route::get('/locations/edit/{id}', [LocationController::class, 'edit'])->name('location.edit');
    Route::patch('locations/{id}', [LocationController::class, 'update'])->name('location.update');
    Route::post('/locations/store', [LocationController::class, 'store'])->name('location.store');
    Route::delete('/locations/destroy/{id}', [LocationController::class, 'destroy'])->name('location.destroy');
    // for room type
    Route::get('/roomtype', [RoomtypeController::class, 'index'])->name('roomtype.index');
    Route::get('/roomtype/create', [RoomtypeController::class, 'create'])->name('roomtype.create');
    Route::post('/roomtype/store', [RoomtypeController::class, 'store'])->name('roomtype.store');
    Route::get('/roomtype/show/{id}', [RoomtypeController::class, 'show'])->name('roomtype.show');
    Route::patch('/roomtype/{id}', [RoomtypeController::class, 'update'])->name('roomtype.update');
    Route::delete('/roomtype/destroy/{id}', [RoomtypeController::class, 'destroy'])->name('roomtype.destroy');
    // for room 
    Route::get('/choose-location', [RoomController::class, 'index'])->name('room.index');
    Route::get('/choose-location/{id}/room-list', [RoomController::class, 'rooms'])->name('room.room_list');
    Route::get('/rooms/create/choose-location', [RoomController::class, 'location'])->name('room.choose_location');
    Route::get('/rooms/create/choose-location/{id}', [RoomController::class, 'create'])->name('room.create_room');
    Route::post('/rooms/store/{id}', [RoomController::class, 'store'])->name('room.store');
    Route::get('/rooms/edit/{room_id}/{location_id}', [RoomController::class, 'edit'])->name('room.edit');
    Route::patch('/rooms/update/{room_id}/{location_id}', [RoomController::class, 'update'])->name('room.update');
    Route::delete('/rooms/destroy/{room_id}/{location_id}', [RoomController::class, 'destroy'])->name('room.destroy');
    Route::delete('/rooms/multi/{location_id}', [RoomController::class, 'multiDestroy'])->name('room.multi_destroy');
    Route::get('/rooms/show/{room_id}/{location_id}', [RoomController::class, 'show'])->name('room.show');
});

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
