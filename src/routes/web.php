<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\TimecardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RestController;

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
 * ログイン関連のページのルーティング
 */
Auth::routes();


Route::post('register/pre_check', [RegisterController::class, 'pre_check'])->name('register.pre_check');
Route::get('register/verify/{token}', [RegisterController::class, 'showForm']);
Route::post('register/main_check/{token}', [RegisterController::class, 'mainCheck'])->name('register.main.check');
Route::post('register/main_register/{token}', [RegisterController::class, 'mainRegister'])->name('register.main.registered');


Route::get('/login', [AuthenticatedSessionController::class, 'login'])
    ->name('login');
Route::get('/logout', [AuthenticatedSessionController::class, 'Logout'])
->name('logout');

Route::get('/', function () { return view('timecard'); })
    ->middleware('auth', 'registered')
    ->name('timecard');

Route::post('/start', [TimecardController::class, 'startWork'])
    ->name('start_work');
Route::patch('/end', [TimecardController::class, 'endWork'])
    ->name('end_work');
Route::post('/rest/start', [RestController::class, 'startRest'])
    ->name('start_rest');
Route::patch('/rest/end', [RestController::class, 'endRest'])
    ->name('end_rest');

Route::get('/attendance', [AttendanceController::class, 'DailyAttendance'])
    ->name('attendance');

Route::get('/user_list', [AttendanceController::class, 'getUserList']);

Route::post('/user_list', [AttendanceController::class, 'postUserList'])
    ->name('user_list');

Route::get('/user_attendance_list', [AttendanceController::class, 'getUserAttendance'])
    ->name('user_attendance');

// Route::get('/user_atte_list', [AttendanceController::class, 'getUserAtte'])
//     ->name('user_atte');
