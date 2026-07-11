<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CookingMethod;
use App\Enums\CulinaryRegion;
use App\Enums\DishRole;
use App\Enums\DishStatus;
use App\Enums\FiveElement;
use App\Enums\ProteinSource;
use App\Enums\ThermalNature;
use App\Models\Dish;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

/**
 * Import kho món từ dataset JSON (verified-only).
 *
 * @see docs/features/what-to-eat-seed-and-kb.md
 */
class DishCatalogImporter
{
    /** Legacy single-file path (fallback when no manifest). */
    public const DEFAULT_DATASET = 'database/data/what-to-eat/dishes_v1.json';

    /** Multi-file catalog (preferred). */
    public const DEFAULT_MANIFEST = 'database/data/what-to-eat/dishes_v1/manifest.json';

    /** Verified Group-III calorie overlay (Fact-A) — optional. */
    public const DEFAULT_CALORIE_FACTS = 'database/data/what-to-eat/facts/calories_fact_a.json';

    /** Ops metadata overlay (cooking_method, protein_source) — optional. */
    public const DEFAULT_OPS_FACTS = 'database/data/what-to-eat/facts/ops_fields_fact_a.json';

    /** Cook-home recipe text (ingredients, steps, cook_minutes). */
    public const DEFAULT_RECIPE_TEXT = 'database/data/what-to-eat/facts/recipes_cook_home_v1.json';

    /** YHCT thermal / five_element — only with tcm_text|expert_tcm. */
    public const DEFAULT_YHCT_FACTS = 'database/data/what-to-eat/facts/yhct_fact_a.json';

    /** Fields that require provenance entry in `facts` unless allowUnverified. */
    public const SENSITIVE_FIELDS = [
        'calories_kcal',
        'serving_grams',
        'five_element',
        'thermal_nature',
        'protein_source',
        'cooking_method',
        'flavor_tags',
        'ingredients',
        'steps',
        'cook_minutes',
        'benefits',
        'harms',
        'advice',
        'dish_role',
    ];

    /**
     * Methods accepted as verified provenance (seed-KB §5.2 / §5.3).
     * Forbidden: guess, similar_dish, chatgpt, average_internet, …
     */
    public const ALLOWED_FACT_METHODS = [
        'fct_table',
        'recipe_sum',
        'label',
        'lab',
        'expert_panel',
        'tcm_text',
        'expert_tcm',
        'committee',
    ];

    /**
     * Prefer multi-file manifest; fall back to single JSON dataset.
     *
     * @return array{
     *     kb_version: string,
     *     imported: int,
     *     purged: int,
     *     skipped_sensitive: int,
     *     slugs: list<string>
     * }
     */
    /**
     * @return array{
     *     kb_version: string,
     *     imported: int,
     *     purged: int,
     *     skipped_sensitive: int,
     *     slugs: list<string>,
     *     calorie_facts_applied?: int
     * }
     */
    public function importDefault(bool $purgeMissing = true, ?bool $allowUnverified = null): array
    {
        $manifest = base_path(self::DEFAULT_MANIFEST);
        if (File::isFile($manifest)) {
            $result = $this->importFromManifest(self::DEFAULT_MANIFEST, $purgeMissing, $allowUnverified);
        } else {
            $result = $this->importFromPath(self::DEFAULT_DATASET, $purgeMissing, $allowUnverified);
        }

        $factsPath = base_path(self::DEFAULT_CALORIE_FACTS);
        if (File::isFile($factsPath)) {
            $overlay = $this->applyCalorieFactsOverlay(self::DEFAULT_CALORIE_FACTS, $allowUnverified);
            $result['calorie_facts_applied'] = $overlay['updated'];
            $result['calorie_facts_kb'] = $overlay['kb_version'];
        }

        $opsPath = base_path(self::DEFAULT_OPS_FACTS);
        if (File::isFile($opsPath)) {
            $ops = $this->applyOpsFieldsOverlay(self::DEFAULT_OPS_FACTS, $allowUnverified);
            $result['ops_facts_applied'] = $ops['updated'];
            $result['ops_facts_kb'] = $ops['kb_version'];
        }

        $recipePath = base_path(self::DEFAULT_RECIPE_TEXT);
        if (File::isFile($recipePath)) {
            $recipes = $this->applySensitiveFieldsOverlay(
                self::DEFAULT_RECIPE_TEXT,
                ['ingredients', 'steps', 'cook_minutes'],
                'recipe_text_kb',
                $allowUnverified,
            );
            $result['recipe_text_applied'] = $recipes['updated'];
            $result['recipe_text_kb'] = $recipes['kb_version'];
        }

        $yhctPath = base_path(self::DEFAULT_YHCT_FACTS);
        if (File::isFile($yhctPath)) {
            $yhct = $this->applySensitiveFieldsOverlay(
                self::DEFAULT_YHCT_FACTS,
                ['thermal_nature', 'five_element'],
                'yhct_kb',
                $allowUnverified,
            );
            $result['yhct_facts_applied'] = $yhct['updated'];
            $result['yhct_facts_kb'] = $yhct['kb_version'];
        }

        return $result;
    }

    /**
     * Generic by_slug overlay for any sensitive fields (recipe text, YHCT, …).
     *
     * @param  list<string>  $fields
     * @return array{kb_version: string, updated: int, skipped: int, missing_slug: list<string>}
     */
    public function applySensitiveFieldsOverlay(
        string $absoluteOrRelativePath,
        array $fields,
        string $metaKey,
        ?bool $allowUnverified = null,
    ): array {
        $allowUnverified ??= (bool) config('what_to_eat.seed_allow_unverified', false);
        $path = $this->resolvePath($absoluteOrRelativePath);
        if (! File::isFile($path)) {
            throw new RuntimeException("Facts overlay not found: {$path}");
        }

        /** @var array<string, mixed> $payload */
        $payload = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($payload)) {
            throw new InvalidArgumentException('Facts overlay root must be an object.');
        }

        $kbVersion = (string) ($payload['kb_version'] ?? '0.0.0');
        $bySlug = $payload['by_slug'] ?? null;
        if (! is_array($bySlug)) {
            throw new InvalidArgumentException('Facts overlay must declare `by_slug` object.');
        }

        $updated = 0;
        $skipped = 0;
        $missing = [];

        foreach ($bySlug as $slug => $row) {
            if (! is_string($slug) || $slug === '' || ! is_array($row)) {
                $skipped++;

                continue;
            }

            $dish = Dish::query()->where('slug', $slug)->first();
            if ($dish === null) {
                $missing[] = $slug;
                $skipped++;

                continue;
            }

            $facts = is_array($row['facts'] ?? null) ? $row['facts'] : [];
            $verified = $this->verifiedFieldMap($facts);
            $writes = [];
            foreach ($fields as $field) {
                if (! array_key_exists($field, $row) || $row[$field] === null || $row[$field] === '') {
                    continue;
                }
                if (! $allowUnverified && ! isset($verified[$field])) {
                    continue;
                }
                $writes[$field] = $this->castSensitive($field, $row[$field], $slug);
            }

            if ($writes === []) {
                $skipped++;

                continue;
            }

            $acceptedFacts = array_values(array_filter(
                $facts,
                function ($f) use ($verified, $allowUnverified, $writes): bool {
                    if (! is_array($f) || ! isset($f['field']) || ! is_string($f['field'])) {
                        return false;
                    }
                    if (! array_key_exists($f['field'], $writes)) {
                        return false;
                    }
                    if ($allowUnverified) {
                        return true;
                    }

                    return isset($verified[$f['field']]);
                },
            ));

            $prevMeta = is_array($dish->facts_meta) ? $dish->facts_meta : [];
            $prevFacts = is_array($prevMeta['facts'] ?? null) ? $prevMeta['facts'] : [];
            $mergedFacts = $this->mergeFactEntries($prevFacts, $acceptedFacts, array_keys($writes));

            $dish->fill(array_merge($writes, [
                'facts_meta' => array_merge($prevMeta, [
                    $metaKey => $kbVersion,
                    'facts' => $mergedFacts,
                    'imported_at' => now()->toIso8601String(),
                ]),
            ]));
            $dish->save();
            $updated++;
        }

        return [
            'kb_version' => $kbVersion,
            'updated' => $updated,
            'skipped' => $skipped,
            'missing_slug' => $missing,
        ];
    }

    /**
     * Apply cooking_method / protein_source overlays (committee-verified ops fields).
     *
     * @return array{kb_version: string, updated: int, skipped: int, missing_slug: list<string>}
     */
    public function applyOpsFieldsOverlay(string $absoluteOrRelativePath, ?bool $allowUnverified = null): array
    {
        return $this->applySensitiveFieldsOverlay(
            $absoluteOrRelativePath,
            ['cooking_method', 'protein_source'],
            'ops_kb',
            $allowUnverified,
        );
    }

    /**
     * Merge verified kcal + serving_grams onto existing dishes by slug (Fact-A).
     * Does not create dishes; skips unknown slugs. Provenance required unless allowUnverified.
     *
     * @return array{kb_version: string, updated: int, skipped: int, missing_slug: list<string>}
     */
    public function applyCalorieFactsOverlay(string $absoluteOrRelativePath, ?bool $allowUnverified = null): array
    {
        $allowUnverified ??= (bool) config('what_to_eat.seed_allow_unverified', false);
        $path = $this->resolvePath($absoluteOrRelativePath);
        if (! File::isFile($path)) {
            throw new RuntimeException("Calorie facts overlay not found: {$path}");
        }

        /** @var array<string, mixed> $payload */
        $payload = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($payload)) {
            throw new InvalidArgumentException('Calorie facts root must be an object.');
        }

        $kbVersion = (string) ($payload['kb_version'] ?? '0.0.0');
        $bySlug = $payload['by_slug'] ?? null;
        if (! is_array($bySlug)) {
            throw new InvalidArgumentException('Calorie facts must declare `by_slug` object.');
        }

        $updated = 0;
        $skipped = 0;
        $missing = [];

        foreach ($bySlug as $slug => $row) {
            if (! is_string($slug) || $slug === '' || ! is_array($row)) {
                $skipped++;

                continue;
            }

            $dish = Dish::query()->where('slug', $slug)->first();
            if ($dish === null) {
                $missing[] = $slug;
                $skipped++;

                continue;
            }

            $facts = $row['facts'] ?? [];
            if (! is_array($facts)) {
                $facts = [];
            }
            $verified = $this->verifiedFieldMap($facts);

            $kcal = $row['calories_kcal'] ?? null;
            $grams = $row['serving_grams'] ?? null;
            if ($kcal === null || $grams === null) {
                $skipped++;

                continue;
            }

            if (! $allowUnverified && (! isset($verified['calories_kcal']) || ! isset($verified['serving_grams']))) {
                // Accept calories_kcal fact that also carries serving_grams in the fact entry
                if (! isset($verified['calories_kcal'])) {
                    $skipped++;

                    continue;
                }
                // verifiedFieldMap may set serving_grams from calories fact
                if (! isset($verified['serving_grams']) && empty(array_filter(
                    $facts,
                    fn ($f) => is_array($f) && ($f['field'] ?? '') === 'calories_kcal' && ! empty($f['serving_grams'])
                ))) {
                    $skipped++;

                    continue;
                }
            }

            $kcal = (int) $kcal;
            $grams = (int) $grams;
            if ($kcal < 0 || $grams < 1) {
                $skipped++;

                continue;
            }

            $acceptedFacts = array_values(array_filter(
                $facts,
                function ($f) use ($verified, $allowUnverified): bool {
                    if (! is_array($f) || ! isset($f['field']) || ! is_string($f['field'])) {
                        return false;
                    }
                    if ($allowUnverified) {
                        return true;
                    }

                    return isset($verified[$f['field']])
                        || ($f['field'] === 'calories_kcal' && isset($verified['calories_kcal']));
                },
            ));

            $prevMeta = is_array($dish->facts_meta) ? $dish->facts_meta : [];
            $prevFacts = is_array($prevMeta['facts'] ?? null) ? $prevMeta['facts'] : [];
            $mergedFacts = $this->mergeFactEntries($prevFacts, $acceptedFacts, ['calories_kcal', 'serving_grams']);

            $dish->fill([
                'calories_kcal' => $kcal,
                'serving_grams' => $grams,
                'facts_meta' => [
                    'kb_version' => $prevMeta['kb_version'] ?? $kbVersion,
                    'facts_a_kb' => $kbVersion,
                    'facts' => $mergedFacts,
                    'imported_at' => now()->toIso8601String(),
                    'allow_unverified' => $allowUnverified,
                ],
            ]);
            $dish->save();
            $updated++;
        }

        return [
            'kb_version' => $kbVersion,
            'updated' => $updated,
            'skipped' => $skipped,
            'missing_slug' => $missing,
        ];
    }

    /**
     * @return array{
     *     kb_version: string,
     *     imported: int,
     *     purged: int,
     *     skipped_sensitive: int,
     *     slugs: list<string>
     * }
     */
    public function importFromPath(string $absoluteOrRelativePath, bool $purgeMissing = true, ?bool $allowUnverified = null): array
    {
        $path = $this->resolvePath($absoluteOrRelativePath);
        if (! File::isFile($path)) {
            throw new RuntimeException("Dish catalog dataset not found: {$path}");
        }

        /** @var array<string, mixed> $payload */
        $payload = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($payload)) {
            throw new InvalidArgumentException('Dataset root must be an object.');
        }

        return $this->importPayload($payload, $purgeMissing, $allowUnverified);
    }

    /**
     * Load `manifest.json` + shard files, merge `dishes`, then import once (single purge).
     *
     * @return array{
     *     kb_version: string,
     *     imported: int,
     *     purged: int,
     *     skipped_sensitive: int,
     *     slugs: list<string>
     * }
     */
    public function importFromManifest(string $absoluteOrRelativePath, bool $purgeMissing = true, ?bool $allowUnverified = null): array
    {
        $manifestPath = $this->resolvePath($absoluteOrRelativePath);
        if (! File::isFile($manifestPath)) {
            throw new RuntimeException("Dish catalog manifest not found: {$manifestPath}");
        }

        /** @var array<string, mixed> $manifest */
        $manifest = json_decode(File::get($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($manifest)) {
            throw new InvalidArgumentException('Manifest root must be an object.');
        }

        $files = $manifest['files'] ?? null;
        if (! is_array($files) || $files === []) {
            throw new InvalidArgumentException('Manifest must declare a non-empty `files` array.');
        }

        $dir = dirname($manifestPath);
        $merged = [];
        $seenSlugs = [];
        $kbVersion = (string) ($manifest['kb_version'] ?? '0.0.0');

        foreach ($files as $index => $file) {
            if (! is_string($file) || $file === '') {
                throw new InvalidArgumentException("Manifest files[{$index}] must be a non-empty string.");
            }
            $shardPath = $dir.DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file);
            if (! File::isFile($shardPath)) {
                throw new RuntimeException("Shard not found: {$shardPath}");
            }

            /** @var array<string, mixed> $shard */
            $shard = json_decode(File::get($shardPath), true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($shard)) {
                throw new InvalidArgumentException("Shard root must be an object: {$file}");
            }
            if (isset($shard['kb_version'])) {
                $kbVersion = (string) $shard['kb_version'];
            }

            $dishes = $shard['dishes'] ?? [];
            if (! is_array($dishes)) {
                throw new InvalidArgumentException("Shard `dishes` must be an array: {$file}");
            }

            foreach ($dishes as $dIndex => $row) {
                if (! is_array($row)) {
                    throw new InvalidArgumentException("Dish at {$file}[{$dIndex}] must be an object.");
                }
                $slug = trim((string) ($row['slug'] ?? ''));
                if ($slug === '') {
                    $slug = Str::slug((string) ($row['name'] ?? '')) ?: "shard-{$index}-{$dIndex}";
                }
                if (isset($seenSlugs[$slug])) {
                    throw new InvalidArgumentException(
                        "Duplicate slug [{$slug}] in {$file} (already in {$seenSlugs[$slug]})."
                    );
                }
                $seenSlugs[$slug] = $file;
                $merged[] = $row;
            }
        }

        return $this->importPayload([
            'kb_version' => $kbVersion,
            'ruleset_min' => $manifest['ruleset_min'] ?? null,
            'dishes' => $merged,
        ], $purgeMissing, $allowUnverified);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *     kb_version: string,
     *     imported: int,
     *     purged: int,
     *     skipped_sensitive: int,
     *     slugs: list<string>
     * }
     */
    public function importPayload(array $payload, bool $purgeMissing = true, ?bool $allowUnverified = null): array
    {
        $allowUnverified ??= (bool) config('what_to_eat.seed_allow_unverified', false);
        $kbVersion = (string) ($payload['kb_version'] ?? '0.0.0');
        $dishes = $payload['dishes'] ?? [];
        if (! is_array($dishes)) {
            throw new InvalidArgumentException('Dataset `dishes` must be an array.');
        }

        $imported = 0;
        $skippedSensitive = 0;
        $slugs = [];

        foreach ($dishes as $index => $row) {
            if (! is_array($row)) {
                throw new InvalidArgumentException("Dish at index {$index} must be an object.");
            }

            $result = $this->upsertDish($row, $allowUnverified, $kbVersion);
            $slugs[] = $result['slug'];
            $imported++;
            $skippedSensitive += $result['skipped_sensitive'];
        }

        $purged = 0;
        if ($purgeMissing) {
            $purged = $this->purgeSystemDishesNotIn($slugs);
        }

        return [
            'kb_version' => $kbVersion,
            'imported' => $imported,
            'purged' => $purged,
            'skipped_sensitive' => $skippedSensitive,
            'slugs' => $slugs,
        ];
    }

    /**
     * Remove system-sourced dishes whose slug is not in the keep list (incl. soft-deleted).
     *
     * @param  list<string>  $keepSlugs
     */
    public function purgeSystemDishesNotIn(array $keepSlugs): int
    {
        $query = Dish::withTrashed()->where('source', 'system');
        if ($keepSlugs !== []) {
            $query->whereNotIn('slug', $keepSlugs);
        }

        $count = 0;
        $query->orderBy('id')->chunkById(100, function ($rows) use (&$count): void {
            foreach ($rows as $dish) {
                /** @var Dish $dish */
                $dish->forceDelete();
                $count++;
            }
        });

        return $count;
    }

    /**
     * @return array{
     *     total: int,
     *     published: int,
     *     system: int,
     *     with_role: int,
     *     with_kcal: int,
     *     with_element: int,
     *     with_thermal: int,
     *     with_region: int,
     *     with_recipe: int,
     *     kb_hint: string|null
     * }
     */
    public function report(): array
    {
        $base = Dish::query();

        return [
            'total' => (clone $base)->count(),
            'published' => (clone $base)->published()->count(),
            'system' => (clone $base)->where('source', 'system')->count(),
            'with_role' => (clone $base)->whereNotNull('dish_role')->count(),
            'with_region' => (clone $base)->whereNotNull('culinary_regions')->count(),
            'with_kcal' => (clone $base)->whereNotNull('calories_kcal')->count(),
            'with_element' => (clone $base)->whereNotNull('five_element')->count(),
            'with_thermal' => (clone $base)->whereNotNull('thermal_nature')->count(),
            'with_recipe' => (clone $base)->where(function ($q): void {
                $q->whereNotNull('ingredients')->orWhereNotNull('steps');
            })->count(),
            'with_cooking_method' => (clone $base)->whereNotNull('cooking_method')->count(),
            'with_protein_source' => (clone $base)->whereNotNull('protein_source')->count(),
            'kb_hint' => $this->readKbVersionHint(),
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{slug: string, skipped_sensitive: int}
     */
    private function upsertDish(array $row, bool $allowUnverified, string $kbVersion): array
    {
        $name = trim((string) ($row['name'] ?? ''));
        $slug = trim((string) ($row['slug'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('Each dish requires a non-empty name.');
        }
        if ($slug === '') {
            $slug = Str::slug($name) ?: 'mon-an';
        }

        $facts = $row['facts'] ?? [];
        if (! is_array($facts)) {
            $facts = [];
        }
        $verifiedFields = $this->verifiedFieldMap($facts);

        $skipped = 0;
        $identity = [
            'name' => $name,
            'slug' => $slug,
            'emoji' => $this->nullableString($row['emoji'] ?? null),
            'summary' => $this->nullableString($row['summary'] ?? null),
            'meal_slots' => $this->requireMealSlots($row),
            'supports_light' => (bool) ($row['supports_light'] ?? false),
            'supports_main' => (bool) ($row['supports_main'] ?? true),
            'supports_dine_out' => (bool) ($row['supports_dine_out'] ?? true),
            'supports_cook_home' => (bool) ($row['supports_cook_home'] ?? true),
            'search_keywords' => $this->nullableString($row['search_keywords'] ?? null),
            'status' => DishStatus::Published,
            'source' => 'system',
            'notes' => $this->nullableString($row['notes'] ?? null),
        ];

        // Vùng miền = taxonomy vận hành (Group II). Accept culinary_regions or region_tags (inventory alias).
        // Null/omit → keep existing DB value on update.
        if (array_key_exists('culinary_regions', $row)) {
            $identity['culinary_regions'] = $this->normalizeCulinaryRegions($row['culinary_regions'], $slug);
        } elseif (array_key_exists('region_tags', $row)) {
            $identity['culinary_regions'] = $this->normalizeCulinaryRegions($row['region_tags'], $slug);
        }

        if (! $identity['supports_light'] && ! $identity['supports_main']) {
            throw new InvalidArgumentException("Dish [{$slug}] must support light and/or main.");
        }
        if (! $identity['supports_dine_out'] && ! $identity['supports_cook_home']) {
            throw new InvalidArgumentException("Dish [{$slug}] must support dine_out and/or cook_home.");
        }

        // Only verified non-null sensitive values are written.
        // Null / missing / unverified → omit on update (keep admin/UGC canonical cache per seed-KB §7).
        $sensitive = [];
        foreach (self::SENSITIVE_FIELDS as $field) {
            if (! array_key_exists($field, $row) || $row[$field] === null) {
                continue;
            }

            if (! $allowUnverified && ! isset($verifiedFields[$field])) {
                $skipped++;

                continue;
            }

            $sensitive[$field] = $this->castSensitive($field, $row[$field], $slug);
        }

        // Paired calorie basis: both or neither when seed attempts to set either.
        $hasKcal = array_key_exists('calories_kcal', $sensitive);
        $hasGrams = array_key_exists('serving_grams', $sensitive);
        if ($hasKcal xor $hasGrams) {
            unset($sensitive['calories_kcal'], $sensitive['serving_grams']);
            $skipped++;
        } elseif ($hasKcal && $hasGrams) {
            if ($sensitive['calories_kcal'] === null || $sensitive['serving_grams'] === null) {
                unset($sensitive['calories_kcal'], $sensitive['serving_grams']);
                $skipped++;
            }
        }

        $existing = Dish::withTrashed()->where('slug', $slug)->first();

        $acceptedFacts = array_values(array_filter(
            $facts,
            function ($f) use ($verifiedFields, $allowUnverified): bool {
                if (! is_array($f) || ! isset($f['field']) || ! is_string($f['field'])) {
                    return false;
                }
                if ($allowUnverified) {
                    return true;
                }

                return isset($verifiedFields[$f['field']]);
            },
        ));

        $prevMeta = is_array($existing?->facts_meta) ? $existing->facts_meta : [];
        $prevFacts = is_array($prevMeta['facts'] ?? null) ? $prevMeta['facts'] : [];
        $mergedFacts = $this->mergeFactEntries($prevFacts, $acceptedFacts, array_keys($sensitive));

        $factsMeta = [
            'kb_version' => $kbVersion,
            'facts' => $mergedFacts,
            'imported_at' => now()->toIso8601String(),
            'allow_unverified' => $allowUnverified,
        ];

        if ($existing) {
            if ($existing->trashed()) {
                $existing->restore();
            }
            // Identity always; sensitive only when seed has verified values (no null wipe).
            $existing->fill(array_merge($identity, $sensitive, ['facts_meta' => $factsMeta]));
            $existing->source = 'system';
            $existing->save();
        } else {
            $create = $identity;
            foreach (self::SENSITIVE_FIELDS as $field) {
                $create[$field] = $sensitive[$field] ?? null;
            }
            $create['facts_meta'] = $factsMeta;
            Dish::query()->create($create);
        }

        return ['slug' => $slug, 'skipped_sensitive' => $skipped];
    }

    /**
     * @param  list<mixed>  $facts
     * @return array<string, true>
     */
    private function verifiedFieldMap(array $facts): array
    {
        $map = [];
        foreach ($facts as $fact) {
            if (! is_array($fact)) {
                continue;
            }
            $field = $fact['field'] ?? null;
            if (! is_string($field) || $field === '') {
                continue;
            }
            $confidence = strtolower((string) ($fact['confidence'] ?? 'medium'));
            if ($confidence === 'low') {
                continue;
            }
            $method = strtolower(trim((string) ($fact['method'] ?? '')));
            if ($method === '' || ! in_array($method, self::ALLOWED_FACT_METHODS, true)) {
                continue;
            }
            // Prefer a concrete source when method is present (soft: method alone still accepted).
            $map[$field] = true;
            // calories pair: verifying one with serving often comes together
            if ($field === 'calories_kcal' && ! empty($fact['serving_grams'])) {
                $map['serving_grams'] = true;
            }
        }

        return $map;
    }

    /**
     * Keep previous provenance for fields not written this import; replace for fields written.
     *
     * @param  list<mixed>  $previous
     * @param  list<mixed>  $incoming
     * @param  list<string>  $updatedFields
     * @return list<array<string, mixed>>
     */
    private function mergeFactEntries(array $previous, array $incoming, array $updatedFields): array
    {
        $byField = [];

        foreach ($previous as $fact) {
            if (! is_array($fact) || ! isset($fact['field']) || ! is_string($fact['field'])) {
                continue;
            }
            if (in_array($fact['field'], $updatedFields, true)) {
                continue;
            }
            $byField[$fact['field']] = $fact;
        }

        foreach ($incoming as $fact) {
            if (! is_array($fact) || ! isset($fact['field']) || ! is_string($fact['field'])) {
                continue;
            }
            // Only store provenance for fields actually written (or all accepted on pure create with no prior).
            if ($updatedFields === [] || in_array($fact['field'], $updatedFields, true)) {
                $byField[$fact['field']] = $fact;
            }
        }

        return array_values($byField);
    }

    private function castSensitive(string $field, mixed $value, string $slug): mixed
    {
        return match ($field) {
            'calories_kcal', 'serving_grams', 'cook_minutes' => $value === null ? null : (int) $value,
            'five_element' => $this->enumValue(FiveElement::class, $value, $field, $slug),
            'thermal_nature' => $this->enumValue(ThermalNature::class, $value, $field, $slug),
            'protein_source' => $this->enumValue(ProteinSource::class, $value, $field, $slug),
            'cooking_method' => $this->enumValue(CookingMethod::class, $value, $field, $slug),
            'dish_role' => $this->enumValue(DishRole::class, $value, $field, $slug),
            'flavor_tags', 'ingredients', 'steps' => is_array($value) ? $value : null,
            'benefits', 'harms', 'advice' => $this->nullableString($value),
            default => $value,
        };
    }

    /**
     * @param  class-string<\BackedEnum>  $enumClass
     */
    private function enumValue(string $enumClass, mixed $value, string $field, string $slug): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $raw = is_string($value) ? $value : (string) $value;
        $enum = $enumClass::tryFrom($raw);
        if ($enum === null) {
            throw new InvalidArgumentException("Invalid {$field} [{$raw}] for dish [{$slug}].");
        }

        return $enum->value;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<string>
     */
    private function requireMealSlots(array $row): array
    {
        $slots = $row['meal_slots'] ?? null;
        if (! is_array($slots) || $slots === []) {
            throw new InvalidArgumentException('Each dish requires non-empty meal_slots.');
        }

        return array_values(array_map('strval', $slots));
    }

    /**
     * @return list<string>|null
     */
    private function normalizeCulinaryRegions(mixed $value, string $slug): ?array
    {
        if ($value === null) {
            return null;
        }
        if (! is_array($value)) {
            throw new InvalidArgumentException("culinary_regions for [{$slug}] must be an array or null.");
        }
        if ($value === []) {
            return null;
        }

        $out = [];
        foreach ($value as $item) {
            $raw = is_string($item) ? $item : (string) $item;
            $enum = CulinaryRegion::tryFrom($raw);
            if ($enum === null) {
                throw new InvalidArgumentException("Invalid culinary_region [{$raw}] for dish [{$slug}].");
            }
            $out[] = $enum->value;
        }

        return array_values(array_unique($out));
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $s = trim((string) $value);

        return $s === '' ? null : $s;
    }

    private function resolvePath(string $path): string
    {
        if (File::isFile($path)) {
            return $path;
        }

        return base_path($path);
    }

    private function readKbVersionHint(): ?string
    {
        foreach ([self::DEFAULT_MANIFEST, self::DEFAULT_DATASET] as $relative) {
            $path = base_path($relative);
            if (! File::isFile($path)) {
                continue;
            }
            try {
                /** @var array<string, mixed> $payload */
                $payload = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

                return isset($payload['kb_version']) ? (string) $payload['kb_version'] : null;
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }
}
