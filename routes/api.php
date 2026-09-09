<?php

use Illuminate\Support\Facades\Route;

/**
 * MobiTrack API routes — auth, settings, core only.
 * Banking/Document/Portal API endpoints removed.
 *
 * @see \App\Providers\Route::mapApiRoutes
 */

Route::group(['as' => 'api.'], function () {
    // Ping
    Route::get('ping', 'Common\Ping@pong')->name('ping');

    // Users
    Route::get('users/{user}/enable', 'Auth\Users@enable')->name('users.enable');
    Route::get('users/{user}/disable', 'Auth\Users@disable')->name('users.disable');
    Route::apiResource('users', 'Auth\Users');

    // Companies
    Route::get('companies/{company}/owner', 'Common\Companies@canAccess')->name('companies.owner');
    Route::get('companies/{company}/enable', 'Common\Companies@enable')->name('companies.enable');
    Route::get('companies/{company}/disable', 'Common\Companies@disable')->name('companies.disable');
    Route::apiResource('companies', 'Common\Companies', ['middleware' => ['dropzone']]);

    // Categories
    Route::get('categories/{category}/enable', 'Settings\Categories@enable')->name('categories.enable');
    Route::get('categories/{category}/disable', 'Settings\Categories@disable')->name('categories.disable');
    Route::apiResource('categories', 'Settings\Categories');

    // Currencies
    Route::get('currencies/{currency}/enable', 'Settings\Currencies@enable')->name('currencies.enable');
    Route::get('currencies/{currency}/disable', 'Settings\Currencies@disable')->name('currencies.disable');
    Route::apiResource('currencies', 'Settings\Currencies');

    // Taxes
    Route::get('taxes/{tax}/enable', 'Settings\Taxes@enable')->name('taxes.enable');
    Route::get('taxes/{tax}/disable', 'Settings\Taxes@disable')->name('taxes.disable');
    Route::apiResource('taxes', 'Settings\Taxes');

    // Settings
    Route::apiResource('settings', 'Settings\Settings');

    // Translations
    Route::get('translations/{locale}/all', 'Common\Translations@all')->name('translations.all');
    Route::get('translations/{locale}/{file}', 'Common\Translations@file')->name('translations.file');

    // MobiTrack Offline Sync Queue — Secured with Dynamic Bearer/Basic Auth, company check, rate limiting
    Route::prefix('sync')->middleware(['auth.dynamic.once', 'company.identify', 'permission:read-api', 'throttle:60,1'])->group(function () {
        Route::get('pending', [\App\Http\Controllers\MobiTrack\SyncController::class, 'pending'])->name('sync.pending');
        Route::post('upload', [\App\Http\Controllers\MobiTrack\SyncController::class, 'upload'])->name('sync.upload');
        Route::post('offset', [\App\Http\Controllers\MobiTrack\SyncController::class, 'offset'])->name('sync.offset');
        Route::get('status', [\App\Http\Controllers\MobiTrack\SyncController::class, 'status'])->name('sync.status');
    });
});
