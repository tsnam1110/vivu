<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\HabitEntryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HabitEntryHistory extends Model
{
    protected $fillable = [
        'user_id',
        'user_habit_item_id',
        'entry_date',
        'from_status',
        'to_status',
        'source',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'changed_at' => 'datetime',
            'from_status' => HabitEntryStatus::class,
            'to_status' => HabitEntryStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userHabitItem(): BelongsTo
    {
        return $this->belongsTo(UserHabitItem::class);
    }
}
