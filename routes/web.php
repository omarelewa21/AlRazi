<?php

use Illuminate\Support\Facades\Route;

Route::get('diagnose', '\App\Http\Controllers\DiagnoseController@create')
    ->middleware(['auth', 'verified'])
    ->name('diagnose');

Route::view('/', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
