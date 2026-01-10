<?php

use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\MemberController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->group(function () {

    Route::get('/login', function () {
        return view('admin.login');
    })->name('admin.login');

    Route::post('login', [AuthController::class, 'login'])->name('login');

    Route::middleware('auth')->group(function () {

        Route::post('logout',[AuthController::class,'logout'])->name('logout');

        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::resource('banners', BannerController::class);
        Route::get('members',[MemberController::class,'index'])->name('members.index');
        Route::get('/members/{member}/kyc',[MemberController::class, 'kyc'])->name('admin.members.kyc');
        Route::post('/members/{member}/kyc/approve',[MemberController::class, 'approveKyc'])->name('admin.members.kyc.approve');
        Route::post('/members/{member}/kyc/reject',[MemberController::class, 'rejectKyc'])->name('admin.members.kyc.reject');

    });
});



