<?php

declare(strict_types=1);

/**
 * Merge Fact-A recipe_sum batch into calories_fact_a.json
 * Run: php database/data/what-to-eat/merge_fact_a_batch.php
 */

$path = __DIR__.DIRECTORY_SEPARATOR.'facts'.DIRECTORY_SEPARATOR.'calories_fact_a.json';
$payload = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

$batch = [
    'su-su-xao-toi' => [
        'calories_kcal' => 126,
        'serving_grams' => 150,
        'facts' => [[
            'field' => 'calories_kcal',
            'method' => 'recipe_sum',
            'source_title' => 'recipe_sum: chayote stir-fry + garlic oil (USDA chayote ~19/100 g; oil 884/100 g)',
            'source_ref' => 'https://fdc.nal.usda.gov/food-search?query=chayote',
            'serving_grams' => 150,
            'portion_note' => 'Chayote 200 g × 0.19 = 38; oil 10 g × 8.84 = 88; total 126 kcal; plated ~150 g',
            'ingredients_breakdown' => [
                ['name' => 'Chayote (su su), raw energy class', 'grams' => 200, 'kcal_per_100g' => 19, 'kcal' => 38],
                ['name' => 'Cooking oil', 'grams' => 10, 'kcal_per_100g' => 884, 'kcal' => 88],
            ],
            'confidence' => 'medium',
            'reviewed_at' => '2026-07-11',
            'reviewed_by' => 'fact-a-recipe-sum',
            'limitations' => 'Restaurant oil often higher; garlic negligible.',
        ]],
    ],
    'nam-xao-toi' => [
        'calories_kcal' => 128,
        'serving_grams' => 150,
        'facts' => [[
            'field' => 'calories_kcal',
            'method' => 'recipe_sum',
            'source_title' => 'recipe_sum: white mushrooms + oil stir-fry',
            'source_ref' => 'https://fdc.nal.usda.gov/food-search?query=mushrooms%20white%20raw',
            'serving_grams' => 150,
            'portion_note' => 'Mushroom 180 g × 0.22 ≈ 40; oil 10 g ≈ 88; total 128 kcal; plated ~150 g',
            'ingredients_breakdown' => [
                ['name' => 'White mushrooms, raw class', 'grams' => 180, 'kcal_per_100g' => 22, 'kcal' => 40],
                ['name' => 'Cooking oil', 'grams' => 10, 'kcal_per_100g' => 884, 'kcal' => 88],
            ],
            'confidence' => 'medium',
            'reviewed_at' => '2026-07-11',
            'reviewed_by' => 'fact-a-recipe-sum',
            'limitations' => 'Species mix slightly different; oil dominates variance.',
        ]],
    ],
    'salad-dua-leo-ca-chua' => [
        'calories_kcal' => 74,
        'serving_grams' => 185,
        'facts' => [[
            'field' => 'calories_kcal',
            'method' => 'recipe_sum',
            'source_title' => 'recipe_sum: cucumber + tomato + light oil dressing',
            'source_ref' => 'https://fdc.nal.usda.gov/food-search?query=cucumber%20raw',
            'serving_grams' => 185,
            'portion_note' => 'Cucumber 100×0.15=15; tomato 80×0.18=14; oil 5×8.84≈44; total ≈74 kcal for 185 g',
            'ingredients_breakdown' => [
                ['name' => 'Cucumber, raw', 'grams' => 100, 'kcal_per_100g' => 15, 'kcal' => 15],
                ['name' => 'Tomato, raw', 'grams' => 80, 'kcal_per_100g' => 18, 'kcal' => 14],
                ['name' => 'Cooking oil (dressing)', 'grams' => 5, 'kcal_per_100g' => 884, 'kcal' => 44],
            ],
            'confidence' => 'medium',
            'reviewed_at' => '2026-07-11',
            'reviewed_by' => 'fact-a-recipe-sum',
            'limitations' => 'No sugar/mayo modeled.',
        ]],
    ],
    'canh-bi-do' => [
        'calories_kcal' => 90,
        'serving_grams' => 320,
        'facts' => [[
            'field' => 'calories_kcal',
            'method' => 'recipe_sum',
            'source_title' => 'recipe_sum: pumpkin soup with shrimp (home bowl)',
            'source_ref' => 'https://fdc.nal.usda.gov/food-search?query=pumpkin%20cooked%20boiled',
            'serving_grams' => 320,
            'portion_note' => 'Pumpkin 120×0.20=24; shrimp 40×0.99≈40; oil 3×8.84≈27; water 157=0; total ≈90 kcal / 320 g',
            'ingredients_breakdown' => [
                ['name' => 'Pumpkin, boiled class', 'grams' => 120, 'kcal_per_100g' => 20, 'kcal' => 24],
                ['name' => 'Shrimp, cooked class', 'grams' => 40, 'kcal_per_100g' => 99, 'kcal' => 40],
                ['name' => 'Cooking oil / fat', 'grams' => 3, 'kcal_per_100g' => 884, 'kcal' => 27],
                ['name' => 'Broth water', 'grams' => 157, 'kcal_per_100g' => 0, 'kcal' => 0],
            ],
            'confidence' => 'medium',
            'reviewed_at' => '2026-07-11',
            'reviewed_by' => 'fact-a-recipe-sum',
            'limitations' => 'Coconut-milk bi do not included; pork variant higher.',
        ]],
    ],
    'canh-cai-thit-bam' => [
        'calories_kcal' => 142,
        'serving_grams' => 350,
        'facts' => [[
            'field' => 'calories_kcal',
            'method' => 'recipe_sum',
            'source_title' => 'recipe_sum: cabbage soup + cooked ground pork',
            'source_ref' => 'https://fdc.nal.usda.gov/food-search?query=cabbage%20cooked%20boiled',
            'serving_grams' => 350,
            'portion_note' => 'Cabbage 100×0.23=23; pork 40×2.97≈119; water 210=0; total 142 kcal / 350 g',
            'ingredients_breakdown' => [
                ['name' => 'Cabbage, boiled class', 'grams' => 100, 'kcal_per_100g' => 23, 'kcal' => 23],
                ['name' => 'Ground pork, cooked class', 'grams' => 40, 'kcal_per_100g' => 297, 'kcal' => 119],
                ['name' => 'Broth water', 'grams' => 210, 'kcal_per_100g' => 0, 'kcal' => 0],
            ],
            'confidence' => 'medium',
            'reviewed_at' => '2026-07-11',
            'reviewed_by' => 'fact-a-recipe-sum',
            'limitations' => 'Fatty pork raises kcal; no fried shallot.',
        ]],
    ],
    'canh-rau-ngot-thit-bam' => [
        'calories_kcal' => 137,
        'serving_grams' => 340,
        'facts' => [[
            'field' => 'calories_kcal',
            'method' => 'recipe_sum',
            'source_title' => 'recipe_sum: leafy green soup + ground pork (rau ngot ≈ spinach-class proxy)',
            'source_ref' => 'https://fdc.nal.usda.gov/food-search?query=spinach%20cooked',
            'serving_grams' => 340,
            'portion_note' => 'Leafy proxy 80×0.23≈18; pork 40×2.97≈119; water 220=0; total 137 kcal / 340 g',
            'ingredients_breakdown' => [
                ['name' => 'Leafy green proxy for rau ngot', 'grams' => 80, 'kcal_per_100g' => 23, 'kcal' => 18],
                ['name' => 'Ground pork, cooked class', 'grams' => 40, 'kcal_per_100g' => 297, 'kcal' => 119],
                ['name' => 'Broth water', 'grams' => 220, 'kcal_per_100g' => 0, 'kcal' => 0],
            ],
            'confidence' => 'medium',
            'reviewed_at' => '2026-07-11',
            'reviewed_by' => 'fact-a-recipe-sum',
            'limitations' => 'Sauropus not direct USDA row — leafy class proxy.',
        ]],
    ],
    'dau-phu-chien' => [
        'calories_kcal' => 220,
        'serving_grams' => 160,
        'facts' => [[
            'field' => 'calories_kcal',
            'method' => 'recipe_sum',
            'source_title' => 'recipe_sum: firm tofu + pan-fry oil absorption',
            'source_ref' => 'https://fdc.nal.usda.gov/food-search?query=tofu%20firm',
            'serving_grams' => 160,
            'portion_note' => 'Tofu 150×0.76=114; oil 12×8.84≈106; total 220 kcal; plated ~160 g',
            'ingredients_breakdown' => [
                ['name' => 'Firm tofu', 'grams' => 150, 'kcal_per_100g' => 76, 'kcal' => 114],
                ['name' => 'Cooking oil absorbed', 'grams' => 12, 'kcal_per_100g' => 884, 'kcal' => 106],
            ],
            'confidence' => 'medium',
            'reviewed_at' => '2026-07-11',
            'reviewed_by' => 'fact-a-recipe-sum',
            'limitations' => 'Deep-fry absorbs more oil.',
        ]],
    ],
    'trung-kho' => [
        'calories_kcal' => 230,
        'serving_grams' => 140,
        'facts' => [[
            'field' => 'calories_kcal',
            'method' => 'recipe_sum',
            'source_title' => 'recipe_sum: 2 hard-boiled eggs braised with sugar + small oil',
            'source_ref' => 'https://fdc.nal.usda.gov/food-search?query=egg%20hard%20boiled',
            'serving_grams' => 140,
            'portion_note' => 'Eggs 100×1.55=155; sugar 8×3.87≈31; oil 5×8.84≈44; total 230 kcal',
            'ingredients_breakdown' => [
                ['name' => 'Egg, hard-boiled class', 'grams' => 100, 'kcal_per_100g' => 155, 'kcal' => 155],
                ['name' => 'Granulated sugar (braise)', 'grams' => 8, 'kcal_per_100g' => 387, 'kcal' => 31],
                ['name' => 'Cooking oil / caramel fat', 'grams' => 5, 'kcal_per_100g' => 884, 'kcal' => 44],
            ],
            'confidence' => 'medium',
            'reviewed_at' => '2026-07-11',
            'reviewed_by' => 'fact-a-recipe-sum',
            'limitations' => 'No coconut water; sweet shop-style higher sugar.',
        ]],
    ],
    'chao-ga' => [
        'calories_kcal' => 213,
        'serving_grams' => 400,
        'facts' => [[
            'field' => 'calories_kcal',
            'method' => 'recipe_sum',
            'source_title' => 'recipe_sum: chicken congee — cooked rice + chicken + water',
            'source_ref' => 'https://fdc.nal.usda.gov/food-search?query=rice%20white%20cooked',
            'serving_grams' => 400,
            'portion_note' => 'Rice 100×1.30=130; chicken 50×1.65≈83; water 250=0; total 213 kcal / 400 g',
            'ingredients_breakdown' => [
                ['name' => 'White rice, cooked class', 'grams' => 100, 'kcal_per_100g' => 130, 'kcal' => 130],
                ['name' => 'Chicken breast meat, roasted class', 'grams' => 50, 'kcal_per_100g' => 165, 'kcal' => 83],
                ['name' => 'Cooking water', 'grams' => 250, 'kcal_per_100g' => 0, 'kcal' => 0],
            ],
            'confidence' => 'medium',
            'reviewed_at' => '2026-07-11',
            'reviewed_by' => 'fact-a-recipe-sum',
            'limitations' => 'Street cháo with skin/oil higher; aligns rice class with com-trang.',
        ]],
    ],
    'cha-trung-hap' => [
        'calories_kcal' => 215,
        'serving_grams' => 180,
        'facts' => [[
            'field' => 'calories_kcal',
            'method' => 'recipe_sum',
            'source_title' => 'recipe_sum: steamed egg cake — whole eggs + water (no meat filling)',
            'source_ref' => 'https://fdc.nal.usda.gov/food-search?query=egg%20whole%20raw',
            'serving_grams' => 180,
            'portion_note' => 'Raw egg 150×1.43≈215; water 30=0; steamed mass ~180 g',
            'ingredients_breakdown' => [
                ['name' => 'Egg, whole, raw class', 'grams' => 150, 'kcal_per_100g' => 143, 'kcal' => 215],
                ['name' => 'Water / light broth', 'grams' => 30, 'kcal_per_100g' => 0, 'kcal' => 0],
            ],
            'confidence' => 'medium',
            'reviewed_at' => '2026-07-11',
            'reviewed_by' => 'fact-a-recipe-sum',
            'limitations' => 'No ground pork/wood ear filling.',
        ]],
    ],
];

foreach ($batch as $slug => $row) {
    $payload['by_slug'][$slug] = $row;
}

$payload['kb_version'] = '1.2.0-fact-a';
$payload['description'] = 'Fact-A FCT + recipe_sum home plate builders (27 dishes). No complex street bowls. No TCM.';
$payload['rules'] = [
    'method: fct_table | recipe_sum',
    'kcal + serving_grams pair required',
    'recipe_sum needs ingredients_breakdown',
    'exclude pho/bun/com-tam/lau until frozen full recipes',
];

file_put_contents($path, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n");
echo 'by_slug count='.count($payload['by_slug']).PHP_EOL;
echo 'kb='.$payload['kb_version'].PHP_EOL;
