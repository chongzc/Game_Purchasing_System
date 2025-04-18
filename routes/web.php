<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\GameController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

// CSRF Cookie for SPA Authentication
Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

// Public routes - No authentication required
Route::get('/', function () {
    return view('application');
})->name('home');

Route::get('/game-store', function () {
    return view('application');
})->name('game-store');

// Auth routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Common routes for all authenticated users
    Route::get('/cart', function () {
        return view('application');
    })->name('cart');

    Route::get('/checkout', function () {
        return view('application');
    })->name('checkout');

    Route::get('/user-library', [GameController::class, 'library'])->name('user-library');
    Route::get('/game-library/games', [GameController::class, 'library']); 
    
    Route::get('/user-wishlist', function () {
        return view('application');
    })->name('user-wishlist');
    
    // Developer routes
    Route::middleware(['role:developer'])->group(function () {
        Route::get('/developer-dashboard', function () {
            return view('application');
        })->name('developer-dashboard');

        Route::get('/create-game', function () {
            return view('application');
        })->name('create-game');
    });
    
    // Admin routes
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin-dashboard', function () {
            return view('application');
        })->name('admin-dashboard');
        
        Route::get('/admin/games', function () {
            return view('application');
        })->name('admin.games');
    });
});

// This is the main entry point for the SPA
Route::get('/{any}', function () {
    return view('application');
})->where('any', '^(?!api).*$');
