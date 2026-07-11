<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\DishCatalogImporter;
use Illuminate\Console\Command;

class WhatToEatSeedReportCommand extends Command
{
    protected $signature = 'what-to-eat:seed-report';

    protected $description = 'Báo cáo độ phủ fact kho món (role, kcal, element, thermal, recipe)';

    public function handle(DishCatalogImporter $importer): int
    {
        $report = $importer->report();

        $this->info('What to Eat — seed / catalog report');
        $this->table(
            ['Metric', 'Count'],
            [
                ['total dishes', $report['total']],
                ['published', $report['published']],
                ['source=system', $report['system']],
                ['with dish_role', $report['with_role']],
                ['with culinary_regions', $report['with_region']],
                ['with calories_kcal', $report['with_kcal']],
                ['with five_element', $report['with_element']],
                ['with thermal_nature', $report['with_thermal']],
                ['with recipe (ingredients|steps)', $report['with_recipe']],
                ['with cooking_method', $report['with_cooking_method'] ?? 0],
                ['with protein_source', $report['with_protein_source'] ?? 0],
                ['dataset kb_version', $report['kb_hint'] ?? '—'],
                ['ruleset_version', (string) config('what_to_eat.ruleset_version')],
            ],
        );

        if ($report['published'] === 0) {
            $this->warn('Catalog published rỗng — gợi ý món sẽ empty cho đến khi bổ sung dataset verified.');
        }

        return self::SUCCESS;
    }
}
