<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

/**
 * 'common' middleware applied to all routes.
 * MobiTrack: Removed portal dashboard, wizard, and uploads routes.
 *
 * @see \App\Providers\Route::mapCommonRoutes
 */

Route::group(['middleware' => 'auth'], function () {
    Route::group(['middleware' => ['permission:read-admin-panel']], function () {
        Route::group(['middleware' => ['menu.admin']], function () {
            Route::get('/', 'Common\Dashboards@show')->name('dashboard');
        });
    });
});

