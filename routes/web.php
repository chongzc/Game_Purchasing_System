<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\GameController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Gate;

Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

Route::get('/', function () {
    return view('application');
})->name('home');

Route::get('/game-store', function () {
    return view('application');
})->name('game-store');

//Get method
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');

//Post method
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {
    // User routes
    Route::get('/cart', function () {
        if (!Gate::allows('is-user')) {
            abort(403, 'Unauthorized action.');
        }

        return view('application');
    })->name('cart');

    Route::get('/checkout', function () {
        if (!Gate::allows('is-user')) {
            abort(403, 'Unauthorized action.');
        }

        return view('application');
    })->name('checkout');

    Route::get('/user-library', function () {
        if (!Gate::allows('is-user')) {
            abort(403, 'Unauthorized action.');
        }

        return [GameController::class, 'library'];
    })->name('user-library');
    
    Route::get('/game-library/games', function () {
        if (!Gate::allows('is-user')) {
            abort(403, 'Unauthorized action.');
        }

        return [GameController::class, 'library'];
    });
    
    Route::get('/user-wishlist', function () {
        if (!Gate::allows('is-user')) {
            abort(403, 'Unauthorized action.');
        }

        return view('application');
    })->name('user-wishlist');

    // Developer routes
    Route::get('/developer-dashboard', function () {
        if (!Gate::allows('is-developer')) {
            abort(403, 'Unauthorized action.');
        }

        return view('application');
    })->name('developer-dashboard');

    Route::get('/create-game', function () {
        if (!Gate::allows('is-developer')) {
            abort(403, 'Unauthorized action.');
        }

        return view('application');
    })->name('create-game');

    // Admin routes
    Route::get('/admin-dashboard', function () {
        if (!Gate::allows('is-admin')) {
            abort(403, 'Unauthorized action.');
        }

        return view('application');
    })->name('admin-dashboard');
    
    Route::get('/admin/games', function () {
        if (!Gate::allows('is-admin')) {
            abort(403, 'Unauthorized action.');
        }

        return view('application');
    })->name('admin.games');
});

Route::get('/{any}', function () {
    return view('application');
})->where('any', '^(?!api).*$');
