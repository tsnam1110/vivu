<?php

declare(strict_types=1);

/**
 * Generator multi-file catalog skeleton (P0 + P1 + P2 curated).
 * P3 stays inventory-only (candidate). Fact III always null.
 * Run: php database/data/what-to-eat/generate_seed_catalog.php
 *
 * @see docs/features/what-to-eat-dish-catalog.md
 */

$fact = static function (string $field, string $phase): array {
    return [
        'field' => $field,
        'method' => 'committee',
        'source_ref' => "docs/features/what-to-eat-dish-catalog.md#seed-{$phase}",
        'source_title' => "ViVu dish catalog inventory Seed-{$phase}",
        'confidence' => 'high',
        'reviewed_at' => '2026-07-11',
        'reviewed_by' => "seed-{$phase}",
    ];
};

/**
 * @param  list<string>  $regions
 * @param  list<string>  $slots
 * @param  list<string>|null  $flavorTags
 */
$d = static function (
    string $slug,
    string $name,
    string $emoji,
    string $summary,
    array $slots,
    bool $light,
    bool $main,
    bool $dine,
    bool $cook,
    string $role,
    array $regions,
    string $phase,
    ?string $keywords = null,
    ?array $flavorTags = null,
) use ($fact): array {
    $facts = [$fact('dish_role', $phase)];
    if ($flavorTags !== null && $flavorTags !== []) {
        $facts[] = $fact('flavor_tags', $phase);
    }

    $row = [
        'slug' => $slug,
        'name' => $name,
        'emoji' => $emoji,
        'summary' => $summary,
        'meal_slots' => $slots,
        'supports_light' => $light,
        'supports_main' => $main,
        'supports_dine_out' => $dine,
        'supports_cook_home' => $cook,
        'dish_role' => $role,
        'region_tags' => $regions,
        'search_keywords' => $keywords,
        'seed_phase' => $phase,
        'calories_kcal' => null,
        'serving_grams' => null,
        'five_element' => null,
        'thermal_nature' => null,
        'protein_source' => null,
        'cooking_method' => null,
        'flavor_tags' => $flavorTags,
        'ingredients' => null,
        'steps' => null,
        'benefits' => null,
        'harms' => null,
        'advice' => null,
        'facts' => $facts,
    ];

    return $row;
};

// [slug, name, emoji, summary, slots, light, main, dine, cook, role, regions, phase, keywords?, flavor?]
$all = [
    // ── one_bowl P0 ──
    ['pho-bo', 'Phở bò', '🍜', 'Món nước dùng bò với bánh phở — phổ biến bữa sáng/trưa.', ['breakfast', 'lunch', 'dinner'], false, true, true, true, 'one_bowl', ['bac', 'quoc_gia'], 'P0', 'pho bo'],
    ['pho-ga', 'Phở gà', '🍜', 'Phở nước dùng gà, bánh phở — lựa chọn sáng phổ biến.', ['breakfast', 'lunch', 'dinner'], false, true, true, true, 'one_bowl', ['bac', 'quoc_gia'], 'P0', 'pho ga'],
    ['bun-bo-hue', 'Bún bò Huế', '🌶️', 'Bún bò sợi to, nước dùng sả ớt — đặc trưng Huế, phổ biến cả nước.', ['breakfast', 'lunch', 'dinner'], false, true, true, true, 'one_bowl', ['trung', 'quoc_gia'], 'P0', 'bun bo hue'],
    ['bun-rieu-cua', 'Bún riêu cua', '🍅', 'Bún nước dùng riêu cua cà chua — món nước quen thuộc.', ['breakfast', 'lunch', 'dinner'], false, true, true, true, 'one_bowl', ['quoc_gia'], 'P0', 'bun rieu'],
    ['bun-cha', 'Bún chả', '🍖', 'Bún, chả nướng và nước mắm chua ngọt — đặc trưng Hà Nội.', ['lunch', 'dinner'], false, true, true, true, 'one_bowl', ['bac'], 'P0', 'bun cha'],
    ['bun-thit-nuong', 'Bún thịt nướng', '🥗', 'Bún kèm thịt nướng, rau sống, nước mắm — phổ biến miền Nam.', ['lunch', 'dinner'], false, true, true, true, 'one_bowl', ['nam', 'quoc_gia'], 'P0', 'bun thit nuong'],
    ['hu-tieu-nam-vang', 'Hủ tiếu Nam Vang', '🍜', 'Hủ tiếu nước dùng heo tôm — phong cách Nam Vang phổ biến Sài Gòn.', ['breakfast', 'lunch', 'dinner'], false, true, true, true, 'one_bowl', ['nam', 'quoc_gia'], 'P0', 'hu tieu'],
    ['mi-quang', 'Mì Quảng', '🍜', 'Mì Quảng nước dùng ít, topping thịt/tôm — đặc trưng Quảng Nam.', ['breakfast', 'lunch', 'dinner'], false, true, true, true, 'one_bowl', ['trung'], 'P0', 'mi quang'],
    ['bo-kho', 'Bò kho', '🍲', 'Thịt bò hầm cà rốt, thường ăn kèm bánh mì hoặc hủ tiếu.', ['breakfast', 'lunch', 'dinner'], false, true, true, true, 'one_bowl', ['quoc_gia'], 'P0', 'bo kho'],
    ['chao-ga', 'Cháo gà', '🥣', 'Cháo loãng nấu gà — bữa sáng/trưa phổ biến, dễ ăn.', ['breakfast', 'lunch', 'dinner'], true, true, true, true, 'one_bowl', ['quoc_gia'], 'P0', 'chao ga'],
    ['banh-mi-thit', 'Bánh mì thịt', '🥖', 'Bánh mì kẹp thịt/pate rau đồ chua — nhanh gọn.', ['breakfast', 'lunch'], true, true, true, true, 'one_bowl', ['quoc_gia'], 'P0', 'banh mi'],
    ['banh-mi-trung', 'Bánh mì trứng', '🥖', 'Bánh mì kẹp trứng ốp la — bữa sáng nhẹ phổ biến.', ['breakfast'], true, false, true, true, 'one_bowl', ['quoc_gia'], 'P0', 'banh mi trung'],
    ['xoi-man', 'Xôi mặn', '🍚', 'Xôi nếp mặn topping chả/lạp xưởng — no lâu buổi sáng.', ['breakfast'], true, true, true, true, 'one_bowl', ['quoc_gia'], 'P0', 'xoi man'],
    ['xoi-ga', 'Xôi gà', '🍚', 'Xôi nếp kèm gà xé/hành phi — bữa sáng no.', ['breakfast', 'lunch'], false, true, true, true, 'one_bowl', ['quoc_gia'], 'P0', 'xoi ga'],
    ['banh-cuon', 'Bánh cuốn', '🥟', 'Bánh cuốn nhân thịt mộc nhĩ, nước chấm — phổ biến Bắc và cả nước.', ['breakfast', 'lunch'], true, true, true, true, 'one_bowl', ['bac', 'quoc_gia'], 'P0', 'banh cuon'],
    ['banh-xeo', 'Bánh xèo', '🥞', 'Bánh xèo giòn nhân tôm thịt giá — đặc trưng Nam, phổ biến cả nước.', ['lunch', 'dinner'], false, true, true, true, 'one_bowl', ['nam', 'quoc_gia'], 'P0', 'banh xeo'],
    ['com-tam-suon', 'Cơm tấm sườn', '🍛', 'Cơm tấm sườn nướng — biểu tượng ẩm thực Nam Bộ / cả nước.', ['breakfast', 'lunch', 'dinner'], false, true, true, true, 'one_bowl', ['nam', 'quoc_gia'], 'P0', 'com tam'],
    ['goi-cuon', 'Gỏi cuốn', '🥬', 'Cuốn tôm thịt rau sống bánh tráng — nhẹ, tươi.', ['lunch', 'dinner'], true, false, true, true, 'one_bowl', ['nam', 'quoc_gia'], 'P0', 'goi cuon'],
    // one_bowl P1
    ['bun-mam', 'Bún mắm', '🍜', 'Bún mắm nước dùng đậm — đặc trưng miền Tây Nam Bộ.', ['lunch', 'dinner'], false, true, true, true, 'one_bowl', ['nam'], 'P1', 'bun mam'],
    ['bun-oc', 'Bún ốc', '🐌', 'Bún ốc nước dùng chua thanh — phổ biến Hà Nội.', ['lunch', 'dinner'], false, true, true, true, 'one_bowl', ['bac'], 'P1', 'bun oc'],
    ['bun-moc', 'Bún mọc', '🍜', 'Bún mọc giò heo/mọc — món nước miền Bắc.', ['breakfast', 'lunch'], false, true, true, true, 'one_bowl', ['bac'], 'P1', 'bun moc'],
    ['bun-thang', 'Bún thang', '🍜', 'Bún thang Hà Nội — nước dùng trong, nhiều topping.', ['breakfast', 'lunch'], false, true, true, true, 'one_bowl', ['bac'], 'P1', 'bun thang'],
    ['bun-bo-nam-bo', 'Bún bò Nam Bộ', '🥗', 'Bún trộn bò xào rau thơm — phong cách Nam.', ['lunch', 'dinner'], false, true, true, true, 'one_bowl', ['nam'], 'P1', 'bun bo nam bo'],
    ['cao-lau', 'Cao lầu', '🍜', 'Cao lầu Hội An — mì đặc, thịt xá xíu, rau sống.', ['lunch', 'dinner'], false, true, true, true, 'one_bowl', ['trung'], 'P1', 'cao lau'],
    ['banh-canh-cua', 'Bánh canh cua', '🦀', 'Bánh canh nước dùng cua — Trung–Nam.', ['breakfast', 'lunch', 'dinner'], false, true, true, true, 'one_bowl', ['trung', 'nam'], 'P1', 'banh canh cua'],
    ['banh-canh-gio-heo', 'Bánh canh giò heo', '🍜', 'Bánh canh giò heo — món nước no phổ biến.', ['breakfast', 'lunch'], false, true, true, true, 'one_bowl', ['trung', 'nam'], 'P1', 'banh canh gio'],
    ['mien-ga', 'Miến gà', '🍜', 'Miến nấu gà — nhẹ, phổ biến cả nước.', ['breakfast', 'lunch', 'dinner'], true, true, true, true, 'one_bowl', ['quoc_gia'], 'P1', 'mien ga'],
    ['chao-long', 'Cháo lòng', '🥣', 'Cháo lòng heo — món sáng bình dân.', ['breakfast', 'lunch'], false, true, true, true, 'one_bowl', ['quoc_gia'], 'P1', 'chao long'],
    ['chao-suon', 'Cháo sườn', '🥣', 'Cháo sườn heo — bữa sáng/trưa no vừa.', ['breakfast', 'lunch'], false, true, true, true, 'one_bowl', ['quoc_gia'], 'P1', 'chao suon'],
    ['banh-mi-xiu-mai', 'Bánh mì xíu mại', '🥖', 'Bánh mì xíu mại sốt cà — sáng phổ biến.', ['breakfast', 'lunch'], true, true, true, true, 'one_bowl', ['nam', 'quoc_gia'], 'P1', 'banh mi xiu mai'],
    ['banh-uot', 'Bánh ướt', '🥟', 'Bánh ướt chấm nước mắm — Bắc/Trung.', ['breakfast'], true, false, true, true, 'one_bowl', ['bac', 'trung'], 'P1', 'banh uot'],
    ['banh-beo', 'Bánh bèo', '🥟', 'Bánh bèo Huế — nhân tôm chấy, nước mắm.', ['breakfast', 'lunch'], true, false, true, true, 'one_bowl', ['trung'], 'P1', 'banh beo'],
    ['com-tam-bi-cha', 'Cơm tấm bì chả', '🍛', 'Cơm tấm bì chả — biến thể cơm tấm Nam.', ['breakfast', 'lunch', 'dinner'], false, true, true, true, 'one_bowl', ['nam'], 'P1', 'com tam bi cha'],
    ['com-ga-hoi-an', 'Cơm gà Hội An', '🍛', 'Cơm gà Hội An — gà xé, cơm vàng, rau sống.', ['lunch', 'dinner'], false, true, true, true, 'one_bowl', ['trung'], 'P1', 'com ga hoi an'],
    ['com-ga-xoi-mo', 'Cơm gà xối mỡ', '🍛', 'Cơm gà xối mỡ — phần cơm gà phổ biến.', ['lunch', 'dinner'], false, true, true, true, 'one_bowl', ['nam', 'quoc_gia'], 'P1', 'com ga xoi mo'],
    ['com-rang-thap-cam', 'Cơm rang thập cẩm', '🍳', 'Cơm rang thập cẩm — ảnh hưởng Hoa–Việt, phổ biến.', ['lunch', 'dinner'], false, true, true, true, 'one_bowl', ['quoc_gia', 'hoa_viet'], 'P1', 'com rang'],
    ['bun-dau-mam-tom', 'Bún đậu mắm tôm', '🥗', 'Bún đậu mắm tôm — đặc trưng Hà Nội; thường ăn nhóm (vẫn gợi ý suất).', ['lunch', 'dinner'], false, true, true, true, 'one_bowl', ['bac'], 'P1', 'bun dau'],
    ['bot-chien', 'Bột chiên', '🍳', 'Bột chiên trứng hành — đặc sản Sài Gòn.', ['breakfast', 'lunch'], true, true, true, true, 'one_bowl', ['nam'], 'P1', 'bot chien'],
    ['bo-ne', 'Bò né', '🍳', 'Bò né trứng — bữa sáng/trưa kiểu quán.', ['breakfast', 'lunch'], false, true, true, false, 'one_bowl', ['nam', 'hoa_viet'], 'P1', 'bo ne'],
    ['mi-xao', 'Mì xào thập cẩm', '🍜', 'Mì trứng xào thập cẩm (mềm hoặc giòn) — suất trọn phổ biến.', ['lunch', 'dinner'], false, true, true, true, 'one_bowl', ['quoc_gia', 'hoa_viet'], 'P1', 'mi xao'],
    ['banh-gio', 'Bánh giò', '🥟', 'Bánh giò nhân thịt — mang đi tiện.', ['breakfast'], true, true, true, true, 'one_bowl', ['bac', 'quoc_gia'], 'P1', 'banh gio'],
    ['banh-bao', 'Bánh bao', '🥟', 'Bánh bao nhân thịt trứng — sáng gọn.', ['breakfast'], true, true, true, true, 'one_bowl', ['quoc_gia', 'hoa_viet'], 'P1', 'banh bao'],

    // soup P0
    ['canh-chua-ca', 'Canh chua cá', '🐟', 'Canh chua cá — mâm nhà miền Nam / cả nước.', ['lunch', 'dinner'], true, false, true, true, 'soup', ['nam', 'quoc_gia'], 'P0', 'canh chua'],
    ['canh-kho-qua-nhoi-thit', 'Canh khổ qua nhồi thịt', '🍲', 'Canh khổ qua nhồi thịt bằm — mâm nhà quen thuộc.', ['lunch', 'dinner'], true, false, true, true, 'soup', ['nam', 'quoc_gia'], 'P0', 'canh kho qua'],
    ['canh-bi-do', 'Canh bí đỏ', '🎃', 'Canh bí đỏ nấu tôm hoặc thịt — ngọt thanh.', ['lunch', 'dinner'], true, false, true, true, 'soup', ['quoc_gia'], 'P0', 'canh bi do'],
    ['canh-rau-ngot-thit-bam', 'Canh rau ngót thịt bằm', '🥬', 'Canh rau ngót nấu thịt bằm — mâm nhà phổ biến.', ['lunch', 'dinner'], true, false, true, true, 'soup', ['quoc_gia'], 'P0', 'canh rau ngot'],
    ['canh-cai-thit-bam', 'Canh cải thịt bằm', '🥬', 'Canh cải nấu thịt bằm.', ['lunch', 'dinner'], true, false, true, true, 'soup', ['quoc_gia'], 'P0', 'canh cai'],
    ['canh-xuong-rau-cu', 'Canh xương rau củ', '🦴', 'Canh xương heo nấu rau củ — nền canh gia đình.', ['lunch', 'dinner'], true, false, true, true, 'soup', ['quoc_gia'], 'P0', 'canh xuong'],
    // soup P1
    ['canh-chua-tom', 'Canh chua tôm', '🦐', 'Canh chua tôm — biến thể canh chua Nam.', ['lunch', 'dinner'], true, false, true, true, 'soup', ['nam'], 'P1', 'canh chua tom'],
    ['canh-muop-moc', 'Canh mướp mọc', '🍲', 'Canh mướp nấu mọc — mâm nhà nhẹ.', ['lunch', 'dinner'], true, false, true, true, 'soup', ['quoc_gia'], 'P1', 'canh muop'],
    ['canh-ga-la-giang', 'Canh gà lá giang', '🐔', 'Canh gà lá giang — Nam/Trung.', ['lunch', 'dinner'], true, false, true, true, 'soup', ['nam', 'trung'], 'P1', 'canh ga la giang'],
    ['canh-cua-rau-day', 'Canh cua rau đay', '🦀', 'Canh cua rau đay — đặc trưng Bắc.', ['lunch', 'dinner'], true, false, true, true, 'soup', ['bac'], 'P1', 'canh cua'],
    ['canh-bau-tom', 'Canh bầu tôm', '🦐', 'Canh bầu nấu tôm — mâm nhà cả nước.', ['lunch', 'dinner'], true, false, true, true, 'soup', ['quoc_gia'], 'P1', 'canh bau'],
    ['sup-cua', 'Súp cua', '🦀', 'Súp cua — khai vị/canh kiểu Hoa–Việt.', ['breakfast', 'lunch', 'dinner'], true, false, true, true, 'soup', ['quoc_gia', 'hoa_viet'], 'P1', 'sup cua'],

    // main_protein P0
    ['thit-kho-tau', 'Thịt kho tàu', '🍖', 'Thịt ba chỉ kho nước dừa/trứng — món mặn mâm Nam / cả nước.', ['lunch', 'dinner'], false, true, true, true, 'main_protein', ['nam', 'quoc_gia'], 'P0', 'thit kho'],
    ['thit-kho-trung', 'Thịt kho trứng', '🥚', 'Thịt kho trứng vị mặn ngọt — mâm nhà cả nước.', ['lunch', 'dinner'], false, true, true, true, 'main_protein', ['quoc_gia'], 'P0', 'thit kho trung'],
    ['ca-kho-to', 'Cá kho tộ', '🐟', 'Cá kho tộ đậm đà — món mặn miền Nam / cả nước.', ['lunch', 'dinner'], false, true, true, true, 'main_protein', ['nam', 'quoc_gia'], 'P0', 'ca kho'],
    ['suon-ram-man', 'Sườn ram mặn', '🍖', 'Sườn heo ram mặn — món mặn mâm cơm.', ['lunch', 'dinner'], false, true, true, true, 'main_protein', ['quoc_gia'], 'P0', 'suon ram'],
    ['ga-kho-gung', 'Gà kho gừng', '🐔', 'Gà kho gừng ấm vị — mâm nhà dễ nấu.', ['lunch', 'dinner'], false, true, true, true, 'main_protein', ['quoc_gia'], 'P0', 'ga kho'],
    ['ga-luoc', 'Gà luộc', '🐔', 'Gà luộc chấm muối tiêu chanh.', ['lunch', 'dinner'], false, true, true, true, 'main_protein', ['quoc_gia'], 'P0', 'ga luoc'],
    ['dau-phu-sot-ca', 'Đậu phụ sốt cà', '🧈', 'Đậu phụ sốt cà chua — đạm thực vật mâm nhà.', ['lunch', 'dinner'], true, true, true, true, 'main_protein', ['quoc_gia'], 'P0', 'dau phu sot ca'],
    ['trung-chien', 'Trứng chiên', '🍳', 'Trứng chiên đơn giản — món mặn nhẹ phổ biến.', ['lunch', 'dinner'], true, true, true, true, 'main_protein', ['quoc_gia'], 'P0', 'trung chien'],
    // main P1
    ['ca-kho-rieng', 'Cá kho riềng', '🐟', 'Cá kho riềng — phong cách Bắc.', ['lunch', 'dinner'], false, true, true, true, 'main_protein', ['bac'], 'P1', 'ca kho rieng'],
    ['ga-rang-muoi', 'Gà rang muối', '🐔', 'Gà rang muối — món mặn mâm/tiệc nhẹ.', ['lunch', 'dinner'], false, true, true, true, 'main_protein', ['quoc_gia'], 'P1', 'ga rang muoi'],
    ['tom-rang-me', 'Tôm rang me', '🦐', 'Tôm rang me — món mặn miền Nam.', ['lunch', 'dinner'], false, true, true, true, 'main_protein', ['nam'], 'P1', 'tom rang me'],
    ['muc-xao-chua-ngot', 'Mực xào chua ngọt', '🦑', 'Mực xào chua ngọt — món mặn phổ biến.', ['lunch', 'dinner'], false, true, true, true, 'main_protein', ['quoc_gia'], 'P1', 'muc xao'],
    ['bo-xao-luc-lac', 'Bò xào lúc lắc', '🥩', 'Bò xào lúc lắc — món mặn đậm đà.', ['lunch', 'dinner'], false, true, true, true, 'main_protein', ['quoc_gia'], 'P1', 'bo luc lac'],
    ['cha-la-lot', 'Chả lá lốt', '🌿', 'Chả thịt bọc lá lốt nướng/chiên.', ['lunch', 'dinner'], false, true, true, true, 'main_protein', ['quoc_gia'], 'P1', 'cha la lot'],
    ['cha-trung-hap', 'Chả trứng hấp', '🥚', 'Chả trứng hấp — món mặn mâm nhà.', ['lunch', 'dinner'], false, true, true, true, 'main_protein', ['quoc_gia'], 'P1', 'cha trung'],
    ['ca-chien', 'Cá chiên', '🐟', 'Cá chiên giòn — món mặn phổ biến.', ['lunch', 'dinner'], false, true, true, true, 'main_protein', ['quoc_gia'], 'P1', 'ca chien'],
    ['thit-xao-nam', 'Thịt xào nấm', '🍄', 'Thịt xào nấm — món mặn mâm cơm.', ['lunch', 'dinner'], false, true, true, true, 'main_protein', ['quoc_gia'], 'P1', 'thit xao nam'],

    // side_veg P0
    ['rau-muong-xao-toi', 'Rau muống xào tỏi', '🌿', 'Rau muống xào tỏi — rau mâm nhà quốc dân.', ['lunch', 'dinner'], true, false, true, true, 'side_veg', ['quoc_gia'], 'P0', 'rau muong'],
    ['cai-xao-toi', 'Cải xào tỏi', '🥬', 'Cải xanh xào tỏi — món rau mâm cơm.', ['lunch', 'dinner'], true, false, true, true, 'side_veg', ['quoc_gia'], 'P0', 'cai xao'],
    ['su-su-xao-toi', 'Su su xào tỏi', '🥒', 'Su su xào tỏi — rau giòn mâm nhà.', ['lunch', 'dinner'], true, false, true, true, 'side_veg', ['quoc_gia'], 'P0', 'su su'],
    ['rau-luoc-kho-quet', 'Rau luộc chấm kho quẹt', '🥬', 'Rau củ luộc chấm kho quẹt — phong cách Nam.', ['lunch', 'dinner'], true, false, true, true, 'side_veg', ['nam'], 'P0', 'rau luoc'],
    ['dua-mon', 'Dưa món', '🥒', 'Dưa món/dưa chua ăn kèm mâm.', ['lunch', 'dinner'], true, false, true, true, 'side_veg', ['quoc_gia'], 'P0', 'dua mon'],
    // side_veg P1
    ['dau-que-xao', 'Đậu que xào', '🫛', 'Đậu que xào tỏi/thịt — rau mâm nhà.', ['lunch', 'dinner'], true, false, true, true, 'side_veg', ['quoc_gia'], 'P1', 'dau que'],
    ['bi-dao-xao', 'Bí đao xào', '🥒', 'Bí đao xào — rau mâm nhà.', ['lunch', 'dinner'], true, false, true, true, 'side_veg', ['quoc_gia'], 'P1', 'bi dao'],
    ['bau-xao-trung', 'Bầu xào trứng', '🥚', 'Bầu xào trứng — rau mâm nhà.', ['lunch', 'dinner'], true, false, true, true, 'side_veg', ['quoc_gia'], 'P1', 'bau xao'],
    ['muop-xao-trung', 'Mướp xào trứng', '🥚', 'Mướp xào trứng — rau mâm nhà.', ['lunch', 'dinner'], true, false, true, true, 'side_veg', ['quoc_gia'], 'P1', 'muop xao'],
    ['rau-mong-toi-xao', 'Rau mồng tơi xào', '🌿', 'Rau mồng tơi xào tỏi.', ['lunch', 'dinner'], true, false, true, true, 'side_veg', ['quoc_gia'], 'P1', 'mong toi'],
    ['cai-thia-xao', 'Cải thìa xào', '🥬', 'Cải thìa xào tỏi.', ['lunch', 'dinner'], true, false, true, true, 'side_veg', ['quoc_gia'], 'P1', 'cai thia'],
    ['goi-du-du', 'Gỏi đu đủ', '🥗', 'Gỏi đu đủ chua cay — Nam / cả nước.', ['lunch', 'dinner'], true, false, true, true, 'side_veg', ['nam', 'quoc_gia'], 'P1', 'goi du du'],
    ['goi-ga-bap-cai', 'Gỏi gà bắp cải', '🥗', 'Gỏi gà bắp cải — món rau/gỏi mâm.', ['lunch', 'dinner'], true, false, true, true, 'side_veg', ['quoc_gia'], 'P1', 'goi ga'],
    ['nam-xao-toi', 'Nấm xào tỏi', '🍄', 'Nấm xào tỏi — rau/nấm mâm nhà.', ['lunch', 'dinner'], true, false, true, true, 'side_veg', ['quoc_gia'], 'P1', 'nam xao'],
    ['salad-dua-leo-ca-chua', 'Salad dưa leo cà chua', '🥗', 'Salad dưa leo cà chua — rau sống nhẹ.', ['lunch', 'dinner'], true, false, true, true, 'side_veg', ['quoc_gia'], 'P1', 'salad'],

    // side_extra
    ['dau-phu-chien', 'Đậu phụ chiên', '🧈', 'Đậu phụ chiên vàng — món phụ mâm cơm.', ['lunch', 'dinner'], true, false, true, true, 'side_extra', ['quoc_gia'], 'P0', 'dau phu chien'],
    ['trung-op-la', 'Trứng ốp la', '🍳', 'Trứng ốp la — phụ bữa sáng/trưa nhanh.', ['breakfast', 'lunch'], true, true, true, true, 'side_extra', ['quoc_gia'], 'P0', 'trung op la'],
    ['nem-ran', 'Nem rán', '🥟', 'Nem rán (chả giò Bắc) — món phụ/chiên.', ['lunch', 'dinner'], true, true, true, true, 'side_extra', ['bac'], 'P1', 'nem ran'],
    ['cha-gio', 'Chả giò', '🥟', 'Chả giò chiên — phong cách Nam.', ['lunch', 'dinner'], true, true, true, true, 'side_extra', ['nam'], 'P1', 'cha gio'],
    ['trung-kho', 'Trứng kho', '🥚', 'Trứng kho mặn — món phụ mâm.', ['lunch', 'dinner'], true, true, true, true, 'side_extra', ['quoc_gia'], 'P1', 'trung kho'],
    ['ruoc', 'Ruốc', '🍖', 'Ruốc/chà bông — phụ cơm/xôi.', ['breakfast', 'lunch', 'dinner'], true, false, true, true, 'side_extra', ['quoc_gia'], 'P1', 'ruoc'],

    // starch
    ['com-trang', 'Cơm trắng', '🍚', 'Cơm trắng — tinh bột nền mâm Việt.', ['lunch', 'dinner'], false, true, true, true, 'starch', ['quoc_gia'], 'P0', 'com trang'],
    ['com-gao-lut', 'Cơm gạo lứt', '🍚', 'Cơm gạo lứt — tinh bột thay thế.', ['lunch', 'dinner'], false, true, true, true, 'starch', ['quoc_gia'], 'P1', 'com gao lut'],
    ['xoi-trang', 'Xôi trắng', '🍚', 'Xôi trắng — tinh bột nếp.', ['breakfast', 'lunch'], false, true, true, true, 'starch', ['quoc_gia'], 'P1', 'xoi trang'],

    // dessert
    ['che-dau-xanh', 'Chè đậu xanh', '🍮', 'Chè đậu xanh — tráng miệng/ăn nhẹ phổ biến.', ['breakfast', 'lunch', 'dinner'], true, false, true, true, 'dessert_light', ['quoc_gia'], 'P0', 'che'],
    ['sua-chua', 'Sữa chua', '🥛', 'Sữa chua — ăn nhẹ gọn.', ['breakfast', 'lunch', 'dinner'], true, false, true, true, 'dessert_light', ['quoc_gia'], 'P0', 'sua chua'],
    ['khoai-lang-nuong', 'Khoai lang nướng', '🍠', 'Khoai lang nướng — ăn nhẹ no vừa.', ['breakfast', 'lunch', 'dinner'], true, false, true, true, 'dessert_light', ['quoc_gia'], 'P0', 'khoai lang'],
    ['trai-cay-dia', 'Trái cây dĩa', '🍎', 'Dĩa trái cây theo mùa — ăn nhẹ tươi.', ['lunch', 'dinner'], true, false, true, true, 'dessert_light', ['quoc_gia'], 'P0', 'trai cay'],
    ['che-ba-mau', 'Chè ba màu', '🍮', 'Chè ba màu — tráng miệng miền Nam.', ['lunch', 'dinner'], true, false, true, true, 'dessert_light', ['nam'], 'P1', 'che ba mau'],
    ['che-thai', 'Chè Thái', '🍮', 'Chè Thái — tráng miệng phổ biến Nam.', ['lunch', 'dinner'], true, false, true, true, 'dessert_light', ['nam'], 'P1', 'che thai'],
    ['che-chuoi', 'Chè chuối', '🍌', 'Chè chuối — tráng miệng cả nước.', ['lunch', 'dinner'], true, false, true, true, 'dessert_light', ['quoc_gia'], 'P1', 'che chuoi'],
    ['banh-flan', 'Bánh flan', '🍮', 'Bánh flan caramen — tráng miệng phổ biến.', ['lunch', 'dinner'], true, false, true, true, 'dessert_light', ['quoc_gia'], 'P1', 'banh flan'],
    ['chuoi-nep-nuong', 'Chuối nếp nướng', '🍌', 'Chuối nếp nướng — ăn nhẹ Nam.', ['lunch', 'dinner'], true, false, true, true, 'dessert_light', ['nam'], 'P1', 'chuoi nep nuong'],
    ['xoi-dau-xanh', 'Xôi đậu xanh', '🍚', 'Xôi đậu xanh ngọt — ăn nhẹ/sáng.', ['breakfast'], true, false, true, true, 'dessert_light', ['quoc_gia'], 'P1', 'xoi dau xanh'],

    // beverage
    ['ca-phe-sua-da', 'Cà phê sữa đá', '☕', 'Cà phê sữa đá — đồ uống đặc trưng Việt.', ['breakfast', 'lunch', 'dinner'], true, false, true, true, 'beverage', ['nam', 'quoc_gia'], 'P0', 'ca phe sua da'],
    ['ca-phe-den-da', 'Cà phê đen đá', '☕', 'Cà phê đen đá — đồ uống phổ biến.', ['breakfast', 'lunch', 'dinner'], true, false, true, true, 'beverage', ['quoc_gia'], 'P0', 'ca phe den'],
    ['tra-da', 'Trà đá', '🍵', 'Trà đá — đồ uống quán ăn Việt.', ['lunch', 'dinner'], true, false, true, true, 'beverage', ['quoc_gia'], 'P0', 'tra da'],
    ['sua-dau-nanh', 'Sữa đậu nành', '🥛', 'Sữa đậu nành — đồ uống sáng nhẹ.', ['breakfast', 'lunch'], true, false, true, true, 'beverage', ['quoc_gia'], 'P0', 'sua dau nanh'],
    ['nuoc-mia', 'Nước mía', '🥤', 'Nước mía — đồ uống đường phố.', ['lunch', 'dinner'], true, false, true, true, 'beverage', ['quoc_gia'], 'P1', 'nuoc mia'],
    ['sinh-to-bo', 'Sinh tố bơ', '🥑', 'Sinh tố bơ — đồ uống miền Nam.', ['lunch', 'dinner'], true, false, true, true, 'beverage', ['nam'], 'P1', 'sinh to bo'],
    ['nuoc-chanh', 'Nước chanh', '🍋', 'Nước chanh — đồ uống giải khát.', ['lunch', 'dinner'], true, false, true, true, 'beverage', ['quoc_gia'], 'P1', 'nuoc chanh'],
    ['tra-tac', 'Trà tắc', '🍊', 'Trà tắc — đồ uống Nam.', ['lunch', 'dinner'], true, false, true, true, 'beverage', ['nam'], 'P1', 'tra tac'],

    // share_feast
    ['lau-thai', 'Lẩu thái', '🍲', 'Lẩu thái chua cay — món chia sẻ nhóm.', ['lunch', 'dinner'], false, true, true, true, 'share_feast', ['quoc_gia'], 'P0', 'lau thai'],
    ['lau-bo', 'Lẩu bò', '🍲', 'Lẩu bò — món chia sẻ nhóm phổ biến.', ['lunch', 'dinner'], false, true, true, true, 'share_feast', ['quoc_gia'], 'P0', 'lau bo'],
    ['nuong-bbq-set', 'Nướng BBQ set', '🔥', 'Set thịt nướng BBQ — ăn chia sẻ nhóm.', ['lunch', 'dinner'], false, true, true, false, 'share_feast', ['quoc_gia'], 'P0', 'nuong bbq'],
    ['lau-hai-san', 'Lẩu hải sản', '🦐', 'Lẩu hải sản — chia sẻ nhóm.', ['lunch', 'dinner'], false, true, true, true, 'share_feast', ['quoc_gia'], 'P1', 'lau hai san'],
    ['lau-mam', 'Lẩu mắm', '🍲', 'Lẩu mắm — đặc trưng miền Tây.', ['lunch', 'dinner'], false, true, true, true, 'share_feast', ['nam'], 'P1', 'lau mam'],
    ['lau-nam', 'Lẩu nấm', '🍄', 'Lẩu nấm — chia sẻ, có thể chay biến thể.', ['lunch', 'dinner'], false, true, true, true, 'share_feast', ['quoc_gia'], 'P1', 'lau nam'],
    ['bo-nuong-la-lot', 'Bò nướng lá lốt', '🌿', 'Bò nướng lá lốt — món nướng chia sẻ.', ['lunch', 'dinner'], false, true, true, true, 'share_feast', ['nam', 'quoc_gia'], 'P1', 'bo nuong la lot'],
    ['oc-xao', 'Ốc xào', '🐌', 'Ốc xào (sa tế/me…) — món chia sẻ/nhậu phổ biến.', ['lunch', 'dinner'], true, true, true, false, 'share_feast', ['quoc_gia'], 'P1', 'oc xao'],

    // chay P1 — role as indicated, flavor_tags chay
    ['pho-chay', 'Phở chay', '🍜', 'Phở chay — suất trọn không thịt.', ['breakfast', 'lunch', 'dinner'], false, true, true, true, 'one_bowl', ['quoc_gia'], 'P1', 'pho chay', ['chay']],
    ['bun-chay', 'Bún chay', '🍜', 'Bún chay — suất trọn chay.', ['lunch', 'dinner'], false, true, true, true, 'one_bowl', ['quoc_gia'], 'P1', 'bun chay', ['chay']],
    ['com-chay-thap-cam', 'Cơm chay thập cẩm', '🍛', 'Cơm chay thập cẩm — suất chay.', ['lunch', 'dinner'], false, true, true, true, 'one_bowl', ['quoc_gia'], 'P1', 'com chay', ['chay']],
    ['xoi-chay', 'Xôi chay', '🍚', 'Xôi chay — sáng chay.', ['breakfast'], true, true, true, true, 'one_bowl', ['quoc_gia'], 'P1', 'xoi chay', ['chay']],
    ['dau-hu-kho-nam', 'Đậu hũ kho nấm', '🧈', 'Đậu hũ kho nấm — món mặn chay.', ['lunch', 'dinner'], false, true, true, true, 'main_protein', ['quoc_gia'], 'P1', 'dau hu kho nam', ['chay']],
    ['nam-kho-tieu', 'Nấm kho tiêu', '🍄', 'Nấm kho tiêu — món mặn chay.', ['lunch', 'dinner'], false, true, true, true, 'main_protein', ['quoc_gia'], 'P1', 'nam kho tieu', ['chay']],
    ['canh-rau-cu-chay', 'Canh rau củ chay', '🍲', 'Canh rau củ chay — canh mâm chay.', ['lunch', 'dinner'], true, false, true, true, 'soup', ['quoc_gia'], 'P1', 'canh chay', ['chay']],
    ['canh-bi-do-chay', 'Canh bí đỏ chay', '🎃', 'Canh bí đỏ chay — canh mâm chay.', ['lunch', 'dinner'], true, false, true, true, 'soup', ['quoc_gia'], 'P1', 'canh bi do chay', ['chay']],
    ['rau-cu-luoc-cham-tuong', 'Rau củ luộc chấm tương', '🥬', 'Rau củ luộc chấm tương — rau mâm chay.', ['lunch', 'dinner'], true, false, true, true, 'side_veg', ['quoc_gia'], 'P1', 'rau luoc chay', ['chay']],
    // Align role with goi-cuon (one_bowl light) for compose/pick consistency
    ['goi-cuon-chay', 'Gỏi cuốn chay', '🥬', 'Gỏi cuốn chay — cuốn rau/nấm bánh tráng, chay.', ['lunch', 'dinner'], true, false, true, true, 'one_bowl', ['quoc_gia'], 'P1', 'goi cuon chay', ['chay']],

    // ── P2 (catalog đầy lô 1 — skeleton; fact III null) ──
    // cook_home=false khi món thiên quán/đặc sản khó nấu nhà (inventory △)
    ['bun-ca', 'Bún cá', '🐟', 'Bún cá (kiểu Hải Phòng/Nha Trang biến thể) — món nước cá phổ biến ven biển/Bắc–Trung.', ['lunch', 'dinner'], false, true, true, false, 'one_bowl', ['bac', 'trung'], 'P2', 'bun ca'],
    ['mien-luon', 'Miến lươn', '🍜', 'Miến lươn — món nước đặc trưng miền Bắc (Nghệ–Hà Nội).', ['lunch', 'dinner'], false, true, true, false, 'one_bowl', ['bac'], 'P2', 'mien luon'],
    ['banh-bot-loc', 'Bánh bột lọc', '🥟', 'Bánh bột lọc Huế — nhân tôm thịt, nước chấm.', ['lunch'], true, false, true, true, 'one_bowl', ['trung'], 'P2', 'banh bot loc'],
    ['banh-can', 'Bánh căn', '🍳', 'Bánh căn trứng cút — đặc trưng Trung–Nam (Nha Trang/Đà Lạt…).', ['breakfast', 'lunch'], true, true, true, false, 'one_bowl', ['trung'], 'P2', 'banh can'],
    ['nem-nuong-nha-trang', 'Nem nướng Nha Trang', '🍖', 'Nem nướng Nha Trang — cuốn bánh tráng rau sống.', ['lunch', 'dinner'], false, true, true, false, 'one_bowl', ['trung'], 'P2', 'nem nuong'],
    ['cha-ca-la-vong', 'Chả cá Lã Vọng', '🐟', 'Chả cá Lã Vọng — cá thì là nghệ, đặc trưng Hà Nội.', ['lunch', 'dinner'], false, true, true, false, 'one_bowl', ['bac'], 'P2', 'cha ca la vong'],
    ['pho-tron', 'Phở trộn', '🍜', 'Phở trộn — bánh phở trộn thịt/rau, ít nước.', ['lunch', 'dinner'], false, true, true, true, 'one_bowl', ['quoc_gia'], 'P2', 'pho tron'],
    ['com-hen', 'Cơm hến', '🍛', 'Cơm hến — đặc sản Huế.', ['lunch'], true, true, true, false, 'one_bowl', ['trung'], 'P2', 'com hen'],
    ['banh-hoi-thit-nuong', 'Bánh hỏi thịt nướng', '🥗', 'Bánh hỏi kèm thịt nướng — Trung–Nam.', ['lunch', 'dinner'], false, true, true, true, 'one_bowl', ['trung', 'nam'], 'P2', 'banh hoi'],
    ['banh-khoai', 'Bánh khoái', '🥞', 'Bánh khoái Huế — bánh xèo nhỏ nhân tôm thịt.', ['lunch', 'dinner'], false, true, true, false, 'one_bowl', ['trung'], 'P2', 'banh khoai'],
    ['banh-trang-tron', 'Bánh tráng trộn', '🥗', 'Bánh tráng trộn — ăn vặt Sài Gòn.', ['lunch'], true, false, true, false, 'one_bowl', ['nam'], 'P2', 'banh trang tron'],

    ['canh-chua-ca-loc', 'Canh chua cá lóc', '🐟', 'Canh chua cá lóc — biến thể canh chua Nam Bộ.', ['lunch', 'dinner'], true, false, true, true, 'soup', ['nam'], 'P2', 'canh chua ca loc'],
    ['canh-khoai-mo', 'Canh khoai mỡ', '🍲', 'Canh khoai mỡ — canh ngọt mâm Nam.', ['lunch', 'dinner'], true, false, true, true, 'soup', ['nam'], 'P2', 'canh khoai mo'],
    ['canh-mong-toi-nau-tom', 'Canh mồng tơi nấu tôm', '🦐', 'Canh mồng tơi nấu tôm — canh mâm nhà.', ['lunch', 'dinner'], true, false, true, true, 'soup', ['quoc_gia'], 'P2', 'canh mong toi'],
    ['canh-bi-dao', 'Canh bí đao', '🥒', 'Canh bí đao — canh thanh mâm nhà.', ['lunch', 'dinner'], true, false, true, true, 'soup', ['quoc_gia'], 'P2', 'canh bi dao'],

    ['vit-om-sau', 'Vịt om sấu', '🦆', 'Vịt om sấu — món mặn miền Bắc.', ['lunch', 'dinner'], false, true, true, true, 'main_protein', ['bac'], 'P2', 'vit om sau'],
    ['heo-quay', 'Heo quay', '🍖', 'Heo quay da giòn — món mặn/tiệc (Hoa–Việt phổ biến).', ['lunch', 'dinner'], false, true, true, false, 'main_protein', ['quoc_gia', 'hoa_viet'], 'P2', 'heo quay'],
    ['tom-chien-bot', 'Tôm chiên bột', '🦐', 'Tôm chiên bột — món mặn chiên phổ biến.', ['lunch', 'dinner'], false, true, true, true, 'main_protein', ['quoc_gia'], 'P2', 'tom chien bot'],
    ['suon-chua-ngot', 'Sườn chua ngọt', '🍖', 'Sườn heo sốt chua ngọt — món mặn mâm cơm.', ['lunch', 'dinner'], false, true, true, true, 'main_protein', ['quoc_gia'], 'P2', 'suon chua ngot'],

    ['nom-hoa-chuoi', 'Nộm hoa chuối', '🥗', 'Nộm hoa chuối — gỏi/rau mâm Bắc và cả nước.', ['lunch', 'dinner'], true, false, true, true, 'side_veg', ['bac', 'quoc_gia'], 'P2', 'nom hoa chuoi'],
    ['rau-lang-xao', 'Rau lang xào', '🌿', 'Rau lang xào tỏi — rau mâm nhà.', ['lunch', 'dinner'], true, false, true, true, 'side_veg', ['quoc_gia'], 'P2', 'rau lang'],
    ['dau-bap-xao', 'Đậu bắp xào', '🫛', 'Đậu bắp xào — rau mâm nhà.', ['lunch', 'dinner'], true, false, true, true, 'side_veg', ['quoc_gia'], 'P2', 'dau bap'],

    ['nem-chua', 'Nem chua', '🍖', 'Nem chua — món phụ/khai vị Trung Bộ.', ['lunch', 'dinner'], true, false, true, false, 'side_extra', ['trung'], 'P2', 'nem chua'],
    ['tep-rang', 'Tép rang', '🦐', 'Tép rang — món phụ mâm nhà.', ['lunch', 'dinner'], true, false, true, true, 'side_extra', ['quoc_gia'], 'P2', 'tep rang'],
    ['cha-lua', 'Chả lụa (miếng)', '🍖', 'Chả lụa thái miếng — món phụ cơm/bánh.', ['breakfast', 'lunch', 'dinner'], true, false, true, false, 'side_extra', ['quoc_gia'], 'P2', 'cha lua'],

    ['bun-tuoi', 'Bún tươi', '🍜', 'Bún tươi — tinh bột kèm mâm (bún chả, bún thịt…).', ['lunch', 'dinner'], false, true, true, true, 'starch', ['quoc_gia'], 'P2', 'bun tuoi'],

    ['che-bap', 'Chè bắp', '🍮', 'Chè bắp — tráng miệng miền Nam.', ['lunch', 'dinner'], true, false, true, true, 'dessert_light', ['nam'], 'P2', 'che bap'],
    ['banh-cam', 'Bánh cam', '🍩', 'Bánh cam/bánh rán — bánh chiên ngọt phổ biến.', ['lunch', 'dinner'], true, false, true, false, 'dessert_light', ['quoc_gia'], 'P2', 'banh cam'],
    ['suong-sao', 'Sương sáo', '🍮', 'Sương sáo/sương sa — tráng miệng mát.', ['lunch', 'dinner'], true, false, true, true, 'dessert_light', ['quoc_gia'], 'P2', 'suong sao'],
    ['banh-chuoi-nuong', 'Bánh chuối nướng', '🍌', 'Bánh chuối nướng — tráng miệng phổ biến.', ['lunch', 'dinner'], true, false, true, true, 'dessert_light', ['quoc_gia'], 'P2', 'banh chuoi nuong'],

    ['sinh-to-xoai', 'Sinh tố xoài', '🥭', 'Sinh tố xoài — đồ uống trái cây.', ['lunch', 'dinner'], true, false, true, true, 'beverage', ['nam', 'quoc_gia'], 'P2', 'sinh to xoai'],
    ['nuoc-dua', 'Nước dừa', '🥥', 'Nước dừa tươi — đồ uống phổ biến Nam / cả nước.', ['lunch', 'dinner'], true, false, true, false, 'beverage', ['nam', 'quoc_gia'], 'P2', 'nuoc dua'],

    ['lau-ga-la-e', 'Lẩu gà lá é', '🍲', 'Lẩu gà lá é — đặc trưng Tây Nguyên / Lâm Đồng.', ['lunch', 'dinner'], false, true, true, false, 'share_feast', ['tay_nguyen', 'trung'], 'P2', 'lau ga la e'],
    ['lau-rieu-cua-bap-bo', 'Lẩu riêu cua bắp bò', '🍲', 'Lẩu riêu cua bắp bò — món chia sẻ nhóm.', ['lunch', 'dinner'], false, true, true, false, 'share_feast', ['quoc_gia'], 'P2', 'lau rieu'],
    ['hai-san-nuong-moi', 'Hải sản nướng mọi', '🦐', 'Hải sản nướng mọi — nướng chia sẻ ven biển Trung–Nam.', ['lunch', 'dinner'], false, true, true, false, 'share_feast', ['trung', 'nam'], 'P2', 'hai san nuong'],
    ['ga-nuong-com-lam', 'Gà nướng cơm lam', '🔥', 'Gà nướng cơm lam — đặc trưng Tây Nguyên / Trung.', ['lunch', 'dinner'], false, true, true, false, 'share_feast', ['tay_nguyen', 'trung'], 'P2', 'ga nuong com lam'],

    ['hu-tieu-chay', 'Hủ tiếu chay', '🍜', 'Hủ tiếu chay — suất trọn chay (Nam).', ['breakfast', 'lunch'], false, true, true, true, 'one_bowl', ['nam'], 'P2', 'hu tieu chay', ['chay']],
    ['com-tam-chay', 'Cơm tấm chay', '🍛', 'Cơm tấm chay — suất chay kiểu cơm tấm.', ['lunch', 'dinner'], false, true, true, true, 'one_bowl', ['nam'], 'P2', 'com tam chay', ['chay']],
    ['mien-xao-chay', 'Miến xào chay', '🍜', 'Miến xào chay — suất trọn chay.', ['lunch', 'dinner'], false, true, true, true, 'one_bowl', ['quoc_gia'], 'P2', 'mien xao chay', ['chay']],
    ['nem-chay-ran', 'Nem chay rán', '🥟', 'Nem chay rán — món phụ chay.', ['lunch', 'dinner'], true, false, true, true, 'side_extra', ['quoc_gia'], 'P2', 'nem chay', ['chay']],
    ['che-thap-cam-chay', 'Chè thập cẩm chay', '🍮', 'Chè thập cẩm chay — tráng miệng chay.', ['lunch', 'dinner'], true, false, true, true, 'dessert_light', ['quoc_gia'], 'P2', 'che chay', ['chay']],
];

$shards = [];
foreach ($all as $row) {
    $flavor = $row[13] ?? null;
    $dish = $d(
        $row[0],
        $row[1],
        $row[2],
        $row[3],
        $row[4],
        $row[5],
        $row[6],
        $row[7],
        $row[8],
        $row[9],
        $row[10],
        $row[11],
        $row[12] ?? null,
        $flavor,
    );

    // Chay dishes with flavor_tags go to chay shard for review; also need dish_role in role pool.
    // Strategy: put chay-tagged into dishes_v1_chay.json ONLY (importer merge all shards).
    // Role-based compose still sees them because dish_role is on the record.
    if (is_array($flavor) && in_array('chay', $flavor, true)) {
        $shards['chay'][] = $dish;
    } else {
        $shards[$row[9]][] = $dish;
    }
}

$baseDir = __DIR__;
$dir = $baseDir.DIRECTORY_SEPARATOR.'dishes_v1';
if (! is_dir($dir) && ! mkdir($dir, 0777, true) && ! is_dir($dir)) {
    throw new RuntimeException("Cannot create {$dir}");
}

// Remove old shard files not in new set
foreach (glob($dir.DIRECTORY_SEPARATOR.'dishes_v1_*.json') ?: [] as $old) {
    // will overwrite known files; leave extras if any
}

$order = [
    'one_bowl',
    'soup',
    'main_protein',
    'side_veg',
    'side_extra',
    'starch',
    'dessert_light',
    'beverage',
    'share_feast',
    'chay',
];

$files = [];
$total = 0;
$p0 = 0;
$p1 = 0;
$p2 = 0;

foreach ($order as $role) {
    if (empty($shards[$role])) {
        continue;
    }
    $dishes = $shards[$role];
    $fname = "dishes_v1_{$role}.json";
    $payload = [
        'kb_version' => '1.2.0-p2',
        'ruleset_min' => '0.1.0-draft',
        'shard' => $role,
        'seed_phase' => 'P0+P1+P2',
        'inventory_doc' => 'docs/features/what-to-eat-dish-catalog.md',
        'dishes' => $dishes,
    ];
    file_put_contents(
        $dir.DIRECTORY_SEPARATOR.$fname,
        json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n"
    );
    $files[] = $fname;
    $total += count($dishes);
    foreach ($dishes as $dish) {
        match ($dish['seed_phase'] ?? '') {
            'P0' => $p0++,
            'P2' => $p2++,
            default => $p1++,
        };
    }
    echo "{$fname}: ".count($dishes).PHP_EOL;
}

$manifest = [
    'kb_version' => '1.2.0-p2',
    'ruleset_min' => '0.1.0-draft',
    'split' => 'dish_role',
    'seed_phase' => 'P0+P1+P2',
    'region_field' => 'region_tags',
    'inventory_doc' => 'docs/features/what-to-eat-dish-catalog.md',
    'description' => 'Seed P0+P1+P2 skeleton (catalog lô 1): I+II + dish_role committee + region_tags; fact III null. P3 not included.',
    'quality_notes' => [
        'no_invented_calories_or_tcm',
        'p3_remains_inventory_candidate',
        'cook_home_false_for_hard_restaurant_signatures',
    ],
    'dish_count' => $total,
    'p0_count' => $p0,
    'p1_count' => $p1,
    'p2_count' => $p2,
    'files' => $files,
];
file_put_contents(
    $dir.DIRECTORY_SEPARATOR.'manifest.json',
    json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n"
);

file_put_contents(
    $baseDir.DIRECTORY_SEPARATOR.'dishes_v1.json',
    json_encode([
        'kb_version' => '1.2.0-p2',
        'ruleset_min' => '0.1.0-draft',
        'description' => 'Deprecated single-file entry. Prefer dishes_v1/manifest.json.',
        'redirect_manifest' => 'dishes_v1/manifest.json',
        'dishes' => [],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n"
);

// slug uniqueness check
$slugs = [];
foreach ($shards as $list) {
    foreach ($list as $dish) {
        $s = $dish['slug'];
        if (isset($slugs[$s])) {
            throw new RuntimeException("Duplicate slug: {$s}");
        }
        $slugs[$s] = true;
    }
}

echo "TOTAL={$total} P0={$p0} P1={$p1} P2={$p2} unique_slugs=".count($slugs).PHP_EOL;
echo "Wrote dishes_v1/manifest.json".PHP_EOL;
