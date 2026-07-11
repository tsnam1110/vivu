<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\ExperienceController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\MatchController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\ProfileController;
use Illuminate\Support\Facades\Route;

// Mặc định: kho cá nhân (auth) / landing (guest). Khám phá là route riêng.
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/explore', [HomeController::class, 'explore'])->name('explore');

Route::get('/experiences/{slug}', [ExperienceController::class, 'show'])->name('experiences.show');
Route::get('/u/{username}', [ProfileController::class, 'show'])->name('profile.show');
Route::get('/terms', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('pages.privacy');

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

    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/matches', MatchController::class)->name('matches.index');
});
