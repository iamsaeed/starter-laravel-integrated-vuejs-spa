<?php

use App\Core\Http\Controllers\ResourceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\CountriesController;
use App\Http\Controllers\Api\EmailTemplateController;
use App\Http\Controllers\Api\ImpersonationController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\TimezonesController;
use App\Http\Controllers\Api\UserSettingsController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('api.forgot-password');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('api.reset-password');

// Public reference data routes
Route::get('/countries', [CountriesController::class, 'index'])->name('api.countries.index');
Route::get('/countries/{code}', [CountriesController::class, 'show'])->name('api.countries.show');
Route::get('/timezones', [TimezonesController::class, 'index'])->name('api.timezones.index');
Route::get('/timezones/{id}', [TimezonesController::class, 'show'])->name('api.timezones.show');

// Public invitation routes (for unauthenticated users)
Route::post('/check-email', [AuthController::class, 'checkEmail'])->name('api.check-email');

// Protected authentication routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('api.profile.update');
    Route::put('/password', [AuthController::class, 'changePassword'])->name('api.password.change');
    Route::post('/logout-all-sessions', [AuthController::class, 'logoutAllSessions'])->name('api.logout-all-sessions');
    Route::post('/logout-other-sessions', [AuthController::class, 'logoutOtherSessions'])->name('api.logout-other-sessions');

    // Impersonation routes
    Route::get('/impersonation/status', [ImpersonationController::class, 'status'])->name('api.impersonation.status');
    Route::post('/impersonation/stop', [ImpersonationController::class, 'stopImpersonating'])->name('api.impersonation.stop');
    Route::middleware('admin')->post('/impersonation/{userId}', [ImpersonationController::class, 'impersonate'])->name('api.impersonation.start');



    // Settings routes (admin-only for global settings)
    Route::middleware('admin')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('api.settings.index');
        Route::get('/settings/groups', [SettingsController::class, 'groups'])->name('api.settings.groups');
        Route::get('/settings/{key}', [SettingsController::class, 'show'])->name('api.settings.show');
        Route::post('/settings', [SettingsController::class, 'store'])->name('api.settings.store');
        Route::put('/settings/{key}', [SettingsController::class, 'update'])->name('api.settings.update');
        Route::delete('/settings/{key}', [SettingsController::class, 'destroy'])->name('api.settings.destroy');
    });

    // Settings lists (public within auth)
    Route::get('/settings/lists/{key}', [SettingsController::class, 'lists'])->name('api.settings.lists');

    // User settings routes
    Route::get('/user/settings', [UserSettingsController::class, 'index'])->name('api.user.settings.index');
    Route::get('/user/settings/{key}', [UserSettingsController::class, 'show'])->name('api.user.settings.show');
    Route::put('/user/settings', [UserSettingsController::class, 'update'])->name('api.user.settings.update');
    Route::put('/user/settings/{key}', [UserSettingsController::class, 'updateSingle'])->name('api.user.settings.update-single');

    // Email template routes (admin-only)
    Route::middleware('admin')->prefix('email-templates')->name('api.email-templates.')->group(function () {
        Route::get('/', [EmailTemplateController::class, 'index'])->name('index');
        Route::post('/', [EmailTemplateController::class, 'store'])->name('store');
        Route::get('/{template}', [EmailTemplateController::class, 'show'])->name('show');
        Route::put('/{template}', [EmailTemplateController::class, 'update'])->name('update');
        Route::delete('/{template}', [EmailTemplateController::class, 'destroy'])->name('destroy');
        Route::post('/{template}/duplicate', [EmailTemplateController::class, 'duplicate'])->name('duplicate');
        Route::post('/{template}/preview', [EmailTemplateController::class, 'preview'])->name('preview');
        Route::post('/{template}/send-test', [EmailTemplateController::class, 'sendTest'])->name('send-test');
        Route::get('/{template}/variables', [EmailTemplateController::class, 'variables'])->name('variables');
    });

    // Media upload routes
    Route::prefix('media')->name('api.media.')->group(function () {
        Route::post('/upload', [MediaController::class, 'upload'])->name('upload');
        Route::post('/upload-multiple', [MediaController::class, 'uploadMultiple'])->name('upload-multiple');
        Route::get('/{media}/download', [MediaController::class, 'download'])->name('download');
        Route::delete('/{media}', [MediaController::class, 'destroy'])->name('destroy');
    });

    // Comments (generic endpoint for any commentable model)
    Route::prefix('comments')->group(function () {
        Route::get('/{commentableType}/{commentableId}', [CommentController::class, 'index'])->name('comments.index');
        Route::post('/{commentableType}/{commentableId}', [CommentController::class, 'store'])->name('comments.store');
        Route::put('/{comment}', [CommentController::class, 'update'])->name('comments.update');
        Route::delete('/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    });

    // Generic Resource CRUD routes (admin-only)
    Route::middleware('admin')->prefix('resources')->name('api.resources.')->group(function () {
        Route::get('{resource}/meta', [ResourceController::class, 'meta'])->name('meta');
        Route::get('{resource}/search', [ResourceController::class, 'searchRelated'])->name('search');
        Route::get('{resource}', [ResourceController::class, 'index'])->name('index');
        Route::post('{resource}', [ResourceController::class, 'store'])->name('store');
        Route::get('{resource}/{id}', [ResourceController::class, 'show'])->name('show');
        Route::put('{resource}/{id}', [ResourceController::class, 'update'])->name('update');
        Route::patch('{resource}/{id}', [ResourceController::class, 'patch'])->name('patch');
        Route::delete('{resource}/{id}', [ResourceController::class, 'destroy'])->name('destroy');
        Route::post('{resource}/bulk/delete', [ResourceController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('{resource}/bulk/update', [ResourceController::class, 'bulkUpdate'])->name('bulk.update');
        Route::post('{resource}/actions/{action}', [ResourceController::class, 'runAction'])->name('actions.run');
    });
});
