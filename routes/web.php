<?php

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

Route::middleware('auth')->group(function () {
    Route::view('admin', 'Backoffice.dashboard')->name('admin.dashboard');
    Route::view('admin/brands', 'Backoffice.dashboard')->name('admin.brands');
    Route::view('admin/brands/{brand}', 'Backoffice.dashboard')->name('admin.brand');
    Route::view('admin/campaigns', 'Backoffice.dashboard')->name('admin.campaigns');
    Route::view('admin/samples', 'Backoffice.dashboard')->name('admin.samples');
    Route::view('admin/participants', 'Backoffice.dashboard')->name('admin.participants');
    Route::view('admin/locations', 'Backoffice.dashboard')->name('admin.locations');
    Route::view('admin/reports', 'Backoffice.dashboard')->name('admin.reports');
});
