<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\RegisterUserController;
use App\Http\Controllers\TimecardController;
use Illuminate\Auth\Events\Authenticated;

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

/**
 * ユーザ登録機能をオフに切替
 */
Auth::routes([
    'register' => false
]);

Route::group(['middleware' => 'guest'], function() {
    // ログイン前のみでのルーティングを記述
    Route::get('/register', [RegisterUserController::class, 'getRegister'])
        ->name('register');
    Route::post('/register', [RegisterUserController::class, 'postRegister'])
        ->name('register');
});


Route::get('/login', [AuthenticatedSessionController::class, 'login'])
    ->name('login');
Route::get('/logout', [AuthenticatedSessionController::class, 'Logout'])
->name('logout');

Route::get('/', function () {
    return view('timecard');
})->middleware('auth')
->name('timecard');

Route::post('/start', [TimecardController::class, 'punchIn'])
    ->name('punchin');
Route::post('/end', [TimecardController::class, 'punchOut'])
    ->name('punchout');
Route::post('/rest/start', [RestController::class, 'startRest'])
    ->name('start_rest');
Route::post('/rest/end', [RestController::class, 'endRest'])
    ->name('end_rest');

Route::get('/attendance', [TimecardController::class, 'showTable'])
    ->name('attendance');
