<?php

use Illuminate\Support\Facades\Route;

/**
 * 'guest' middleware applied to all routes
 *
 * @see \App\Providers\Route::mapGuestRoutes
 */

Route::group(['prefix' => 'auth', 'middleware' => ['auth.redirect']], function () {
    Route::get('login', 'Auth\Login@create')->name('login');
    Route::post('login', 'Auth\Login@store')->name('login.store');

    Route::get('forgot', 'Auth\Forgot@create')->name('forgot');
    Route::post('forgot', 'Auth\Forgot@store')->name('forgot.store');

    Route::get('reset/{token}', 'Auth\Reset@create')->name('reset');
    Route::post('reset', 'Auth\Reset@store')->name('reset.store');

    Route::get('register/{token}', 'Auth\Register@create')->name('register');
    Route::post('register', 'Auth\Register@store')->name('register.store');
});

// ══════════════════════════════════════════════════════════
// MobiTrack Public Website & Customer Portal Routes
// ══════════════════════════════════════════════════════════
Route::get('/', 'MobileShopController@publicLanding')->name('public.landing');
Route::get('shop', 'MobileShopController@publicStore')->name('public.store');
Route::get('about', 'MobileShopController@publicAbout')->name('public.about');
Route::get('contact', 'MobileShopController@publicContact')->name('public.contact');
Route::post('contact', 'MobileShopController@submitContact')->name('public.contact.submit');
Route::get('track-repair', 'MobileShopController@publicTrackRepair')->name('public.track_repair');

// Compatibility Aliases
Route::get('home', function() { return redirect()->route('public.landing'); });
Route::get('auth/shop', function() { return redirect()->route('public.store'); });
Route::get('auth/track-repair', function() { return redirect()->route('public.track_repair'); });

