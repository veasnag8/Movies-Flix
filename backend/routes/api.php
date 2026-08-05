<?php

use App\Http\Controllers\Api\Admin\CategoryAdminController;
use App\Http\Controllers\Api\Admin\MovieAdminController;
use App\Http\Controllers\Api\Admin\UserAdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\UserLibraryController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:api-auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('throttle:api-public')->group(function () {
    Route::get('/movies', [MovieController::class, 'index']);
    Route::get('/movies/{id}', [MovieController::class, 'show']);
    Route::get('/categories', [MovieController::class, 'categories']);
});

Route::middleware('throttle:api-search')->group(function () {
    Route::get('/search', [MovieController::class, 'search']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:api-user')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::get('/favorites', [UserLibraryController::class, 'favorites']);
        Route::post('/favorites', [UserLibraryController::class, 'addFavorite']);
        Route::delete('/favorites/{movieId}', [UserLibraryController::class, 'removeFavorite']);

        Route::get('/watch-history', [UserLibraryController::class, 'watchHistory']);
        Route::post('/watch-history', [UserLibraryController::class, 'saveProgress']);
    });

    Route::prefix('admin')->middleware([EnsureUserIsAdmin::class, 'throttle:api-admin'])->group(function () {
        Route::get('/movies', [MovieAdminController::class, 'index']);
        Route::post('/movie', [MovieAdminController::class, 'store']);
        Route::put('/movie/{id}', [MovieAdminController::class, 'update']);
        Route::post('/movie/{id}', [MovieAdminController::class, 'update']);
        Route::delete('/movie/{id}', [MovieAdminController::class, 'destroy']);
        Route::post('/upload/video', [MovieAdminController::class, 'uploadVideo']);
        Route::post('/upload/poster', [MovieAdminController::class, 'uploadPoster']);

        Route::get('/categories', [CategoryAdminController::class, 'index']);
        Route::post('/categories', [CategoryAdminController::class, 'store']);
        Route::put('/categories/{id}', [CategoryAdminController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryAdminController::class, 'destroy']);

        Route::get('/users', [UserAdminController::class, 'index']);
        Route::post('/users', [UserAdminController::class, 'store']);
        Route::put('/users/{id}', [UserAdminController::class, 'update']);
        Route::delete('/users/{id}', [UserAdminController::class, 'destroy']);
    });
});
