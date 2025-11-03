<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Vue.js SPA routes - login route
Route::get('/login', function () {
    return view('spa');
})->name('login');

// Vue.js SPA routes - auth routes (register, forgot-password, reset-password, verify-email)
Route::get('/auth/{any?}', function () {
    return view('spa');
})->where('any', '.*')->name('auth');

// Vue.js SPA routes - catch all admin routes and let Vue Router handle authentication
Route::get('/admin/{any?}', function () {
    return view('spa');
})->where('any', '.*')->name('admin');

// Vue.js SPA routes - catch all user routes and let Vue Router handle authentication
Route::get('/user/{any?}', function () {
    return view('spa');
})->where('any', '.*')->name('user');
