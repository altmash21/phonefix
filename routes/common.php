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
    Route::group(['middleware' => ['permission:read-admin-panel|read-mobileshop-dashboard']], function () {
        Route::get('/', function ($company_id) {
            return redirect()->route('mobileshop.dashboard', ['company_id' => $company_id]);
        })->name('dashboard');
    });
});

