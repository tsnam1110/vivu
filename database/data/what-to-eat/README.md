# Dataset kho món (What to Eat)

## Snapshot

| Hạng mục | Giá trị |
|---|---|
| Skeleton | **182** · `dishes_v1/` · `1.2.1-fix` |
| Fact-A calo | **116** / 182 · `2.3.0-fact-a` · null **~66** (chủ đích) |
| Confidence high | **9** (sau phase B) |
| Còn `fdc_id` trên fact calo | **~13** |
| FCT VN pilot | **35** món (`fct_source: vn_2007`) |
| Ops-A | **182** cooking + protein |
| Recipe text | **155** cook_home |
| Standard bowls | `1.1.0-vn` (FCT VN) |
| FCT VN ingredients | **~35** (+ yield cơm/xôi) |
| YHCT | **10** medium |
| Implicit rice config | **206** kcal / 150 g (= `com-trang`) |
| Plan | [`docs/features/what-to-eat-fact-completion-plan.md`](../../../docs/features/what-to-eat-fact-completion-plan.md) |

## Build & seed

```bash
php database/data/what-to-eat/build_fact_overlays.php
php database/data/what-to-eat/build_cook_home_recipes.php
php database/data/what-to-eat/upgrade_fdc_high.php
php database/data/what-to-eat/build_fct_vn_phase_b.php   # FCT VN + yield + bowls → calories
php artisan db:seed --class=DishSeeder
php artisan what-to-eat:seed-report
```

Seeder chain: skeleton → calorie → ops → recipe text → YHCT.

## Chất lượng

- Calo: `fct_table` / `recipe_sum` + provenance; street bowls = **vivu-standard-v1** + FCT VN (medium + limitations).
- Gạo chín: **vivu-yield-v1** (2.5 cơm / 2.2 xôi) → `com-trang` **206**/150g; config `implicit_rice_kcal=206`.
- Sau phase B: **high=9**, còn ~13 `fdc_id` (không còn ~14/~29 của peak FDC-lock).
- Bún `1020`, bánh phở `1013` gắn standard bowl.
- Null calo (~66) cố ý cho món biến thiên cao.
- YHCT: chỉ `tcm_text` / `expert_tcm`.
