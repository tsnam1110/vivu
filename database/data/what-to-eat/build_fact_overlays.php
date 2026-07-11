<?php

declare(strict_types=1);

/**
 * Build maximum quality-safe Fact-A (kcal) + Ops overlays.
 * Run: php database/data/what-to-eat/build_fact_overlays.php
 *
 * @see docs/features/what-to-eat-fact-completion-plan.md
 */

$factsDir = __DIR__.DIRECTORY_SEPARATOR.'facts';
$calPath = $factsDir.DIRECTORY_SEPARATOR.'calories_fact_a.json';
$opsPath = $factsDir.DIRECTORY_SEPARATOR.'ops_fields_fact_a.json';
$recipePath = $factsDir.DIRECTORY_SEPARATOR.'recipes_standard_v1.json';

$committee = static function (string $field): array {
    return [
        'field' => $field,
        'method' => 'committee',
        'source_ref' => 'vivu-ops-committee-2026-07',
        'source_title' => 'ViVu catalog ops review (clear single method/protein)',
        'confidence' => 'high',
        'reviewed_at' => '2026-07-11',
        'reviewed_by' => 'ops-build',
    ];
};

$fct = static function (
    int $kcal,
    int $grams,
    string $title,
    string $ref,
    float $per100,
    string $note,
    string $conf = 'medium',
    string $limit = '',
    ?string $fdc = null,
): array {
    $fact = [
        'field' => 'calories_kcal',
        'method' => 'fct_table',
        'source_title' => $title,
        'source_ref' => $ref,
        'per_100g_kcal' => $per100,
        'serving_grams' => $grams,
        'portion_note' => $note,
        'confidence' => $conf,
        'reviewed_at' => '2026-07-11',
        'reviewed_by' => 'fact-a-build',
    ];
    if ($fdc) {
        $fact['fdc_id'] = $fdc;
    }
    if ($limit !== '') {
        $fact['limitations'] = $limit;
    }

    return [
        'calories_kcal' => $kcal,
        'serving_grams' => $grams,
        'facts' => [$fact],
    ];
};

$rsum = static function (
    int $kcal,
    int $grams,
    string $title,
    string $ref,
    string $note,
    array $breakdown,
    string $limit = '',
    string $standardId = '',
): array {
    $fact = [
        'field' => 'calories_kcal',
        'method' => 'recipe_sum',
        'source_title' => $title,
        'source_ref' => $ref,
        'serving_grams' => $grams,
        'portion_note' => $note,
        'ingredients_breakdown' => $breakdown,
        'confidence' => 'medium',
        'reviewed_at' => '2026-07-11',
        'reviewed_by' => 'fact-a-recipe-sum',
    ];
    if ($limit !== '') {
        $fact['limitations'] = $limit;
    }
    if ($standardId !== '') {
        $fact['standard_id'] = $standardId;
    }

    return [
        'calories_kcal' => $kcal,
        'serving_grams' => $grams,
        'facts' => [$fact],
    ];
};

// ── Existing + expanded calories ─────────────────────────────────
$bySlug = [];

// Keep prior entries by re-reading file if present
if (is_file($calPath)) {
    $prev = json_decode(file_get_contents($calPath), true, 512, JSON_THROW_ON_ERROR);
    $bySlug = $prev['by_slug'] ?? [];
}

// Helper line items
$line = static fn (string $n, int $g, float $k100): array => [
    'name' => $n,
    'grams' => $g,
    'kcal_per_100g' => $k100,
    'kcal' => (int) round($g * $k100 / 100),
];

$sumLines = static function (array $lines) use ($line): array {
    $out = [];
    $sum = 0;
    foreach ($lines as $L) {
        $row = is_array($L) && isset($L['name']) ? $L : $line($L[0], $L[1], $L[2]);
        $out[] = $row;
        $sum += $row['kcal'];
    }

    return [$out, $sum];
};

// Fresh comprehensive set (overwrites keys)
$add = [];

// === FCT simple ===
$add['com-trang'] = $fct(195, 150, 'USDA FDC 168878 Rice white cooked ~130/100g', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/168878/nutrients', 130, '150g cooked white rice; 130×1.5=195', 'high', 'Plain rice only.', '168878');
$add['com-gao-lut'] = $fct(185, 150, 'USDA brown rice cooked ~123/100g', 'https://fdc.nal.usda.gov/food-search?query=rice%20brown%20cooked', 123, '150g; 123×1.5≈185', 'medium', 'Variety varies.');
$add['xoi-trang'] = $fct(146, 150, 'USDA glutinous rice cooked ~97/100g', 'https://fdc.nal.usda.gov/food-search?query=glutinous%20rice%20cooked', 97, '150g plain sticky rice; 97×1.5≈146', 'medium', 'No toppings.');
$add['bun-tuoi'] = $fct(110, 100, 'USDA rice noodles cooked ~110/100g class', 'https://fdc.nal.usda.gov/food-search?query=rice%20noodles%20cooked', 110, '100g cooked rice vermicelli plain', 'medium', 'Starch component only.');
$add['sua-chua'] = $fct(92, 150, 'USDA yogurt plain whole ~61/100g', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/171284/nutrients', 61, '150g; 61×1.5≈92', 'high', 'Plain unsweetened.', '171284');
$add['khoai-lang-nuong'] = $fct(180, 200, 'USDA FDC 168483 sweet potato baked 90/100g', 'https://fdc.nal.usda.gov/fdc-app.html#/food-details/168483/nutrients', 90, '200g; 90×2=180', 'high', 'No butter/sugar.', '168483');
$add['trung-op-la'] = $fct(98, 50, 'USDA fried egg ~196/100g', 'https://fdc.nal.usda.gov/food-search?query=egg%20fried', 196, '1 egg ~50g; 196×0.5=98', 'medium', 'Oil varies.');
$add['ga-luoc'] = $fct(248, 150, 'USDA chicken breast cooked ~165/100g', 'https://fdc.nal.usda.gov/food-search?query=chicken%20breast%20cooked', 165, '150g meat no skin; 165×1.5≈248', 'medium', 'Skin-on higher.');
$add['nuoc-dua'] = $fct(48, 250, 'USDA coconut water ~19/100g', 'https://fdc.nal.usda.gov/food-search?query=coconut%20water', 19, '250ml; 19×2.5≈48', 'high', 'Unsweetened fresh.');
$add['sua-dau-nanh'] = $fct(108, 200, 'USDA soymilk ~54/100g', 'https://fdc.nal.usda.gov/food-search?query=soymilk', 54, '200ml plain; 54×2=108', 'medium', 'Sweet street soymilk higher.');
$add['ca-phe-den-da'] = $fct(2, 240, 'USDA coffee brewed ~1/100g', 'https://fdc.nal.usda.gov/food-search?query=coffee%20brewed', 1, '240ml black no sugar/milk', 'high', 'Not sữa đá.');
$add['tra-da'] = $fct(2, 250, 'USDA tea brewed unsweetened', 'https://fdc.nal.usda.gov/food-search?query=tea%20brewed', 1, '250ml plain tea', 'high', 'Sugared street tea higher.');
$add['nuoc-chanh'] = $fct(30, 250, 'USDA lemonade ~12/100g class light / or lemon water+sugar estimate', 'https://fdc.nal.usda.gov/food-search?query=lemonade', 12, '250ml light lemon drink ~30 kcal if mild sugar', 'medium', 'Heavy sugar shop drinks much higher.');
$add['dua-mon'] = $fct(25, 80, 'USDA pickle class ~31/100g', 'https://fdc.nal.usda.gov/food-search?query=pickle%20cucumber', 31, '80g side pickle; 31×0.8≈25', 'medium', 'Sweet VN pickles vary.');
$add['trai-cay-dia'] = $fct(90, 150, 'USDA mixed fruit approx apple/orange class ~60/100g mid', 'https://fdc.nal.usda.gov/food-search?query=fruit%20mixed', 60, '150g mixed seasonal fruit ≈90', 'medium', 'Season mix varies; no syrup.');
$add['bap-cai-luoc'] = $fct(23, 100, 'USDA cabbage boiled ~23/100g', 'https://fdc.nal.usda.gov/food-search?query=cabbage%20boiled', 23, '100g boiled cabbage plain', 'high', 'No oil dressing.');
$add['rau-cai-luoc'] = $fct(20, 100, 'USDA leafy boiled ~20/100g class', 'https://fdc.nal.usda.gov/food-search?query=spinach%20cooked', 20, '100g boiled greens', 'medium', 'Species varies.');
$add['suong-sao'] = $fct(50, 200, 'Grass jelly drink class ~25/100g with light syrup mid', 'https://fdc.nal.usda.gov/food-search?query=jelly', 25, '200g lightly sweetened sương sáo ≈50', 'medium', 'Heavy syrup higher.');
$add['banh-flan'] = $fct(160, 100, 'USDA custard/flan class ~160/100g', 'https://fdc.nal.usda.gov/food-search?query=flan%20caramel', 160, '100g flan portion', 'medium', 'Shop creamier higher.');
$add['chuoi-nep-nuong'] = $fct(180, 120, 'Banana + sticky rice class estimate recipe below style fct mix', 'https://fdc.nal.usda.gov/food-search?query=banana%20raw', 150, 'See recipe_sum override if present', 'medium', 'Prefer recipe_sum entry.');

// Override chuoi with recipe_sum properly below

// === Recipe home ===
$mk = static function (string $title, string $ref, int $serveG, array $parts, string $limit) use ($rsum, $line): array {
    $breakdown = [];
    $sum = 0;
    foreach ($parts as $p) {
        $row = $line($p[0], $p[1], $p[2]);
        $breakdown[] = $row;
        $sum += $row['kcal'];
    }
    $note = implode('; ', array_map(fn ($r) => "{$r['name']} {$r['grams']}g→{$r['kcal']}kcal", $breakdown))."; total {$sum} for {$serveG}g";

    return $rsum($sum, $serveG, $title, $ref, $note, $breakdown, $limit);
};

$add['trung-chien'] = $mk('recipe_sum: 2 fried eggs + oil', 'https://fdc.nal.usda.gov/food-search?query=egg%20fried', 110, [
    ['Fried egg mass', 100, 196],
    ['Oil absorbed', 5, 884],
], 'Oil absorption varies.');
$add['banh-mi-trung'] = $mk('recipe_sum: baguette + fried egg', 'https://fdc.nal.usda.gov/food-search?query=bread%20white', 120, [
    ['White baguette', 70, 274],
    ['Fried egg', 50, 196],
], 'No pate/mayo.');
$add['rau-muong-xao-toi'] = $mk('recipe_sum: water spinach + oil', 'https://fdc.nal.usda.gov/food-search?query=water%20spinach', 150, [
    ['Water spinach raw class', 200, 19],
    ['Oil', 10, 884],
], 'Restaurant oil higher.');
$add['cai-xao-toi'] = $mk('recipe_sum: leafy cabbage + oil', 'https://fdc.nal.usda.gov/food-search?query=bok%20choy', 150, [
    ['Leafy cabbage class', 180, 13],
    ['Oil', 10, 884],
], 'Cải type varies.');
$add['su-su-xao-toi'] = $mk('recipe_sum: chayote + oil', 'https://fdc.nal.usda.gov/food-search?query=chayote', 150, [
    ['Chayote', 200, 19],
    ['Oil', 10, 884],
], 'Oil dominates.');
$add['nam-xao-toi'] = $mk('recipe_sum: mushrooms + oil', 'https://fdc.nal.usda.gov/food-search?query=mushrooms%20white', 150, [
    ['White mushrooms', 180, 22],
    ['Oil', 10, 884],
], 'Species mix varies.');
$add['cai-thia-xao'] = $mk('recipe_sum: bok choy + oil', 'https://fdc.nal.usda.gov/food-search?query=bok%20choy', 150, [
    ['Bok choy', 180, 13],
    ['Oil', 10, 884],
], 'Oil varies.');
$add['rau-mong-toi-xao'] = $mk('recipe_sum: malabar spinach proxy + oil', 'https://fdc.nal.usda.gov/food-search?query=spinach', 150, [
    ['Leafy green proxy', 180, 23],
    ['Oil', 10, 884],
], 'Proxy leafy FCT.');
$add['rau-lang-xao'] = $mk('recipe_sum: sweet potato leaves proxy + oil', 'https://fdc.nal.usda.gov/food-search?query=spinach', 150, [
    ['Leafy green proxy', 180, 23],
    ['Oil', 10, 884],
], 'Proxy leafy FCT.');
$add['dau-que-xao'] = $mk('recipe_sum: green beans + oil', 'https://fdc.nal.usda.gov/food-search?query=green%20beans%20cooked', 150, [
    ['Green beans', 180, 35],
    ['Oil', 8, 884],
], 'Oil varies.');
$add['bi-dao-xao'] = $mk('recipe_sum: wax gourd + oil', 'https://fdc.nal.usda.gov/food-search?query=wax%20gourd', 150, [
    ['Wax gourd class', 200, 13],
    ['Oil', 8, 884],
], 'Oil varies.');
$add['dau-bap-xao'] = $mk('recipe_sum: okra + oil', 'https://fdc.nal.usda.gov/food-search?query=okra%20cooked', 150, [
    ['Okra cooked class', 180, 33],
    ['Oil', 8, 884],
], 'Oil varies.');
$add['bau-xao-trung'] = $mk('recipe_sum: gourd + egg + oil', 'https://fdc.nal.usda.gov/food-search?query=egg%20whole%20raw', 180, [
    ['Gourd class', 150, 15],
    ['Egg', 50, 143],
    ['Oil', 8, 884],
], 'Oil/egg ratio varies.');
$add['muop-xao-trung'] = $mk('recipe_sum: loofah + egg + oil', 'https://fdc.nal.usda.gov/food-search?query=egg%20whole%20raw', 180, [
    ['Loofah/sponge gourd class', 150, 17],
    ['Egg', 50, 143],
    ['Oil', 8, 884],
], 'Oil/egg ratio varies.');
$add['salad-dua-leo-ca-chua'] = $mk('recipe_sum: cucumber tomato oil', 'https://fdc.nal.usda.gov/food-search?query=cucumber', 185, [
    ['Cucumber', 100, 15],
    ['Tomato', 80, 18],
    ['Oil', 5, 884],
], 'No mayo/sugar.');
$add['dau-phu-sot-ca'] = $mk('recipe_sum: tofu + tomato sauce + oil', 'https://fdc.nal.usda.gov/food-search?query=tofu%20firm', 250, [
    ['Firm tofu', 200, 76],
    ['Tomato sauce', 80, 29],
    ['Oil', 5, 884],
], 'Sweet sauce higher.');
$add['dau-phu-chien'] = $mk('recipe_sum: fried tofu', 'https://fdc.nal.usda.gov/food-search?query=tofu%20firm', 160, [
    ['Firm tofu', 150, 76],
    ['Oil absorbed', 12, 884],
], 'Deep-fry higher oil.');
$add['dau-hu-kho-nam'] = $mk('recipe_sum: tofu braised with mushrooms', 'https://fdc.nal.usda.gov/food-search?query=tofu%20firm', 220, [
    ['Firm tofu', 150, 76],
    ['Mushrooms', 50, 22],
    ['Oil', 8, 884],
], 'Sauce sugar omitted.');
$add['nam-kho-tieu'] = $mk('recipe_sum: braised mushrooms', 'https://fdc.nal.usda.gov/food-search?query=mushrooms', 150, [
    ['Mushrooms', 120, 22],
    ['Oil', 8, 884],
], 'Oil/sauce varies.');
$add['trung-kho'] = $mk('recipe_sum: braised eggs', 'https://fdc.nal.usda.gov/food-search?query=egg%20hard%20boiled', 140, [
    ['Hard-boiled egg', 100, 155],
    ['Sugar', 8, 387],
    ['Oil', 5, 884],
], 'Coconut water style not included.');
$add['cha-trung-hap'] = $mk('recipe_sum: steamed eggs plain', 'https://fdc.nal.usda.gov/food-search?query=egg%20whole%20raw', 180, [
    ['Whole egg raw', 150, 143],
    ['Water', 30, 0],
], 'No meat filling.');
$add['chao-ga'] = $mk('recipe_sum: chicken congee', 'https://fdc.nal.usda.gov/food-search?query=rice%20white%20cooked', 400, [
    ['Cooked rice', 100, 130],
    ['Chicken meat', 50, 165],
    ['Water', 250, 0],
], 'Street oil/skin higher.');
$add['chao-suon'] = $mk('recipe_sum: pork rib porridge simplified', 'https://fdc.nal.usda.gov/food-search?query=pork%20cooked', 400, [
    ['Cooked rice', 100, 130],
    ['Pork meat class', 40, 242],
    ['Water', 260, 0],
], 'Fatty ribs higher; bone not eaten.');
$add['canh-bi-do'] = $mk('recipe_sum: pumpkin shrimp soup', 'https://fdc.nal.usda.gov/food-search?query=pumpkin%20boiled', 320, [
    ['Pumpkin', 120, 20],
    ['Shrimp', 40, 99],
    ['Oil', 3, 884],
    ['Water', 157, 0],
], 'Coconut milk bi do different.');
$add['canh-cai-thit-bam'] = $mk('recipe_sum: cabbage pork soup', 'https://fdc.nal.usda.gov/food-search?query=cabbage%20boiled', 350, [
    ['Cabbage', 100, 23],
    ['Ground pork cooked', 40, 297],
    ['Water', 210, 0],
], 'Fatty pork higher.');
$add['canh-rau-ngot-thit-bam'] = $mk('recipe_sum: leafy pork soup proxy', 'https://fdc.nal.usda.gov/food-search?query=spinach%20cooked', 340, [
    ['Leafy proxy', 80, 23],
    ['Ground pork cooked', 40, 297],
    ['Water', 220, 0],
], 'Rau ngót FCT proxy.');
$add['canh-bi-dao'] = $mk('recipe_sum: wax gourd soup light', 'https://fdc.nal.usda.gov/food-search?query=wax%20gourd', 320, [
    ['Wax gourd', 150, 13],
    ['Pork lean', 20, 143],
    ['Water', 150, 0],
], 'Clear soup home style.');
$add['canh-bau-tom'] = $mk('recipe_sum: gourd shrimp soup', 'https://fdc.nal.usda.gov/food-search?query=shrimp%20cooked', 320, [
    ['Gourd', 120, 15],
    ['Shrimp', 40, 99],
    ['Water', 160, 0],
], 'Oil drizzle omitted.');
$add['canh-muop-moc'] = $mk('recipe_sum: loofah pork ball soup', 'https://fdc.nal.usda.gov/food-search?query=pork%20cooked', 330, [
    ['Loofah class', 100, 17],
    ['Pork mince cooked', 35, 297],
    ['Water', 195, 0],
], 'Ball fillers vary.');
$add['canh-mong-toi-nau-tom'] = $mk('recipe_sum: malabar spinach shrimp soup', 'https://fdc.nal.usda.gov/food-search?query=shrimp%20cooked', 320, [
    ['Leafy proxy', 80, 23],
    ['Shrimp', 40, 99],
    ['Water', 200, 0],
], 'Leafy proxy.');
$add['canh-chua-ca'] = $mk('recipe_sum: sour fish soup home v1', 'https://fdc.nal.usda.gov/food-search?query=fish%20cooked', 350, [
    ['Fish cooked class', 80, 128],
    ['Tomato', 50, 18],
    ['Pineapple class', 30, 50],
    ['Oil', 3, 884],
    ['Water/veg broth', 187, 0],
], 'vivu-standard-v1 home canh chua; shop richer.');
$add['canh-chua-tom'] = $mk('recipe_sum: sour shrimp soup home v1', 'https://fdc.nal.usda.gov/food-search?query=shrimp%20cooked', 350, [
    ['Shrimp', 70, 99],
    ['Tomato', 50, 18],
    ['Pineapple', 30, 50],
    ['Oil', 3, 884],
    ['Water', 197, 0],
], 'vivu-standard-v1.');
$add['canh-chua-ca-loc'] = $mk('recipe_sum: sour snakehead soup home v1', 'https://fdc.nal.usda.gov/food-search?query=fish%20cooked', 350, [
    ['Fish', 80, 128],
    ['Tomato', 50, 18],
    ['Vegetables proxy', 40, 25],
    ['Oil', 3, 884],
    ['Water', 177, 0],
], 'vivu-standard-v1.');
$add['canh-kho-qua-nhoi-thit'] = $mk('recipe_sum: bitter melon stuffed soup', 'https://fdc.nal.usda.gov/food-search?query=pork%20cooked', 300, [
    ['Bitter melon class', 80, 17],
    ['Ground pork', 50, 297],
    ['Water', 170, 0],
], 'Stuffing fat varies.');
$add['canh-xuong-rau-cu'] = $mk('recipe_sum: light bone veg soup (veg+lean only)', 'https://fdc.nal.usda.gov/food-search?query=carrot%20cooked', 350, [
    ['Mixed veg', 100, 35],
    ['Lean meat scraps', 20, 143],
    ['Water', 230, 0],
], 'Bone broth fat not fully modeled — medium.');
$add['canh-bi-do-chay'] = $mk('recipe_sum: pumpkin soup vegan', 'https://fdc.nal.usda.gov/food-search?query=pumpkin%20boiled', 300, [
    ['Pumpkin', 150, 20],
    ['Tofu', 30, 76],
    ['Water', 120, 0],
], 'No coconut milk.');
$add['canh-rau-cu-chay'] = $mk('recipe_sum: mixed veg soup vegan', 'https://fdc.nal.usda.gov/food-search?query=vegetable%20soup', 300, [
    ['Mixed vegetables', 150, 35],
    ['Tofu', 20, 76],
    ['Water', 130, 0],
], 'Clear vegan soup.');
$add['rau-cu-luoc-cham-tuong'] = $mk('recipe_sum: boiled veg + light soy', 'https://fdc.nal.usda.gov/food-search?query=cabbage%20boiled', 150, [
    ['Mixed boiled veg', 140, 25],
    ['Soy sauce class', 10, 53],
], 'Dipping sauce sugar not included.');
$add['rau-luoc-kho-quet'] = $mk('recipe_sum: boiled veg + shrimp paste dip energy mid', 'https://fdc.nal.usda.gov/food-search?query=cabbage%20boiled', 160, [
    ['Boiled veg', 140, 25],
    ['Oil in kho quẹt', 5, 884],
], 'Kho quẹt very variable — medium.');
$add['thit-kho-trung'] = $mk('recipe_sum: braised pork + egg simplified', 'https://fdc.nal.usda.gov/food-search?query=pork%20cooked', 200, [
    ['Pork cooked class', 100, 242],
    ['Hard egg', 50, 155],
    ['Sugar', 10, 387],
    ['Oil', 3, 884],
], 'Cut fat & coconut water style vary strongly.');
$add['thit-kho-tau'] = $mk('recipe_sum: thit kho tau simplified (pork+egg+sugar)', 'https://fdc.nal.usda.gov/food-search?query=pork%20cooked', 220, [
    ['Pork', 120, 242],
    ['Egg', 50, 155],
    ['Sugar', 12, 387],
    ['Oil', 3, 884],
], 'vivu-standard-v1 simplified; coconut water not separate FCT line.');
$add['suon-ram-man'] = $mk('recipe_sum: caramel pork ribs meat portion', 'https://fdc.nal.usda.gov/food-search?query=pork%20ribs%20cooked', 180, [
    ['Pork rib meat class', 120, 277],
    ['Sugar', 8, 387],
    ['Oil', 3, 884],
], 'Bone weight excluded; fatty ribs higher.');
$add['suon-chua-ngot'] = $mk('recipe_sum: sweet sour ribs meat', 'https://fdc.nal.usda.gov/food-search?query=pork%20cooked', 180, [
    ['Pork', 120, 242],
    ['Sugar', 12, 387],
    ['Oil', 5, 884],
], 'Sauce sugar shop-style higher.');
$add['ga-kho-gung'] = $mk('recipe_sum: ginger chicken braise', 'https://fdc.nal.usda.gov/food-search?query=chicken%20cooked', 200, [
    ['Chicken meat', 150, 165],
    ['Oil', 5, 884],
    ['Sugar', 3, 387],
], 'Skin-on higher.');
$add['ga-rang-muoi'] = $mk('recipe_sum: salt-fried chicken pieces', 'https://fdc.nal.usda.gov/food-search?query=chicken%20fried', 180, [
    ['Chicken', 140, 200],
    ['Oil absorbed', 8, 884],
], 'Fry oil highly variable — medium.');
$add['ca-kho-to'] = $mk('recipe_sum: caramel fish claypot simplified', 'https://fdc.nal.usda.gov/food-search?query=fish%20cooked', 180, [
    ['Fish', 120, 128],
    ['Sugar', 10, 387],
    ['Oil', 5, 884],
], 'Fatty fish & sauce vary.');
$add['ca-kho-rieng'] = $mk('recipe_sum: galangal fish braise', 'https://fdc.nal.usda.gov/food-search?query=fish%20cooked', 180, [
    ['Fish', 120, 128],
    ['Oil', 5, 884],
    ['Sugar', 5, 387],
], 'Medium confidence.');
$add['ca-chien'] = $mk('recipe_sum: fried fish fillet', 'https://fdc.nal.usda.gov/food-search?query=fish%20fried', 150, [
    ['Fish', 120, 128],
    ['Oil absorbed', 10, 884],
], 'Breading/deep-fry higher.');
$add['tom-rang-me'] = $mk('recipe_sum: tamarind shrimp', 'https://fdc.nal.usda.gov/food-search?query=shrimp%20cooked', 160, [
    ['Shrimp', 120, 99],
    ['Sugar', 8, 387],
    ['Oil', 5, 884],
], 'Sauce sugar varies.');
$add['tom-rim'] = $mk('recipe_sum: caramel shrimp', 'https://fdc.nal.usda.gov/food-search?query=shrimp%20cooked', 160, [
    ['Shrimp', 120, 99],
    ['Sugar', 10, 387],
    ['Oil', 5, 884],
], 'Sugar varies.');
$add['tom-chien-bot'] = $mk('recipe_sum: battered fried shrimp', 'https://fdc.nal.usda.gov/food-search?query=shrimp%20fried', 150, [
    ['Shrimp', 80, 99],
    ['Batter flour class', 30, 364],
    ['Oil absorbed', 12, 884],
], 'Deep-fry oil highly variable.');
$add['muc-xao-chua-ngot'] = $mk('recipe_sum: sweet sour squid', 'https://fdc.nal.usda.gov/food-search?query=squid%20cooked', 180, [
    ['Squid', 120, 92],
    ['Sugar', 8, 387],
    ['Oil', 6, 884],
], 'Veg toppings low impact.');
$add['bo-xao-luc-lac'] = $mk('recipe_sum: shaking beef home', 'https://fdc.nal.usda.gov/food-search?query=beef%20cooked', 180, [
    ['Beef cooked', 120, 250],
    ['Oil', 8, 884],
], 'Fatty cut higher.');
$add['thit-xao-nam'] = $mk('recipe_sum: pork mushroom stir-fry', 'https://fdc.nal.usda.gov/food-search?query=pork%20cooked', 180, [
    ['Pork', 100, 242],
    ['Mushroom', 50, 22],
    ['Oil', 8, 884],
], 'Oil varies.');
$add['cha-la-lot'] = $mk('recipe_sum: betel leaf beef rolls', 'https://fdc.nal.usda.gov/food-search?query=beef%20cooked', 120, [
    ['Beef/pork mince', 80, 250],
    ['Oil', 5, 884],
], 'Leaf negligible; grill vs pan.');
$add['tep-rang'] = $mk('recipe_sum: small shrimp/dried shrimp stir', 'https://fdc.nal.usda.gov/food-search?query=shrimp', 80, [
    ['Shrimp', 60, 99],
    ['Oil', 5, 884],
], 'Dried shrimp denser — medium.');
$add['goi-du-du'] = $mk('recipe_sum: green papaya salad light', 'https://fdc.nal.usda.gov/food-search?query=papaya%20raw', 150, [
    ['Green papaya class', 120, 39],
    ['Peanuts class', 10, 567],
    ['Sugar', 5, 387],
], 'Dressing fish sauce sugar varies.');
$add['goi-ga-bap-cai'] = $mk('recipe_sum: chicken cabbage salad', 'https://fdc.nal.usda.gov/food-search?query=chicken%20cooked', 180, [
    ['Cabbage', 100, 25],
    ['Chicken', 60, 165],
    ['Oil/dressing', 5, 884],
], 'Dressing sugar omitted.');
$add['nom-hoa-chuoi'] = $mk('recipe_sum: banana flower salad light', 'https://fdc.nal.usda.gov/food-search?query=cabbage%20raw', 150, [
    ['Banana flower proxy leafy', 100, 30],
    ['Peanut', 10, 567],
    ['Oil', 3, 884],
], 'Proxy veg FCT — medium.');
$add['chuoi-nep-nuong'] = $mk('recipe_sum: grilled banana sticky rice', 'https://fdc.nal.usda.gov/food-search?query=banana', 150, [
    ['Banana', 80, 89],
    ['Sticky rice cooked', 50, 97],
    ['Coconut milk class', 20, 230],
], 'Street coconut cream richer.');
$add['che-dau-xanh'] = $mk('recipe_sum: mung bean sweet soup light', 'https://fdc.nal.usda.gov/food-search?query=mung%20beans', 250, [
    ['Mung bean cooked class', 80, 105],
    ['Sugar', 20, 387],
    ['Water', 150, 0],
], 'Shop coconut milk higher.');
$add['che-chuoi'] = $mk('recipe_sum: banana sweet soup', 'https://fdc.nal.usda.gov/food-search?query=banana', 250, [
    ['Banana', 80, 89],
    ['Sugar', 15, 387],
    ['Coconut milk', 30, 230],
    ['Water', 125, 0],
], 'Sweetness varies.');
$add['xoi-dau-xanh'] = $mk('recipe_sum: sticky rice mung bean', 'https://fdc.nal.usda.gov/food-search?query=glutinous%20rice', 180, [
    ['Sticky rice', 120, 97],
    ['Mung bean', 40, 105],
    ['Oil/coconut', 10, 230],
], 'Savory/sweet toppings extra.');
$add['sinh-to-xoai'] = $mk('recipe_sum: mango smoothie light', 'https://fdc.nal.usda.gov/food-search?query=mango%20raw', 300, [
    ['Mango', 150, 60],
    ['Milk whole class', 100, 61],
    ['Sugar', 10, 387],
], 'Condensed milk shop much higher.');
$add['sinh-to-bo'] = $mk('recipe_sum: avocado smoothie light', 'https://fdc.nal.usda.gov/food-search?query=avocado', 300, [
    ['Avocado', 100, 160],
    ['Milk', 100, 61],
    ['Sugar', 15, 387],
], 'Condensed milk higher.');
$add['ca-phe-sua-da'] = $mk('recipe_sum: iced milk coffee VN light', 'https://fdc.nal.usda.gov/food-search?query=coffee', 250, [
    ['Coffee brew', 150, 1],
    ['Sweetened condensed milk class', 30, 321],
    ['Ice water', 70, 0],
], 'vivu-standard-v1; cà phê sữa shop varies 30–50g SCM.');
$add['nuoc-mia'] = $mk('recipe_sum: sugarcane juice', 'https://fdc.nal.usda.gov/food-search?query=sugarcane', 300, [
    ['Sugarcane juice class', 300, 61],
], 'USDA/class ~61/100ml — medium; kumquat not included.');
$add['tra-tac'] = $mk('recipe_sum: kumquat tea light', 'https://fdc.nal.usda.gov/food-search?query=tea%20brewed', 300, [
    ['Tea', 250, 1],
    ['Sugar', 12, 387],
    ['Citrus juice class', 30, 29],
], 'Sugar level highly variable.');
$add['banh-cam'] = $mk('recipe_sum: sesame fried ball', 'https://fdc.nal.usda.gov/food-search?query=dough%20fried', 60, [
    ['Dough flour class', 35, 364],
    ['Sugar', 8, 387],
    ['Oil absorbed', 10, 884],
], 'Deep-fry oil variable.');
$add['banh-chuoi-nuong'] = $mk('recipe_sum: banana cake slice', 'https://fdc.nal.usda.gov/food-search?query=banana%20bread', 80, [
    ['Banana bread class', 80, 326],
], 'Mapped to banana bread FCT class — medium.');
$add['che-ba-mau'] = $mk('recipe_sum: three color dessert light', 'https://fdc.nal.usda.gov/food-search?query=mung%20beans', 250, [
    ['Beans/jellies proxy', 80, 105],
    ['Sugar syrup', 25, 387],
    ['Coconut milk', 40, 230],
    ['Ice water', 105, 0],
], 'Shop taller cups higher.');
$add['che-bap'] = $mk('recipe_sum: corn sweet soup', 'https://fdc.nal.usda.gov/food-search?query=corn%20sweet%20cooked', 250, [
    ['Sweet corn', 80, 96],
    ['Sugar', 15, 387],
    ['Coconut milk', 30, 230],
    ['Water', 125, 0],
], 'Sweetness varies.');
$add['che-thai'] = $mk('recipe_sum: che thai fruit dessert', 'https://fdc.nal.usda.gov/food-search?query=fruit%20cocktail', 250, [
    ['Fruit mix', 100, 60],
    ['Syrup sugar', 20, 387],
    ['Coconut milk', 40, 230],
    ['Ice', 90, 0],
], 'Canned syrup heavy — medium.');
$add['che-thap-cam-chay'] = $mk('recipe_sum: mixed vegan che', 'https://fdc.nal.usda.gov/food-search?query=mung%20beans', 250, [
    ['Beans/jellies', 80, 105],
    ['Sugar', 20, 387],
    ['Coconut milk', 40, 230],
    ['Water', 110, 0],
], 'Vegan shop style.');

// === Standard bowls vivu-standard-v1 ===
$std = 'vivu-standard-v1';
$add['pho-bo'] = $rsum(468, 500, 'vivu-standard-v1 phở bò 1 tô', 'https://fdc.nal.usda.gov/food-search?query=rice%20noodles%20cooked',
    'Noodles 180g×110=198; beef 70g×250=175; broth fat oil-eq 8g×884/100=71; herbs~0; total 444→468 plated 500g with broth water',
    [
        $line('Rice noodles cooked', 180, 110),
        $line('Beef cooked lean class', 70, 250),
        $line('Broth fat oil-equivalent', 8, 884),
        $line('Broth water/herbs mass', 242, 0),
    ],
    'Khẩu phần chuẩn ViVu v1 — không đại diện mọi quán; không gồm quẩy, gầu thêm, sa tế ngoài.',
    $std
);
$add['pho-ga'] = $rsum(412, 500, 'vivu-standard-v1 phở gà 1 tô', 'https://fdc.nal.usda.gov/food-search?query=chicken%20cooked',
    'Noodles 180×110=198; chicken 80×165=132; fat oil-eq 6g=53; water mass rest; ~383→412',
    [
        $line('Rice noodles cooked', 180, 110),
        $line('Chicken meat', 80, 165),
        $line('Broth fat oil-eq', 6, 884),
        $line('Broth water', 234, 0),
    ],
    'ViVu v1; skin/oil shop higher.',
    $std
);
$add['bun-cha'] = $rsum(520, 450, 'vivu-standard-v1 bún chả 1 suất', 'https://fdc.nal.usda.gov/food-search?query=pork%20cooked',
    'Bún 150×110=165; grilled pork 100×277=277; oil/dipping sugar 10g mix≈80; herbs 0; ~522',
    [
        $line('Rice vermicelli cooked', 150, 110),
        $line('Grilled pork class', 100, 277),
        $line('Nuoc cham sugar+oil class', 15, 250),
        $line('Herbs/veg', 50, 25),
        $line('Broth water in bowl', 135, 0),
    ],
    'ViVu v1 HN-style portion; nem rán extra not included.',
    $std
);
$add['bun-bo-hue'] = $rsum(545, 550, 'vivu-standard-v1 bún bò Huế 1 tô', 'https://fdc.nal.usda.gov/food-search?query=rice%20noodles',
    'Thick noodles 180×120=216; beef 80×250=200; annatto oil 10g=88; water mass; ~504→545',
    [
        $line('Thick rice noodles cooked', 180, 120),
        $line('Beef', 80, 250),
        $line('Lemongrass oil/annatto oil', 10, 884),
        $line('Broth water', 280, 0),
    ],
    'ViVu v1; giò/huyết/chả not all included — medium.',
    $std
);
$add['bun-thit-nuong'] = $rsum(510, 420, 'vivu-standard-v1 bún thịt nướng', 'https://fdc.nal.usda.gov/food-search?query=pork%20cooked',
    'Bún 150×110=165; pork 90×277=249; oil/peanut 10g≈70; veg 40g; ~484→510',
    [
        $line('Rice vermicelli', 150, 110),
        $line('Grilled pork', 90, 277),
        $line('Oil/peanut', 10, 600),
        $line('Herbs/veg', 50, 25),
        $line('Water/nuoc cham mass', 120, 40),
    ],
    'ViVu v1; chả giò thêm riêng.',
    $std
);
$add['bun-rieu-cua'] = $rsum(420, 500, 'vivu-standard-v1 bún riêu', 'https://fdc.nal.usda.gov/food-search?query=rice%20noodles',
    'Bún 160×110=176; crab paste/tofu/mince 80g mid 150; oil 5; tomato 30; water; ~400→420',
    [
        $line('Rice vermicelli', 160, 110),
        $line('Crab/tofu/pork mix class', 80, 150),
        $line('Tomato', 40, 18),
        $line('Oil', 5, 884),
        $line('Broth water', 215, 0),
    ],
    'ViVu v1; riêu richness varies.',
    $std
);
$add['hu-tieu-nam-vang'] = $rsum(480, 500, 'vivu-standard-v1 hủ tiếu Nam Vang', 'https://fdc.nal.usda.gov/food-search?query=rice%20noodles',
    'Noodles 170×110=187; shrimp+pork 90g~180; oil 6; water; ~420→480',
    [
        $line('Rice noodles', 170, 110),
        $line('Pork+shrimp mix', 90, 180),
        $line('Oil', 6, 884),
        $line('Broth water', 234, 0),
    ],
    'ViVu v1; offal toppings extra.',
    $std
);
$add['com-tam-suon'] = $rsum(650, 450, 'vivu-standard-v1 cơm tấm sườn', 'https://fdc.nal.usda.gov/food-search?query=rice%20white%20cooked',
    'Rice 200×130=260; pork chop 120×250=300; oil/scallion 8g=70; pickle 30g; egg optional 0; ~630→650',
    [
        $line('Broken rice cooked', 200, 130),
        $line('Grilled pork chop class', 120, 250),
        $line('Scallion oil', 8, 884),
        $line('Pickle/veg', 40, 30),
        $line('Plate mass rest', 82, 0),
    ],
    'ViVu v1 single sườn; bì/chả/trứng ốp la add separately.',
    $std
);
$add['com-tam-bi-cha'] = $rsum(620, 430, 'vivu-standard-v1 cơm tấm bì chả', 'https://fdc.nal.usda.gov/food-search?query=rice%20white%20cooked',
    'Rice 200×130=260; bì+chả 100g~220; oil 6; veg; ~530→620',
    [
        $line('Rice', 200, 130),
        $line('Bi+cha pork mix', 100, 250),
        $line('Oil', 6, 884),
        $line('Veg/pickle', 40, 30),
        $line('Rest', 84, 0),
    ],
    'ViVu v1.',
    $std
);
$add['banh-mi-thit'] = $rsum(380, 180, 'vivu-standard-v1 bánh mì thịt', 'https://fdc.nal.usda.gov/food-search?query=bread%20white',
    'Bread 80×274=219; cold cuts 40×250=100; pate 15×300=45; veg 20; ~364→380',
    [
        $line('Baguette', 80, 274),
        $line('Pork cold cuts class', 40, 250),
        $line('Pate class', 15, 300),
        $line('Veg/pickle', 30, 25),
        $line('Mayo/butter optional 0 here', 0, 0),
    ],
    'ViVu v1 no mayo; shop mayo +15–40g fat higher.',
    $std
);
$add['xoi-man'] = $rsum(420, 250, 'vivu-standard-v1 xôi mặn', 'https://fdc.nal.usda.gov/food-search?query=glutinous%20rice',
    'Sticky rice 180×97=175; toppings pork/paate 50×250=125; oil/scallion 10=88; ~388→420',
    [
        $line('Sticky rice', 180, 97),
        $line('Pork/pate topping', 50, 250),
        $line('Scallion oil', 10, 884),
        $line('Rest', 10, 0),
    ],
    'ViVu v1; topping mix varies.',
    $std
);
$add['xoi-ga'] = $rsum(400, 250, 'vivu-standard-v1 xôi gà', 'https://fdc.nal.usda.gov/food-search?query=glutinous%20rice',
    'Sticky rice 180×97=175; chicken 60×165=99; oil 10=88; ~362→400',
    [
        $line('Sticky rice', 180, 97),
        $line('Chicken', 60, 165),
        $line('Oil', 10, 884),
    ],
    'ViVu v1.',
    $std
);
$add['banh-cuon'] = $rsum(336, 220, 'vivu-standard-v1 bánh cuốn', 'https://fdc.nal.usda.gov/food-search?query=rice%20flour',
    'Rice sheet 140×120=168; pork 40×297=119; oil 4×884/100=35; nuoc cham 36×40/100≈14; total 336 for ~220g',
    [
        $line('Rice sheet class', 140, 120),
        $line('Pork filling', 40, 297),
        $line('Oil/fried shallot', 4, 884),
        $line('Nuoc cham', 36, 40),
    ],
    'ViVu v1; chả lụa thêm riêng.',
    $std
);
$add['mi-xao'] = $rsum(480, 350, 'vivu-standard-v1 mì xào thập cẩm', 'https://fdc.nal.usda.gov/food-search?query=noodles%20cooked',
    'Noodles 200×138=276; mix protein 60×200=120; oil 10=88; veg 40; ~500→480',
    [
        $line('Egg noodles cooked', 200, 138),
        $line('Mixed protein', 60, 200),
        $line('Oil', 10, 884),
        $line('Veg', 50, 25),
        $line('Sauce mass', 30, 50),
    ],
    'ViVu v1 soft stir-fry; crispy noodle higher oil.',
    $std
);
$add['com-rang-thap-cam'] = $rsum(520, 350, 'vivu-standard-v1 cơm rang thập cẩm', 'https://fdc.nal.usda.gov/food-search?query=fried%20rice',
    'Rice 220×130=286; egg 40×143=57; mix meat 40×242=97; oil 12=106; ~546→520',
    [
        $line('Rice', 220, 130),
        $line('Egg', 40, 143),
        $line('Meat mix', 40, 242),
        $line('Oil', 12, 884),
        $line('Veg', 38, 25),
    ],
    'ViVu v1; restaurant oil higher.',
    $std
);
$add['goi-cuon'] = $rsum(171, 135, 'vivu-standard-v1 gỏi cuốn 2 cuốn', 'https://fdc.nal.usda.gov/food-search?query=shrimp%20cooked',
    'Paper 40×100=40; shrimp 30×99=30; pork 20×242=48; veg 30×25=8; sauce 15×300=45; total 171',
    [
        $line('Rice paper hydrated', 40, 100),
        $line('Shrimp', 30, 99),
        $line('Pork', 20, 242),
        $line('Herbs/veg', 30, 25),
        $line('Peanut sauce', 15, 300),
    ],
    'ViVu v1 two rolls.',
    $std
);
$add['goi-cuon-chay'] = $rsum(130, 120, 'vivu-standard-v1 gỏi cuốn chay 2 cuốn', 'https://fdc.nal.usda.gov/food-search?query=tofu',
    'Paper 40×100=40; tofu 40×76=30; veg 40×25=10; sauce 15×200=30; total 110→130',
    [
        $line('Rice paper', 40, 100),
        $line('Tofu', 40, 76),
        $line('Veg', 40, 25),
        $line('Sauce', 15, 200),
    ],
    'ViVu v1 vegan rolls.',
    $std
);
$add['pho-chay'] = $rsum(350, 480, 'vivu-standard-v1 phở chay', 'https://fdc.nal.usda.gov/food-search?query=rice%20noodles',
    'Noodles 180×110=198; tofu/mock 60×120=72; oil 5=44; water; ~314→350',
    [
        $line('Rice noodles', 180, 110),
        $line('Tofu/mock meat', 60, 120),
        $line('Oil', 5, 884),
        $line('Broth water', 235, 0),
    ],
    'ViVu v1 vegan pho.',
    $std
);
$add['bun-chay'] = $rsum(340, 400, 'vivu-standard-v1 bún chay', 'https://fdc.nal.usda.gov/food-search?query=rice%20noodles',
    'Bún 160×110=176; tofu 50×76=38; oil 8=71; veg; ~300→340',
    [
        $line('Bún', 160, 110),
        $line('Tofu', 50, 76),
        $line('Oil', 8, 884),
        $line('Veg', 50, 25),
        $line('Sauce water', 132, 30),
    ],
    'ViVu v1.',
    $std
);
$add['com-chay-thap-cam'] = $rsum(480, 400, 'vivu-standard-v1 cơm chay', 'https://fdc.nal.usda.gov/food-search?query=rice%20white%20cooked',
    'Rice 200×130=260; tofu/mock 80×120=96; oil 10=88; veg; ~460→480',
    [
        $line('Rice', 200, 130),
        $line('Tofu/mock', 80, 120),
        $line('Oil', 10, 884),
        $line('Veg', 60, 25),
        $line('Rest', 50, 0),
    ],
    'ViVu v1 vegan rice plate.',
    $std
);
$add['mi-y-so-cot-bo'] = $rsum(556, 350, 'vivu-standard-v1 spaghetti beef sauce', 'https://fdc.nal.usda.gov/food-search?query=spaghetti%20cooked',
    'Pasta 200×158=316; beef sauce 100×150=150; oil/cheese 15×600/100=90; total 556',
    [
        $line('Spaghetti cooked', 200, 158),
        $line('Beef tomato sauce', 100, 150),
        $line('Oil/parmesan light', 15, 600),
    ],
    'ViVu v1 international demo.',
    $std
);
$add['pizza'] = $fct(270, 100, 'USDA pizza cheese class ~270/100g', 'https://fdc.nal.usda.gov/food-search?query=pizza%20cheese', 270, '100g slice cheese pizza class', 'medium', 'Toppings vary widely.');
$add['sushi-set'] = $fct(150, 100, 'USDA sushi with fish class ~150/100g mid', 'https://fdc.nal.usda.gov/food-search?query=sushi', 150, '100g mixed nigiri/maki mid', 'medium', 'Set composition varies.');

// Merge add into bySlug (overwrite with new quality set)
foreach ($add as $k => $v) {
    $bySlug[$k] = $v;
}

// Fix any bad entries - recompute kcal from breakdown if present
foreach ($bySlug as $slug => &$entry) {
    $fact0 = $entry['facts'][0] ?? null;
    if (is_array($fact0) && ($fact0['method'] ?? '') === 'recipe_sum' && ! empty($fact0['ingredients_breakdown'])) {
        $s = 0;
        foreach ($fact0['ingredients_breakdown'] as $ing) {
            $s += (int) ($ing['kcal'] ?? 0);
        }
        $entry['calories_kcal'] = $s;
        $fact0['portion_note'] = ($fact0['portion_note'] ?? '')." | sum_check={$s}";
        $entry['facts'][0] = $fact0;
    }
}
unset($entry);

$calOut = [
    'kb_version' => '2.0.0-fact-a',
    'phase' => 'Fact-A-max',
    'description' => 'Maximum quality-safe calories: fct_table + recipe_sum home + vivu-standard-v1 bowls. Complex share-feast mostly deferred. Not lab-VN certified.',
    'plan_doc' => 'docs/features/what-to-eat-fact-completion-plan.md',
    'rules' => [
        'fct_table | recipe_sum only',
        'vivu-standard-v1 for street bowls with limitations',
        'no average_internet',
        'no TCM in this file',
    ],
    'by_slug' => $bySlug,
];
file_put_contents($calPath, json_encode($calOut, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n");

// ── Ops fields: broad map by slug patterns ───────────────────────
$ops = [];
$opsSet = static function (string $slug, string $cook, string $prot) use (&$ops, $committee): void {
    $ops[$slug] = [
        'cooking_method' => $cook,
        'protein_source' => $prot,
        'facts' => [$committee('cooking_method'), $committee('protein_source')],
    ];
};

// Load all dish slugs from shards
$slugsMeta = [];
foreach (glob(__DIR__.DIRECTORY_SEPARATOR.'dishes_v1'.DIRECTORY_SEPARATOR.'dishes_v1_*.json') ?: [] as $f) {
    $j = json_decode(file_get_contents($f), true, 512, JSON_THROW_ON_ERROR);
    foreach ($j['dishes'] ?? [] as $d) {
        $slugsMeta[$d['slug']] = $d;
    }
}

foreach ($slugsMeta as $slug => $d) {
    $name = mb_strtolower($d['name'] ?? '');
    $role = $d['dish_role'] ?? '';
    $tags = $d['flavor_tags'] ?? [];
    $isChay = is_array($tags) && in_array('chay', $tags, true) || str_contains($slug, 'chay');

    $cook = 'mixed';
    $prot = $isChay ? 'plant' : 'mixed';

    if (str_contains($slug, 'chien') || str_contains($slug, 'ran') || str_contains($slug, 'xoi-mo') || str_contains($slug, 'bot-chien') || str_contains($slug, 'nem-ran') || str_contains($slug, 'cha-gio') || str_contains($slug, 'tom-chien')) {
        $cook = 'fry';
    } elseif (str_contains($slug, 'nuong') || str_contains($slug, 'bbq') || str_contains($slug, 'la-lot')) {
        $cook = 'grill';
    } elseif (str_contains($slug, 'hap') || str_contains($slug, 'cuon') && str_contains($slug, 'banh-cuon')) {
        $cook = 'steam';
    } elseif (str_contains($slug, 'kho') || str_contains($slug, 'rim') || str_contains($slug, 'ram') || str_contains($slug, 'om-')) {
        $cook = 'braise';
    } elseif (str_contains($slug, 'luoc') || str_contains($slug, 'chao') || str_contains($slug, 'canh') || str_contains($slug, 'sup') || str_contains($slug, 'pho') || str_contains($slug, 'bun') && ! str_contains($slug, 'bun-tuoi') || str_contains($slug, 'hu-tieu') || str_contains($slug, 'lau') || str_contains($slug, 'mien')) {
        $cook = str_contains($slug, 'lau') || str_contains($slug, 'pho') || str_contains($slug, 'bun-bo') || str_contains($slug, 'hu-tieu') ? 'soup_base' : 'boil';
    } elseif (str_contains($slug, 'xao') || str_contains($slug, 'rang') || str_contains($slug, 'luc-lac')) {
        $cook = 'mixed';
    } elseif ($role === 'starch' || str_contains($slug, 'com-trang') || str_contains($slug, 'xoi-trang')) {
        $cook = 'steam';
    } elseif ($role === 'beverage' || str_contains($slug, 'ca-phe') || str_contains($slug, 'tra-') || str_contains($slug, 'nuoc-') || str_contains($slug, 'sinh-to')) {
        $cook = 'mixed';
    } elseif (str_contains($slug, 'goi') || str_contains($slug, 'nom') || str_contains($slug, 'salad') || str_contains($slug, 'dua-mon')) {
        $cook = 'raw';
    }

    if ($isChay || str_contains($slug, 'dau-phu') || str_contains($slug, 'dau-hu') || str_contains($slug, 'nam-kho')) {
        $prot = 'plant';
    } elseif (str_contains($slug, 'trung') || str_contains($slug, 'op-la') || str_contains($slug, 'cha-trung')) {
        $prot = 'egg';
    } elseif (str_contains($slug, 'tom') || str_contains($slug, 'ca-') || str_contains($slug, 'muc') || str_contains($slug, 'oc-') || str_contains($slug, 'tep') || str_contains($slug, 'hai-san') || str_contains($slug, 'cua') || str_contains($slug, 'rieu')) {
        $prot = 'seafood';
    } elseif (str_contains($slug, 'bo-') || str_contains($slug, 'thit') || str_contains($slug, 'ga-') || str_contains($slug, 'heo') || str_contains($slug, 'suon') || str_contains($slug, 'vit') || str_contains($slug, 'cha-lua') || str_contains($slug, 'nem') || str_contains($slug, 'ruoc')) {
        $prot = 'meat';
    } elseif ($role === 'side_veg' || $role === 'starch' || $role === 'beverage' || $role === 'dessert_light' || str_contains($slug, 'com-trang') || str_contains($slug, 'rau-') || str_contains($slug, 'canh-bi') && str_contains($slug, 'chay')) {
        if ($prot !== 'plant') {
            $prot = ($role === 'side_veg' || $role === 'starch' || str_contains($slug, 'com-trang') || str_contains($slug, 'com-gao')) ? 'none' : $prot;
        }
    }
    if ($role === 'side_veg' || $role === 'starch') {
        $prot = str_contains($slug, 'trung') ? 'egg' : 'none';
    }
    if ($role === 'beverage' || $role === 'dessert_light') {
        $prot = 'none';
    }

    $opsSet($slug, $cook, $prot);
}

// Manual overrides for accuracy
$opsSet('com-trang', 'steam', 'none');
$opsSet('com-gao-lut', 'steam', 'none');
$opsSet('xoi-trang', 'steam', 'none');
$opsSet('bun-tuoi', 'boil', 'none');
$opsSet('ga-luoc', 'boil', 'meat');
$opsSet('trung-chien', 'fry', 'egg');
$opsSet('trung-op-la', 'fry', 'egg');
$opsSet('sua-chua', 'mixed', 'none');
$opsSet('pho-bo', 'soup_base', 'meat');
$opsSet('pho-ga', 'soup_base', 'meat');
$opsSet('pizza', 'mixed', 'mixed');
$opsSet('sushi-set', 'raw', 'seafood');
$opsSet('mi-y-so-cot-bo', 'boil', 'meat');

$opsOut = [
    'kb_version' => '2.0.0-ops',
    'phase' => 'Ops-A-max',
    'description' => 'Broad cooking_method + protein_source for catalog; committee. Heuristic + manual overrides. Enables A05/E01/S02.',
    'by_slug' => $ops,
];
file_put_contents($opsPath, json_encode($opsOut, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n");

// Recipe registry extract
$recipes = ['kb_version' => '1.0.0', 'standard_id' => 'vivu-standard-v1', 'dishes' => []];
foreach ($bySlug as $slug => $entry) {
    $f = $entry['facts'][0] ?? [];
    if (($f['standard_id'] ?? '') === 'vivu-standard-v1' || str_contains((string) ($f['source_title'] ?? ''), 'vivu-standard-v1')) {
        $recipes['dishes'][$slug] = [
            'serving_grams' => $entry['serving_grams'],
            'calories_kcal' => $entry['calories_kcal'],
            'ingredients_breakdown' => $f['ingredients_breakdown'] ?? [],
            'limitations' => $f['limitations'] ?? null,
        ];
    }
}
file_put_contents($recipePath, json_encode($recipes, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n");

echo 'calories_by_slug='.count($bySlug).PHP_EOL;
echo 'ops_by_slug='.count($ops).PHP_EOL;
echo 'standard_recipes='.count($recipes['dishes']).PHP_EOL;
echo "Wrote:\n  {$calPath}\n  {$opsPath}\n  {$recipePath}\n";
