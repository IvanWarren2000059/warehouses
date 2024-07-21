<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;


Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/register', function () {
    return view('register');
})->name('register');


Route::post('/login', [LoginController::class, 'authenticate']);
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return view('login');
});

Route::post('/register', [UserController::class, 'store'])->name('user.store');


Route::middleware(['auth'])->group(function () {
    Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
    
    Route::get('/posts/create', function () {
        return view('posts.create');
    })->name('posts.create');
    
    Route::post('/create', [PostController::class, 'store'])->name('create');
    Route::get('/show/{id}', [PostController::class, 'show'])->name('show');
    Route::get('/edit/{id}', [PostController::class, 'edit'])->name('edit');
    Route::put('/update/{id}', [PostController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [PostController::class, 'destroy'])->name('delete');
});
