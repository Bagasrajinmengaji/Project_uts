<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Auth with rate limiting (6 requests per minute)
Route::middleware('throttle:6,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('api.register');
    Route::post('/login', [AuthController::class, 'login'])->name('api.login');
});

// Google OAuth
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('api.auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('api.auth.google.callback');

// Categories & Public Posts Exploration
Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');
Route::get('/posts', [PostController::class, 'index'])->name('api.posts.index');
Route::get('/posts/{slug}', [PostController::class, 'showBySlug'])->name('api.posts.show');

/*
|--------------------------------------------------------------------------
| Protected Routes (Authenticated via Laravel Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Current user profile & logout
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    // Author's personal posts
    Route::get('/my-posts', [PostController::class, 'myPosts'])->name('api.posts.my');

    // Create post
    Route::post('/posts', [PostController::class, 'store'])->name('api.posts.store');

    // Show single post by ID (useful for edit form hydration)
    Route::get('/posts/id/{id}', [PostController::class, 'show'])->name('api.posts.showById');

    // Update post (supports PUT and POST for multipart/form-data with or without _method)
    Route::match(['put', 'post'], '/posts/{id}', [PostController::class, 'update'])->name('api.posts.update');

    // Delete post
    Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('api.posts.destroy');
});
