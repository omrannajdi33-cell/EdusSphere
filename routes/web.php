<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

Route::get('/login', fn () => view('auth.login'))->name('login');

Route::get('/offline', fn () => view('offline'))->name('offline');

Route::prefix('admin')->group(function () {
    Route::get('/', fn () => view('admin.dashboard'))->name('admin.dashboard');
});

Route::prefix('student')->group(function () {
    Route::get('/', fn () => view('student.dashboard'))->name('student.dashboard');
});
