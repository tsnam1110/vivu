<?php

declare(strict_types=1);

/**
 * Phase B — FCT VN:
 *  1) Expand ingredients + cooked-rice yield factors (vivu-yield-v1)
 *  2) Rebuild vivu-standard bowls with VN food_code components
 *  3) Merge rice + bowls + extra home recipes into calories_fact_a
 *
 * Run: php database/data/what-to-eat/build_fct_vn_phase_b.php
 * Then: php artisan db:seed --class=DishSeeder
 */

$factsDir = __DIR__.DIRECTORY_SEPARATOR.'facts';
$ingPath = $factsDir.DIRECTORY_SEPARATOR.'fct_vn_ingredients.json';
$stdPath = $factsDir.DIRECTORY_SEPARATOR.'recipes_standard_v1.json';
$pilotPath = $factsDir.DIRECTORY_SEPARATOR.'recipes_fct_vn_pilot_v1.json';
$calPath = $factsDir.DIRECTORY_SEPARATOR.'calories_fact_a.json';
$src = 'https://www.fao.org/fileadmin/templates/food_composition/documents/pdf/VTN_FCT_2007.pdf';

$round = static fn (float $n): int => (int) round($n);
$kcalLine = static fn (float $grams, float $per100): int => (int) round($grams * $per100 / 100);

// —— 1) Load + expand ingredient table ——
$ing = json_decode(file_get_contents($ingPath), true, 512, JSON_THROW_ON_ERROR);
$byId = [];
foreach ($ing['ingredients'] as $row) {
    $byId[$row['id']] = $row;
}

$extraIngredients = [
    [
        'id' => 'vn-banh-pho',
        'name_vi' => 'Bánh phở',
        'name_en' => 'Rice noodles',
        'kcal_per_100g' => 143,
        'state' => 'as_sold',
        'food_code' => '1013',
        'pdf_page' => 26,
        'stt' => 13,
        'method' => 'fct_table',
        'source_ref' => $src.'#page=26',
        'source_title' => 'Bảng TPTP VN 2007 — 1013 Bánh phở',
        'confidence' => 'high',
        'reviewed_by' => 'fct-vn-curator',
        'reviewed_at' => '2026-07-11',
        'notes' => 'Dòng bánh phở trong FCT (143 kcal/100g) — ẩm hơn bún khô, khác bún 1020.',
    ],
    [
        'id' => 'vn-banh-da-nem',
        'name_vi' => 'Bánh đa nem / bánh tráng cuốn',
        'name_en' => 'Rice paper for rollers',
        'kcal_per_100g' => 333,
        'state' => 'dry',
        'food_code' => '1010',
        'pdf_page' => 23,
        'stt' => 10,
        'method' => 'fct_table',
        'source_ref' => $src.'#page=23',
        'source_title' => 'Bảng TPTP VN 2007 — 1010 Bánh đa nem',
        'confidence' => 'high',
        'reviewed_by' => 'fct-vn-curator',
        'reviewed_at' => '2026-07-11',
    ],
    [
        'id' => 'vn-thit-heo-nua-nac',
        'name_vi' => 'Thịt lợn nửa nạc nửa mỡ',
        'name_en' => 'Pork, medium fat',
        'kcal_per_100g' => 260,
        'state' => 'raw',
        'food_code' => '7018',
        'pdf_page' => 322,
        'stt' => 296,
        'method' => 'fct_table',
        'source_ref' => $src.'#page=322',
        'source_title' => 'Bảng TPTP VN 2007 — 7018 Thịt lợn nửa nạc',
        'confidence' => 'high',
        'reviewed_by' => 'fct-vn-curator',
        'reviewed_at' => '2026-07-11',
        'notes' => 'Proxy thịt nướng/chả quán — cao hơn nạc 7017.',
    ],
    [
        'id' => 'vn-ca-qua',
        'name_vi' => 'Cá quả (cá lóc)',
        'name_en' => 'Fish, snake head',
        'kcal_per_100g' => 97,
        'state' => 'raw',
        'food_code' => '8022',
        'pdf_page' => 410,
        'stt' => 382,
        'method' => 'fct_table',
        'source_ref' => $src.'#page=410',
        'source_title' => 'Bảng TPTP VN 2007 — 8022 Cá quả',
        'confidence' => 'high',
        'reviewed_by' => 'fct-vn-curator',
        'reviewed_at' => '2026-07-11',
    ],
    [
        'id' => 'vn-ca-thu',
        'name_vi' => 'Cá thu',
        'name_en' => 'Mackerel, codfish, kingfish',
        'kcal_per_100g' => 166,
        'state' => 'raw',
        'food_code' => '8026',
        'pdf_page' => 414,
        'stt' => 386,
        'method' => 'fct_table',
        'source_ref' => $src.'#page=414',
        'source_title' => 'Bảng TPTP VN 2007 — 8026 Cá thu',
        'confidence' => 'high',
        'reviewed_by' => 'fct-vn-curator',
        'reviewed_at' => '2026-07-11',
    ],
    [
        'id' => 'vn-rau-mong-toi',
        'name_vi' => 'Rau mồng tơi',
        'name_en' => 'Malabar night shade, Ceylon spinach',
        'kcal_per_100g' => 14,
        'state' => 'raw',
        'food_code' => '4080',
        'pdf_page' => 182,
        'stt' => 162,
        'method' => 'fct_table',
        'source_ref' => $src.'#page=182',
        'source_title' => 'Bảng TPTP VN 2007 — 4080 Rau mồng tơi',
        'confidence' => 'high',
        'reviewed_by' => 'fct-vn-curator',
        'reviewed_at' => '2026-07-11',
    ],
    [
        'id' => 'vn-cai-thia',
        'name_vi' => 'Cải thìa (cải trắng)',
        'name_en' => 'Chinese cabbage, white',
        'kcal_per_100g' => 17,
        'state' => 'raw',
        'food_code' => '4015',
        'pdf_page' => 117,
        'stt' => 97,
        'method' => 'fct_table',
        'source_ref' => $src.'#page=117',
        'source_title' => 'Bảng TPTP VN 2007 — 4015 Cải thìa',
        'confidence' => 'high',
        'reviewed_by' => 'fct-vn-curator',
        'reviewed_at' => '2026-07-11',
    ],
    [
        'id' => 'vn-duong-cat',
        'name_vi' => 'Đường cát (đồng nghĩa đường tinh luyện)',
        'name_en' => 'Refined sugar',
        'kcal_per_100g' => 390,
        'state' => 'dry',
        'food_code' => '12013',
        'pdf_page' => 509,
        'stt' => 473,
        'method' => 'fct_table',
        'source_ref' => $src.'#page=509',
        'source_title' => 'Bảng TPTP VN 2007 — 12013 Đường cát',
        'confidence' => 'high',
        'reviewed_by' => 'fct-vn-curator',
        'reviewed_at' => '2026-07-11',
        'alias_of' => 'vn-duong-trang',
    ],
];

// Derived cooked rice (vivu-yield-v1)
$yields = [
    'vn-com-trang-chin' => [
        'from' => 'vn-gao-te-trang-kho',
        'name_vi' => 'Cơm trắng chín (yield từ gạo tẻ máy 1004)',
        'yield_factor' => 2.5,
        'notes' => 'vivu-yield-v1: cooked_mass/dry_mass = 2.5 (nồi cơm điện nhà). kcal_cooked = dry_kcal/yield.',
    ],
    'vn-com-gao-lut-chin' => [
        'from' => 'vn-gao-lut-kho',
        'name_vi' => 'Cơm gạo lứt chín (yield từ 1005)',
        'yield_factor' => 2.5,
        'notes' => 'vivu-yield-v1 yield 2.5.',
    ],
    'vn-xoi-nep-chin' => [
        'from' => 'vn-gao-nep-cai',
        'name_vi' => 'Xôi nếp chín (yield từ gạo nếp cái 1001)',
        'yield_factor' => 2.2,
        'notes' => 'vivu-yield-v1: xôi đặc hơn cơm tẻ → yield 2.2 (ít nước hơn).',
    ],
];

foreach ($extraIngredients as $row) {
    $byId[$row['id']] = $row;
}

foreach ($yields as $id => $meta) {
    $dry = $byId[$meta['from']];
    $per = $dry['kcal_per_100g'] / $meta['yield_factor'];
    $byId[$id] = [
        'id' => $id,
        'name_vi' => $meta['name_vi'],
        'name_en' => 'Cooked rice derived from '.$dry['food_code'],
        'kcal_per_100g' => round($per, 1),
        'state' => 'cooked_derived',
        'derived_from' => $meta['from'],
        'food_code_dry' => $dry['food_code'],
        'pdf_page_dry' => $dry['pdf_page'],
        'yield_factor' => $meta['yield_factor'],
        'yield_standard' => 'vivu-yield-v1',
        'method' => 'recipe_sum',
        'source_ref' => $dry['source_ref'],
        'source_title' => 'Derived cooked from FCT VN '.$dry['food_code'].' ÷ yield '.$meta['yield_factor'],
        'confidence' => 'medium',
        'reviewed_by' => 'fct-vn-phase-b',
        'reviewed_at' => '2026-07-11',
        'notes' => $meta['notes'],
    ];
}

$ing['ingredients'] = array_values($byId);
$ing['kb_version'] = '1.1.0-vn-fct';
$ing['status'] = 'audited_partial_plus_yield';
$ing['yield_standard'] = [
    'id' => 'vivu-yield-v1',
    'description' => 'Hệ số khối lượng chín/khô nội bộ ViVu cho gạo khi FCT chỉ có gạo khô.',
    'rice_white' => 2.5,
    'rice_brown' => 2.5,
    'sticky_xoi' => 2.2,
];
$ing['next_steps'] = [
    'Mở rộng audit thêm dòng khi recipe_sum mới cần',
    'Cân nhắc đo yield thực tế 3 nồi cơm để siết high',
];
file_put_contents($ingPath, json_encode($ing, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n");
echo 'ingredients='.count($ing['ingredients']).PHP_EOL;

$P = static function (string $id) use ($byId): array {
    if (! isset($byId[$id])) {
        throw new RuntimeException("Missing ingredient {$id}");
    }

    return $byId[$id];
};

$line = static function (string $id, float $grams, ?string $name = null) use ($P, $kcalLine): array {
    $p = $P($id);
    $per = (float) $p['kcal_per_100g'];

    return [
        'name' => $name ?? $p['name_vi'],
        'ingredient_id' => $id,
        'food_code' => $p['food_code'] ?? ($p['food_code_dry'] ?? null),
        'grams' => $grams,
        'kcal_per_100g' => $per,
        'kcal' => $kcalLine($grams, $per),
    ];
};

$sum = static function (array $lines): int {
    $t = 0;
    foreach ($lines as $l) {
        $t += (int) $l['kcal'];
    }

    return $t;
};

// —— 2) Rebuild standard bowls ——
$stdDishes = [];

// Helpers for oil
$oilId = 'vn-dau-thao-moc';

$stdDishes['pho-bo'] = (static function () use ($line, $sum, $oilId) {
    $lines = [
        $line('vn-banh-pho', 180, 'Bánh phở'),
        $line('vn-thit-bo-loai-1', 70, 'Thịt bò loại I (tái/chín mỏng)'),
        $line($oilId, 8, 'Mỡ nước dùng (oil-eq)'),
        ['name' => 'Nước dùng + rau thơm', 'grams' => 242, 'kcal_per_100g' => 0, 'kcal' => 0],
    ];
    $kcal = $sum($lines);

    return [
        'serving_grams' => 500,
        'calories_kcal' => $kcal,
        'ingredients_breakdown' => $lines,
        'fct_source' => 'vn_2007',
        'limitations' => 'ViVu v1 + FCT VN; bò raw grade-I (tái). Không quẩy/gầu/sa tế ngoài. Bánh phở 1013 (143kcal) khác bún 1020.',
    ];
})();

$stdDishes['pho-ga'] = (static function () use ($line, $sum, $oilId) {
    $lines = [
        $line('vn-banh-pho', 180, 'Bánh phở'),
        $line('vn-thit-ga-ta', 80, 'Thịt gà ta'),
        $line($oilId, 6, 'Mỡ nước dùng (oil-eq)'),
        ['name' => 'Nước dùng', 'grams' => 234, 'kcal_per_100g' => 0, 'kcal' => 0],
    ];

    return [
        'serving_grams' => 500,
        'calories_kcal' => $sum($lines),
        'ingredients_breakdown' => $lines,
        'fct_source' => 'vn_2007',
        'limitations' => 'ViVu v1 + FCT VN; gà raw average 7013 — da/mỡ quán cao hơn.',
    ];
})();

$stdDishes['pho-chay'] = (static function () use ($line, $sum, $oilId) {
    $lines = [
        $line('vn-banh-pho', 180, 'Bánh phở'),
        $line('vn-dau-phu', 60, 'Đậu phụ / topping chay'),
        $line($oilId, 5, 'Dầu'),
        ['name' => 'Nước dùng chay', 'grams' => 235, 'kcal_per_100g' => 0, 'kcal' => 0],
    ];

    return [
        'serving_grams' => 480,
        'calories_kcal' => $sum($lines),
        'ingredients_breakdown' => $lines,
        'fct_source' => 'vn_2007',
        'limitations' => 'ViVu v1 + FCT VN vegan pho.',
    ];
})();

foreach (['bun-cha' => 150, 'bun-thit-nuong' => 150, 'bun-rieu-cua' => 160, 'bun-chay' => 160, 'bun-bo-hue' => 180] as $slug => $bunG) {
    // built individually below for protein differences
}

$stdDishes['bun-cha'] = (static function () use ($line, $sum, $oilId) {
    $lines = [
        $line('vn-bun', 150, 'Bún'),
        $line('vn-thit-heo-nua-nac', 100, 'Thịt nướng (nửa nạc)'),
        $line('vn-duong-trang', 5, 'Đường trong nước chấm (ước)'),
        $line($oilId, 3, 'Dầu/mỡ chả'),
        $line('vn-rau-muong', 50, 'Rau sống proxy'),
        ['name' => 'Nước chấm + nước', 'grams' => 142, 'kcal_per_100g' => 0, 'kcal' => 0],
    ];
    // fix serving to 450: adjust water
    $used = 150 + 100 + 5 + 3 + 50;
    $lines[5]['grams'] = 450 - $used;

    return [
        'serving_grams' => 450,
        'calories_kcal' => $sum($lines),
        'ingredients_breakdown' => $lines,
        'fct_source' => 'vn_2007',
        'limitations' => 'ViVu v1 HN; nem rán thêm riêng. Nước chấm chỉ ước đường 5g.',
    ];
})();

$stdDishes['bun-thit-nuong'] = (static function () use ($line, $sum, $oilId) {
    $lines = [
        $line('vn-bun', 150, 'Bún'),
        $line('vn-thit-heo-nua-nac', 90, 'Thịt nướng'),
        $line($oilId, 8, 'Dầu + đậu phộng eq'),
        $line('vn-rau-muong', 50, 'Rau sống proxy'),
        $line('vn-duong-trang', 8, 'Đường nước mắm'),
        ['name' => 'Nước mắm loãng mass', 'grams' => 114, 'kcal_per_100g' => 0, 'kcal' => 0],
    ];

    return [
        'serving_grams' => 420,
        'calories_kcal' => $sum($lines),
        'ingredients_breakdown' => $lines,
        'fct_source' => 'vn_2007',
        'limitations' => 'ViVu v1; chả giò thêm riêng.',
    ];
})();

$stdDishes['bun-bo-hue'] = (static function () use ($line, $sum, $oilId) {
    $lines = [
        $line('vn-bun', 180, 'Bún (proxy sợi to)'),
        $line('vn-thit-bo-loai-1', 80, 'Thịt bò'),
        $line($oilId, 10, 'Dầu điều/sả'),
        ['name' => 'Nước dùng', 'grams' => 280, 'kcal_per_100g' => 0, 'kcal' => 0],
    ];

    return [
        'serving_grams' => 550,
        'calories_kcal' => $sum($lines),
        'ingredients_breakdown' => $lines,
        'fct_source' => 'vn_2007',
        'limitations' => 'ViVu v1; giò/huyết/chả chưa full — medium. Bún 1020 proxy sợi Huế.',
    ];
})();

$stdDishes['bun-rieu-cua'] = (static function () use ($line, $sum, $oilId) {
    $lines = [
        $line('vn-bun', 160, 'Bún'),
        $line('vn-dau-phu', 40, 'Đậu phụ'),
        $line('vn-thit-heo-nac', 40, 'Thịt/riêu class'),
        $line('vn-ca-chua', 40, 'Cà chua'),
        $line($oilId, 5, 'Dầu'),
        ['name' => 'Nước dùng', 'grams' => 215, 'kcal_per_100g' => 0, 'kcal' => 0],
    ];

    return [
        'serving_grams' => 500,
        'calories_kcal' => $sum($lines),
        'ingredients_breakdown' => $lines,
        'fct_source' => 'vn_2007',
        'limitations' => 'ViVu v1; cua đồng không có dòng FCT riêng — dùng thịt+đậu proxy.',
    ];
})();

$stdDishes['bun-chay'] = (static function () use ($line, $sum, $oilId) {
    $lines = [
        $line('vn-bun', 160, 'Bún'),
        $line('vn-dau-phu', 50, 'Đậu phụ'),
        $line($oilId, 8, 'Dầu'),
        $line('vn-rau-muong', 50, 'Rau'),
        $line('vn-duong-trang', 5, 'Đường nước chấm'),
        ['name' => 'Nước chấm mass', 'grams' => 127, 'kcal_per_100g' => 0, 'kcal' => 0],
    ];

    return [
        'serving_grams' => 400,
        'calories_kcal' => $sum($lines),
        'ingredients_breakdown' => $lines,
        'fct_source' => 'vn_2007',
        'limitations' => 'ViVu v1 chay.',
    ];
})();

$stdDishes['hu-tieu-nam-vang'] = (static function () use ($line, $sum, $oilId) {
    $lines = [
        $line('vn-banh-pho', 170, 'Hủ tiếu / bánh phở class'),
        $line('vn-thit-heo-nac', 50, 'Thịt heo'),
        $line('vn-tom-bien', 40, 'Tôm'),
        $line($oilId, 6, 'Dầu'),
        ['name' => 'Nước dùng', 'grams' => 234, 'kcal_per_100g' => 0, 'kcal' => 0],
    ];

    return [
        'serving_grams' => 500,
        'calories_kcal' => $sum($lines),
        'ingredients_breakdown' => $lines,
        'fct_source' => 'vn_2007',
        'limitations' => 'ViVu v1; lòng/offal thêm riêng.',
    ];
})();

$stdDishes['com-tam-suon'] = (static function () use ($line, $sum, $oilId) {
    $lines = [
        $line('vn-com-trang-chin', 200, 'Cơm tấm (com yield)'),
        $line('vn-thit-heo-nua-nac', 120, 'Sườn nướng class'),
        $line($oilId, 8, 'Mỡ hành'),
        $line('vn-ca-chua', 40, 'Đồ chua proxy'),
        ['name' => 'Rest plate', 'grams' => 82, 'kcal_per_100g' => 0, 'kcal' => 0],
    ];

    return [
        'serving_grams' => 450,
        'calories_kcal' => $sum($lines),
        'ingredients_breakdown' => $lines,
        'fct_source' => 'vn_2007',
        'limitations' => 'ViVu v1 single sườn; bì/chả/trứng thêm riêng. Cơm từ yield gạo 1004.',
    ];
})();

$stdDishes['com-tam-bi-cha'] = (static function () use ($line, $sum, $oilId) {
    $lines = [
        $line('vn-com-trang-chin', 200, 'Cơm tấm'),
        $line('vn-thit-heo-nua-nac', 100, 'Bì + chả class'),
        $line($oilId, 6, 'Mỡ hành'),
        $line('vn-ca-chua', 40, 'Đồ chua proxy'),
        ['name' => 'Rest', 'grams' => 84, 'kcal_per_100g' => 0, 'kcal' => 0],
    ];

    return [
        'serving_grams' => 430,
        'calories_kcal' => $sum($lines),
        'ingredients_breakdown' => $lines,
        'fct_source' => 'vn_2007',
        'limitations' => 'ViVu v1.',
    ];
})();

$stdDishes['banh-mi-thit'] = (static function () use ($line, $sum) {
    $lines = [
        $line('vn-banh-mi', 80, 'Bánh mì'),
        $line('vn-thit-heo-nua-nac', 40, 'Thịt nguội class'),
        $line('vn-thit-heo-nac', 15, 'Pate proxy nạc'),
        $line('vn-rau-muong', 30, 'Rau/đồ chua proxy'),
    ];

    return [
        'serving_grams' => 180,
        'calories_kcal' => $sum($lines),
        'ingredients_breakdown' => $lines,
        'fct_source' => 'vn_2007',
        'limitations' => 'ViVu v1 no mayo; pate proxy bằng thịt — shop mayo cao hơn.',
    ];
})();

$stdDishes['xoi-man'] = (static function () use ($line, $sum, $oilId) {
    $lines = [
        $line('vn-xoi-nep-chin', 180, 'Xôi nếp'),
        $line('vn-thit-heo-nua-nac', 50, 'Topping mặn'),
        $line($oilId, 10, 'Mỡ hành'),
        ['name' => 'Rest', 'grams' => 10, 'kcal_per_100g' => 0, 'kcal' => 0],
    ];

    return [
        'serving_grams' => 250,
        'calories_kcal' => $sum($lines),
        'ingredients_breakdown' => $lines,
        'fct_source' => 'vn_2007',
        'limitations' => 'ViVu v1; topping mix varies. Xôi yield 2.2.',
    ];
})();

$stdDishes['xoi-ga'] = (static function () use ($line, $sum, $oilId) {
    $lines = [
        $line('vn-xoi-nep-chin', 180, 'Xôi nếp'),
        $line('vn-thit-ga-ta', 60, 'Gà'),
        $line($oilId, 10, 'Mỡ hành'),
    ];

    return [
        'serving_grams' => 250,
        'calories_kcal' => $sum($lines),
        'ingredients_breakdown' => $lines,
        'fct_source' => 'vn_2007',
        'limitations' => 'ViVu v1.',
    ];
})();

$stdDishes['goi-cuon'] = (static function () use ($line, $sum) {
    // rice paper dry 333 — hydrated ~40g dry equivalent ~12g dry paper? Use 12g dry paper
    $paperDry = 12.0;
    $lines = [
        $line('vn-banh-da-nem', $paperDry, 'Bánh tráng (khô eq)'),
        $line('vn-tom-bien', 30, 'Tôm'),
        $line('vn-thit-heo-nac', 20, 'Thịt'),
        $line('vn-rau-muong', 30, 'Rau'),
        $line('vn-duong-trang', 5, 'Tương/đường proxy'),
        $line('vn-dau-thao-moc', 3, 'Dầu/đậu phộng eq'),
    ];

    return [
        'serving_grams' => 135,
        'calories_kcal' => $sum($lines),
        'ingredients_breakdown' => $lines,
        'fct_source' => 'vn_2007',
        'limitations' => 'ViVu v1 ~2 cuốn; bánh tráng quy về gram khô FCT 1010.',
    ];
})();

$stdDishes['goi-cuon-chay'] = (static function () use ($line, $sum) {
    $lines = [
        $line('vn-banh-da-nem', 12, 'Bánh tráng khô eq'),
        $line('vn-dau-phu', 40, 'Đậu phụ'),
        $line('vn-rau-muong', 40, 'Rau'),
        $line('vn-duong-trang', 4, 'Tương ngọt proxy'),
        $line('vn-dau-thao-moc', 2, 'Dầu'),
    ];

    return [
        'serving_grams' => 120,
        'calories_kcal' => $sum($lines),
        'ingredients_breakdown' => $lines,
        'fct_source' => 'vn_2007',
        'limitations' => 'ViVu v1 vegan rolls.',
    ];
})();

$stdDishes['com-chay-thap-cam'] = (static function () use ($line, $sum, $oilId) {
    $lines = [
        $line('vn-com-trang-chin', 200, 'Cơm'),
        $line('vn-dau-phu', 80, 'Đậu/nấm proxy'),
        $line($oilId, 10, 'Dầu'),
        $line('vn-rau-muong', 60, 'Rau củ'),
        ['name' => 'Rest', 'grams' => 50, 'kcal_per_100g' => 0, 'kcal' => 0],
    ];

    return [
        'serving_grams' => 400,
        'calories_kcal' => $sum($lines),
        'ingredients_breakdown' => $lines,
        'fct_source' => 'vn_2007',
        'limitations' => 'ViVu v1 vegan rice plate.',
    ];
})();

$stdDishes['com-rang-thap-cam'] = (static function () use ($line, $sum, $oilId) {
    $lines = [
        $line('vn-com-trang-chin', 220, 'Cơm'),
        $line('vn-trung-ga-toan-phan', 40, 'Trứng'),
        $line('vn-thit-heo-nac', 40, 'Thịt'),
        $line($oilId, 12, 'Dầu'),
        $line('vn-rau-muong', 38, 'Rau'),
    ];

    return [
        'serving_grams' => 350,
        'calories_kcal' => $sum($lines),
        'ingredients_breakdown' => $lines,
        'fct_source' => 'vn_2007',
        'limitations' => 'ViVu v1; quán dầu cao hơn.',
    ];
})();

// Keep non-VN-converted bowls from previous file if any remaining needed
$oldStd = json_decode(file_get_contents($stdPath), true, 512, JSON_THROW_ON_ERROR);
foreach (['banh-cuon', 'mi-xao', 'mi-y-so-cot-bo'] as $keep) {
    if (isset($oldStd['dishes'][$keep])) {
        $stdDishes[$keep] = $oldStd['dishes'][$keep];
        $stdDishes[$keep]['fct_source'] = $stdDishes[$keep]['fct_source'] ?? 'mixed_usda';
        $stdDishes[$keep]['limitations'] = ($stdDishes[$keep]['limitations'] ?? '').' [chưa chuyển full FCT VN]';
    }
}

$stdOut = [
    'kb_version' => '1.1.0-vn',
    'standard_id' => 'vivu-standard-v1',
    'fct_source' => 'vn_2007',
    'yield_standard' => 'vivu-yield-v1',
    'description' => 'Khẩu phần chuẩn ViVu v1 — components ưu tiên FCT VN 2007 + yield gạo nội bộ.',
    'dishes' => $stdDishes,
];
file_put_contents($stdPath, json_encode($stdOut, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n");
echo 'standard_bowls='.count($stdDishes).PHP_EOL;

// —— 3) Build phase-B pilot rows (rice + bowls + extra home) ——
$factRecipe = static function (array $lines, int $serving, string $title, string $ref, string $limitations, string $conf = 'medium'): array {
    $kcal = 0;
    foreach ($lines as $l) {
        $kcal += (int) $l['kcal'];
    }
    $parts = [];
    foreach ($lines as $l) {
        if ((int) $l['kcal'] === 0 && ($l['kcal_per_100g'] ?? 0) == 0) {
            continue;
        }
        $parts[] = ($l['name'] ?? '?').' '.$l['grams'].'g→'.$l['kcal'];
    }

    return [
        'calories_kcal' => $kcal,
        'serving_grams' => $serving,
        'facts' => [[
            'field' => 'calories_kcal',
            'method' => 'recipe_sum',
            'fct_source' => 'vn_2007',
            'source_title' => $title,
            'source_ref' => $ref,
            'serving_grams' => $serving,
            'portion_note' => implode('; ', $parts)."; total {$kcal}",
            'ingredients_breakdown' => $lines,
            'confidence' => $conf,
            'reviewed_at' => '2026-07-11',
            'reviewed_by' => 'fct-vn-phase-b',
            'limitations' => $limitations,
            'standard_id' => str_contains($ref, 'standard') ? 'vivu-standard-v1' : null,
        ]],
    ];
};

$factFct = static function (string $ingId, float $grams, string $slugLabel) use ($P, $kcalLine): array {
    $p = $P($ingId);
    $per = (float) $p['kcal_per_100g'];
    $kcal = $kcalLine($grams, $per);
    $isDerived = ($p['state'] ?? '') === 'cooked_derived';

    return [
        'calories_kcal' => $kcal,
        'serving_grams' => (int) $grams,
        'facts' => [[
            'field' => 'calories_kcal',
            'method' => $isDerived ? 'recipe_sum' : 'fct_table',
            'fct_source' => 'vn_2007',
            'source_title' => $p['source_title'],
            'source_ref' => $p['source_ref'],
            'food_code' => $p['food_code'] ?? ($p['food_code_dry'] ?? null),
            'ingredient_id' => $ingId,
            'per_100g_kcal' => $per,
            'serving_grams' => (int) $grams,
            'portion_note' => sprintf('%sg × %.1f/100g = %d%s', (int) $grams, $per, $kcal, $isDerived ? ' (yield-derived)' : ''),
            'yield_factor' => $p['yield_factor'] ?? null,
            'yield_standard' => $p['yield_standard'] ?? null,
            'confidence' => $isDerived ? 'medium' : ($p['confidence'] ?? 'medium'),
            'reviewed_at' => '2026-07-11',
            'reviewed_by' => 'fct-vn-phase-b',
            'limitations' => $isDerived
                ? 'Gạo chín suy ra từ gạo khô FCT ÷ vivu-yield-v1 — không phải dòng nấu sẵn trong bảng.'
                : ($p['notes'] ?? 'Plain portion.'),
        ]],
    ];
};

// Load existing pilot A and extend
$pilot = json_decode(file_get_contents($pilotPath), true, 512, JSON_THROW_ON_ERROR);
$bySlug = $pilot['by_slug'];

// Rice yields
$bySlug['com-trang'] = $factFct('vn-com-trang-chin', 150, 'com-trang');
$bySlug['com-gao-lut'] = $factFct('vn-com-gao-lut-chin', 150, 'com-gao-lut');
$bySlug['xoi-trang'] = $factFct('vn-xoi-nep-chin', 150, 'xoi-trang');
$bySlug['bun-tuoi'] = $factFct('vn-bun', 100, 'bun-tuoi');

// Extra home recipe_sum
$bySlug['cai-xao-toi'] = $factRecipe(
    [
        $line('vn-cai-thia', 200, 'Cải thìa'),
        $line('vn-dau-thao-moc', 10, 'Dầu'),
    ],
    150,
    'FCT VN recipe_sum — cải xào tỏi (4015+6002)',
    'vivu-fct-vn-phase-b:cai-xao-toi',
    'Tỏi/nước mắm bỏ qua; dầu quán cao hơn.',
);

$bySlug['tom-rang-me'] = $factRecipe(
    [
        $line('vn-tom-bien', 200, 'Tôm biển'),
        $line('vn-duong-trang', 15, 'Đường'),
        $line('vn-dau-thao-moc', 5, 'Dầu'),
    ],
    180,
    'FCT VN recipe_sum — tôm rang me (8051+12013+6002)',
    'vivu-fct-vn-phase-b:tom-rang-me',
    'Me/nước mắm kcal thấp bỏ qua; đường 15g ước lượng.',
);

$bySlug['canh-chua-ca'] = $factRecipe(
    [
        $line('vn-ca-qua', 150, 'Cá quả/lóc'),
        $line('vn-ca-chua', 80, 'Cà chua'),
        $line('vn-duong-trang', 8, 'Đường'),
        $line('vn-dau-thao-moc', 3, 'Dầu'),
        ['name' => 'Nước canh + rau', 'grams' => 259, 'kcal_per_100g' => 0, 'kcal' => 0],
    ],
    500,
    'FCT VN recipe_sum — canh chua cá (8022+4005+…)',
    'vivu-fct-vn-phase-b:canh-chua-ca',
    'Rau canh chua (đậu bắp, giá…) gần 0 kcal bỏ gộp nước; me bỏ qua.',
);

$bySlug['ca-kho-to'] = $factRecipe(
    [
        $line('vn-ca-thu', 200, 'Cá thu/proxy cá kho'),
        $line('vn-duong-trang', 12, 'Đường kho'),
        $line('vn-dau-thao-moc', 5, 'Dầu'),
    ],
    180,
    'FCT VN recipe_sum — cá kho tộ (8026+12013+6002)',
    'vivu-fct-vn-phase-b:ca-kho-to',
    'Cá basa/lóc thay bằng thu class; nước mắm bỏ qua.',
);

$bySlug['canh-mong-toi-nau-tom'] = $factRecipe(
    [
        $line('vn-rau-mong-toi', 200, 'Mồng tơi'),
        $line('vn-tom-bien', 80, 'Tôm'),
        ['name' => 'Nước', 'grams' => 220, 'kcal_per_100g' => 0, 'kcal' => 0],
    ],
    350,
    'FCT VN recipe_sum — canh mồng tơi tôm (4080+8051)',
    'vivu-fct-vn-phase-b:canh-mong-toi-nau-tom',
    'Canh loãng; tôm bóc vỏ.',
);

// Standard bowls → pilot rows
foreach ($stdDishes as $slug => $dish) {
    if (($dish['fct_source'] ?? '') !== 'vn_2007') {
        continue;
    }
    $lines = $dish['ingredients_breakdown'];
    $bySlug[$slug] = $factRecipe(
        $lines,
        (int) $dish['serving_grams'],
        'FCT VN + vivu-standard-v1 — '.$slug,
        'vivu-standard-v1+vn:'.$slug,
        (string) ($dish['limitations'] ?? ''),
        'medium',
    );
    $bySlug[$slug]['facts'][0]['standard_id'] = 'vivu-standard-v1';
}

$pilotOut = [
    'kb_version' => '1.1.0-fct-vn-pilot',
    'phase' => 'FCT-VN-phase-B',
    'description' => 'Pilot A + rice yield + standard bowls VN + extra home recipe_sum.',
    'fct_table_ref' => 'facts/fct_vn_ingredients.json',
    'yield_standard' => 'vivu-yield-v1',
    'by_slug' => $bySlug,
];
file_put_contents($pilotPath, json_encode($pilotOut, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n");
echo 'pilot_slugs='.count($bySlug).PHP_EOL;

// —— 4) Merge into calories ——
$calories = json_decode(file_get_contents($calPath), true, 512, JSON_THROW_ON_ERROR);
$updated = 0;
foreach ($bySlug as $slug => $row) {
    $prev = $calories['by_slug'][$slug] ?? null;
    $fact0 = $row['facts'][0];
    // strip null standard_id
    if (($fact0['standard_id'] ?? null) === null) {
        unset($fact0['standard_id']);
    }
    if (($fact0['yield_factor'] ?? null) === null) {
        unset($fact0['yield_factor']);
    }
    if (($fact0['yield_standard'] ?? null) === null) {
        unset($fact0['yield_standard']);
    }
    $calories['by_slug'][$slug] = [
        'calories_kcal' => (int) $row['calories_kcal'],
        'serving_grams' => (int) $row['serving_grams'],
        'facts' => [$fact0],
    ];
    if (is_array($prev) && isset($prev['calories_kcal'])) {
        $calories['by_slug'][$slug]['facts'][0]['replaced_from'] = [
            'calories_kcal' => $prev['calories_kcal'],
            'method' => $prev['facts'][0]['method'] ?? null,
        ];
    }
    $updated++;
    $prevK = is_array($prev) ? (string) ($prev['calories_kcal'] ?? '?') : 'new';
    echo "{$slug}: {$prevK} → {$row['calories_kcal']}\n";
}

$calories['kb_version'] = '2.3.0-fact-a';
$calories['fct_vn_pilot'] = [
    'kb_version' => '1.1.0-fct-vn-pilot',
    'applied_at' => '2026-07-11',
    'phase' => 'B',
    'slugs' => array_keys($bySlug),
    'yield_standard' => 'vivu-yield-v1',
    'note' => 'Rice yield + standard bowls + home recipes use VN FCT 2007',
];

file_put_contents($calPath, json_encode($calories, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n");
echo "calories_updated={$updated} kb={$calories['kb_version']}\n";
