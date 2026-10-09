<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'app')->name('spa.home');
Route::view('/login', 'app')->name('login');

Route::view('/{path}', 'app')
    ->where('path', '^(?!api(?:/|$)).+')
    ->name('spa');
