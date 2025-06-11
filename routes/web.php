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


//Route::domain('www.ilogistix.net')->group(function () {
    Route::middleware(['auth', 'web'])->group(function () {
        // Forward all /api/tesla/* calls to the local Tesla vehicle HTTP proxy using Teslacore token
        Route::any('/api/1/{any}', [\App\Http\Controllers\TeslaApiProxyController::class, 'forward'])
            ->where('any', '.*')
            ->name('tesla.api.proxy');
    });
//});

// Wave routes
Wave::routes();
