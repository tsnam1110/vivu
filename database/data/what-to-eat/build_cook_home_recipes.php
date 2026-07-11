<?php

declare(strict_types=1);

/**
 * Build cook-home ingredients/steps overlay for UI.
 * Run: php database/data/what-to-eat/build_cook_home_recipes.php
 */

$prov = static function (string $field): array {
    return [
        'field' => $field,
        'method' => 'committee',
        'source_ref' => 'vivu-cook-home-recipe-v1',
        'source_title' => 'ViVu cook-home standard recipe text v1 (home kitchen, not restaurant)',
        'confidence' => 'high',
        'reviewed_at' => '2026-07-11',
        'reviewed_by' => 'recipe-text-build',
    ];
};

$dish = static function (
    array $ingredients,
    array $steps,
    int $minutes,
) use ($prov): array {
    return [
        'ingredients' => $ingredients,
        'steps' => $steps,
        'cook_minutes' => $minutes,
        'facts' => [
            $prov('ingredients'),
            $prov('steps'),
            $prov('cook_minutes'),
        ],
    ];
};

$ing = static fn (string $name, string $amount): array => ['name' => $name, 'amount' => $amount];

$bySlug = [
    'com-trang' => $dish(
        [$ing('Gạo tẻ', '1 chén (≈180 g khô)'), $ing('Nước', '1,5–2 chén'), $ing('Muối', '1 nhúm (tuỳ chọn)')],
        ['Vo gạo sạch.', 'Cho nước theo tỉ lệ, nấu nồi cơm điện hoặc hấp đến chín.', 'Để nghỉ 5 phút rồi xới.'],
        35
    ),
    'com-gao-lut' => $dish(
        [$ing('Gạo lứt', '1 chén'), $ing('Nước', '2–2,5 chén'), $ing('Muối', 'tuỳ chọn')],
        ['Ngâm gạo lứt 30–60 phút (tuỳ loại).', 'Nấu với nước nhiều hơn gạo trắng.', 'Hấp thêm 5–10 phút cho mềm.'],
        50
    ),
    'trung-chien' => $dish(
        [$ing('Trứng gà', '2 quả'), $ing('Dầu ăn', '1–2 muỗng canh'), $ing('Hành lá, muối tiêu', 'vừa đủ')],
        ['Đánh tan trứng với chút muối tiêu.', 'Làm nóng chảo dầu vừa.', 'Đổ trứng, chiên hai mặt hoặc để sệt tuỳ khẩu vị.', 'Rắc hành lá.'],
        10
    ),
    'trung-op-la' => $dish(
        [$ing('Trứng gà', '1–2 quả'), $ing('Dầu ăn', '1 muỗng canh'), $ing('Muối tiêu', 'chút')],
        ['Làm nóng chảo dầu.', 'Đập trứng, chiên lòng đào hoặc chín kỹ.', 'Nêm muối tiêu.'],
        5
    ),
    'trung-kho' => $dish(
        [$ing('Trứng gà', '4 quả'), $ing('Nước mắm', '2 muỗng canh'), $ing('Đường', '1–2 muỗng canh'), $ing('Nước dừa hoặc nước lọc', '200 ml'), $ing('Hành tím, tiêu', 'vừa đủ')],
        ['Luộc trứng, bóc vỏ, chiên nhẹ cho vàng (tuỳ chọn).', 'Phi hành, thắng nhẹ đường nước mắm.', 'Cho trứng và nước, kho lửa nhỏ 15–20 phút.'],
        35
    ),
    'cha-trung-hap' => $dish(
        [$ing('Trứng gà', '3 quả'), $ing('Nước lọc hoặc nước dùng', '3 muỗng canh'), $ing('Muối tiêu, hành', 'vừa đủ')],
        ['Đánh đều trứng với nước và gia vị.', 'Lọc bọt nếu muốn mịn.', 'Hấp lửa vừa 12–15 phút đến đông.'],
        20
    ),
    'ga-luoc' => $dish(
        [$ing('Gà ta hoặc đùi/ức gà', '500–700 g'), $ing('Gừng, hành', 'vài lát'), $ing('Muối', 'vừa đủ')],
        ['Rửa gà, cho vào nồi nước lạnh cùng gừng hành muối.', 'Đun sôi, hạ lửa liu riu đến chín (khoảng 25–40 phút tuỳ miếng).', 'Vớt, để ráo, chấm muối tiêu chanh.'],
        45
    ),
    'ga-kho-gung' => $dish(
        [$ing('Thịt gà cắt miếng', '500 g'), $ing('Gừng', '1 củ nhỏ'), $ing('Nước mắm, đường', 'vừa đủ'), $ing('Dầu ăn, hành tím', 'vừa đủ')],
        ['Ướp gà với nước mắm, đường, gừng băm 15 phút.', 'Phi hành gừng, xào săn gà.', 'Thêm chút nước, kho nhỏ lửa 15–20 phút.'],
        40
    ),
    'rau-muong-xao-toi' => $dish(
        [$ing('Rau muống', '300–400 g'), $ing('Tỏi', '3–4 tép'), $ing('Dầu ăn', '1–2 muỗng canh'), $ing('Muối / nước mắm', 'vừa đủ')],
        ['Nhặt rau, cắt khúc, để ráo.', 'Phi tỏi vàng nhẹ.', 'Xào rau lửa lớn nhanh tay, nêm vừa.'],
        15
    ),
    'cai-xao-toi' => $dish(
        [$ing('Cải ngọt / cải thìa', '300 g'), $ing('Tỏi', '3 tép'), $ing('Dầu ăn', '1–2 muỗng canh')],
        ['Rửa sạch, cắt khúc.', 'Phi tỏi, xào cải lửa lớn.', 'Nêm muối hoặc nước mắm loãng.'],
        12
    ),
    'su-su-xao-toi' => $dish(
        [$ing('Su su', '2–3 quả'), $ing('Tỏi', '3 tép'), $ing('Dầu ăn', '1–2 muỗng canh')],
        ['Gọt vỏ, bào sợi hoặc thái miếng.', 'Phi tỏi, xào su su đến vừa chín giòn.', 'Nêm vừa ăn.'],
        15
    ),
    'nam-xao-toi' => $dish(
        [$ing('Nấm các loại', '300 g'), $ing('Tỏi', '3 tép'), $ing('Dầu ăn', '1–2 muỗng canh')],
        ['Rửa nấm, để ráo, cắt miếng.', 'Phi tỏi, xào nấm đến săn.', 'Nêm muối tiêu.'],
        15
    ),
    'cai-thia-xao' => $dish(
        [$ing('Cải thìa', '300 g'), $ing('Tỏi', '3 tép'), $ing('Dầu ăn', '1–2 muỗng canh')],
        ['Tách lá, rửa sạch.', 'Xào tỏi lửa lớn vài phút.', 'Nêm vừa.'],
        12
    ),
    'dau-que-xao' => $dish(
        [$ing('Đậu que', '300 g'), $ing('Tỏi', '2 tép'), $ing('Dầu ăn', '1–2 muỗng canh')],
        ['Cắt hai đầu đậu, rửa sạch.', 'Xào tỏi + đậu lửa lớn.', 'Có thể thêm chút nước cho mềm.'],
        15
    ),
    'dau-phu-sot-ca' => $dish(
        [$ing('Đậu phụ', '2 bìa'), $ing('Cà chua', '2–3 quả'), $ing('Hành tím, tỏi', 'vừa đủ'), $ing('Nước mắm, đường', 'vừa đủ')],
        ['Cắt đậu, chiên vàng nhẹ (tuỳ chọn).', 'Phi hành tỏi, xào cà chua nhừ.', 'Cho đậu vào, nêm, nấu sệt.'],
        25
    ),
    'dau-phu-chien' => $dish(
        [$ing('Đậu phụ', '2 bìa'), $ing('Dầu ăn', 'đủ chiên'), $ing('Muối', 'chút')],
        ['Cắt miếng, thấm khô.', 'Chiên ngập hoặc chảo chống dính đến vàng.', 'Vớt để ráo dầu.'],
        15
    ),
    'dau-hu-kho-nam' => $dish(
        [$ing('Đậu hũ', '2 bìa'), $ing('Nấm hương/mộc nhĩ', '50–80 g'), $ing('Nước tương, đường', 'vừa đủ')],
        ['Ngâm nấm, cắt miếng.', 'Xào nấm với gia vị.', 'Cho đậu, kho nhỏ lửa 10 phút.'],
        30
    ),
    'canh-bi-do' => $dish(
        [$ing('Bí đỏ', '300 g'), $ing('Tôm hoặc thịt bằm', '50–80 g'), $ing('Hành, gia vị', 'vừa đủ')],
        ['Gọt bí, cắt miếng.', 'Xào nhẹ tôm/thịt, thêm nước đun sôi.', 'Cho bí vào nấu chín mềm, nêm.'],
        25
    ),
    'canh-cai-thit-bam' => $dish(
        [$ing('Cải thảo / cải bẹ', '200 g'), $ing('Thịt heo bằm', '80 g'), $ing('Hành, nước mắm', 'vừa đủ')],
        ['Xào săn thịt bằm.', 'Thêm nước, đun sôi.', 'Cho cải vào, nấu vừa chín, nêm.'],
        20
    ),
    'canh-rau-ngot-thit-bam' => $dish(
        [$ing('Rau ngót', '1 bó'), $ing('Thịt bằm', '80 g'), $ing('Gia vị', 'vừa đủ')],
        ['Nhặt rau ngót.', 'Xào thịt, thêm nước sôi.', 'Cho rau, tắt bếp khi rau vừa chìm, nêm.'],
        15
    ),
    'canh-chua-ca' => $dish(
        [$ing('Cá (basa/lóc…)', '300 g'), $ing('Cà chua, dứa, đậu bắp, giá', 'vừa đủ'), $ing('Me hoặc gia vị chua', 'vừa đủ'), $ing('Rau thơm, ớt', 'vừa đủ')],
        ['Nấu nước dùng nhẹ, nêm chua ngọt.', 'Cho cà chua, dứa, đậu bắp.', 'Cho cá, nấu chín, thêm giá và rau thơm.'],
        30
    ),
    'canh-chua-tom' => $dish(
        [$ing('Tôm', '200 g'), $ing('Cà chua, dứa, đậu bắp', 'vừa đủ'), $ing('Me/gia vị chua', 'vừa đủ')],
        ['Nấu nước chua ngọt.', 'Cho rau củ, sau đó tôm.', 'Nêm và tắt bếp khi tôm chín.'],
        25
    ),
    'thit-kho-trung' => $dish(
        [$ing('Thịt ba chỉ hoặc nạc vai', '400 g'), $ing('Trứng', '3–4 quả'), $ing('Nước mắm, đường', 'vừa đủ'), $ing('Nước dừa hoặc nước lọc', '300 ml')],
        ['Luộc/chiên trứng, bóc vỏ.', 'Ướp thịt, kho với nước mắm đường.', 'Cho trứng, kho lửa nhỏ 30–40 phút.'],
        55
    ),
    'thit-kho-tau' => $dish(
        [$ing('Thịt ba chỉ', '500 g'), $ing('Trứng', '4 quả'), $ing('Nước dừa', '400 ml'), $ing('Nước mắm, đường, hành', 'vừa đủ')],
        ['Cắt thịt miếng vừa, ướp.', 'Thắng nước màu nhẹ (tuỳ chọn).', 'Kho thịt + trứng + nước dừa lửa nhỏ đến mềm.'],
        60
    ),
    'suon-ram-man' => $dish(
        [$ing('Sườn non', '500 g'), $ing('Nước mắm, đường', 'vừa đủ'), $ing('Tỏi, hành', 'vừa đủ')],
        ['Chần sườn, ướp gia vị.', 'Xào săn, thêm chút nước.', 'Ram nhỏ lửa đến cạn sốt bóng.'],
        45
    ),
    'ca-kho-to' => $dish(
        [$ing('Cá (basa/lóc/thu)', '400 g'), $ing('Nước mắm, đường', 'vừa đủ'), $ing('Tiêu, ớt, hành', 'vừa đủ')],
        ['Ướp cá.', 'Xếp vào nồi đất/chảo, kho lửa nhỏ.', 'Đến khi sốt sánh, cá ngấm.'],
        40
    ),
    'ca-chien' => $dish(
        [$ing('Cá tươi', '400 g'), $ing('Muối, bột chiên (tuỳ chọn)', 'vừa đủ'), $ing('Dầu ăn', 'đủ chiên')],
        ['Làm sạch cá, thấm khô, ướp muối.', 'Chiên vàng đều hai mặt.', 'Vớt để ráo.'],
        20
    ),
    'tom-rang-me' => $dish(
        [$ing('Tôm', '300 g'), $ing('Me chua', '1–2 muỗng'), $ing('Đường, nước mắm', 'vừa đủ'), $ing('Tỏi ớt', 'vừa đủ')],
        ['Làm sạch tôm.', 'Pha sốt me chua ngọt.', 'Xào tỏi, rang tôm với sốt đến sệt.'],
        25
    ),
    'bo-xao-luc-lac' => $dish(
        [$ing('Thịt bò thăn', '300 g'), $ing('Tỏi, hành tây', 'vừa đủ'), $ing('Dầu hào, nước tương', 'vừa đủ')],
        ['Thái hạt lựu, ướp.', 'Xào lửa lớn nhanh tay.', 'Thêm hành tây, đảo đều, tắt bếp.'],
        20
    ),
    'thit-xao-nam' => $dish(
        [$ing('Thịt heo nạc', '250 g'), $ing('Nấm', '150 g'), $ing('Tỏi, gia vị', 'vừa đủ')],
        ['Thái thịt, nấm.', 'Xào thịt săn, cho nấm.', 'Nêm và xào chín.'],
        20
    ),
    'chao-ga' => $dish(
        [$ing('Gạo', '1/2 chén'), $ing('Thịt gà', '150–200 g'), $ing('Gừng, hành', 'vừa đủ'), $ing('Nước', '1–1,2 lít')],
        ['Nấu gạo với nước đến nát cháo.', 'Cho gà, nấu chín, xé sợi.', 'Nêm, rắc hành gừng.'],
        40
    ),
    'chao-suon' => $dish(
        [$ing('Gạo', '1/2 chén'), $ing('Sườn non', '250 g'), $ing('Hành, tiêu', 'vừa đủ')],
        ['Chần sườn, nấu mềm.', 'Cho gạo nấu cháo.', 'Nêm, rắc hành.'],
        50
    ),
    'banh-mi-trung' => $dish(
        [$ing('Bánh mì', '1 ổ'), $ing('Trứng', '1–2 quả'), $ing('Rau/dưa leo', 'vừa đủ'), $ing('Gia vị', 'vừa đủ')],
        ['Chiên trứng ốp la hoặc trứng tráng.', 'Nướng nhẹ bánh mì.', 'Kẹp trứng và rau.'],
        10
    ),
    'salad-dua-leo-ca-chua' => $dish(
        [$ing('Dưa leo', '1–2 quả'), $ing('Cà chua', '1–2 quả'), $ing('Dầu olive/dầu mè', '1 muỗng'), $ing('Muối, chanh', 'vừa đủ')],
        ['Thái lát dưa leo, cà chua.', 'Trộn với dầu và gia vị.', 'Ăn ngay.'],
        10
    ),
    'dau-phu-chien' => $dish(
        [$ing('Đậu phụ', '2 bìa'), $ing('Dầu ăn', 'đủ chiên')],
        ['Cắt miếng, thấm khô.', 'Chiên vàng.', 'Chấm nước tương.'],
        15
    ),
    'bau-xao-trung' => $dish(
        [$ing('Bầu', '1 trái nhỏ'), $ing('Trứng', '2 quả'), $ing('Tỏi, dầu', 'vừa đủ')],
        ['Gọt bầu, thái miếng.', 'Xào bầu sơ, đổ trứng đánh tan.', 'Đảo đều đến chín.'],
        15
    ),
    'muop-xao-trung' => $dish(
        [$ing('Mướp', '1–2 trái'), $ing('Trứng', '2 quả'), $ing('Tỏi, dầu', 'vừa đủ')],
        ['Gọt mướp, thái.', 'Xào mướp, cho trứng.', 'Nêm vừa.'],
        15
    ),
    'canh-bau-tom' => $dish(
        [$ing('Bầu', '300 g'), $ing('Tôm', '100–150 g'), $ing('Hành, gia vị', 'vừa đủ')],
        ['Xào tôm, thêm nước.', 'Cho bầu nấu chín.', 'Nêm.'],
        20
    ),
    'canh-bi-dao' => $dish(
        [$ing('Bí đao', '300 g'), $ing('Thịt nạc/tôm', '50–80 g'), $ing('Gia vị', 'vừa đủ')],
        ['Cắt bí.', 'Nấu nước thịt/tôm.', 'Cho bí, nêm khi chín.'],
        25
    ),
    'canh-muop-moc' => $dish(
        [$ing('Mướp', '1–2 trái'), $ing('Thịt bằm / mọc', '80 g'), $ing('Hành, tiêu', 'vừa đủ')],
        ['Nặn mọc hoặc xào thịt bằm.', 'Thêm nước, cho mướp.', 'Nêm và tắt bếp.'],
        20
    ),
    'nam-kho-tieu' => $dish(
        [$ing('Nấm đùi gà / nấm rơm', '300 g'), $ing('Tiêu, nước tương', 'vừa đủ'), $ing('Tỏi, dầu', 'vừa đủ')],
        ['Cắt nấm.', 'Xào tỏi, cho nấm và gia vị.', 'Kho sệt.'],
        20
    ),
    'goi-cuon-chay' => $dish(
        [$ing('Bánh tráng', '8–10 cái'), $ing('Bún, đậu hũ, rau sống', 'vừa đủ'), $ing('Tương chấm', 'vừa đủ')],
        ['Trụng bún, chiên/luộc đậu.', 'Ướt bánh tráng, xếp nhân, cuốn.', 'Chấm tương.'],
        25
    ),
    'com-chay-thap-cam' => $dish(
        [$ing('Cơm trắng', '2 chén'), $ing('Đậu hũ, nấm, rau củ', 'vừa đủ'), $ing('Nước tương, dầu', 'vừa đủ')],
        ['Xào nấm đậu rau với gia vị chay.', 'Dọn kèm cơm.'],
        30
    ),
    'canh-bi-do-chay' => $dish(
        [$ing('Bí đỏ', '300 g'), $ing('Đậu hũ', '1/2 bìa'), $ing('Gia vị chay', 'vừa đủ')],
        ['Nấu bí với nước đến mềm.', 'Cho đậu hũ, nêm.'],
        25
    ),
    'canh-rau-cu-chay' => $dish(
        [$ing('Cà rốt, su su, bắp cải…', '300 g'), $ing('Đậu hũ (tuỳ chọn)', 'vừa đủ')],
        ['Cắt củ.', 'Nấu nước sôi, cho củ theo độ cứng.', 'Nêm chay.'],
        25
    ),
    'rau-cu-luoc-cham-tuong' => $dish(
        [$ing('Rau củ các loại', '400 g'), $ing('Tương hột / nước tương', 'vừa đủ')],
        ['Luộc rau củ đến vừa chín.', 'Pha chén tương chấm.', 'Dọn nóng.'],
        20
    ),
    'xoi-trang' => $dish(
        [$ing('Gạo nếp', '2 chén'), $ing('Nước/muối', 'vừa đủ')],
        ['Ngâm nếp 4–6 giờ.', 'Hấp chín.', 'Xới nhẹ.'],
        50
    ),
    'sua-chua' => $dish(
        [$ing('Sữa tươi không đường', '1 lít'), $ing('Men sữa chua', '1 hộp nhỏ / men khô theo hướng dẫn')],
        ['Đun sữa ấm ~40–45°C.', 'Trộn men, rót hũ.', 'Ủ 6–8 giờ đến đông.'],
        20
    ),
    'khoai-lang-nuong' => $dish(
        [$ing('Khoai lang', '2–3 củ')],
        ['Rửa sạch, để nguyên vỏ.', 'Nướng lò/than/nồi chiên không dầu đến mềm.', 'Tách vỏ ăn.'],
        40
    ),
    'bap-cai-luoc' => $dish(
        [$ing('Bắp cải', '300 g'), $ing('Muối', 'chút')],
        ['Thái múi.', 'Luộc nước sôi muối vừa chín tới.', 'Vớt để ráo.'],
        15
    ),
    'rau-cai-luoc' => $dish(
        [$ing('Rau cải', '300 g'), $ing('Muối', 'chút')],
        ['Nhặt rửa.', 'Trụng/luộc nhanh.', 'Chấm kho quẹt hoặc nước mắm.'],
        10
    ),
    'ga-rang-muoi' => $dish(
        [$ing('Thịt gà', '500 g'), $ing('Muối, bột chiên giòn (tuỳ)', 'vừa đủ'), $ing('Dầu ăn', 'đủ chiên/rang')],
        ['Ướp gà.', 'Rang hoặc chiên đến vàng.', 'Lắc muối tiêu.'],
        35
    ),
    'suon-chua-ngot' => $dish(
        [$ing('Sườn non', '400 g'), $ing('Đường, giấm, nước mắm, tương cà', 'vừa đủ'), $ing('Tỏi ớt', 'vừa đủ')],
        ['Chiên/săn sườn.', 'Pha sốt chua ngọt.', 'Kho/sốt sườn đến bóng.'],
        40
    ),
    'tom-rim' => $dish(
        [$ing('Tôm', '300 g'), $ing('Đường, nước mắm', 'vừa đủ'), $ing('Tỏi ớt', 'vừa đủ')],
        ['Làm sạch tôm.', 'Thắng nước màu nhẹ, rim tôm sệt.'],
        20
    ),
    'muc-xao-chua-ngot' => $dish(
        [$ing('Mực', '300 g'), $ing('Ớt chuông, hành tây', 'vừa đủ'), $ing('Sốt chua ngọt', 'vừa đủ')],
        ['Làm sạch mực, cắt miếng.', 'Xào nhanh mực, để riêng.', 'Xào rau, cho mực + sốt, đảo đều.'],
        20
    ),
    'cha-la-lot' => $dish(
        [$ing('Thịt bằm', '250 g'), $ing('Lá lốt', '30–40 lá'), $ing('Sả tỏi', 'vừa đủ')],
        ['Ướp thịt.', 'Gói lá lốt.', 'Nướng hoặc chiên vàng.'],
        35
    ),
    'tep-rang' => $dish(
        [$ing('Tép tươi hoặc khô', '200 g'), $ing('Tỏi, ớt, nước mắm đường', 'vừa đủ')],
        ['Rang tép với tỏi.', 'Nêm mặn ngọt.', 'Sấy khô vị.'],
        20
    ),
    'canh-chua-ca-loc' => $dish(
        [$ing('Cá lóc', '400 g'), $ing('Me, cà chua, rau canh chua', 'vừa đủ')],
        ['Nấu nước me chua.', 'Cho cá và rau.', 'Nêm và thêm rau thơm.'],
        30
    ),
    'canh-kho-qua-nhoi-thit' => $dish(
        [$ing('Khổ qua', '2–3 trái'), $ing('Thịt bằm', '150 g'), $ing('Mộc nhĩ, gia vị', 'vừa đủ')],
        ['Moi ruột khổ qua, nhồi thịt.', 'Nấu nước dùng, cho khổ qua.', 'Nấu đến mềm, nêm.'],
        40
    ),
    'canh-xuong-rau-cu' => $dish(
        [$ing('Xương heo', '300 g'), $ing('Cà rốt, su hào, hành tây', 'vừa đủ')],
        ['Chần xương, hầm nước ngọt.', 'Cho rau củ nấu chín.', 'Nêm.'],
        60
    ),
    'sup-cua' => $dish(
        [$ing('Thịt cua / gạch cua', '100–150 g'), $ing('Trứng', '1 quả'), $ing('Bột năng', '1–2 muỗng'), $ing('Nước dùng', '800 ml')],
        ['Nấu nước dùng với cua.', 'Hòa bột năng tạo sệt.', 'Đổ trứng tan, nêm tiêu.'],
        25
    ),
    'banh-mi-thit' => $dish(
        [$ing('Bánh mì', '1 ổ'), $ing('Thịt nguội / chả / pate', 'vừa đủ'), $ing('Rau, đồ chua, tương', 'vừa đủ')],
        ['Nướng nhẹ bánh.', 'Phết pate, xếp thịt rau.', 'Rưới tương.'],
        10
    ),
    'xoi-man' => $dish(
        [$ing('Gạo nếp', '2 chén'), $ing('Chả, lạp xưởng, ruốc (tuỳ)', 'vừa đủ'), $ing('Hành phi, mỡ hành', 'vừa đủ')],
        ['Ngâm nếp, hấp chín.', 'Xào/hấp topping.', 'Trộn hoặc rắc lên xôi.'],
        55
    ),
    'xoi-ga' => $dish(
        [$ing('Gạo nếp', '2 chén'), $ing('Thịt gà', '200 g'), $ing('Hành phi, nước mắm', 'vừa đủ')],
        ['Hấp xôi.', 'Luộc/xé gà trộn gia vị.', 'Dọn xôi + gà + hành phi.'],
        55
    ),

    // —— Lô B: đủ cờ supports_cook_home còn thiếu (home kitchen text) ——
    'ca-phe-den-da' => $dish(
        [$ing('Cà phê rang xay hoặc phin', '2–3 muỗng'), $ing('Nước sôi', '80–100 ml'), $ing('Đá', 'vừa đủ')],
        ['Pha phin hoặc pour-over.', 'Rót vào ly đá.', 'Không đường hoặc đường riêng.'],
        10
    ),
    'ca-phe-sua-da' => $dish(
        [$ing('Cà phê', '2–3 muỗng'), $ing('Sữa đặc', '2–3 muỗng'), $ing('Đá', 'vừa đủ')],
        ['Pha cà phê đậm.', 'Cho sữa đặc vào ly, rót cà phê, khuấy.', 'Thêm đá.'],
        10
    ),
    'tra-da' => $dish(
        [$ing('Trà túi lọc / trà khô', '1–2 gói'), $ing('Nước sôi', '300 ml'), $ing('Đá, đường (tuỳ)', 'vừa đủ')],
        ['Hãm trà 3–5 phút.', 'Pha loãng, để nguội.', 'Rót ly đá.'],
        8
    ),
    'sua-dau-nanh' => $dish(
        [$ing('Đậu nành khô', '100 g'), $ing('Nước', '1–1,2 lít'), $ing('Đường (tuỳ)', 'vừa đủ')],
        ['Ngâm đậu 6–8 giờ, xay với nước.', 'Lọc bã, nấu sôi 10–15 phút, khuấy đều.', 'Nêm đường nếu thích, để nguội.'],
        40
    ),
    'nuoc-mia' => $dish(
        [$ing('Mía tươi đã gọt', '3–5 cây'), $ing('Tắc/chanh, đá (tuỳ)', 'vừa đủ')],
        ['Rửa mía.', 'Ép máy hoặc nhờ quán ép mang về.', 'Pha tắc/đá nếu muốn. (Nhà thường mua mía ép sẵn.)'],
        15
    ),
    'nuoc-chanh' => $dish(
        [$ing('Chanh', '2–3 quả'), $ing('Đường hoặc mật', '2–3 muỗng'), $ing('Nước, đá', 'vừa đủ')],
        ['Vắt chanh, lọc hạt.', 'Hòa đường với chút nước ấm.', 'Thêm nước đá, khuấy.'],
        8
    ),
    'tra-tac' => $dish(
        [$ing('Tắc', '8–10 quả'), $ing('Trà đen/xanh', '1 gói'), $ing('Đường, đá', 'vừa đủ')],
        ['Hãm trà.', 'Vắt tắc, hòa đường.', 'Pha trà + tắc + đá.'],
        10
    ),
    'sinh-to-bo' => $dish(
        [$ing('Bơ chín', '1 quả'), $ing('Sữa đặc / sữa tươi', '2–3 muỗng'), $ing('Đá', 'vừa đủ')],
        ['Lấy thịt bơ.', 'Xay với sữa và đá đến mịn.', 'Nếm độ ngọt.'],
        10
    ),
    'sinh-to-xoai' => $dish(
        [$ing('Xoài chín', '1–2 quả'), $ing('Sữa hoặc sữa chua', '100 ml'), $ing('Đá, đường (tuỳ)', 'vừa đủ')],
        ['Gọt xoài, cắt miếng.', 'Xay với sữa/đá.', 'Điều chỉnh ngọt.'],
        10
    ),
    'nuoc-dua' => $dish(
        [$ing('Dừa tươi', '1 quả')],
        ['Mở dừa lấy nước.', 'Uống mát; có thể thêm cơm dừa.'],
        5
    ),
    'bun-tuoi' => $dish(
        [$ing('Bún tươi hoặc bún khô', '300 g'), $ing('Nước sôi, muối', 'vừa đủ')],
        ['Trụng bún tươi vài giây hoặc luộc bún khô theo bao bì.', 'Xả nước lạnh, để ráo.', 'Dùng kèm món mặn.'],
        15
    ),
    'pho-bo' => $dish(
        [$ing('Xương bò + gân/nạm (tuỳ)', '800 g–1 kg'), $ing('Bánh phở', '400 g'), $ing('Thịt bò tái/chín', '200 g'), $ing('Hành, gừng, quế hồi, rau thơm', 'vừa đủ')],
        ['Chần xương, hầm nước dùng 2–4 giờ với gừng hành khô.', 'Trụng bánh phở, xếp thịt.', 'Chan nước dùng sôi, thêm hành ngò.'],
        180
    ),
    'pho-ga' => $dish(
        [$ing('Gà ta', '1/2–1 con'), $ing('Bánh phở', '400 g'), $ing('Hành, gừng, rau thơm', 'vừa đủ')],
        ['Luộc gà lấy nước ngọt, xé thịt.', 'Trụng phở, xếp gà.', 'Chan nước, hành ngò, chanh ớt.'],
        90
    ),
    'bun-bo-hue' => $dish(
        [$ing('Xương ống + giò heo', '1 kg'), $ing('Bún bò', '400 g'), $ing('Chả cua, huyết, thịt bò (tuỳ)', 'vừa đủ'), $ing('Sả, mắm ruốc, ớt sa tế', 'vừa đủ')],
        ['Hầm xương sả.', 'Nêm mắm ruốc, sa tế (lượng vừa).', 'Trụng bún, xếp topping, chan nước.'],
        150
    ),
    'bun-rieu-cua' => $dish(
        [$ing('Cua đồng xay / riêu cua gói', '300 g'), $ing('Cà chua', '3–4 quả'), $ing('Bún', '400 g'), $ing('Đậu phụ, huyết, rau', 'vừa đủ')],
        ['Nấu nước dùng cà chua.', 'Cho riêu cua đông lại.', 'Thêm đậu phụ; trụng bún dọn tô.'],
        60
    ),
    'bun-cha' => $dish(
        [$ing('Thịt ba chỉ + thịt nạc xay', '400 g'), $ing('Bún', '400 g'), $ing('Nước mắm pha chua ngọt', 'vừa đủ'), $ing('Rau sống, dưa góp', 'vừa đủ')],
        ['Ướp thịt, nướng chả viên và miếng ba chỉ.', 'Pha nước chấm.', 'Dọn bún + chả + rau.'],
        50
    ),
    'bun-thit-nuong' => $dish(
        [$ing('Thịt heo nạc', '300 g'), $ing('Bún, đồ chua, rau', 'vừa đủ'), $ing('Nước mắm chua ngọt, đậu phộng', 'vừa đủ')],
        ['Ướp thịt sả tỏi, nướng.', 'Trụng bún, xếp thịt rau.', 'Rưới nước mắm, đậu phộng.'],
        40
    ),
    'hu-tieu-nam-vang' => $dish(
        [$ing('Xương heo + tôm khô', '800 g'), $ing('Hủ tiếu', '400 g'), $ing('Tôm, thịt, trứng cút (tuỳ)', 'vừa đủ'), $ing('Hành tỏi phi, rau', 'vừa đủ')],
        ['Hầm nước dùng ngọt.', 'Trụng hủ tiếu, xếp topping.', 'Chan nước, hành phi.'],
        120
    ),
    'mi-quang' => $dish(
        [$ing('Mì Quảng', '400 g'), $ing('Tôm, thịt, trứng', 'vừa đủ'), $ing('Nước dùng sệt, đậu phộng, bánh tráng', 'vừa đủ')],
        ['Nấu nước dùng đậm, ít nước.', 'Trụng mì, xếp topping.', 'Rắc đậu phộng, ăn kèm bánh tráng.'],
        60
    ),
    'bo-kho' => $dish(
        [$ing('Thịt bò thăn/gân', '500 g'), $ing('Cà rốt', '2 củ'), $ing('Sả, gừng, bột bò kho', 'vừa đủ'), $ing('Bánh mì hoặc hủ tiếu', 'vừa đủ')],
        ['Ướp bò, xào săn.', 'Hầm mềm với gia vị bò kho.', 'Thêm cà rốt; dọn bánh mì/hủ tiếu.'],
        90
    ),
    'banh-cuon' => $dish(
        [$ing('Bột gạo bánh cuốn', '300 g'), $ing('Thịt bằm, mộc nhĩ', '200 g'), $ing('Hành phi, nước chấm', 'vừa đủ')],
        ['Pha bột loãng, tráng chảo chống dính.', 'Xào nhân thịt mộc nhĩ.', 'Cuốn, rắc hành phi, chấm nước mắm.'],
        45
    ),
    'banh-xeo' => $dish(
        [$ing('Bột bánh xèo', '1 gói / 250 g'), $ing('Tôm, thịt ba chỉ', 'vừa đủ'), $ing('Giá đỗ, rau sống, nước mắm', 'vừa đủ')],
        ['Pha bột nghỉ 30 phút.', 'Đổ bột tráng mỏng, cho nhân giá.', 'Gấp đôi, ăn kèm rau + nước mắm.'],
        40
    ),
    'com-tam-suon' => $dish(
        [$ing('Gạo tấm', '2 chén'), $ing('Sườn cốt lết', '2 miếng'), $ing('Đồ chua, mỡ hành, nước mắm', 'vừa đủ')],
        ['Nấu cơm tấm.', 'Ướp sườn, nướng/chiên.', 'Dọn cơm + sườn + đồ chua + mỡ hành.'],
        50
    ),
    'com-tam-bi-cha' => $dish(
        [$ing('Cơm tấm', '2 chén'), $ing('Bì, chả trứng', 'vừa đủ'), $ing('Đồ chua, mỡ hành', 'vừa đủ')],
        ['Nấu cơm tấm.', 'Chuẩn bị bì + chả (mua hoặc tự hấp).', 'Dọn đĩa đầy đủ.'],
        40
    ),
    'goi-cuon' => $dish(
        [$ing('Bánh tráng', '10 cái'), $ing('Tôm, thịt luộc, bún, rau', 'vừa đủ'), $ing('Tương đen / nước chấm', 'vừa đủ')],
        ['Luộc tôm thịt, trụng bún.', 'Ướt bánh tráng, xếp nhân, cuốn chặt.', 'Chấm tương.'],
        30
    ),
    'bun-mam' => $dish(
        [$ing('Mắm cá linh/sặc', '200 g'), $ing('Tôm, thịt, cà tím, rau', 'vừa đủ'), $ing('Bún', '400 g')],
        ['Lọc mắm, nấu nước mắm me.', 'Thêm topping hải sản thịt.', 'Dọn bún + rau.'],
        50
    ),
    'bun-oc' => $dish(
        [$ing('Ốc', '400 g'), $ing('Cà chua, giấm bỗng (tuỳ vùng)', 'vừa đủ'), $ing('Bún, rau', 'vừa đủ')],
        ['Luộc ốc, lấy thịt.', 'Nấu nước dùng cà chua.', 'Dọn bún + ốc.'],
        45
    ),
    'bun-moc' => $dish(
        [$ing('Mọc (thịt bằm + mộc nhĩ)', '300 g'), $ing('Xương heo', '500 g'), $ing('Bún, hành', 'vừa đủ')],
        ['Hầm xương.', 'Nặn mọc, luộc trong nước dùng.', 'Dọn bún + mọc.'],
        60
    ),
    'bun-thang' => $dish(
        [$ing('Bún', '400 g'), $ing('Thịt gà, giò, trứng, tôm khô', 'vừa đủ'), $ing('Nước dùng gà, hành', 'vừa đủ')],
        ['Nấu nước dùng gà.', 'Thái sợi topping xếp màu.', 'Chan nước, rắc tôm khô hành.'],
        70
    ),
    'bun-bo-nam-bo' => $dish(
        [$ing('Thịt bò', '300 g'), $ing('Bún, rau sống, đậu phộng', 'vừa đủ'), $ing('Nước mắm chua ngọt, tỏi ớt', 'vừa đủ')],
        ['Xào bò lửa lớn.', 'Trụng bún, xếp rau bò.', 'Rưới nước mắm, đậu phộng.'],
        30
    ),
    'cao-lau' => $dish(
        [$ing('Mì Cao lầu (hoặc mì trụng)', '400 g'), $ing('Thịt xá xíu', '200 g'), $ing('Rau sống, tóp mỡ, nước sốt', 'vừa đủ')],
        ['Trụng mì, để ráo.', 'Thái xá xíu.', 'Trộn/xếp tô với rau tóp mỡ (phiên bản nhà).'],
        40
    ),
    'banh-canh-cua' => $dish(
        [$ing('Bánh canh', '400 g'), $ing('Thịt cua / gạch', '150 g'), $ing('Nước dùng, hành', 'vừa đủ')],
        ['Nấu nước dùng với cua.', 'Trụng bánh canh.', 'Chan nước, thêm hành.'],
        45
    ),
    'banh-canh-gio-heo' => $dish(
        [$ing('Giò heo', '400 g'), $ing('Bánh canh', '400 g'), $ing('Nước dùng, gia vị', 'vừa đủ')],
        ['Hầm giò mềm.', 'Trụng bánh canh, xếp giò.', 'Chan nước dùng.'],
        90
    ),
    'mien-ga' => $dish(
        [$ing('Miến', '200 g'), $ing('Gà', '1/2 con'), $ing('Nấm, hành, nước dùng', 'vừa đủ')],
        ['Luộc gà, xé thịt.', 'Ngâm miến, trụng.', 'Chan nước dùng gà + nấm.'],
        50
    ),
    'chao-long' => $dish(
        [$ing('Gạo', '1/2 chén'), $ing('Lòng heo làm sạch', '300 g'), $ing('Hành, tiêu, nước mắm', 'vừa đủ')],
        ['Nấu cháo nhừ.', 'Sơ chế lòng, luộc/chần đúng lửa.', 'Xếp lòng lên cháo, nêm.'],
        60
    ),
    'banh-mi-xiu-mai' => $dish(
        [$ing('Bánh mì', '2 ổ'), $ing('Thịt xíu mại', '200 g'), $ing('Sốt cà, rau', 'vừa đủ')],
        ['Hấp/xíu mại với sốt.', 'Nướng bánh mì.', 'Kẹp xíu mại + sốt.'],
        35
    ),
    'banh-uot' => $dish(
        [$ing('Bột gạo', '300 g'), $ing('Thịt/chả, hành phi', 'vừa đủ'), $ing('Nước chấm', 'vừa đủ')],
        ['Tráng bột thành bánh ướt.', 'Cuốn hoặc cắt miếng với nhân.', 'Rắc hành phi, chấm.'],
        40
    ),
    'banh-beo' => $dish(
        [$ing('Bột gạo', '250 g'), $ing('Tôm khô giã', '50 g'), $ing('Mỡ hành, nước mắm', 'vừa đủ')],
        ['Pha bột, đổ chén nhỏ hấp.', 'Rắc tôm + mỡ hành.', 'Chan nước mắm.'],
        40
    ),
    'com-ga-hoi-an' => $dish(
        [$ing('Gà ta', '1/2 con'), $ing('Gạo', '2 chén'), $ing('Nghệ, gừng, nước mắm gừng', 'vừa đủ')],
        ['Luộc gà, giữ nước.', 'Nấu cơm với nước gà + nghệ.', 'Xé gà, chấm mắm gừng.'],
        70
    ),
    'com-ga-xoi-mo' => $dish(
        [$ing('Gà', '1/2 con'), $ing('Gạo', '2 chén'), $ing('Mỡ gà, hành', 'vừa đủ')],
        ['Luộc/xé gà.', 'Nấu cơm với mỡ gà hành.', 'Dọn kèm muối tiêu chanh.'],
        60
    ),
    'com-rang-thap-cam' => $dish(
        [$ing('Cơm nguội', '3 chén'), $ing('Trứng, lạp xưởng/tôm/đậu', 'vừa đủ'), $ing('Hành, nước tương', 'vừa đủ')],
        ['Xào trứng để riêng.', 'Xào topping, cho cơm.', 'Nêm, trộn trứng, đảo lửa lớn.'],
        25
    ),
    'bun-dau-mam-tom' => $dish(
        [$ing('Bún', '400 g'), $ing('Đậu phụ chiên', '2 bìa'), $ing('Mắm tôm pha, rau sống, chả', 'vừa đủ')],
        ['Chiên đậu, trụng bún.', 'Pha mắm tôm chanh ớt đường.', 'Dọn mẹt: bún, đậu, rau, chả.'],
        30
    ),
    'bot-chien' => $dish(
        [$ing('Bột năng / bột chiên', '300 g'), $ing('Trứng, hành, tương ớt', 'vừa đủ'), $ing('Dầu chiên', 'vừa đủ')],
        ['Hấp khối bột, cắt viên.', 'Chiên vàng, đảo với trứng hành.', 'Chấm tương.'],
        35
    ),
    'mi-xao' => $dish(
        [$ing('Mì trứng/mì gói bỏ gia vị', '2 vắt'), $ing('Rau củ, thịt/tôm', 'vừa đủ'), $ing('Nước tương, dầu hào', 'vừa đủ')],
        ['Trụng mì để ráo.', 'Xào topping, cho mì.', 'Nêm và đảo đều.'],
        20
    ),
    'banh-gio' => $dish(
        [$ing('Bột gạo', '300 g'), $ing('Thịt bằm, mộc nhĩ, trứng muối (tuỳ)', 'vừa đủ'), $ing('Lá chuối', 'vừa đủ')],
        ['Nấu bột sệt.', 'Xào nhân.', 'Gói lá chuối, hấp 30–40 phút.'],
        70
    ),
    'banh-bao' => $dish(
        [$ing('Bột bánh bao', '400 g'), $ing('Nhân thịt trứng cút', 'vừa đủ'), $ing('Men nở', 'theo gói')],
        ['Nhồi bột, ủ.', 'Gói nhân, ủ lần 2.', 'Hấp 15–20 phút.'],
        90
    ),
    'banh-bot-loc' => $dish(
        [$ing('Bột năng', '300 g'), $ing('Tôm tươi, thịt ba chỉ', 'vừa đủ'), $ing('Lá chuối, nước mắm', 'vừa đủ')],
        ['Trần bột sệt.', 'Gói nhân tôm thịt trong lá.', 'Hấp chín, chấm nước mắm.'],
        50
    ),
    'pho-tron' => $dish(
        [$ing('Bánh phở', '400 g'), $ing('Thịt bò xào', '200 g'), $ing('Rau thơm, nước sốt trộn', 'vừa đủ')],
        ['Trụng phở để ráo.', 'Xào bò.', 'Trộn phở với sốt + bò + rau.'],
        30
    ),
    'banh-hoi-thit-nuong' => $dish(
        [$ing('Bánh hỏi', '400 g'), $ing('Thịt nướng', '300 g'), $ing('Mỡ hành, nước mắm, rau', 'vừa đủ')],
        ['Hấp bánh hỏi, quét mỡ hành.', 'Nướng thịt.', 'Dọn kèm rau + nước mắm.'],
        40
    ),
    'mi-y-so-cot-bo' => $dish(
        [$ing('Mì Ý', '250 g'), $ing('Thịt bò bằm', '250 g'), $ing('Sốt cà chua, tỏi hành', 'vừa đủ')],
        ['Luộc mì al dente.', 'Xào bò + sốt cà.', 'Trộn hoặc chan sốt lên mì.'],
        30
    ),
    'nem-ran' => $dish(
        [$ing('Thịt bằm, miến, mộc nhĩ, trứng', 'vừa đủ'), $ing('Bánh đa nem', '20–25 cái'), $ing('Dầu chiên, nước chấm', 'vừa đủ')],
        ['Trộn nhân.', 'Gói nem, chiên vàng.', 'Chấm nước mắm chua ngọt.'],
        50
    ),
    'cha-gio' => $dish(
        [$ing('Nhân thịt tôm miến', 'vừa đủ'), $ing('Bánh tráng/bánh đa', 'vừa đủ'), $ing('Dầu chiên', 'vừa đủ')],
        ['Trộn nhân, gói.', 'Chiên giòn.', 'Ăn kèm bún/rau hoặc chấm.'],
        45
    ),
    'ruoc' => $dish(
        [$ing('Thịt nạc', '300 g'), $ing('Nước mắm, đường', 'vừa đủ')],
        ['Luộc thịt, xé sợi.', 'Rang nhỏ lửa với nước mắm đường đến khô tơi.', 'Để nguội, bảo quản hộp.'],
        50
    ),
    'rau-luoc-kho-quet' => $dish(
        [$ing('Rau củ các loại', '400 g'), $ing('Tép/thịt, mắm, đường', 'vừa đủ')],
        ['Kho quẹt tép/thịt cháy cạnh.', 'Luộc rau.', 'Chấm kho quẹt.'],
        25
    ),
    'dua-mon' => $dish(
        [$ing('Củ cải, cà rốt', '400 g'), $ing('Đường, giấm, muối', 'vừa đủ')],
        ['Thái sợi, ướp muối rút nước.', 'Pha giấm đường.', 'Ngâm vài giờ đến 1 ngày.'],
        30
    ),
    'dua-mon-hue' => $dish(
        [$ing('Dưa leo / củ quả', '400 g'), $ing('Nước mắm, đường, tỏi ớt', 'vừa đủ')],
        ['Thái miếng, để ráo.', 'Pha nước mắm chua ngọt đặc trưng.', 'Ngâm ngắn hoặc dùng ngay.'],
        25
    ),
    'bi-dao-xao' => $dish(
        [$ing('Bí đao', '400 g'), $ing('Tỏi, dầu, muối', 'vừa đủ')],
        ['Gọt bí, thái miếng.', 'Phi tỏi, xào bí đến trong.', 'Nêm vừa.'],
        15
    ),
    'rau-mong-toi-xao' => $dish(
        [$ing('Rau mồng tơi', '1 bó'), $ing('Tỏi, dầu', 'vừa đủ')],
        ['Nhặt rau, để ráo.', 'Xào tỏi lửa lớn nhanh.', 'Nêm nhẹ (rau ra nhiều nước).'],
        12
    ),
    'rau-lang-xao' => $dish(
        [$ing('Rau lang', '1 bó'), $ing('Tỏi, dầu', 'vừa đủ')],
        ['Nhặt lá/ngọn lang.', 'Xào tỏi lửa lớn.', 'Nêm vừa.'],
        12
    ),
    'dau-bap-xao' => $dish(
        [$ing('Đậu bắp', '300 g'), $ing('Tỏi, dầu', 'vừa đủ')],
        ['Cắt đầu đuôi, chần sơ (tuỳ).', 'Xào tỏi nhanh.', 'Nêm chút muối.'],
        12
    ),
    'goi-du-du' => $dish(
        [$ing('Đu đủ xanh bào', '300 g'), $ing('Tôm khô / đậu phộng', 'vừa đủ'), $ing('Nước mắm chua ngọt, rau thơm', 'vừa đủ')],
        ['Bào đu đủ, để ráo.', 'Trộn nước mắm + topping.', 'Rắc đậu phộng.'],
        20
    ),
    'goi-ga-bap-cai' => $dish(
        [$ing('Gà luộc xé', '200 g'), $ing('Bắp cải bào', '300 g'), $ing('Rau răm, nước mắm chua, đậu phộng', 'vừa đủ')],
        ['Xé gà, bào bắp cải.', 'Trộn với nước mắm chua ngọt.', 'Rắc đậu phộng rau răm.'],
        25
    ),
    'nom-hoa-chuoi' => $dish(
        [$ing('Hoa chuối bào', '300 g'), $ing('Thịt/tôm (tuỳ)', 'vừa đủ'), $ing('Nước mắm chua, đậu phộng, rau thơm', 'vừa đủ')],
        ['Bào hoa chuối ngâm nước chanh chống thâm.', 'Trộn gia vị + protein.', 'Rắc đậu phộng.'],
        25
    ),
    'ca-kho-rieng' => $dish(
        [$ing('Cá', '400 g'), $ing('Riềng, ớt, nước mắm đường', 'vừa đủ')],
        ['Ướp cá với riềng nước mắm.', 'Kho nhỏ lửa đến sệt.', 'Ăn kèm cơm.'],
        40
    ),
    'ca-loc-kho' => $dish(
        [$ing('Cá lóc', '1 con / 500 g'), $ing('Nước mắm, đường, tiêu ớt', 'vừa đủ')],
        ['Ướp cá.', 'Kho nồi đất/chảo lửa nhỏ.', 'Sốt sánh.'],
        45
    ),
    'ga-kho-sa' => $dish(
        [$ing('Gà cắt miếng', '500 g'), $ing('Sả, ớt, nước mắm đường', 'vừa đủ')],
        ['Ướp gà sả.', 'Xào săn, kho sệt.', 'Nêm vừa.'],
        40
    ),
    'vit-om-sau' => $dish(
        [$ing('Vịt', '1/2 con'), $ing('Sấu tươi/chua', 'vừa đủ'), $ing('Gừng sả, gia vị', 'vừa đủ')],
        ['Chần vịt, ướp.', 'Om với sấu gừng đến mềm.', 'Nêm chua mặn cân bằng.'],
        70
    ),
    'tom-chien-bot' => $dish(
        [$ing('Tôm', '300 g'), $ing('Bột chiên giòn', '100 g'), $ing('Dầu chiên', 'đủ ngập')],
        ['Lột tôm chừa đuôi, thấm khô.', 'Áo bột, chiên vàng.', 'Vớt ráo dầu.'],
        25
    ),
    'thit-rang-chay-canh' => $dish(
        [$ing('Thịt ba chỉ', '400 g'), $ing('Nước mắm, đường', 'vừa đủ')],
        ['Luộc sơ, thái miếng.', 'Rang cháy cạnh với nước mắm đường.', 'Cạn nước bóng cạnh.'],
        40
    ),
    'canh-ga-la-giang' => $dish(
        [$ing('Gà', '300 g'), $ing('Lá giang', '1 bó'), $ing('Gia vị', 'vừa đủ')],
        ['Nấu nước gà.', 'Cho lá giang, nêm chua nhẹ.', 'Tắt bếp khi lá vừa chìm.'],
        35
    ),
    'canh-cua-rau-day' => $dish(
        [$ing('Cua đồng', '300 g'), $ing('Rau đay, mướp (tuỳ)', 'vừa đủ')],
        ['Giã/lọc cua lấy nước.', 'Nấu riêu cua đông.', 'Cho rau đay.'],
        40
    ),
    'canh-khoai-mo' => $dish(
        [$ing('Khoai mỡ', '300 g'), $ing('Tôm/thịt bằm', '80 g'), $ing('Gia vị', 'vừa đủ')],
        ['Gọt khoai (đeo bao tay nếu ngứa).', 'Nấu với tôm/thịt đến sánh.', 'Nêm.'],
        30
    ),
    'canh-mong-toi-nau-tom' => $dish(
        [$ing('Rau mồng tơi', '1 bó'), $ing('Tôm', '100 g'), $ing('Gia vị', 'vừa đủ')],
        ['Xào tôm, thêm nước.', 'Cho mồng tơi, nêm, tắt bếp sớm.'],
        15
    ),
    'pho-chay' => $dish(
        [$ing('Bánh phở', '400 g'), $ing('Nấm, đậu hũ, rau củ', 'vừa đủ'), $ing('Nước dùng chay (nấm/củ)', 'vừa đủ')],
        ['Hầm nước dùng chay.', 'Trụng phở, xếp nấm đậu.', 'Chan nước, hành ngò.'],
        50
    ),
    'bun-chay' => $dish(
        [$ing('Bún', '400 g'), $ing('Đậu hũ, nấm, đồ chay', 'vừa đủ'), $ing('Nước dùng chay', 'vừa đủ')],
        ['Nấu nước dùng chay.', 'Trụng bún, xếp topping.', 'Chan nước.'],
        40
    ),
    'xoi-chay' => $dish(
        [$ing('Gạo nếp', '2 chén'), $ing('Đậu xanh / nấm', 'vừa đủ'), $ing('Muối mè, hành phi chay', 'vừa đủ')],
        ['Ngâm nếp, hấp.', 'Nấu đậu/nấm rắc lên.', 'Dọn xôi chay.'],
        50
    ),
    'hu-tieu-chay' => $dish(
        [$ing('Hủ tiếu', '400 g'), $ing('Nấm, đậu, rau', 'vừa đủ'), $ing('Nước dùng chay', 'vừa đủ')],
        ['Nấu nước chay ngọt.', 'Trụng hủ tiếu + topping.', 'Chan nước.'],
        45
    ),
    'com-tam-chay' => $dish(
        [$ing('Cơm tấm', '2 chén'), $ing('Sườn chay / đậu', 'vừa đủ'), $ing('Đồ chua, mỡ hành chay', 'vừa đủ')],
        ['Nấu cơm tấm.', 'Chiên/nướng sườn chay.', 'Dọn đĩa như cơm tấm mặn.'],
        40
    ),
    'mien-xao-chay' => $dish(
        [$ing('Miến', '200 g'), $ing('Nấm, cà rốt, bắp cải', 'vừa đủ'), $ing('Nước tương, dầu', 'vừa đủ')],
        ['Ngâm miến.', 'Xào rau nấm, cho miến.', 'Nêm chay.'],
        25
    ),
    'nem-chay-ran' => $dish(
        [$ing('Khoai môn/đậu / miến chay', 'vừa đủ'), $ing('Bánh đa nem', 'vừa đủ'), $ing('Dầu chiên', 'vừa đủ')],
        ['Trộn nhân chay.', 'Gói, chiên vàng.', 'Chấm tương.'],
        40
    ),
    'che-thap-cam-chay' => $dish(
        [$ing('Đậu đỏ, đậu xanh, khoai', 'vừa đủ'), $ing('Nước cốt dừa, đường', 'vừa đủ')],
        ['Nấu từng loại đậu/khoai với đường.', 'Xếp ly, chan cốt dừa.'],
        60
    ),
    'che-dau-xanh' => $dish(
        [$ing('Đậu xanh không vỏ', '200 g'), $ing('Đường', '100–150 g'), $ing('Nước cốt dừa (tuỳ)', 'vừa đủ')],
        ['Nấu đậu mềm.', 'Thêm đường.', 'Chan dừa nếu thích.'],
        40
    ),
    'trai-cay-dia' => $dish(
        [$ing('Trái cây theo mùa', '400–500 g'), $ing('Đá (tuỳ)', 'vừa đủ')],
        ['Rửa, gọt, thái miếng.', 'Xếp đĩa.', 'Dùng ngay.'],
        15
    ),
    'che-ba-mau' => $dish(
        [$ing('Đậu đỏ, đậu xanh, thạch', 'vừa đủ'), $ing('Nước cốt dừa, đá bào, đường', 'vừa đủ')],
        ['Nấu đậu, làm thạch.', 'Xếp lớp trong ly.', 'Chan dừa + đá bào.'],
        50
    ),
    'che-thai' => $dish(
        [$ing('Trái cây đóng hộp / tươi', 'vừa đủ'), $ing('Sữa đặc, nước cốt dừa, thạch', 'vừa đủ'), $ing('Đá', 'vừa đủ')],
        ['Cắt trái cây, thạch.', 'Pha sữa dừa ngọt.', 'Trộn đá, dùng lạnh.'],
        20
    ),
    'che-chuoi' => $dish(
        [$ing('Chuối sứ/sáp', '4–5 quả'), $ing('Nước cốt dừa, đường, bột báng', 'vừa đủ')],
        ['Cắt chuối, nấu với đường.', 'Thêm bột báng, chan dừa.'],
        25
    ),
    'banh-flan' => $dish(
        [$ing('Trứng gà', '4 quả'), $ing('Sữa tươi', '400 ml'), $ing('Đường (làm caramel + ngọt)', '120 g')],
        ['Thắng caramel đổ khuôn.', 'Đánh trứng sữa, lọc.', 'Hấp cách thủy 30–40 phút.'],
        50
    ),
    'chuoi-nep-nuong' => $dish(
        [$ing('Chuối', '4 quả'), $ing('Xôi nếp dẻo', 'vừa đủ'), $ing('Nước cốt dừa, đậu phộng', 'vừa đủ')],
        ['Bọc chuối với nếp (hoặc nướng chuối).', 'Nướng đến thơm.', 'Rưới dừa, rắc đậu phộng.'],
        35
    ),
    'xoi-dau-xanh' => $dish(
        [$ing('Gạo nếp', '2 chén'), $ing('Đậu xanh không vỏ', '100 g'), $ing('Muối, hành phi', 'vừa đủ')],
        ['Ngâm nếp + đậu.', 'Hấp chín.', 'Trộn muối, rắc hành phi.'],
        55
    ),
    'che-bap' => $dish(
        [$ing('Bắp mỹ/nếp', '2–3 trái'), $ing('Đường, bột năng', 'vừa đủ'), $ing('Nước cốt dừa', 'vừa đủ')],
        ['Tách hạt, nấu mềm với đường.', 'Sệt bằng bột năng.', 'Chan dừa.'],
        35
    ),
    'suong-sao' => $dish(
        [$ing('Bột sương sáo', '1 gói'), $ing('Đường, nước', 'theo hướng dẫn gói'), $ing('Nước cốt dừa / siro (tuỳ)', 'vừa đủ')],
        ['Nấu bột theo bao bì, đổ khuôn.', 'Để đông, cắt miếng.', 'Dùng với đường/dừa.'],
        30
    ),
    'banh-chuoi-nuong' => $dish(
        [$ing('Chuối chín', '5–6 quả'), $ing('Bột năng / bột gạo', 'vừa đủ'), $ing('Nước cốt dừa, đường', 'vừa đủ')],
        ['Nghiền chuối trộn bột dừa đường.', 'Đổ khay, nướng đến vàng mặt.', 'Cắt miếng.'],
        55
    ),
];

// Only keep slugs that exist in shards (optional safety — still write all)
$path = __DIR__.DIRECTORY_SEPARATOR.'facts'.DIRECTORY_SEPARATOR.'recipes_cook_home_v1.json';
$out = [
    'kb_version' => '1.0.0-cook-home',
    'phase' => 'Recipe-text-A',
    'description' => 'Home-cook ingredients/steps for UI. Neutral cooking text, not restaurant-scale. Provenance committee.',
    'by_slug' => $bySlug,
];
file_put_contents($path, json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)."\n");
echo 'cook_home_recipes='.count($bySlug).PHP_EOL;
echo "Wrote {$path}\n";
