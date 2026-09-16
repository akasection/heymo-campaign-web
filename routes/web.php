<?php

use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\OpenTrackingController;
use App\Http\Controllers\UnsubscribeController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('app');
});

Route::view('login', 'AdminLogin.login')->middleware('guest')->name('login');

Route::get('angles/{brandId}/{landingIdentifier}/quiz', [LandingPageController::class, 'quiz'])
    ->whereNumber('brandId')
    ->whereIn('landingIdentifier', array_keys(config('landing-pages', [])))
    ->name('landing.quiz');
Route::get('angles/{brandId}/{landingIdentifier}', [LandingPageController::class, 'show'])
    ->whereNumber('brandId')
    ->whereIn('landingIdentifier', array_keys(config('landing-pages', [])))
    ->name('landing.page');

Route::get('open/{openToken}', OpenTrackingController::class)
    ->middleware('throttle:open')
    ->where('openToken', '[0-9a-f-]{36}')
    ->name('open.track');

Route::get('unsubscribe/{visitor}', UnsubscribeController::class)
    ->middleware('signed')
    ->name('unsubscribe');

Route::middleware('auth')->group(function () {
    Route::view('admin', 'Backoffice.dashboard')->name('admin.dashboard');
    Route::view('admin/brands', 'Backoffice.dashboard')->name('admin.brands');
    Route::view('admin/brands/{id}', 'Backoffice.dashboard')->whereNumber('id')->name('admin.brand');
    Route::view('admin/angles/{brandId?}', 'Backoffice.dashboard')->whereNumber('brandId')->name('admin.angles');
    Route::view('admin/angles/{brandId}/{angleId}', 'Backoffice.dashboard')
        ->whereNumber('brandId')
        ->whereNumber('angleId')
        ->name('admin.angle');
    Route::view('admin/campaigns', 'Backoffice.dashboard')->name('admin.campaigns');
    Route::view('admin/samples', 'Backoffice.dashboard')->name('admin.samples');
    Route::view('admin/participants', 'Backoffice.dashboard')->name('admin.participants');
    Route::view('admin/locations', 'Backoffice.dashboard')->name('admin.locations');
    Route::view('admin/reports', 'Backoffice.dashboard')->name('admin.reports');
    Route::view('admin/audit', 'Backoffice.dashboard')->name('admin.audit');
    Route::view('admin/audit/{id}', 'Backoffice.dashboard')->whereNumber('id')->name('admin.audit.detail');
});
