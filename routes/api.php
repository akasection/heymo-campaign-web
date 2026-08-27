<?php

use App\Http\Controllers\Auth\PasswordlessLoginController;
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
