<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web', 'auth'], 'prefix' => 'diagnose'], function () {
    Route::get('create', App\Livewire\Pages\ImageDiagnose\Create::class)->name('diagnose.create');
    Route::get('show/{diagnose}', App\Livewire\Pages\ImageDiagnose\Show::class)->name('diagnose.show');
});

Route::view('/', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
