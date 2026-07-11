<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealSuggestionLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'meal_slot',
        'meal_size',
        'meal_mode',
        'filters_json',
        'suggested_dish_ids',
        'chosen_dish_id',
        'outcome',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'filters_json' => 'array',
            'suggested_dish_ids' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chosenDish(): BelongsTo
    {
        return $this->belongsTo(Dish::class, 'chosen_dish_id');
    }
}
