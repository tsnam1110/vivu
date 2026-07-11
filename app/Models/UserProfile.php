<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'personality',
        'interests',
        'location_city',
        'is_matchable',
    ];

    protected function casts(): array
    {
        return [
            'personality' => 'array',
            'interests' => 'array',
            'is_matchable' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return list<string> */
    public function allTraitSlugs(): array
    {
        return array_values(array_unique(array_merge(
            $this->personality ?? [],
            $this->interests ?? [],
        )));
    }
}
