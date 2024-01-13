<?php


use App\Http\Controllers\Sms\SmsReportController;
use App\Http\Controllers\Sms\SmsSendController;
use Illuminate\Http\Request;


use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix(env('API_VERSION'))->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix(env('API_VERSION'))->middleware('auth:api')->group(function () {
    Route::get('/sms-report', SmsReportController::class . '@reportSms');
    Route::get('/sms-report/{id}', SmsReportController::class . '@reportSmsDetail');
    Route::post('/send-sms', SmsSendController::class . '@sendSms');
});


require __DIR__.'/auth.php';
