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
        Route::get('/', function () {
            return redirect()->route('mobileshop.dashboard', ['company_id' => company_id()]);
        })->name('dashboard');

        Route::get('dashboards', function () {
            return redirect()->route('mobileshop.dashboard', ['company_id' => company_id()]);
        })->name('dashboards.index');

        Route::get('dashboards/create', function () {
            return redirect()->route('mobileshop.dashboard', ['company_id' => company_id()]);
        })->name('dashboards.create');

        Route::get('dashboards/{id}/edit', function ($id = null) {
            return redirect()->route('mobileshop.dashboard', ['company_id' => company_id()]);
        })->name('dashboards.edit');

        Route::get('dashboards/{id}/switch', function ($id = null) {
            return redirect()->route('mobileshop.dashboard', ['company_id' => company_id()]);
        })->name('dashboards.switch');

        Route::delete('dashboards/{id}', function ($id = null) {
            return redirect()->route('mobileshop.dashboard', ['company_id' => company_id()]);
        })->name('dashboards.destroy');
    });
});

