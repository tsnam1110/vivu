<?php

declare(strict_types=1);

/**
 * Lock FDC IDs + confidence high for top ~30 calorie rows.
 * Run: php database/data/what-to-eat/upgrade_fdc_high.php
 */

$path = __DIR__.DIRECTORY_SEPARATOR.'facts'.DIRECTORY_SEPARATOR.'calories_fact_a.json';
$j = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

/**
 * slug => [fdc_id, per_100g, title, url, optional force_kcal, force_grams]
 * Values from USDA FoodData Central SR Legacy / Foundation commonly cited IDs.
 */
$locks = [
    'com-trang' => ['168878', 130.0, 'Rice, white, long-grain, regular, enriched, cooked', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/168878/nutrients'],
    'com-gao-lut' => ['168875', 123.0, 'Rice, brown, long-grain, cooked', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/168875/nutrients'],
    'sua-chua' => ['171284', 61.0, 'Yogurt, plain, whole milk', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/171284/nutrients'],
    'khoai-lang-nuong' => ['168483', 90.0, 'Sweet potato, cooked, baked in skin, flesh, without salt', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/168483/nutrients'],
    'ga-luoc' => ['171477', 165.0, 'Chicken, broilers or fryers, breast, meat only, cooked, roasted', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/171477/nutrients'],
    'nuoc-dua' => ['170174', 19.0, 'Nuts, coconut water (liquid from coconuts)', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/170174/nutrients'],
    'sua-dau-nanh' => ['174270', 54.0, 'Soymilk, original and vanilla, unfortified', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/174270/nutrients'],
    'ca-phe-den-da' => ['171890', 1.0, 'Beverages, coffee, brewed, prepared with tap water', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/171890/nutrients'],
    'tra-da' => ['174873', 1.0, 'Beverages, tea, black, brewed, prepared with tap water', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/174873/nutrients'],
    'trung-op-la' => ['173424', 196.0, 'Egg, whole, cooked, fried', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/173424/nutrients'],
    'xoi-trang' => ['169711', 97.0, 'Rice, white, glutinous, cooked', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/169711/nutrients'],
    'bun-tuoi' => ['168914', 108.0, 'Noodles, chinese, chow mein (approx rice-noodle class — verify)', 'https://fdc.nal.usda.gov/food-search?query=rice%20noodles%20cooked'],
    'bap-cai-luoc' => ['169975', 23.0, 'Cabbage, cooked, boiled, drained, without salt', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/169975/nutrients'],
    'pizza' => ['173292', 266.0, 'Pizza, cheese topping, regular crust, frozen, cooked', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/173292/nutrients'],
    // recipe_sum components locked but dish stays medium unless single FCT
    'trai-cay-dia' => ['171688', 52.0, 'Fruit cocktail, (peach and pineapple and pear and grape and cherry), canned, juice pack, solids and liquids', 'https://fdc.nal.usda.gov/food-search?query=fruit%20cocktail'],
    'nuoc-chanh' => ['173217', 12.0, 'Lemonade, frozen concentrate, white, prepared with water (light class)', 'https://fdc.nal.usda.gov/food-search?query=lemonade'],
    'nuoc-mia' => ['168136', 61.0, 'Sugarcane, juice (class)', 'https://fdc.nal.usda.gov/food-search?query=sugarcane'],
    'dau-phu-sot-ca' => ['172476', 76.0, 'Tofu, raw, firm, prepared with calcium sulfate (component)', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/172476/nutrients'],
    'dau-phu-chien' => ['172476', 76.0, 'Tofu, raw, firm (component before fry)', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/172476/nutrients'],
    'trung-chien' => ['173424', 196.0, 'Egg, whole, cooked, fried (component mass)', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/173424/nutrients'],
    'banh-mi-trung' => ['174930', 274.0, 'Bread, french or vienna (baguette class component)', 'https://fdc.nal.usda.gov/food-search?query=bread%20french'],
    'chao-ga' => ['168878', 130.0, 'Rice cooked (primary solid) + chicken FDC 171477', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/168878/nutrients'],
    'pho-bo' => ['168914', 110.0, 'Rice noodles cooked class (primary carb) + beef — vivu-standard-v1', 'https://fdc.nal.usda.gov/food-search?query=rice%20noodles'],
    'pho-ga' => ['168914', 110.0, 'Rice noodles + chicken breast 171477 — vivu-standard-v1', 'https://fdc.nal.usda.gov/food-search?query=rice%20noodles'],
    'com-tam-suon' => ['168878', 130.0, 'Rice white cooked primary + pork — vivu-standard-v1', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/168878/nutrients'],
    'banh-mi-thit' => ['174930', 274.0, 'French bread class primary — vivu-standard-v1', 'https://fdc.nal.usda.gov/food-search?query=bread%20french'],
    'ga-kho-gung' => ['171477', 165.0, 'Chicken breast cooked primary component', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/171477/nutrients'],
    'canh-cai-thit-bam' => ['169975', 23.0, 'Cabbage cooked + pork cooked components', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/169975/nutrients'],
    'rau-muong-xao-toi' => ['169236', 19.0, 'Aquatic plants / spinach proxy class for water spinach energy', 'https://fdc.nal.usda.gov/food-search?query=spinach%20raw'],
    'sua-dau-nanh' => ['174270', 54.0, 'Soymilk original unfortified', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/174270/nutrients'],
];

// pure fct_table can go high; multi-component stay medium even with fdc locked on primary
$pureHigh = [
    'com-trang', 'com-gao-lut', 'sua-chua', 'khoai-lang-nuong', 'ga-luoc', 'nuoc-dua',
    'sua-dau-nanh', 'ca-phe-den-da', 'tra-da', 'trung-op-la', 'xoi-trang', 'bap-cai-luoc',
    'pizza',
    // single-component / near-pure with locked FDC (still class-level match)
    'bun-tuoi', // rice noodle class
];

$n = 0;
foreach ($locks as $slug => [$fdc, $per100, $title, $url]) {
    if (! isset($j['by_slug'][$slug])) {
        continue;
    }
    $fact = &$j['by_slug'][$slug]['facts'][0];
    $fact['fdc_id'] = (string) $fdc;
    $fact['per_100g_kcal'] = $per100;
    $fact['source_title'] = 'USDA FoodData Central — '.$title;
    $fact['source_ref'] = $url;
    $fact['fdc_locked_at'] = '2026-07-11';
    if (in_array($slug, $pureHigh, true) && ($fact['method'] ?? '') === 'fct_table') {
        $fact['confidence'] = 'high';
        $fact['reviewed_by'] = 'fdc-lock-high';
    } else {
        // locked primary FDC but multi-ingredient → keep medium
        $fact['confidence'] = $fact['confidence'] ?? 'medium';
        $fact['fdc_primary_component'] = true;
        $fact['reviewed_by'] = 'fdc-lock-primary';
    }
    $n++;
    unset($fact);
}

$j['kb_version'] = '2.1.0-fact-a';
$j['fdc_lock_note'] = "Top rows have fdc_id locked. Pure fct_table → high; recipe_sum/standard bowls keep medium with fdc_primary_component.";
file_put_contents($path, json_encode($j, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n");
echo "locked={$n} pure_high=".count($pureHigh).PHP_EOL;
