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

    // MobiTrack Staff Onboarding via Admin Token
    Route::get('employee-register', 'MobileShop\StaffOnboardingController@showRegister')->name('mobileshop.register');
    Route::post('employee-register', 'MobileShop\StaffOnboardingController@processRegister')->name('mobileshop.register.store');

    // MobiTrack Password Reset via Email OTP
    Route::get('forgot-password', 'MobileShop\PasswordResetOtpController@showForgot')->name('mobileshop.password.forgot');
    Route::post('forgot-password/send-otp', 'MobileShop\PasswordResetOtpController@sendOtp')->name('mobileshop.password.send_otp');
    Route::get('reset-password', 'MobileShop\PasswordResetOtpController@showReset')->name('mobileshop.password.reset');
    Route::post('reset-password', 'MobileShop\PasswordResetOtpController@processReset')->name('mobileshop.password.process_reset');
});

// ══════════════════════════════════════════════════════════
// MobiTrack Public Website & Customer Portal Routes
// ══════════════════════════════════════════════════════════
Route::get('/', 'MobileShop\PublicStoreController@publicLanding')->name('public.landing');
Route::get('shop', 'MobileShop\PublicStoreController@publicStore')->name('public.store');
Route::get('shop/{id}', 'MobileShop\PublicStoreController@publicProductDetail')->name('public.product.show');
Route::get('about', 'MobileShop\PublicStoreController@publicAbout')->name('public.about');
Route::get('contact', 'MobileShop\PublicStoreController@publicContact')->name('public.contact');
Route::post('contact', 'MobileShop\PublicStoreController@submitContact')->name('public.contact.submit');
Route::get('track-repair', 'MobileShop\PublicStoreController@publicTrackRepair')->name('public.track_repair');

// Compatibility Aliases
Route::get('home', function() { return redirect()->route('public.landing'); });
Route::get('auth/shop', function() { return redirect()->route('public.store'); });
Route::get('auth/track-repair', function() { return redirect()->route('public.track_repair'); });

