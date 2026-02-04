<?php

use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CommunityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HotTopicController;
use App\Http\Controllers\Api\MadarsaController;
use App\Http\Controllers\Api\MemberCategoryController;
use App\Http\Controllers\Api\MasjidController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\OngoingWorkApiController;
use App\Http\Controllers\Api\ReligiousInfoController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\StaticDataController;
use App\Http\Controllers\Api\TopicUpdateController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\YateemsHelpController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('user')->group(function () {

    Route::post('signup/send-otp', [AuthController::class, 'userSignup']);
    Route::post('signup/verify-otp', [AuthController::class, 'verifyUserOtp']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [AuthController::class, 'userProfile']);
        Route::post('masjid', [MasjidController::class, 'store']);
    });
});

Route::prefix('member')->group(function () {

    Route::post('signup/send-otp', [AuthController::class, 'sendMemberOtp']);
    Route::post('signup/verify-otp', [AuthController::class, 'verifyOtpAndCreateMember']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [AuthController::class, 'memberProfile']);
        Route::post('kyc', [MemberController::class, 'store']);
        Route::post('kyc/submit', [MemberController::class, 'submit']);
        Route::post('masjid', [MasjidController::class, 'store']);
    });
});

Route::get('member-categories', [MemberCategoryController::class, 'show']);
Route::get('masjid', [MasjidController::class, 'show']);
Route::get('madarsa', [MadarsaController::class, 'show']);
Route::post('login/send-otp', [AuthController::class, 'userSendLoginOtp']);
Route::post('login/verify-otp', [AuthController::class, 'userVerifyOtp']);
Route::get('banners', [BannerController::class, 'getBanners']);
Route::get('hot-topics', [HotTopicController::class, 'index']);
Route::get('hot-topics/{hotTopic}/updates', [TopicUpdateController::class, 'index']);
Route::get('quran/by-surah', [StaticDataController::class, 'surah']);
Route::get('quran/by-page', [StaticDataController::class, 'pages']);
Route::get('quran/by-juzs', [StaticDataController::class, 'juzs']);
Route::get('quran/by-hizb', [StaticDataController::class, 'hizb']);
Route::get('stac', [StaticDataController::class, 'stac']);
Route::get('ongoing-works', [OngoingWorkApiController::class, 'index']);
Route::get('ongoing-works/{id}', [OngoingWorkApiController::class, 'show']);
Route::get('religious-info', [ReligiousInfoController::class, 'index']);
Route::get('religious-info/{id}', [ReligiousInfoController::class, 'show']);
Route::get('videos', [VideoController::class, 'index']);
Route::get('communities', [CommunityController::class, 'communityList']);
Route::get('13-line-quran', function () {
    return response()->json([
        'type' => '13-line',
        'language' => 'arabic',
        'pdf_url' => asset('storage/quran/Quran Majeed (Arabic only - 13 Line).pdf'),
    ]);
});
Route::post('upload-file', function (Request $request) {

    $request->validate([
        'file' => 'required|file|mimes:pdf|max:30720',
    ]);

    $path = $request->file('file')->store('quran', 'public');

    return response()->json([
        'file_url' => asset('storage/' . $path),
    ]);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('masjids-list', [MasjidController::class, 'listMasjids']);
    Route::get('masjids-details/{id}', [MasjidController::class, 'showMasjidDetails']);
    Route::post('address', [AuthController::class, 'updateAddress']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('yateems-helps', [YateemsHelpController::class, 'storeUser']);
    Route::get('yateems-helps', [YateemsHelpController::class, 'index']);
    Route::get('yateems-helps/{id}', [YateemsHelpController::class, 'show']);
    Route::post('restaurants', [RestaurantController::class, 'store']);
    Route::get('restaurants', [RestaurantController::class, 'index']);
    Route::get('restaurants/{id}', [RestaurantController::class, 'restaurantDetails']);
    Route::post('madarsas', [MadarsaController::class, 'store']);
    Route::get('madarsas', [MadarsaController::class, 'listMadarsas']);
    Route::get('madarsas/{id}', [MadarsaController::class, 'showMadarsaDetails']);
    Route::post('language', [AuthController::class, 'updateLanguage']);
});
