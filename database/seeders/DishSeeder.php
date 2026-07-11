<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\DishCatalogImporter;
use Illuminate\Database\Seeder;

/**
 * Import multi-file skeleton catalog + optional Fact-A calorie overlay.
 *
 * @see docs/features/what-to-eat-seed-and-kb.md
 * @see docs/features/what-to-eat-dish-catalog.md
 * @see database/data/what-to-eat/facts/calories_fact_a.json
 */
class DishSeeder extends Seeder
{
    public function run(): void
    {
        $importer = app(DishCatalogImporter::class);

        $result = $importer->importDefault(
            purgeMissing: true,
            allowUnverified: (bool) config('what_to_eat.seed_allow_unverified', false),
        );

        if ($this->command) {
            $this->command->info(sprintf(
                'Dish catalog kb=%s imported=%d purged=%d kcal=%s ops=%s recipes=%s yhct=%s',
                $result['kb_version'],
                $result['imported'],
                $result['purged'],
                (string) ($result['calorie_facts_applied'] ?? 0),
                (string) ($result['ops_facts_applied'] ?? 0),
                (string) ($result['recipe_text_applied'] ?? 0),
                (string) ($result['yhct_facts_applied'] ?? 0),
            ));
        }
    }
}
