<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserHabitItem extends Model
{
    /**
     * Preset icons users can pick when creating/editing personal items.
     *
     * @var list<string>
     */
    public const ICONS = [
        '✨', '✅', '🎯', '💪', '🏃', '🚶', '🧘', '💤', '😴',
        '💧', '🥗', '🍎', '🥦', '☕', '🍵', '🥤',
        '📚', '📝', '💻', '🎓', '🧠', '💡',
        '🧹', '🏠', '🌱', '🌿', '☀️', '🌙',
        '❤️', '😊', '🙏', '🎨', '🎵', '🎮',
        '💰', '📱', '⏰', '📅', '🔥', '⭐',
        '🐶', '🐱', '🚴', '🏊', '🏔️', '🌊',
    ];

    protected $fillable = [
        'user_id',
        'template_habit_item_id',
        'name',
        'description',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(HabitItem::class, 'template_habit_item_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(HabitEntry::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(HabitEntryHistory::class);
    }

    public function scopeForUser(Builder $query, User|int $user): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('user_id', $userId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function isCustom(): bool
    {
        return $this->template_habit_item_id === null;
    }
}
