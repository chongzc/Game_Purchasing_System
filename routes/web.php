<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\GameController;

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

// Game routes
// Route::middleware(['auth'])->group(function () {
//     Route::get('/cart', function () {
//         return view('application');
//     })->name('cart');

//     Route::get('/user-library', [GameController::class, 'library'])->name('user-library');
//     Route::get('/game-library/games', [GameController::class, 'library']); // API endpoint for library data

//     Route::get('/checkout', function () {
//         return view('application');
//     })->name('checkout');
//  });

Route::get('/cart', function () {
    return view('application');
})->name('cart');

Route::get('/user-library', [GameController::class, 'library'])->name('user-library');
Route::get('/game-library/games', [GameController::class, 'library']); // API endpoint for library data

Route::get('/checkout', function () {
    return view('application');
})->name('checkout');

// Catch-all route for Vue Router
Route::get('/{any}', function () {
    return view('application');
})->where('any', '.*');
