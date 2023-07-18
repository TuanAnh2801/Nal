<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\VerifyEmailController;

Route::post('/auth/login', [
    AuthController::class, 'login'
]);

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
    'middleware' => 'jwt.auth',
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
    'middleware' => 'jwt.auth',
    'prefix' => 'post'
], function () {
    Route::get('/', [PostController::class, 'index']);
    Route::post('/restore', [PostController::class, 'restore']);
    Route::post('/create', [PostController::class, 'store']);
    Route::post('/update/{post}', [PostController::class, 'update']);
    Route::get('/{post}', [PostController::class, 'show']);
    Route::post('/delete', [PostController::class, 'destroy']);
});
// Phân quyền
Route::group([
    'middleware' => ['jwt.auth'],
    'prefix' => 'user'
], function () {
    Route::get('/', [AuthController::class, 'show'])->middleware('check.permission:show');
    Route::get('/view', [AuthController::class, 'view']);
    Route::get('/update', [AuthController::class, 'update'])->middleware('check.permission:update');
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/update/{user}', [AuthController::class, 'updateAll'])->middleware('check.permission:updateAll');
    Route::post('/delete', [AuthController::class, 'destroy'])->middleware('check.permission:destroy');

});

// Verify email
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// Resend link to verify email
Route::post('/email/verify/resend', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth:api', 'throttle:6,1'])->name('verification.send');
