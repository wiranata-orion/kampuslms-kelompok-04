<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/tentang', function () {
   return 'Kelompok 4: Feniria, Fitriyansyah Wicaksonoaji, Hamzah Wiranata, Jeshua Austin Daceka';
   });