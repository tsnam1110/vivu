<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TraitType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class TasteTrait extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'type',
        'name',
        'slug',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => TraitType::class,
            'is_active' => 'boolean',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
