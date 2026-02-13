<?php

use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CommunityController;
use App\Http\Controllers\Admin\HotTopicController;
use App\Http\Controllers\Admin\MadrasaController;
use App\Http\Controllers\Admin\MasjidController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\MemberCategoryController;
use App\Http\Controllers\Admin\OngoingWorkController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ReligionInfoController;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TopicUpdateController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\YateemController;
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
})->name('home');

Route::get('/terms-conditions', function () {
    return view('terms-conditions');
})->name('terms-conditions');

Route::get('/privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy-policy');

Route::prefix('admin')->group(function () {

    Route::get('/login', function () {
        return view('admin.login');
    })->name('admin.login');

    Route::post('login', [AuthController::class, 'login'])->name('login');

    Route::middleware('auth')->group(function () {

        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::resource('banners', BannerController::class);

        Route::get('members', [MemberController::class, 'index'])->name('members.index');
        Route::get('/members/{member}/kyc', [MemberController::class, 'kyc'])->name('admin.members.kyc');
        Route::post('/members/{member}/kyc/approve', [MemberController::class, 'approveKyc'])->name('admin.members.kyc.approve');
        Route::post('/members/{member}/kyc/reject', [MemberController::class, 'rejectKyc'])->name('admin.members.kyc.reject');
        Route::post('/members/{member}/toggle-status', [MemberController::class, 'toggleStatus'])->name('members.toggle-status');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        Route::resource('membercategories', MemberCategoryController::class);
        Route::patch('membercategories/{membercategory}/toggle-status', [MemberCategoryController::class, 'toggleStatus'])->name('membercategories.toggle-status');

        Route::resource('communities', CommunityController::class);
        Route::patch('communities/{community}/toggle-status', [CommunityController::class, 'toggleStatus'])->name('communities.toggle-status');

        Route::resource('hot-topics', HotTopicController::class);
        Route::patch('hot-topics/{hotTopic}/toggle-status', [HotTopicController::class, 'toggleStatus'])->name('hot-topics.toggle-status');

        Route::resource('hot-topics.topic-updates', TopicUpdateController::class);

        Route::resource('ongoing-work', OngoingWorkController::class);

        Route::resource('religion-info', ReligionInfoController::class);

        Route::resource('videos', VideoController::class);

        Route::resource('masjids', MasjidController::class);
        Route::patch('/masjids/{masjid}/status', [MasjidController::class, 'cycleStatus'])
            ->name('masjids.cycle-status');

        Route::resource('yateems-helps', YateemController::class);
        Route::patch('/yateems-helps/{yateemsHelp}/status', [YateemController::class, 'toggleStatus'])->name('yateems-helps.toggle-status');

        Route::resource('restaurants', RestaurantController::class);
        Route::post('restaurants/{restaurant}/approve', [RestaurantController::class, 'approve'])->name('admin.restaurants.approve');
        Route::post('restaurants/{restaurant}/reject', [RestaurantController::class, 'reject'])->name('admin.restaurants.reject');

        Route::resource('madarsas', MadrasaController::class);
        Route::patch(
            'madarsas/{madarsa}/status',
            [MadrasaController::class, 'cycleStatus']
        )->name('madarsas.status');

        Route::resource('roles', RoleController::class);

        Route::resource('permissions', PermissionController::class)
            ->parameters(['permissions' => 'user']);
        Route::patch(
            'permissions/{user}/toggle-status',
            [PermissionController::class, 'toggleStatus']
        )->name('permissions.toggleStatus');
    });
});
