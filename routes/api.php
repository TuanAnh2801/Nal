<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
Route::post('/auth/login', [
    AuthController::class, 'login'
]);
Route::post('/auth/refresh', [
    AuthController::class, 'refresh'
]);
Route::post('/auth/logout', [
    AuthController::class, 'logout']);

Route::post('/auth/register', [
    AuthController::class, 'register'
]);

Route::post('/auth/me', [
    AuthController::class, 'me'
])->name('me');
// Media
Route::group([
    'middlaware' => 'jwt.auth',
    'prefix' => 'media'
], function () {
    Route::get('/index', [MediaController::class, 'index']);
    Route::delete('/{media}', [MediaController::class, 'destroy']);
    Route::post('/create', [MediaController::class, 'store']);
    Route::post('/update/{media}', [MediaController::class, 'update']);
});
//Category
Route::group([
    'middlaware' => 'jwt.auth',
    'prefix' => 'category'
], function () {
    Route::post('/create', [CategoryController::class, 'store']);
    Route::get('/', [CategoryController::class, 'index']);
    Route::post('/update/{category}', [CategoryController::class, 'update']);
    Route::get('/{category} ', [CategoryController::class, 'show']);
    Route::delete('/delete/{category}', [CategoryController::class, 'destroy']);
});
// post
Route::group([
    'middlaware' => 'jwt.auth',
    'prefix' => 'post'
], function () {
    Route::get('/', [PostController::class, 'index']);
    Route::post('/restore', [PostController::class, 'restore']);
    Route::post('/create', [PostController::class, 'store']);
    Route::post('/update/{post}', [PostController::class, 'update']);
    Route::get('/{post}', [PostController::class, 'show']);
    Route::post('/delete', [PostController::class, 'destroy']);
});

