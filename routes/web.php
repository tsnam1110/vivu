<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\ExperienceController;
use App\Http\Controllers\Web\HabitController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\MatchController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\WhatToEatController;
use Illuminate\Support\Facades\Route;

// Mặc định: kho cá nhân (auth) / landing (guest). Khám phá là route riêng.
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/explore', [HomeController::class, 'explore'])->name('explore');

Route::get('/experiences/{slug}', [ExperienceController::class, 'show'])->name('experiences.show');
Route::get('/u/{username}', [ProfileController::class, 'show'])->name('profile.show');
Route::get('/terms', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/community', [PageController::class, 'community'])->name('pages.community');
Route::get('/cookies', [PageController::class, 'cookies'])->name('pages.cookies');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth:web')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/experiences/create/new', [ExperienceController::class, 'create'])->name('experiences.create');
    Route::post('/experiences', [ExperienceController::class, 'store'])->name('experiences.store');
    Route::get('/experiences/{experience}/edit', [ExperienceController::class, 'edit'])->name('experiences.edit');
    Route::put('/experiences/{experience}', [ExperienceController::class, 'update'])->name('experiences.update');
    Route::delete('/experiences/{experience}', [ExperienceController::class, 'destroy'])->name('experiences.destroy');

    Route::get('/profile', [ProfileController::class, 'me'])->name('profile.me');
    Route::patch('/profile/account', [ProfileController::class, 'updateAccount'])->name('profile.account.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])
        ->middleware('throttle:10,1')
        ->name('profile.password.update');
    Route::post('/profile/premium-avatar', [ProfileController::class, 'enablePremiumAvatar'])
        ->middleware('throttle:10,1')
        ->name('profile.premium-avatar');

    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/matches', MatchController::class)->name('matches.index');

    Route::get('/habits', [HabitController::class, 'index'])->name('habits.index');
    Route::get('/habits/items', [HabitController::class, 'items'])->name('habits.items');
    Route::post('/habits/items', [HabitController::class, 'storeItem'])->name('habits.items.store');
    Route::put('/habits/items/{userHabitItem}', [HabitController::class, 'updateItem'])->name('habits.items.update');
    Route::delete('/habits/items/{userHabitItem}', [HabitController::class, 'destroyItem'])->name('habits.items.destroy');
    Route::get('/habits/history', [HabitController::class, 'history'])->name('habits.history');
    Route::post('/habits/cycle', [HabitController::class, 'cycle'])
        ->middleware('throttle:120,1')
        ->name('habits.cycle');

    Route::post('/what-to-eat/suggest', [WhatToEatController::class, 'suggest'])
        ->middleware('throttle:30,1')
        ->name('what-to-eat.suggest');
    Route::get('/what-to-eat/dishes/{dish:slug}', [WhatToEatController::class, 'show'])
        ->name('what-to-eat.dishes.show');
    Route::post('/what-to-eat/dishes/{dish:slug}/contributions', [WhatToEatController::class, 'contribute'])
        ->middleware('throttle:10,1')
        ->name('what-to-eat.dishes.contribute');
    Route::post('/what-to-eat/choose', [WhatToEatController::class, 'choose'])
        ->middleware('throttle:30,1')
        ->name('what-to-eat.choose');
    Route::get('/what-to-eat/history', [WhatToEatController::class, 'history'])
        ->name('what-to-eat.history');
    Route::get('/what-to-eat/preferences', [WhatToEatController::class, 'showPreferences'])
        ->name('what-to-eat.preferences.show');
    Route::put('/what-to-eat/preferences', [WhatToEatController::class, 'updatePreferences'])
        ->name('what-to-eat.preferences.update');
});
