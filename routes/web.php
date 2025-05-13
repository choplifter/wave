<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;
use Wave\Facades\Wave;
use Laravel\Socialite\Facades\Socialite;

// Wave routes
Wave::routes();

Route::get('auth/tesla', function () {
    return Socialite::driver('tesla')->redirect();
});

Route::get('auth/tesla/callback', function () {
    $user = Socialite::driver('tesla')->user();

    // Handle user login or registration
    dd($user);
});
