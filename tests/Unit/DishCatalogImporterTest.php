<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\CulinaryRegion;
use App\Enums\DishRole;
use App\Enums\DishStatus;
use App\Models\Dish;
use App\Services\DishCatalogImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DishCatalogImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_dataset_purges_system_dishes(): void
    {
        Dish::factory()->create(['slug' => 'old-pho', 'source' => 'system', 'status' => DishStatus::Published]);
        Dish::factory()->create(['slug' => 'user-dish', 'source' => 'user', 'status' => DishStatus::Published]);

        $importer = app(DishCatalogImporter::class);
        $result = $importer->importPayload([
            'kb_version' => '1.0.0',
            'dishes' => [],
        ], purgeMissing: true);

        $this->assertSame(0, $result['imported']);
        $this->assertSame(1, $result['purged']);
        $this->assertNull(Dish::withTrashed()->where('slug', 'old-pho')->first());
        $this->assertNotNull(Dish::query()->where('slug', 'user-dish')->first());
    }

    public function test_sensitive_fields_without_provenance_are_null(): void
    {
        $importer = app(DishCatalogImporter::class);
        $importer->importPayload([
            'kb_version' => '1.0.0',
            'dishes' => [[
                'slug' => 'canh-rau',
                'name' => 'Canh rau',
                'meal_slots' => ['lunch'],
                'supports_light' => true,
                'supports_main' => false,
                'supports_dine_out' => true,
                'supports_cook_home' => true,
                'calories_kcal' => 200,
                'serving_grams' => 300,
                'five_element' => 'wood',
                'dish_role' => 'soup',
                'facts' => [],
            ]],
        ], purgeMissing: true, allowUnverified: false);

        $dish = Dish::query()->where('slug', 'canh-rau')->first();
        $this->assertNotNull($dish);
        $this->assertNull($dish->calories_kcal);
        $this->assertNull($dish->serving_grams);
        $this->assertNull($dish->five_element);
        $this->assertNull($dish->dish_role);
    }

    public function test_verified_facts_are_imported(): void
    {
        $importer = app(DishCatalogImporter::class);
        $importer->importPayload([
            'kb_version' => '1.0.0',
            'dishes' => [[
                'slug' => 'canh-rau-v',
                'name' => 'Canh rau verified',
                'meal_slots' => ['lunch', 'dinner'],
                'supports_light' => true,
                'supports_main' => false,
                'supports_dine_out' => true,
                'supports_cook_home' => true,
                'calories_kcal' => 80,
                'serving_grams' => 250,
                'dish_role' => DishRole::Soup->value,
                'facts' => [
                    [
                        'field' => 'calories_kcal',
                        'method' => 'recipe_sum',
                        'source_ref' => 'internal-test-ref',
                        'confidence' => 'high',
                        'serving_grams' => 250,
                    ],
                    [
                        'field' => 'dish_role',
                        'method' => 'committee',
                        'source_ref' => 'role-review-2026',
                        'confidence' => 'high',
                    ],
                ],
            ]],
        ], purgeMissing: true, allowUnverified: false);

        $dish = Dish::query()->where('slug', 'canh-rau-v')->first();
        $this->assertNotNull($dish);
        $this->assertSame(80, $dish->calories_kcal);
        $this->assertSame(250, $dish->serving_grams);
        $this->assertSame(DishRole::Soup, $dish->dish_role);
        $this->assertSame('1.0.0', $dish->facts_meta['kb_version'] ?? null);
    }

    public function test_report_counts(): void
    {
        Dish::factory()->create([
            'calories_kcal' => 100,
            'serving_grams' => 100,
            'dish_role' => DishRole::Soup,
            'status' => DishStatus::Published,
        ]);

        $report = app(DishCatalogImporter::class)->report();
        $this->assertSame(1, $report['total']);
        $this->assertSame(1, $report['with_kcal']);
        $this->assertSame(1, $report['with_role']);
    }

    public function test_null_seed_does_not_wipe_existing_sensitive_fields(): void
    {
        Dish::factory()->create([
            'slug' => 'pho-bo',
            'name' => 'Phở bò',
            'source' => 'system',
            'status' => DishStatus::Published,
            'calories_kcal' => 450,
            'serving_grams' => 500,
            'dish_role' => DishRole::OneBowl,
            'five_element' => null,
            'benefits' => 'Từ contribution đã duyệt',
        ]);

        $importer = app(DishCatalogImporter::class);
        $importer->importPayload([
            'kb_version' => '1.0.1',
            'dishes' => [[
                'slug' => 'pho-bo',
                'name' => 'Phở bò (skeleton)',
                'meal_slots' => ['breakfast', 'lunch'],
                'supports_light' => true,
                'supports_main' => true,
                'supports_dine_out' => true,
                'supports_cook_home' => true,
                'calories_kcal' => null,
                'serving_grams' => null,
                'dish_role' => null,
                'benefits' => null,
                'facts' => [],
            ]],
        ], purgeMissing: true, allowUnverified: false);

        $dish = Dish::query()->where('slug', 'pho-bo')->first();
        $this->assertNotNull($dish);
        $this->assertSame('Phở bò (skeleton)', $dish->name);
        $this->assertSame(450, $dish->calories_kcal);
        $this->assertSame(500, $dish->serving_grams);
        $this->assertSame(DishRole::OneBowl, $dish->dish_role);
        $this->assertSame('Từ contribution đã duyệt', $dish->benefits);
    }

    public function test_forbidden_fact_method_is_rejected(): void
    {
        $importer = app(DishCatalogImporter::class);
        $importer->importPayload([
            'kb_version' => '1.0.0',
            'dishes' => [[
                'slug' => 'guess-dish',
                'name' => 'Guess dish',
                'meal_slots' => ['lunch'],
                'supports_light' => false,
                'supports_main' => true,
                'supports_dine_out' => true,
                'supports_cook_home' => true,
                'calories_kcal' => 999,
                'serving_grams' => 100,
                'facts' => [[
                    'field' => 'calories_kcal',
                    'method' => 'chatgpt',
                    'source_ref' => 'n/a',
                    'confidence' => 'high',
                    'serving_grams' => 100,
                ]],
            ]],
        ], purgeMissing: true, allowUnverified: false);

        $dish = Dish::query()->where('slug', 'guess-dish')->first();
        $this->assertNotNull($dish);
        $this->assertNull($dish->calories_kcal);
        $this->assertNull($dish->serving_grams);
    }

    public function test_culinary_regions_import_without_provenance(): void
    {
        $importer = app(DishCatalogImporter::class);
        $importer->importPayload([
            'kb_version' => '1.0.0',
            'dishes' => [[
                'slug' => 'bun-bo-hue',
                'name' => 'Bún bò Huế',
                'meal_slots' => ['breakfast', 'lunch'],
                'supports_light' => false,
                'supports_main' => true,
                'supports_dine_out' => true,
                'supports_cook_home' => false,
                'culinary_regions' => [CulinaryRegion::Trung->value],
                'facts' => [],
            ]],
        ], purgeMissing: true, allowUnverified: false);

        $dish = Dish::query()->where('slug', 'bun-bo-hue')->first();
        $this->assertNotNull($dish);
        $this->assertSame([CulinaryRegion::Trung->value], $dish->culinary_regions);
        $this->assertTrue(
            Dish::query()->forCulinaryRegion(CulinaryRegion::Trung)->where('id', $dish->id)->exists(),
        );
    }

    public function test_omit_culinary_regions_keeps_existing(): void
    {
        Dish::factory()->create([
            'slug' => 'pho-bo',
            'source' => 'system',
            'status' => DishStatus::Published,
            'culinary_regions' => [CulinaryRegion::Bac->value, CulinaryRegion::QuocGia->value],
        ]);

        $importer = app(DishCatalogImporter::class);
        $importer->importPayload([
            'kb_version' => '1.0.1',
            'dishes' => [[
                'slug' => 'pho-bo',
                'name' => 'Phở bò',
                'meal_slots' => ['breakfast'],
                'supports_light' => true,
                'supports_main' => true,
                'supports_dine_out' => true,
                'supports_cook_home' => true,
                // culinary_regions omitted — keep existing
                'facts' => [],
            ]],
        ], purgeMissing: true, allowUnverified: false);

        $dish = Dish::query()->where('slug', 'pho-bo')->first();
        $this->assertSame(
            [CulinaryRegion::Bac->value, CulinaryRegion::QuocGia->value],
            $dish->culinary_regions,
        );
    }

    public function test_seed_p0_manifest_imports_skeleton_with_role_and_region(): void
    {
        $manifest = base_path(DishCatalogImporter::DEFAULT_MANIFEST);
        if (! is_file($manifest)) {
            $this->markTestSkipped('Seed-P0 manifest not present.');
        }

        $importer = app(DishCatalogImporter::class);
        $result = $importer->importFromManifest(
            DishCatalogImporter::DEFAULT_MANIFEST,
            purgeMissing: true,
            allowUnverified: false,
        );

        $this->assertGreaterThanOrEqual(170, $result['imported']);
        $this->assertSame(0, $result['skipped_sensitive']);

        $pho = Dish::query()->where('slug', 'pho-bo')->first();
        $this->assertNotNull($pho);
        $this->assertSame(DishRole::OneBowl, $pho->dish_role);
        $this->assertContains(CulinaryRegion::Bac->value, $pho->culinary_regions ?? []);
        $this->assertContains(CulinaryRegion::QuocGia->value, $pho->culinary_regions ?? []);
        $this->assertNull($pho->calories_kcal);
        $this->assertNull($pho->five_element);
        $this->assertNull($pho->thermal_nature);

        $soup = Dish::query()->where('slug', 'canh-chua-ca')->first();
        $this->assertNotNull($soup);
        $this->assertSame(DishRole::Soup, $soup->dish_role);

        // P1 / P2 / chay samples
        $this->assertNotNull(Dish::query()->where('slug', 'bun-mam')->first());
        $this->assertNotNull(Dish::query()->where('slug', 'cha-ca-la-vong')->first());
        $this->assertNotNull(Dish::query()->where('slug', 'lau-ga-la-e')->first());
        $chay = Dish::query()->where('slug', 'pho-chay')->first();
        $this->assertNotNull($chay);
        $this->assertSame(DishRole::OneBowl, $chay->dish_role);
        $this->assertContains('chay', $chay->flavor_tags ?? []);

        // P3 must NOT be in seed (quality gate)
        $this->assertNull(Dish::query()->where('slug', 'pho-chua')->first());
        $this->assertNull(Dish::query()->where('slug', 'de-nuong')->first());
    }

    public function test_calorie_facts_overlay_applies_verified_kcal(): void
    {
        $factsPath = base_path(DishCatalogImporter::DEFAULT_CALORIE_FACTS);
        if (! is_file($factsPath)) {
            $this->markTestSkipped('Fact-A overlay missing.');
        }

        // Minimal skeleton target
        Dish::factory()->create([
            'slug' => 'com-trang',
            'source' => 'system',
            'status' => DishStatus::Published,
            'calories_kcal' => null,
            'serving_grams' => null,
        ]);

        $overlay = app(DishCatalogImporter::class)->applyCalorieFactsOverlay(
            DishCatalogImporter::DEFAULT_CALORIE_FACTS,
            allowUnverified: false,
        );

        $this->assertGreaterThanOrEqual(1, $overlay['updated']);

        $rice = Dish::query()->where('slug', 'com-trang')->first();
        $this->assertNotNull($rice);
        // FCT VN dry rice 1004 ÷ vivu-yield-v1 2.5 → ~137.6 kcal/100g cooked × 150g ≈ 206
        $this->assertSame(206, $rice->calories_kcal);
        $this->assertSame(150, $rice->serving_grams);
        $this->assertNotEmpty($rice->facts_meta['facts'] ?? []);

        $source = $rice->calorieSourceSummary();
        $this->assertNotNull($source);
        // Yield-derived cooked rice is recipe_sum (not a single FCT cooked row)
        $this->assertContains($source['method'], ['fct_table', 'recipe_sum']);
        $fact = $rice->facts_meta['facts'][0] ?? [];
        $this->assertSame('vn_2007', $fact['fct_source'] ?? null);
    }
}
