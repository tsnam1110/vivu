<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFoodPreference extends Model
{
    protected $fillable = [
        'user_id',
        'diet_flags',
        'disliked_dish_ids',
        'preferred_elements',
        'default_meal_mode',
        'max_calories_default',
        'balance_elements',
    ];

    protected function casts(): array
    {
        return [
            'diet_flags' => 'array',
            'disliked_dish_ids' => 'array',
            'preferred_elements' => 'array',
            'max_calories_default' => 'integer',
            'balance_elements' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
