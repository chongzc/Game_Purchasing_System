<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GameController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout']);
Route::get('/user', [LoginController::class, 'getUser']);

// Profile routes - no authentication required for assignment/demo
Route::get('/profile', [UserController::class, 'profile']);
Route::post('/profile', [UserController::class, 'updateProfile']);
Route::get('/users', [UserController::class, 'getUsers']);

// Game routes
Route::get('/languages', [GameController::class, 'getLanguages']);
Route::get('/categories', [GameController::class, 'getCategories']);
Route::get('/browseGames', [GameController::class, 'browseGames']);
Route::post('/games', [GameController::class, 'store']);

// Get authenticated user
Route::middleware('auth:sanctum')->get('/auth/user', function (Request $request) {
    return $request->user();
});

// Test upload endpoint 
Route::post('/test-upload', function (Request $request) {
    // Check if a file was uploaded
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        
        // Generate a unique filename
        $filename = time() . '_' . $file->getClientOriginalName();
        
        // Store the file in the public/uploads directory
        $path = $file->storeAs('uploads', $filename, 'public');
        
        return response()->json([
            'message' => 'File uploaded successfully',
            'path' => $path
        ]);
    }
    
    return response()->json([
        'message' => 'No file was uploaded',
        'inputs' => $request->all()
    ]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/developer/games', [GameController::class, 'getDeveloperGames']);
    Route::get('/library-games', [GameController::class, 'getLibraryGames']);
});
