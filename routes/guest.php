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
    Route::post('employee-register/verify', 'MobileShop\StaffOnboardingController@verifyToken')->name('mobileshop.register.verify');

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

Route::get('privacy-policy', 'MobileShop\PublicStoreController@publicPrivacy')->name('public.privacy');
Route::get('terms-and-conditions', 'MobileShop\PublicStoreController@publicTerms')->name('public.terms');
Route::get('refund-policy', 'MobileShop\PublicStoreController@publicRefunds')->name('public.refunds');
Route::get('warranty-policy', 'MobileShop\PublicStoreController@publicWarranty')->name('public.warranty');
Route::get('shipping-policy', 'MobileShop\PublicStoreController@publicShipping')->name('public.shipping');

// Public Customer Bill & PDF Routes (from WhatsApp links)
Route::get('bill/{invoice_number}', 'MobileShop\PublicBillController@show')->name('public.bill.show');
Route::get('bill/{invoice_number}/pdf', 'MobileShop\PublicBillController@pdf')->name('public.bill.pdf');

// Compatibility & Clean Short Aliases
Route::get('home', function() { return redirect()->route('public.landing'); });
Route::get('staff', function() { return redirect()->route('login'); })->name('staff');
Route::get('staff-portal', function() { return redirect()->route('login'); });
Route::get('staff/login', function() { return redirect()->route('login'); });
Route::get('login', function() { return redirect()->route('login'); });
Route::get('privacy', function() { return redirect()->route('public.privacy'); });
Route::get('terms', function() { return redirect()->route('public.terms'); });
Route::get('refunds', function() { return redirect()->route('public.refunds'); });
Route::get('warranty', function() { return redirect()->route('public.warranty'); });
Route::get('shipping', function() { return redirect()->route('public.shipping'); });
Route::get('auth/shop', function() { return redirect()->route('public.store'); });
Route::get('auth/track-repair', function() { return redirect()->route('public.track_repair'); });

// ══════════════════════════════════════════════════════════
// MobiTrack Super Admin & Developer Control Center (/home/ad)
// ══════════════════════════════════════════════════════════
Route::get('home/ad', 'MobileShop\SuperAdminDevPortalController@index')->name('dev.portal');
Route::post('home/ad/login', 'MobileShop\SuperAdminDevPortalController@login')->name('dev.portal.login');
Route::match(['get', 'post'], 'home/ad/lock', 'MobileShop\SuperAdminDevPortalController@lock')->name('dev.portal.lock');
Route::get('ad', function() { return redirect()->route('dev.portal'); });

Route::post('home/ad/backup', 'MobileShop\SuperAdminDevPortalController@createBackup')->name('dev.portal.backup');
Route::get('home/ad/backup/download/{filename}', 'MobileShop\SuperAdminDevPortalController@downloadBackup')->name('dev.portal.backup.download');
Route::delete('home/ad/backup/{filename}', 'MobileShop\SuperAdminDevPortalController@deleteBackup')->name('dev.portal.backup.delete');
Route::post('home/ad/settings', 'MobileShop\SuperAdminDevPortalController@saveSettings')->name('dev.portal.settings');
Route::post('home/ad/reset', 'MobileShop\SuperAdminDevPortalController@resetDatabase')->name('dev.portal.reset');

