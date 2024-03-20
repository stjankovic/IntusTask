<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/submit-url', [UrlController::class, 'store']);
Route::get('/{hash}', [UrlController::class, 'redirect']);

Route::get('/{hash}', 'App\Http\Controllers\UrlController@redirect')->where('hash', '[0-9a-zA-Z]{6}');
