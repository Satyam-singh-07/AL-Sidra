<?php

use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CommunityController;
use App\Http\Controllers\Admin\HotTopicController;
use App\Http\Controllers\Admin\JobController;
use App\Http\Controllers\Admin\JobCategoryController;
use App\Http\Controllers\Admin\MadarsaCourseController;
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
use App\Http\Controllers\Admin\RuhaniIjalController;
use App\Http\Controllers\Admin\RuhaniIjalCategoryController;
use App\Http\Controllers\Admin\RuhaniIjalAamilController;
use App\Http\Controllers\Admin\VideoCategoryController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\YateemController;
use App\Http\Controllers\Admin\DailyQuoteController;
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

Route::get('/contact-us', function () {
    return view('contact');
})->name('contact');

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

        Route::resource('jobs', JobController::class);
        Route::post('jobs/{job}/approve', [JobController::class, 'approve'])->name('admin.jobs.approve');
        Route::post('jobs/{job}/reject', [JobController::class, 'reject'])->name('admin.jobs.reject');
        Route::resource('job-categories', JobCategoryController::class);

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

        Route::resource('video-categories', VideoCategoryController::class);
        Route::patch(
            'video-categories/{id}/toggle-status',
            [VideoCategoryController::class, 'toggleStatus']
        )
            ->name('video-categories.toggle-status');

        Route::resource('masjids', MasjidController::class);
        Route::patch('/masjids/{masjid}/status', [MasjidController::class, 'cycleStatus'])
            ->name('masjids.cycle-status');
        Route::delete('masjids/{masjid}/video', [MasjidController::class, 'deleteVideo'])->name('masjids.delete-video');
        Route::delete('masjids/images/{image}', [MasjidController::class, 'deleteImage'])->name('masjids.delete-image');

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
        Route::delete('madarsas/{madarsa}/video', [MadrasaController::class, 'deleteVideo'])->name('madarsas.delete-video');
        Route::delete('madarsas/images/{image}', [MadrasaController::class, 'deleteImage'])->name('madarsas.delete-image');

        Route::resource('madarsa-courses', MadarsaCourseController::class);

        Route::resource('ruhani-ijal', RuhaniIjalController::class);
        Route::resource('ruhani-ijal-categories', RuhaniIjalCategoryController::class);
        Route::resource('ruhani-ijal-aamils', RuhaniIjalAamilController::class);
        Route::patch('ruhani-ijal-aamils/{aamil}/status', [RuhaniIjalAamilController::class, 'cycleStatus'])
            ->name('admin.ruhani-ijal-aamils.cycle-status');
        Route::post('ruhani-ijal-aamils/{aamil}/approve', [RuhaniIjalAamilController::class, 'approve'])->name('admin.ruhani-ijal-aamils.approve');
        Route::post('ruhani-ijal-aamils/{aamil}/reject', [RuhaniIjalAamilController::class, 'reject'])->name('admin.ruhani-ijal-aamils.reject');

        Route::resource('roles', RoleController::class);

        Route::resource('daily-quotes', DailyQuoteController::class);
        Route::get('daily-quote-logs', [DailyQuoteController::class, 'logs'])->name('daily-quotes.logs');

        Route::resource('permissions', PermissionController::class)
            ->parameters(['permissions' => 'user']);
        Route::patch(
            'permissions/{user}/toggle-status',
            [PermissionController::class, 'toggleStatus']
        )->name('permissions.toggleStatus');
    });
});
