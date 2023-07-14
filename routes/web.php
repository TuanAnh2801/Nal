<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\EmailController;
use App\Mail\Mailtanh;
use Illuminate\Support\Facades\Mail;

Route::get('/',[
   LoginController::class,'checkLogin'
])->name('check');
Route::get('/home',[
    ProductController::class,'index'
])->name('home');
Route::get('/create',[
    ProductController::class,'create'
])->name('product_create');
Route::post('/store',[
    ProductController::class,'store'
])->name('product_store');
Route::get('/edit/{id}',[
    ProductController::class,'edit'
])->name('product_edit');
Route::post('/update/{id}',[
    ProductController::class,'update'
])->name('product_update');
Route::get('/delete/{product}',[
    ProductController::class,'destroy'
])->name('product_delete');

Route::get('/login_form',[
   LoginController::class,'loginForm'
])->name('login.form');
Route::post('/login',[
    LoginController::class,'login'
])->name('login');
Route::get('/register_form',[
    LoginController::class,'registerForm'
])->name('register.form');
Route::post('/register',[
    LoginController::class,'register'
])->name('register');


Route::get('/email',function(){
    Mail::to('anhnt5@nal.vn')->send(new Mailtanh());
});
