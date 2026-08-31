<?php

use App\Http\Controllers\AngleController;
use App\Http\Controllers\Auth\PasswordlessLoginController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CaptureController;
use Illuminate\Http\Request;
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

Route::prefix('auth')->middleware('web')->group(function () {
    Route::post('request-code', [PasswordlessLoginController::class, 'requestCode']);
    Route::post('verify-code', [PasswordlessLoginController::class, 'verifyCode']);

    Route::middleware('auth')->group(function () {
        Route::post('token', [PasswordlessLoginController::class, 'issueForSession']);
    });

    Route::middleware('auth.jwt')->group(function () {
        Route::post('logout', [PasswordlessLoginController::class, 'logout']);
    });
});

Route::middleware('auth.jwt')->get('user', function (Request $request) {
    return response()->json($request->user()->toAuthPayload());
});

Route::post('capture', [CaptureController::class, 'store'])->middleware(['web', 'throttle:capture']);

Route::prefix('brands')->middleware(['web', 'auth.jwt'])->group(function () {
    Route::get('options', [BrandController::class, 'options']);
    Route::get('/', [BrandController::class, 'index']);
    Route::post('/', [BrandController::class, 'store']);
    Route::get('{brand}', [BrandController::class, 'show']);
    Route::put('{brand}', [BrandController::class, 'update']);
    Route::delete('{brand}', [BrandController::class, 'destroy']);
    Route::post('{brand}/restore', [BrandController::class, 'restore']);
    Route::post('{brand}/logo', [BrandController::class, 'uploadLogo']);
});

Route::prefix('brands/{brand}/angles')->middleware(['web', 'auth.jwt'])->group(function () {
    Route::get('options', [AngleController::class, 'options']);
    Route::get('/', [AngleController::class, 'index']);
    Route::post('/', [AngleController::class, 'store']);
    Route::get('{angle}', [AngleController::class, 'show']);
    Route::put('{angle}', [AngleController::class, 'update']);
    Route::delete('{angle}', [AngleController::class, 'destroy']);
    Route::post('{angle}/restore', [AngleController::class, 'restore']);
});
