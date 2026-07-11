# What to Eat — Kế hoạch hoàn thiện thông tin món

> **Mục tiêu:** Đưa tri thức món (calo, ops, sau là YHCT) lên mức **tốt nhất có thể**
> mà vẫn **verified-only** — không blog-guess, không claim y khoa giả.  
> **Liên quan:**  
> [`what-to-eat-seed-and-kb.md`](what-to-eat-seed-and-kb.md) ·  
> [`what-to-eat-fact-a-calories.md`](what-to-eat-fact-a-calories.md) ·  
> [`what-to-eat-dish-catalog.md`](what-to-eat-dish-catalog.md) ·  
> [`what-to-eat-ruleset.md`](what-to-eat-ruleset.md)

---

## 1. Vì sao trước đây “chưa full calo”?

Không phải không làm được — mà **hai lớp dữ liệu** phải có trước khi gán số:

| Lớp | Ý nghĩa | Khi thiếu |
|---|---|---|
| **Công thức gram khóa (frozen recipe)** | 1 slug = 1 khẩu phần chuẩn ViVu với gram từng thành phần | Không biết nhân FCT với bao nhiêu |
| **FCT (bảng thành phần)** | kcal/100g từng nguyên liệu (USDA và/hoặc Viện Dinh dưỡng VN) | Không chứng minh được năng lượng |

**Giải pháp vận hành (đã chốt):**

1. **Ngay:** USDA FCT + `recipe_sum` / `fct_table` cho mọi món **định lượng được**.  
2. **Chuẩn ViVu v1:** với món phố (phở, bún…), khóa **một** công thức nội bộ ghi rõ *“khẩu phần chuẩn ViVu v1 — không phải trung bình mọi quán”* + `confidence: medium` + `limitations`.  
3. **Sau (curator):** thay USDA component bằng FCT VN khi có quyền trích + mã dòng; khóa FDC/VN ID → `confidence: high`.

Slogan: *Có recipe khóa + FCT component thì làm; không có thì null; không bịa blog.*

---

## 2. Phân tầng hoàn thiện (DoD)

### Tầng 0 — Skeleton (đã xong)

- Tên, slug, meal_slots, flags, `dish_role`, `region_tags`  
- **DoD:** catalog gợi ý / compose chạy được  

### Tầng 1 — Ops (non-medical)

- `cooking_method`, `protein_source` (+ sau `flavor_tags` khi rõ)  
- Method provenance: `committee`  
- **DoD:** rule A05 (chiên), E01 (đạm), S02 (chay) có data  

### Tầng 2 — Fact-A calo

| Nhánh | Method | Đối tượng |
|---|---|---|
| 2a | `fct_table` | 1 món ≈ 1 FCT (cơm, trà, sữa chua…) |
| 2b | `recipe_sum` home | Canh, xào, kho, trứng, đậu… |
| 2c | `recipe_sum` **standard bowl v1** | Phở/bún/cơm tấm… với recipe ViVu khóa |

- **DoD lô hiện tại:** phủ tối đa 2a+2b+2c defensible; mọi dòng có provenance + limitation  
- **Không DoD:** 100% kcal “chuẩn lab VN” hay “đúng mọi quán”  

### Tầng 3 — Recipe text (ingredients/steps)

- Công thức hiển thị user (JSON ingredients/steps)  
- Có thể đồng bộ từ frozen recipe khi approve  
- **DoD sau:** lô cook_home ưu tiên  

### Tầng 4 — Fact-C/D YHCT

- `thermal_nature`, `five_element`  
- **Chỉ** `tcm_text` / `expert_tcm` / `committee` có biên bản  
- **Không** LLM gán  

### Tầng 5 — Macro / natri / allergen

- Khi có cột + FCT đủ  

---

## 3. Registry “Khẩu phần chuẩn ViVu v1”

Mọi `recipe_sum` cho món phố **bắt buộc** ghi:

```yaml
standard_id: vivu-standard-v1
slug: pho-bo
serving_label: "1 tô chuẩn ViVu"
serving_grams: 500
ingredients:
  - { name: "...", grams: N, fct_class: "...", kcal_per_100g: X }
limitations: "Không gồm quẩy, mỡ nổi thêm, ớt sa tế ngoài tô..."
confidence: medium
```

User-facing: disclaimer đã có + `calorie_source.limitations`.

Khi curator đổi recipe → bump `vivu-standard-v2` + tính lại, không im lặng sửa số.

---

## 4. Nguồn FCT

| Ưu tiên | Nguồn | Dùng khi |
|---|---|---|
| 1 | USDA FoodData Central (URL/FDC) | Component quốc tế, hiện tại |
| 2 | Bảng TP TP Việt Nam (Viện Dinh dưỡng) | Khi có trích hợp pháp + mã dòng |
| 3 | Label / lab | Sản phẩm đóng gói / đo thực |

**Không:** chatgpt, average_internet, similar_dish.

---

## 5. Lộ trình & trạng thái

| Phase | Nội dung | Status |
|---|---|---|
| **P-fact-0** | Plan + registry rules (file này) | ✅ |
| **P-fact-1** | Fact-A home + FCT đơn | ✅ |
| **P-fact-2** | Mở rộng recipe_sum home + ops full catalog | ✅ (~116 kcal, 182 ops) |
| **P-fact-3** | Standard bowl v1 (phở, bún, cơm tấm, hủ tiếu…) | ✅ medium + limitations |
| **P-fact-4** | Recipe text UI lô cook_home | ✅ `recipes_cook_home_v1.json` (~155, đủ `supports_cook_home`) |
| **P-fact-5** | Map FCT VN chính thức | ✅ `1.1.0-vn-fct` (~35 ing + yield); pilot **35** món; standard bowls `1.1.0-vn` |
| **P-fact-6** | YHCT thermal/element | ✅ pipeline + bootstrap ~10 `tcm_text` medium; expert_queue chờ `expert_tcm` |

---

## 6. Ma trận ưu tiên calo (thứ tự làm)

1. **Starch / beverage / fruit / plain protein** → `fct_table`  
2. **Side veg + soup + side_extra nhà** → `recipe_sum`  
3. **Main protein kho/rang đơn** → `recipe_sum`  
4. **One-bowl sáng đơn** (xôi, cháo, bánh mì tối giản) → `recipe_sum`  
5. **One-bowl phố** → `vivu-standard-v1` medium  
6. **Share feast** → chỉ khi có “suất 1 người ước” + limitation mạnh, hoặc để null  

---

## 7. File & lệnh

```text
docs/features/what-to-eat-fact-completion-plan.md   ← file này
docs/features/what-to-eat-fact-a-calories.md
docs/features/what-to-eat-fct-vn.md
docs/features/what-to-eat-yhct.md
database/data/what-to-eat/facts/calories_fact_a.json
database/data/what-to-eat/facts/ops_fields_fact_a.json
database/data/what-to-eat/facts/recipes_standard_v1.json  (registry công thức khóa)
database/data/what-to-eat/facts/recipes_cook_home_v1.json (ingredients/steps UI)
database/data/what-to-eat/facts/fct_vn_ingredients.json
database/data/what-to-eat/facts/yhct_fact_a.json
```

```bash
php database/data/what-to-eat/build_fact_overlays.php      # calo + ops
php database/data/what-to-eat/build_cook_home_recipes.php  # recipe text cook_home
php database/data/what-to-eat/upgrade_fdc_high.php         # khóa FDC + high thuần
php artisan db:seed --class=DishSeeder
php artisan what-to-eat:seed-report
```

---

## 8. Definition of Done — “hoàn thiện tốt nhất có thể” (lô hiện tại)

- [x] Plan & nguyên tắc ghi tài liệu  
- [x] Catalog skeleton P0–P2 (~182)  
- [x] ≥60% published có calo (**~64% = 116/182**)  
- [x] 100% có `cooking_method` + `protein_source`  
- [x] UI nguồn calo + disclaimer  
- [x] Standard bowl v1 (phở, bún, cơm tấm, hủ tiếu, xôi, bánh mì…)  
- [x] 0 field YHCT bịa  
- [x] Test seed + WhatToEat (chạy sau build)  
- [x] ingredients/steps cook_home (~155)  
- [x] FDC lock peak (~29 `fdc_id` / ~14 pure high) — lịch sử trước phase B  
- [x] Sau phase B: **high=9**, còn ~**13** `fdc_id`, pilot **35** `vn_2007`  
- [x] FCT VN + yield gạo; `com-trang` / `implicit_rice_kcal` = **206**/150g  
- [x] Standard bowl gắn `vn-bun` / `vn-banh-pho` + protein VN  
- [x] Null calo ~66 chủ đích  
- [x] YHCT pipeline + bootstrap medium  
- [ ] Đo yield cơm thực tế → high; chuyển nốt mì/bánh cuốn  
- [ ] YHCT `expert_tcm` + queue phức tạp (chuyên gia)  

> “Hoàn thiện” ở đây = **phủ tối đa có nguồn + honest limitations**,  
> không = “mọi món đúng lab VN / đúng mọi quán”.

---

## 9. Changelog

| Ngày | Thay đổi |
|---|---|
| 2026-07-11 | Ban hành plan; chốt vivu-standard-v1; phân tầng 0–6; DoD lô hiện tại |
| 2026-07-11 | Build `2.0.0-fact-a` (~116 kcal) + ops full 182 + recipes_standard_v1; script `build_fact_overlays.php` |
| 2026-07-11 | P-fact-4/5/6: cook_home recipes 155; FDC lock 29 / high 14; FCT VN 15; YHCT 10; calories `2.1.0-fact-a` |
| 2026-07-11 | FCT VN curator audit `1.0.0-vn-fct` (24 high); pilot 8 món → calories `2.2.0-fact-a` |
| 2026-07-11 | Phase B: yield gạo + bowls VN + 35 pilot → `2.3.0-fact-a`; script `build_fct_vn_phase_b.php` |
| 2026-07-12 | Sync: high=9 / fdc~13 / null~66; `implicit_rice_kcal=206`; bảng mẫu fact-a theo data |
