# WTE-S2-01 — Bơm calo one_bowl phổ biến (verified-only)

**Plan:** [`../../features/what-to-eat-next-plan.md`](../../features/what-to-eat-next-plan.md)  
**Sóng:** S2 · **Owner:** Data

---

## Nhiệm vụ

Tăng coverage `calories_kcal` + `serving_grams` cho **one_bowl** phổ biến còn null, đúng verified-only.

## Đọc bắt buộc

- `docs/features/what-to-eat-seed-and-kb.md`
- `docs/features/what-to-eat-fact-a-calories.md`
- `docs/features/what-to-eat-fct-vn.md`
- `docs/features/what-to-eat-fact-completion-plan.md`
- `app/Services/DishCatalogImporter.php` (`ALLOWED_FACT_METHODS`)
- `database/data/what-to-eat/`

## Việc cần làm

1. Liệt kê one_bowl thiếu calo.
2. Ưu tiên: phở, bún, hủ tiếu, cơm tấm, bánh mì…
3. Chỉ ghi khi method ∈ allowlist + `source_ref` + confidence medium|high; luôn cặp gram.
4. Không chắc → null.
5. `db:seed --class=DishSeeder` + `what-to-eat:seed-report`.
6. Cập nhật số coverage trong `what-to-eat-fact-a-calories.md` / `what-to-eat.md` status.
7. Đánh WTE-S2-01 done; ghi N món đã thêm calo.

## DoD

- [ ] +N one_bowl có calo verified (N ≥ 10 khuyến nghị)
- [ ] `skipped_sensitive=0`; không unpaired kcal/grams
- [ ] Tests DishCatalog / calorie pass

## Không làm

- Không LLM invent kcal; không gán YHCT hàng loạt
