<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ArticleController;
use App\Models\Post;
use App\Models\Article;
use App\Http\Controllers\TopPageController;
use App\Http\Controllers\RevisionArticleController;
use App\Http\Controllers\DashboardController;
Route::group([
    'prefix' => 'user'
], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});
// Media
Route::group([
    'middlaware' => 'jwt.auth',
    'prefix' => 'media'
], function () {
    Route::delete('/{media}', [MediaController::class, 'destroy']);
    Route::post('/create', [MediaController::class, 'store']);
    Route::post('/update/{media}', [MediaController::class, 'update']);
    Route::get('/index', [MediaController::class, 'index']);
});
//Upload
Route::group([
    'middleware' => 'jwt.auth',
    'prefix' => 'upload'
], function () {
    Route::post('/create', [UploadController::class, 'create']);
    Route::post('/upload', [UploadController::class, 'upload']);
});
//Category
Route::group([
    'middleware' => 'jwt.auth',
    'prefix' => 'category'
], function () {
    Route::delete('/delete', [CategoryController::class, 'destroy'])->middleware('can:delete,App\Models\Category');
    Route::post('/create', [CategoryController::class, 'store'])->middleware('can:create,App\Models\Category');
    Route::put('/update/{category}', [CategoryController::class, 'update'])->middleware('can:update,category');
    Route::get('/{category} ', [CategoryController::class, 'show']);
    Route::post('/restore', [CategoryController::class, 'restore'])->middleware('can:restore,App\Models\Category');
    Route::get('/', [CategoryController::class, 'index']);

});
// post
Route::group([
    'middleware' => 'jwt.auth',
    'prefix' => 'post'
], function () {
    Route::delete('/delete', [PostController::class, 'destroy'])->middleware('can:delete,App\Models\Post');
    Route::post('/restore', [PostController::class, 'restore'])->middleware('can:restore,App\Models\Post');
    Route::post('/create', [PostController::class, 'store'])->can('create', Post::class);;
    Route::put('/update/{post}', [PostController::class, 'update'])->middleware('can:update,post');
    Route::get('/{post}', [PostController::class, 'show']);
    Route::put('/updateDetail/{post}', [PostController::class, 'update_postDetail'])->middleware('can:update,post');
    Route::get('/', [PostController::class, 'index'])->middleware('can:show,App\Models\Post');
});
// article
Route::group([
    'middleware' => 'jwt.auth',
    'prefix' => 'article'
], function () {
    Route::delete('/delete', [ArticleController::class, 'destroy'])->middleware('can:delete,App\Models\Article');
    Route::post('/create', [ArticleController::class, 'store'])->can('create', Article::class);;
    Route::put('/update/{article}', [ArticleController::class, 'update'])->middleware('can:update,article');
    Route::get('/{article}', [ArticleController::class, 'show'])->middleware('can:read,article');
    Route::put('/updateDetail/{article}', [ArticleController::class, 'update_Detail'])->middleware('can:update,article');
    Route::post('/restore', [ArticleController::class, 'restore'])->middleware('can:restore,App\Models\Article');
    Route::get('/', [ArticleController::class, 'index'])->middleware('can:show,App\Models\Article');

});
// revision
Route::group([
    'middleware' => 'jwt.auth',
    'prefix' => 'revision'
], function () {
    Route::delete('/delete', [RevisionArticleController::class, 'destroy'])->middleware('can:delete,App\Models\Revision');
    Route::post('/create/{article}', [RevisionArticleController::class, 'store'])->middleware('can:create,App\Models\Revision');
    Route::get('/{article}', [RevisionArticleController::class, 'show'])->middleware('can:show ,App\Models\Revision');
    Route::put('/update/{revision}/{article}', [RevisionArticleController::class, 'update'])->middleware('can:update,revision');
    Route::put('/updateDetail/{revision}', [RevisionArticleController::class, 'update_Detail'])->middleware('can:update,revision');
    Route::post('/review', [RevisionArticleController::class, 'review'])->middleware('can:update,App\Models\Revision');
    Route::post('/restore', [RevisionArticleController::class, 'restore'])->middleware('can:restore,App\Models\Revision');
    Route::get('/', [RevisionArticleController::class, 'index'])->middleware('can:show,App\Models\Revision');

});
// topPage
Route::group([
    'middleware' => 'jwt.auth',
    'prefix' => 'topPage'
], function () {
    Route::post('/create', [TopPageController::class, 'store'])->middleware('can:create,App\Models\TopPage');
    Route::put('/update/{topPage}', [TopPageController::class, 'update'])->middleware('can:update,topPage');
    Route::get('/{topPage}', [TopPageController::class, 'show'])->middleware('can:show,topPage');
    Route::put('/updateDetail/{topPage}', [TopPageController::class, 'update_Detail'])->middleware('can:update,topPage');

});
// dashboard
Route::group([
    'middleware' => 'jwt.auth',
    'prefix' => 'show'
], function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard']);
});
// Phân quyền
Route::group([
    'middleware' => 'jwt.auth',
    'prefix' => 'user'
], function () {
    Route::delete('/delete', [UserController::class, 'destroy'])->middleware('can:delete,App\Models\User');
    Route::get('/', [UserController::class, 'show'])->middleware('can:show,App\Models\User');
    Route::post('/create', [UserController::class, 'create'])->middleware('can:create,App\Models\User');
    Route::put('/update', [UserController::class, 'update']);
    Route::put('/update/{user}', [UserController::class, 'updateAll'])->middleware('can:updateAll,user');
    Route::post('/approveArticle', [UserController::class, 'approveArticle'])->middleware('can:status,App\Models\User');
    Route::post('/approveRevision/{revision}', [UserController::class, 'approveRevision'])->middleware('can:status,App\Models\User');
    Route::post('/setMood', [UserController::class, 'setMood']);
    Route::put('/updateMood/{user_meta}', [UserController::class, 'updateMood']);
    Route::post('/getMood', [UserController::class, 'getMood']);
    Route::get('/view', [UserController::class, 'index'])->middleware('can:viewAll,App\Models\User');
    Route::get('/viewMe', [UserController::class, 'view']);


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
