# WTE-S1-02 — UI tổng kcal mâm + meal_budget

**Plan:** [`../../features/what-to-eat-next-plan.md`](../../features/what-to-eat-next-plan.md)  
**Sóng:** S1 · **Owner:** FE  
**Phụ thuộc gợi ý:** S1-01 (copy) có thể làm trước hoặc cùng PR cẩn thận conflict modal

---

## Nhiệm vụ

Khi `meta.composition` có `totals`, hiển thị tổng kcal mâm, mục tiêu bữa, trạng thái band / thiếu data.

## Đọc trước

- `app/Services/MealComposer.php` (`totals`, `within_band`, `all_have_kcal`)
- `app/Http/Controllers/Web/WhatToEatController.php`
- `resources/views/components/what-to-eat-modal.blade.php` (khối composition)
- `lang/vi/what_to_eat.php`

## Việc cần làm

1. Dùng `composition.totals.kcal`, `meal_budget`, `within_band`, `all_have_kcal`.
2. UI:
   - Đủ kcal: tổng ~X · mục tiêu ~Y · khớp/lệch
   - Thiếu: message incomplete — **không** cộng null thành 0 giả
3. Optional: 1–2 `plate_reasons`.
4. Lang VI + test nếu cần.
5. Đánh WTE-S1-02 done.

## DoD

- [ ] Compose full plate hiện tổng + budget khi `all_have_kcal`
- [ ] Thiếu calo không hiện số bịa
- [ ] Tests WhatToEat pass

## Không làm

- Không đổi công thức `DailyCalorieEstimator` trừ bug
