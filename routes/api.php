<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\AvatarFrameController as AdminAvatarFrameController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\DishContributionController as AdminDishContributionController;
use App\Http\Controllers\Admin\DishController as AdminDishController;
use App\Http\Controllers\Admin\ExperienceController as AdminExperienceController;
use App\Http\Controllers\Admin\HabitItemController as AdminHabitItemController;
use App\Http\Controllers\Admin\PremiumSubscriptionController as AdminPremiumSubscriptionController;
use App\Http\Controllers\Admin\SampleAvatarController as AdminSampleAvatarController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Admin\TasteTraitController as AdminTasteTraitController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\UserHabitController as AdminUserHabitController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ExperienceController;
use App\Http\Controllers\Api\HabitController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\ReactionController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TasteTraitController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API
|--------------------------------------------------------------------------
*/
Route::get('/experiences', [ExperienceController::class, 'index']);
Route::get('/experiences/{slug}', [ExperienceController::class, 'show']);
Route::get('/experiences/{experience}/comments', [CommentController::class, 'index']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/tags', [TagController::class, 'index']);
Route::get('/taste-traits', [TasteTraitController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Authenticated user API (session guard web)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:web')->group(function () {
    Route::get('/me', [MeController::class, 'show']);
    Route::patch('/me/profile', [MeController::class, 'updateProfile']);
    // Must be registered before /users/{username}
    Route::get('/users/matches', [MatchController::class, 'index']);

    Route::post('/experiences', [ExperienceController::class, 'store']);
    Route::patch('/experiences/{experience}', [ExperienceController::class, 'update']);
    Route::delete('/experiences/{experience}', [ExperienceController::class, 'destroy']);

    Route::post('/experiences/{experience}/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    Route::post('/experiences/{experience}/reactions', [ReactionController::class, 'store']);
    Route::delete('/experiences/{experience}/reactions', [ReactionController::class, 'destroy']);

    Route::get('/habits/grid', [HabitController::class, 'grid']);
    Route::get('/habits/items', [HabitController::class, 'items']);
    Route::post('/habits/items', [HabitController::class, 'storeItem']);
    Route::patch('/habits/items/{userHabitItem}', [HabitController::class, 'updateItem']);
    Route::delete('/habits/items/{userHabitItem}', [HabitController::class, 'destroyItem']);
    Route::post('/habits/cycle', [HabitController::class, 'cycle'])->middleware('throttle:120,1');
    Route::get('/habits/history', [HabitController::class, 'history']);
});

Route::get('/users/{username}', [UserController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Admin API (Sanctum + admin guard)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware(['auth:admin', 'admin'])->group(function () {

        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::get('/me', [AdminAuthController::class, 'me']);

        Route::get('/users', [AdminUserController::class, 'index']);
        Route::patch('/users/{user}', [AdminUserController::class, 'update']);
        Route::patch('/users/{user}/premium', [AdminUserController::class, 'grantPremium']);
        Route::get('/users/{user}/habits/summary', [AdminUserHabitController::class, 'summary']);
        Route::get('/users/{user}/habits/history', [AdminUserHabitController::class, 'history']);
        Route::get('/users/{user}/habits/grid', [AdminUserHabitController::class, 'grid']);

        Route::get('/experiences', [AdminExperienceController::class, 'index']);
        Route::patch('/experiences/{experience}', [AdminExperienceController::class, 'update']);

        Route::apiResource('categories', AdminCategoryController::class)->except(['show']);
        Route::apiResource('habit-items', AdminHabitItemController::class)
            ->parameters(['habit-items' => 'habitItem'])
            ->except(['show']);
        Route::apiResource('dishes', AdminDishController::class)->except(['show']);
        Route::get('/dish-contributions', [AdminDishContributionController::class, 'index']);
        Route::patch('/dish-contributions/{dishContribution}/status', [AdminDishContributionController::class, 'updateStatus']);
        Route::apiResource('tags', AdminTagController::class)->except(['show']);
        Route::patch('/tags/{tag}/status', [AdminTagController::class, 'updateStatus']);
        Route::apiResource('taste-traits', AdminTasteTraitController::class)
            ->parameters(['taste-traits' => 'tasteTrait'])
            ->except(['show']);

        Route::apiResource('avatar-frames', AdminAvatarFrameController::class)
            ->parameters(['avatar-frames' => 'avatarFrame'])
            ->except(['show']);

        Route::apiResource('sample-avatars', AdminSampleAvatarController::class)
            ->parameters(['sample-avatars' => 'sampleAvatar'])
            ->except(['show']);

        Route::get('/premium-subscriptions', [AdminPremiumSubscriptionController::class, 'index']);
        Route::post('/premium-subscriptions', [AdminPremiumSubscriptionController::class, 'store']);
        Route::patch('/premium-subscriptions/{premiumSubscription}', [AdminPremiumSubscriptionController::class, 'update']);

        Route::get('/comments', [AdminCommentController::class, 'index']);
        Route::patch('/comments/{comment}', [AdminCommentController::class, 'update']);
    });
});
