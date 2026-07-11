<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\DishStatus;
use App\Enums\FiveElement;
use App\Enums\MealSlot;
use App\Models\Dish;
use Illuminate\Database\Seeder;

class DishSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->dishes() as $row) {
            Dish::query()->updateOrCreate(
                ['slug' => $row['slug']],
                array_merge($row, [
                    'status' => DishStatus::Published,
                    'source' => 'system',
                ]),
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function dishes(): array
    {
        $b = MealSlot::Breakfast->value;
        $l = MealSlot::Lunch->value;
        $d = MealSlot::Dinner->value;

        return [
            $this->dish('Pho bo', 'pho-bo', '🍜', 'Phở bò truyền thống — nước dùng đậm đà.',
                [$b, $l], true, true, true, true, FiveElement::Water, 450, 30,
                [['name' => 'Bánh phở', 'amount' => '200g'], ['name' => 'Thịt bò', 'amount' => '100g'], ['name' => 'Hành, rau thơm', 'amount' => 'vừa đủ']],
                ['Hầm xương lấy nước dùng', 'Trần bánh phở', 'Xếp thịt, chan nước, thêm rau'],
                'Dễ tiêu, ấm bụng buổi sáng.', 'Nhiều natrium nếu ăn nhiều nước dùng.', 'Thêm chanh và ớt tuỳ khẩu vị.', 'pho bo noodle'),

            $this->dish('Banh mi thit', 'banh-mi-thit', '🥖', 'Bánh mì thịt nguội / đồ chua — nhanh gọn.',
                [$b, $l], true, true, true, true, FiveElement::Earth, 380, 10,
                [['name' => 'Bánh mì', 'amount' => '1 ổ'], ['name' => 'Thịt nguội / pate', 'amount' => 'vừa'], ['name' => 'Đồ chua, rau', 'amount' => 'vừa']],
                ['Nướng nhẹ bánh', 'Phết pate, xếp thịt và rau', 'Rưới tương ớt'],
                'Tiện mang đi, đủ no nhẹ.', 'Vỏ bánh nhiều gluten.', 'Chọn ổ nóng giòn.', 'banh mi sandwich'),

            $this->dish('Xoi ga', 'xoi-ga', '🍚', 'Xôi gà mặn — no lâu, hợp bữa sáng.',
                [$b], false, true, true, true, FiveElement::Earth, 520, 40,
                [['name' => 'Gạo nếp', 'amount' => '200g'], ['name' => 'Thịt gà', 'amount' => '150g'], ['name' => 'Hành phi', 'amount' => 'vừa']],
                ['Ngâm nếp, hấp xôi', 'Luộc/xé gà trộn gia vị', 'Rắc hành phi'],
                'Năng lượng cao cho buổi sáng bận rộn.', 'Khó tiêu nếu ăn quá no.', 'Ăn kèm dưa leo.', 'xoi sticky rice'),

            $this->dish('Chao ga', 'chao-ga', '🥣', 'Cháo gà loãng — nhẹ bụng.',
                [$b, $d], true, true, true, true, FiveElement::Water, 280, 35,
                [['name' => 'Gạo', 'amount' => '80g'], ['name' => 'Thịt gà', 'amount' => '100g'], ['name' => 'Gừng', 'amount' => 'ít']],
                ['Nấu cháo nhừ', 'Cho gà xé và gừng', 'Nêm vừa miệng'],
                'Dễ tiêu, hợp khi mệt.', 'Ít chất xơ nếu không thêm rau.', 'Rắc tiêu và hành lá.', 'chao porridge'),

            $this->dish('Bun bo Hue', 'bun-bo-hue', '🌶️', 'Bún bò Huế cay nồng đặc trưng.',
                [$b, $l], false, true, true, false, FiveElement::Fire, 550, null,
                null, null,
                'Ấm người, giàu protein.', 'Cay và nhiều dầu — dạ dày yếu nên hạn chế.', 'Gọi mức cay vừa phải.', 'bun bo hue'),

            $this->dish('Com tam suon', 'com-tam-suon', '🍛', 'Cơm tấm sườn bì chả — bữa trưa kinh điển.',
                [$l, $d], false, true, true, true, FiveElement::Earth, 680, 45,
                [['name' => 'Cơm tấm', 'amount' => '1 đĩa'], ['name' => 'Sườn nướng', 'amount' => '1 miếng'], ['name' => 'Đồ chua, mỡ hành', 'amount' => 'vừa']],
                ['Ướp và nướng sườn', 'Nấu cơm tấm', 'Bày đĩa với đồ chua'],
                'No đủ cho buổi chiều làm việc.', 'Calo cao nếu thêm nhiều mỡ hành.', 'Bớt nước mắm ngọt nếu cần giảm đường.', 'com tam broken rice'),

            $this->dish('Bun cha', 'bun-cha', '🥩', 'Bún chả Hà Nội — thịt nướng nước chấm.',
                [$l, $d], false, true, true, true, FiveElement::Fire, 520, 40,
                [['name' => 'Bún', 'amount' => '200g'], ['name' => 'Thịt ba chỉ / chả', 'amount' => '150g'], ['name' => 'Rau sống', 'amount' => '1 đĩa']],
                ['Nướng thịt', 'Pha nước chấm', 'Chấm thịt với bún và rau'],
                'Cân bằng thịt và rau sống.', 'Nướng than nhiều có thể có chất khó lành.', 'Ăn nhiều rau.', 'bun cha'),

            $this->dish('Bun rieu', 'bun-rieu', '🦀', 'Bún riêu cua — chua nhẹ dễ ăn.',
                [$l, $d], false, true, true, true, FiveElement::Water, 480, 50,
                [['name' => 'Bún', 'amount' => '200g'], ['name' => 'Riêu cua', 'amount' => '1 bát'], ['name' => 'Cà chua, đậu', 'amount' => 'vừa']],
                ['Nấu nước dùng cà chua', 'Thả riêu', 'Chan bún, thêm rau'],
                'Giàu canxi từ cua (tuỳ công thức).', 'Có thể tanh với người nhạy cảm.', 'Thêm chanh và rau sống.', 'bun rieu'),

            $this->dish('Mi Quang', 'mi-quang', '🥘', 'Mì Quảng — đặc sản miền Trung.',
                [$l, $d], false, true, true, false, FiveElement::Earth, 560, null,
                null, null,
                'Nhiều topping, no lâu.', 'Nước dùng đặc, cân nhắc lượng dầu.', 'Ăn kèm bánh tráng mè.', 'mi quang'),

            $this->dish('Com ga', 'com-ga', '🍗', 'Cơm gà Hội An / xé phay.',
                [$l, $d], false, true, true, true, FiveElement::Metal, 540, 40,
                [['name' => 'Cơm', 'amount' => '1 chén'], ['name' => 'Gà luộc', 'amount' => '1/4 con'], ['name' => 'Nước mắm gừng', 'amount' => 'chén nhỏ']],
                ['Luộc gà, xé thịt', 'Nấu cơm với nước luộc', 'Chấm mắm gừng'],
                'Protein tốt, dễ chế biến.', 'Da gà nhiều mỡ.', 'Bỏ da nếu muốn nhẹ hơn.', 'com ga chicken rice'),

            $this->dish('Canh chua ca', 'canh-chua-ca', '🐟', 'Canh chua cá — thanh mát bữa cơm.',
                [$l, $d], true, true, true, true, FiveElement::Wood, 220, 25,
                [['name' => 'Cá', 'amount' => '300g'], ['name' => 'Me / cà chua', 'amount' => 'vừa'], ['name' => 'Giá, bạc hà', 'amount' => 'vừa']],
                ['Nấu nước me/cà chua', 'Cho cá chín', 'Nêm và thêm rau'],
                'Nhiều vitamin từ rau.', 'Xương cá — cẩn thận khi ăn.', 'Ăn nóng với cơm.', 'canh chua'),

            $this->dish('Trung chien', 'trung-chien', '🍳', 'Trứng chiên — tự nấu siêu nhanh.',
                [$b, $l, $d], true, true, false, true, FiveElement::Metal, 180, 8,
                [['name' => 'Trứng gà', 'amount' => '2 quả'], ['name' => 'Hành lá', 'amount' => 'ít'], ['name' => 'Dầu ăn', 'amount' => '1 muỗng']],
                ['Đánh trứng với muối hành', 'Chiên vàng hai mặt', 'Cắt miếng ăn với cơm/rau'],
                'Protein rẻ, nhanh.', 'Chiên nhiều dầu tăng calo.', 'Có thể hấp/luộc thay chiên.', 'trung egg'),

            $this->dish('Uc ga hap', 'uc-ga-hap', '🥗', 'Ức gà hấp / áp chảo — nhẹ.',
                [$l, $d], true, true, false, true, FiveElement::Metal, 250, 20,
                [['name' => 'Ức gà', 'amount' => '150g'], ['name' => 'Gia vị', 'amount' => 'vừa'], ['name' => 'Rau củ', 'amount' => '1 đĩa']],
                ['Ướp nhẹ ức gà', 'Hấp hoặc áp chảo', 'Ăn kèm salad'],
                'Ít béo, giàu protein.', 'Có thể khô nếu nấu quá lửa.', 'Ướp yogurt hoặc mật ong loãng cho mềm.', 'chicken breast'),

            $this->dish('Rau xao toi', 'rau-xao-toi', '🥬', 'Rau xanh xào tỏi — món phụ hoặc ăn nhẹ.',
                [$l, $d], true, false, true, true, FiveElement::Wood, 90, 10,
                [['name' => 'Rau cải / bắp cải', 'amount' => '300g'], ['name' => 'Tỏi', 'amount' => '3 tép'], ['name' => 'Dầu', 'amount' => '1 muỗng']],
                ['Phi tỏi thơm', 'Xào rau lửa lớn', 'Nêm nước mắm loãng'],
                'Chất xơ, vitamin.', 'Xào kỹ quá mất chất xanh.', 'Không cho quá nhiều dầu.', 'rau xao vegetable'),

            $this->dish('Sua chua trai cay', 'sua-chua-trai-cay', '🍓', 'Sữa chua trái cây — ăn nhẹ.',
                [$b, $l, $d], true, false, true, true, FiveElement::Earth, 150, 5,
                [['name' => 'Sữa chua', 'amount' => '1 hộp'], ['name' => 'Trái cây', 'amount' => '100g']],
                ['Cắt trái cây', 'Trộn với sữa chua', 'Thêm chút mật ong (tuỳ)'],
                'Lợi khuẩn, dễ làm.', 'Sữa chua có đường — chọn loại ít đường.', 'Dùng làm bữa phụ chiều.', 'yogurt fruit'),

            $this->dish('Trai cay toi', 'trai-cay-toi', '🍎', 'Đĩa trái cây tươi — snack lành.',
                [$b, $l, $d], true, false, true, true, FiveElement::Wood, 100, 3,
                [['name' => 'Trái cây theo mùa', 'amount' => '200g']],
                ['Rửa sạch', 'Cắt miếng', 'Thưởng thức ngay'],
                'Vitamin, hydrat.', 'Một số trái nhiều đường tự nhiên.', 'Kết hợp 2–3 loại màu.', 'fruit'),

            $this->dish('Banh trang tron', 'banh-trang-tron', '🥙', 'Bánh tráng trộn — ăn vặt đường phố.',
                [$l, $d], true, false, true, true, FiveElement::Fire, 320, 15,
                [['name' => 'Bánh tráng', 'amount' => '2–3 cái'], ['name' => 'Xoài, khô bò', 'amount' => 'vừa'], ['name' => 'Tương ớt, trứng cút', 'amount' => 'vừa']],
                ['Cắt bánh tráng', 'Trộn topping và sốt', 'Ăn ngay cho giòn'],
                'Vui miệng, chia sẻ nhóm.', 'Nhiều gia vị và calo ẩn.', 'Bớt sốt dầu nếu muốn nhẹ.', 'banh trang'),

            $this->dish('Lau thai', 'lau-thai', '🍲', 'Lẩu Thái — hợp ăn ngoài tối.',
                [$d], false, true, true, false, FiveElement::Fire, 700, null,
                null, null,
                'Đa dạng rau và hải sản.', 'Nước lẩu cay chua — dạ dày yếu cẩn thận.', 'Ưu tiên rau trước, thịt sau.', 'lau hotpot thai'),

            $this->dish('Nuong BBQ', 'nuong-bbq', '🔥', 'Đồ nướng BBQ — tiệc tối.',
                [$d], false, true, true, false, FiveElement::Fire, 750, null,
                null, null,
                'Không khí vui, protein nhiều.', 'Thịt nướng cháy có thể không tốt nếu thường xuyên.', 'Kẹp nhiều rau sống.', 'nuong grill bbq'),

            $this->dish('Hu tieu', 'hu-tieu', '🍜', 'Hủ tiếu Nam Vang / xương — sáng hoặc trưa.',
                [$b, $l], true, true, true, false, FiveElement::Water, 420, null,
                null, null,
                'Nước trong, topping đa dạng.', 'Có thể nhiều bột năng.', 'Gọi khô nếu muốn bớt nước dùng.', 'hu tieu'),

            $this->dish('Banh cuon', 'banh-cuon', '🥟', 'Bánh cuốn nhân thịt — nhẹ bụng.',
                [$b, $l], true, true, true, true, FiveElement::Earth, 300, 35,
                [['name' => 'Bột gạo', 'amount' => '200g'], ['name' => 'Thịt băm + mộc nhĩ', 'amount' => '150g'], ['name' => 'Nước mắm chua ngọt', 'amount' => 'chén']],
                ['Tráng bánh mỏng', 'Gói nhân', 'Chan nước chấm, chả'],
                'Dễ tiêu hơn phở đặc.', 'Nước chấm ngọt — hạn chế đường.', 'Ăn kèm rau thơm.', 'banh cuon'),

            $this->dish('Goi cuon', 'goi-cuon', '🥗', 'Gỏi cuốn tôm thịt — tươi mát.',
                [$l, $d], true, true, true, true, FiveElement::Wood, 200, 20,
                [['name' => 'Bánh tráng', 'amount' => '4 cái'], ['name' => 'Tôm, thịt', 'amount' => 'vừa'], ['name' => 'Bún, rau', 'amount' => 'vừa']],
                ['Luộc tôm thịt', 'Cuốn với bún rau', 'Chấm tương hoặc mắm nêm'],
                'Ít dầu, nhiều rau.', 'Nước chấm béo nếu dùng tương đậu phộng nhiều.', 'Cuốn vừa ăn, không để khô.', 'goi cuon spring roll'),

            $this->dish('Com chay', 'com-chay', ' greenery', 'Cơm chay — đạm thực vật.',
                [$l, $d], false, true, true, true, FiveElement::Wood, 450, 30,
                [['name' => 'Cơm', 'amount' => '1 chén'], ['name' => 'Đậu phụ / nấm', 'amount' => '150g'], ['name' => 'Rau củ', 'amount' => '1 đĩa']],
                ['Xào nấm đậu', 'Nấu canh rau', 'Bày mâm cơm chay'],
                'Thân thiện người ăn chay.', 'Cần đa dạng nguồn đạm thực vật.', 'Hạn chế chiên nhiều.', 'com chay vegetarian'),

            $this->dish('Pho chay', 'pho-chay', '🌿', 'Phở chay — nước dùng rau củ.',
                [$b, $l], true, true, true, true, FiveElement::Wood, 320, 40,
                [['name' => 'Bánh phở', 'amount' => '200g'], ['name' => 'Nấm, đậu', 'amount' => 'vừa'], ['name' => 'Rau thơm', 'amount' => 'vừa']],
                ['Nấu nước dùng rau củ', 'Trần bánh', 'Chan và thêm topping'],
                'Nhẹ, phù hợp ngày chay.', 'Ít protein nếu thiếu đậu/nấm.', 'Thêm tàu hũ ky hoặc đậu phụ.', 'pho chay'),

            $this->dish('Mi xao bo', 'mi-xao-bo', '🍝', 'Mì xào bò — no, hợp tối.',
                [$l, $d], false, true, true, true, FiveElement::Metal, 600, 25,
                [['name' => 'Mì trứng', 'amount' => '1 vắt'], ['name' => 'Thịt bò', 'amount' => '100g'], ['name' => 'Rau cải', 'amount' => 'vừa']],
                ['Luộc mì', 'Xào bò và rau', 'Đảo đều với mì'],
                'Nhanh no, đủ chất.', 'Nhiều dầu khi xào.', 'Lửa lớn, ít dầu.', 'mi xao noodles'),

            $this->dish('Chao long', 'chao-long', '🥣', 'Cháo lòng — đặc sản sáng.',
                [$b], false, true, true, false, FiveElement::Water, 400, null,
                null, null,
                'Ấm bụng, no.', 'Không hợp người kỵ nội tạng / mỡ.', 'Chọn quán sạch sẽ.', 'chao long'),

            $this->dish('Banh bao', 'banh-bao', '🥟', 'Bánh bao nhân thịt/trứng — mang đi.',
                [$b, $l], true, true, true, true, FiveElement::Earth, 280, 45,
                [['name' => 'Bột mì men', 'amount' => '300g'], ['name' => 'Nhân thịt', 'amount' => '200g']],
                ['Nhồi bột, ủ', 'Gói nhân', 'Hấp chín'],
                'Tiện, no vừa.', 'Vỏ bột tinh bột cao.', 'Hấp lại nếu để nguội.', 'banh bao bun'),

            $this->dish('Che ba mau', 'che-ba-mau', '🍧', 'Chè ba màu — tráng miệng / ăn nhẹ.',
                [$l, $d], true, false, true, true, FiveElement::Earth, 280, 20,
                [['name' => 'Đậu, thạch, bột báng', 'amount' => 'vừa'], ['name' => 'Nước cốt dừa', 'amount' => 'vừa']],
                ['Nấu từng lớp đậu', 'Xếp ly', 'Chan cốt dừa'],
                'Giải nhiệt, vui miệng.', 'Nhiều đường.', 'Xin ít đường khi mua.', 'che dessert'),

            $this->dish('Ca phe sua da', 'ca-phe-sua-da', '☕', 'Cà phê sữa đá — “bữa” nhẹ sáng.',
                [$b], true, false, true, true, FiveElement::Water, 180, 10,
                [['name' => 'Cà phê phin', 'amount' => '1 phin'], ['name' => 'Sữa đặc', 'amount' => '2 muỗng'], ['name' => 'Đá', 'amount' => 'cốc']],
                ['Pha phin', 'Khuấy sữa', 'Thêm đá'],
                'Tỉnh táo buổi sáng.', 'Cafein và đường — hạn chế buổi tối.', 'Có thể dùng sữa tươi ít đường.', 'ca phe coffee'),

            $this->dish('Sinh to bo', 'sinh-to-bo', '🥑', 'Sinh tố bơ — no nhẹ.',
                [$b, $l], true, false, true, true, FiveElement::Earth, 320, 8,
                [['name' => 'Bơ', 'amount' => '1/2 quả'], ['name' => 'Sữa / đá', 'amount' => 'vừa'], ['name' => 'Đường (tuỳ)', 'amount' => 'ít']],
                ['Xay bơ với sữa đá', 'Nêm nhẹ', 'Uống ngay'],
                'Chất béo tốt, no lâu.', 'Calo cao nếu nhiều sữa đặc.', 'Bớt đường.', 'sinh to smoothie'),

            $this->dish('Sup cua', 'sup-cua', '🥣', 'Súp cua — khai vị hoặc ăn nhẹ.',
                [$l, $d], true, false, true, true, FiveElement::Water, 200, 25,
                [['name' => 'Thịt cua', 'amount' => '100g'], ['name' => 'Trứng', 'amount' => '1 quả'], ['name' => 'Bắp / nấm', 'amount' => 'vừa']],
                ['Nấu nước dùng', 'Cho cua và bột năng', 'Đổ trứng tan'],
                'Nhẹ bụng, ấm.', 'Bột năng nhiều làm đặc calo rỗng.', 'Thêm tiêu và rau mùi.', 'sup soup'),

            $this->dish('Nem ran', 'nem-ran', '🥟', 'Nem rán / chả giò — món phụ hoặc chính nhẹ.',
                [$l, $d], true, true, true, true, FiveElement::Fire, 350, 40,
                [['name' => 'Bánh đa nem', 'amount' => '10 cái'], ['name' => 'Thịt + miến + rau', 'amount' => 'nhân'], ['name' => 'Dầu chiên', 'amount' => 'đủ']],
                ['Gói nem', 'Chiên vàng', 'Chấm nước mắm'],
                'Giòn ngon, hợp sum họp.', 'Chiên ngập dầu — không nên thường xuyên.', 'Chiên air-fryer nếu có.', 'nem spring roll fried'),

            $this->dish('Lau ca keo', 'lau-ca-keo', '🐟', 'Lẩu cá kèo — đặc sản tối.',
                [$d], false, true, true, false, FiveElement::Water, 550, null,
                null, null,
                'Ngọt nước, nhiều rau.', 'Xương cá nhỏ — cẩn thận.', 'Nhúng rau nhiều.', 'lau ca'),

            $this->dish('Com rang', 'com-rang', '🍳', 'Cơm rang trứng/dưa bò — tận dụng cơm nguội.',
                [$l, $d], false, true, true, true, FiveElement::Earth, 500, 15,
                [['name' => 'Cơm nguội', 'amount' => '1–2 chén'], ['name' => 'Trứng / thịt', 'amount' => 'vừa'], ['name' => 'Hành, gia vị', 'amount' => 'vừa']],
                ['Phi thơm hành', 'Đảo cơm lửa lớn', 'Cho trứng/thịt'],
                'Nhanh, giảm lãng phí cơm.', 'Dễ mặn và dầu.', 'Ít nước tương, nhiều rau.', 'com rang fried rice'),

            $this->dish('Bun thit nuong', 'bun-thit-nuong', '🥩', 'Bún thịt nướng — trưa/tối.',
                [$l, $d], false, true, true, true, FiveElement::Fire, 500, 35,
                [['name' => 'Bún', 'amount' => '200g'], ['name' => 'Thịt nướng', 'amount' => '120g'], ['name' => 'Đồ chua, rau', 'amount' => 'vừa']],
                ['Ướp nướng thịt', 'Bày bún rau', 'Chan nước mắm'],
                'Cân bằng tinh bột – đạm – rau.', 'Nước mắm ngọt nhiều đường.', 'Xin nước mắm riêng.', 'bun thit nuong'),

            $this->dish('Banh xeo', 'banh-xeo', '🥞', 'Bánh xèo giòn — cuốn rau sống.',
                [$l, $d], false, true, true, true, FiveElement::Earth, 480, 30,
                [['name' => 'Bột bánh xèo', 'amount' => '200g'], ['name' => 'Tôm thịt giá', 'amount' => 'nhân'], ['name' => 'Rau sống', 'amount' => '1 đĩa']],
                ['Pha bột', 'Đổ khuôn chiên giòn', 'Cuốn lá và chấm'],
                'Nhiều rau khi cuốn.', 'Chiên dầu — ăn vừa phải.', 'Làm mỏng để giòn và nhẹ hơn.', 'banh xeo'),

            $this->dish('Oc xao', 'oc-xao', '🐌', 'Ốc xào/luộc — ăn vặt tối.',
                [$d], true, true, true, false, FiveElement::Water, 300, null,
                null, null,
                'Vui miệng, giàu khoáng.', 'Vệ sinh ốc rất quan trọng.', 'Chọn quán uy tín.', 'oc snail'),

            $this->dish('Banh canh', 'banh-canh', '🍜', 'Bánh canh cua / giò — đặc sệt.',
                [$l, $d], false, true, true, false, FiveElement::Water, 480, null,
                null, null,
                'No lâu, ấm bụng.', 'Nước đặc nhiều tinh bột.', 'Bớt bánh nếu muốn nhẹ.', 'banh canh'),

            $this->dish('Salad ga', 'salad-ga', '🥗', 'Salad ức gà — ăn nhẹ / chính nhẹ.',
                [$l, $d], true, true, true, true, FiveElement::Wood, 280, 15,
                [['name' => 'Ức gà', 'amount' => '120g'], ['name' => 'Xà lách, cà chua', 'amount' => '1 tô'], ['name' => 'Sốt dầu giấm', 'amount' => '2 muỗng']],
                ['Luộc/nướng ức gà', 'Trộn rau', 'Chan sốt vừa đủ'],
                'Ít tinh bột, nhiều xơ.', 'Sốt kem/mayo làm tăng calo.', 'Ưu tiên sốt chua nhẹ.', 'salad chicken'),

            $this->dish('Chao yen mach', 'chao-yen-mach', '🥣', 'Cháo yến mạch trái cây — sáng healthy.',
                [$b], true, true, false, true, FiveElement::Wood, 220, 12,
                [['name' => 'Yến mạch', 'amount' => '40g'], ['name' => 'Sữa / nước', 'amount' => '200ml'], ['name' => 'Trái cây, hạt', 'amount' => 'vừa']],
                ['Nấu yến mạch', 'Thêm trái cây', 'Rắc hạt'],
                'No lâu, tốt tiêu hoá.', 'Một số người đầy hơi với yến mạch.', 'Ngâm qua đêm cho mềm.', 'oat porridge'),

            $this->dish('Mi cay', 'mi-cay', '🌶️', 'Mì cay Hàn — ăn ngoài / tự nấu gói.',
                [$l, $d], false, true, true, true, FiveElement::Fire, 550, 12,
                [['name' => 'Mì gói cay', 'amount' => '1 gói'], ['name' => 'Trứng, phô mai (tuỳ)', 'amount' => 'topping']],
                ['Luộc mì', 'Nêm gói gia vị vừa', 'Thêm topping'],
                'Nhanh, “đã” cay.', 'Natri và cay cao — không nên thường xuyên.', 'Bớt bột cay, thêm rau.', 'mi cay ramyeon'),

            $this->dish('Dimsum', 'dimsum', '🥟', 'Dimsum hấp — ăn ngoài trưa/sáng muộn.',
                [$b, $l], true, true, true, false, FiveElement::Earth, 400, null,
                null, null,
                'Đa dạng, chia sẻ nhóm.', 'Một số món chiên calo cao.', 'Ưu tiên hấp hơn chiên.', 'dimsum ha cao'),

            $this->dish('Pizza lat', 'pizza-lat', '🍕', 'Pizza — bữa chính ăn ngoài.',
                [$l, $d], false, true, true, false, FiveElement::Fire, 800, null,
                null, null,
                'No, vui miệng.', 'Nhiều bột và phô mai.', 'Chọn đế mỏng, nhiều rau.', 'pizza'),

            $this->dish('Sushi set', 'sushi-set', '🍣', 'Set sushi / sashimi — ăn ngoài.',
                [$l, $d], true, true, true, false, FiveElement::Water, 450, null,
                null, null,
                'Protein cá, phần nhẹ.', 'Cần độ tươi cao.', 'Wasabi và gừng hỗ trợ cảm nhận tươi.', 'sushi'),

            $this->dish('Ga ran', 'ga-ran', '🍗', 'Gà rán — chính hoặc chia sẻ.',
                [$l, $d], false, true, true, false, FiveElement::Fire, 650, null,
                null, null,
                'Ngon miệng, no.', 'Chiên nhiều dầu.', 'Kèm salad, hạn chế nước ngọt.', 'ga ran fried chicken'),

            $this->dish('Canh rau ngot', 'canh-rau-ngot', '🌿', 'Canh rau ngót / rau củ — món phụ tự nấu.',
                [$l, $d], true, false, false, true, FiveElement::Wood, 60, 15,
                [['name' => 'Rau ngót / bí', 'amount' => '200g'], ['name' => 'Thịt băm (tuỳ)', 'amount' => '50g']],
                ['Nấu nước dùng nhẹ', 'Cho rau vừa chín tới', 'Nêm nhạt'],
                'Thanh, nhiều vitamin.', 'Nấu lâu mất màu xanh.', 'Ăn kèm cơm và món mặn.', 'canh rau'),
        ];
    }

    /**
     * @param  list<string>  $slots
     * @param  list<array{name: string, amount: string}>|null  $ingredients
     * @param  list<string>|null  $steps
     * @return array<string, mixed>
     */
    private function dish(
        string $name,
        string $slug,
        string $emoji,
        string $summary,
        array $slots,
        bool $light,
        bool $main,
        bool $dineOut,
        bool $cookHome,
        FiveElement $element,
        ?int $kcal,
        ?int $cookMinutes,
        ?array $ingredients,
        ?array $steps,
        string $benefits,
        string $harms,
        string $advice,
        string $keywords,
    ): array {
        // Fix typo in com-chay emoji if any
        if ($emoji === ' greenery') {
            $emoji = '🌱';
        }

        return [
            'name' => $name === 'Pho bo' ? 'Phở bò'
                : ($name === 'Banh mi thit' ? 'Bánh mì thịt'
                : $this->viName($slug, $name)),
            'slug' => $slug,
            'emoji' => $emoji,
            'summary' => $summary,
            'meal_slots' => $slots,
            'supports_light' => $light,
            'supports_main' => $main,
            'supports_dine_out' => $dineOut,
            'supports_cook_home' => $cookHome,
            'five_element' => $element,
            'calories_kcal' => $kcal,
            'serving_grams' => $kcal !== null
                ? $this->defaultServingGrams($slug, $light, $main)
                : null,
            'cook_minutes' => $cookMinutes,
            'ingredients' => $ingredients,
            'steps' => $steps,
            'benefits' => $benefits,
            'harms' => $harms,
            'advice' => $advice,
            'notes' => null,
            'search_keywords' => $keywords,
        ];
    }

    private function defaultServingGrams(string $slug, bool $light, bool $main): int
    {
        return match (true) {
            str_contains($slug, 'ca-phe') => 250,
            str_contains($slug, 'sinh-to') => 300,
            str_contains($slug, 'sua-chua'), str_contains($slug, 'trai-cay'), str_contains($slug, 'che-') => 150,
            str_contains($slug, 'salad'), str_contains($slug, 'goi-cuon'), str_contains($slug, 'sup-') => 200,
            $light && ! $main => 120,
            default => 350,
        };
    }

    private function viName(string $slug, string $fallback): string
    {
        return match ($slug) {
            'xoi-ga' => 'Xôi gà',
            'chao-ga' => 'Cháo gà',
            'bun-bo-hue' => 'Bún bò Huế',
            'com-tam-suon' => 'Cơm tấm sườn',
            'bun-cha' => 'Bún chả',
            'bun-rieu' => 'Bún riêu',
            'mi-quang' => 'Mì Quảng',
            'com-ga' => 'Cơm gà',
            'canh-chua-ca' => 'Canh chua cá',
            'trung-chien' => 'Trứng chiên',
            'uc-ga-hap' => 'Ức gà hấp',
            'rau-xao-toi' => 'Rau xào tỏi',
            'sua-chua-trai-cay' => 'Sữa chua trái cây',
            'trai-cay-toi' => 'Trái cây tươi',
            'banh-trang-tron' => 'Bánh tráng trộn',
            'lau-thai' => 'Lẩu Thái',
            'nuong-bbq' => 'Đồ nướng BBQ',
            'hu-tieu' => 'Hủ tiếu',
            'banh-cuon' => 'Bánh cuốn',
            'goi-cuon' => 'Gỏi cuốn',
            'com-chay' => 'Cơm chay',
            'pho-chay' => 'Phở chay',
            'mi-xao-bo' => 'Mì xào bò',
            'chao-long' => 'Cháo lòng',
            'banh-bao' => 'Bánh bao',
            'che-ba-mau' => 'Chè ba màu',
            'ca-phe-sua-da' => 'Cà phê sữa đá',
            'sinh-to-bo' => 'Sinh tố bơ',
            'sup-cua' => 'Súp cua',
            'nem-ran' => 'Nem rán',
            'lau-ca-keo' => 'Lẩu cá kèo',
            'com-rang' => 'Cơm rang',
            'bun-thit-nuong' => 'Bún thịt nướng',
            'banh-xeo' => 'Bánh xèo',
            'oc-xao' => 'Ốc',
            'banh-canh' => 'Bánh canh',
            'salad-ga' => 'Salad gà',
            'chao-yen-mach' => 'Cháo yến mạch',
            'mi-cay' => 'Mì cay',
            'dimsum' => 'Dimsum',
            'pizza-lat' => 'Pizza',
            'sushi-set' => 'Sushi',
            'ga-ran' => 'Gà rán',
            'canh-rau-ngot' => 'Canh rau',
            default => $fallback,
        };
    }
}
