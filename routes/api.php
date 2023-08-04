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
    'middleware' => 'jwt.auth'
], function () {
    Route::post('/user/login', [AuthController::class, 'login']);
    Route::post('/user/register', [AuthController::class, 'register']);

//Upload
    Route::post('/upload/create', [UploadController::class, 'create']);
    Route::post('/upload/upload', [UploadController::class, 'upload']);

//Category
    Route::delete('/category/delete', [CategoryController::class, 'destroy'])->middleware('can:delete,App\Models\Category');
    Route::post('/category/create', [CategoryController::class, 'store'])->middleware('can:create,App\Models\Category');
    Route::put('/category/update/{category}', [CategoryController::class, 'update'])->middleware('can:update,category');
    Route::get('/category/{category} ', [CategoryController::class, 'show']);
    Route::post('/category/restore', [CategoryController::class, 'restore'])->middleware('can:restore,App\Models\Category');
    Route::get('/category/', [CategoryController::class, 'index']);

// post
    Route::delete('/post/delete', [PostController::class, 'destroy'])->middleware('can:delete,App\Models\Post');
    Route::post('/post/restore', [PostController::class, 'restore'])->middleware('can:restore,App\Models\Post');
    Route::post('/post/create', [PostController::class, 'store'])->can('create', Post::class);;
    Route::put('/post/update/{post}', [PostController::class, 'update'])->middleware('can:update,post');
    Route::get('/post/{post}', [PostController::class, 'show']);
    Route::put('/post/updateDetail/{post}', [PostController::class, 'update_postDetail'])->middleware('can:update,post');
    Route::get('/post/', [PostController::class, 'index'])->middleware('can:show,App\Models\Post');

// article
    Route::delete('/article/delete', [ArticleController::class, 'destroy'])->middleware('can:delete,App\Models\Article');
    Route::post('/article/create', [ArticleController::class, 'store'])->can('create', Article::class);;
    Route::put('/article/update/{article}', [ArticleController::class, 'update'])->middleware('can:update,article');
    Route::get('/article/{article}', [ArticleController::class, 'show'])->middleware('can:read,article');
    Route::put('/article/updateDetail/{article}', [ArticleController::class, 'update_Detail'])->middleware('can:update,article');
    Route::post('/article/restore', [ArticleController::class, 'restore'])->middleware('can:restore,App\Models\Article');
    Route::get('/article/', [ArticleController::class, 'index'])->middleware('can:show,App\Models\Article');

// revision
    Route::delete('/revision/delete', [RevisionArticleController::class, 'destroy'])->middleware('can:delete,App\Models\Revision');
    Route::post('/revision/create/{article}', [RevisionArticleController::class, 'store'])->middleware('can:create,App\Models\Revision');
    Route::get('/revision/{article}', [RevisionArticleController::class, 'show'])->middleware('can:show ,App\Models\Revision');
    Route::put('/revision/update/{revision}/{article}', [RevisionArticleController::class, 'update'])->middleware('can:update,revision');
    Route::put('/revision/updateDetail/{revision}', [RevisionArticleController::class, 'update_Detail'])->middleware('can:update,revision');
    Route::post('/revision/review', [RevisionArticleController::class, 'review'])->middleware('can:update,App\Models\Revision');
    Route::post('/revision/restore', [RevisionArticleController::class, 'restore'])->middleware('can:restore,App\Models\Revision');
    Route::get('/revision/', [RevisionArticleController::class, 'index'])->middleware('can:show,App\Models\Revision');

// topPage
    Route::post('/topPage/create', [TopPageController::class, 'store'])->middleware('can:create,App\Models\TopPage');
    Route::put('/topPage/update/{topPage}', [TopPageController::class, 'update'])->middleware('can:update,topPage');
    Route::get('/topPage/{topPage}', [TopPageController::class, 'show'])->middleware('can:show,topPage');
    Route::put('/topPage/updateDetail/{topPage}', [TopPageController::class, 'update_Detail'])->middleware('can:update,topPage');
// dashboard
    Route::get('/show/dashboard', [DashboardController::class, 'dashboard']);

// Phân quyền
    Route::delete('/user/delete', [UserController::class, 'destroy'])->middleware('can:delete,App\Models\User');
    Route::get('/user/', [UserController::class, 'show'])->middleware('can:show,App\Models\User');
    Route::post('/user/create', [UserController::class, 'create'])->middleware('can:create,App\Models\User');
    Route::put('/user/update', [UserController::class, 'update']);
    Route::put('/user/update/{user}', [UserController::class, 'updateAll'])->middleware('can:updateAll,user');
    Route::post('/user/approveArticle', [UserController::class, 'approveArticle'])->middleware('can:status,App\Models\User');
    Route::post('/user/approveRevision/{revision}', [UserController::class, 'approveRevision'])->middleware('can:status,App\Models\User');
    Route::post('/user/setMood', [UserController::class, 'setMood']);
    Route::put('/user/updateMood/{user_meta}', [UserController::class, 'updateMood']);
    Route::post('/user/getMood', [UserController::class, 'getMood']);
    Route::get('/user/view', [UserController::class, 'index'])->middleware('can:viewAll,App\Models\User');
    Route::get('/user/viewMe', [UserController::class, 'view']);

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
