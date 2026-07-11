# WTE-S5-01 — Seed canh Bắc + món Tây Nguyên

**Plan:** [`../../features/what-to-eat-next-plan.md`](../../features/what-to-eat-next-plan.md)  
**Sóng:** S5 · **Owner:** Data

---

## Nhiệm vụ

Tăng độ phủ vùng:

- +2–4 `soup` gắn `bac` (cook_home, lunch/dinner)
- +2–3 món `tay_nguyen` (one_bowl hoặc share_feast hợp lý)

Skeleton I+II + `dish_role` committee + `region_tags`; fact III null trừ pipeline verified sẵn.

## Đọc trước

- `what-to-eat-dish-catalog.md`
- `what-to-eat-seed-and-kb.md`
- `dishes_v1/*` + manifest
- `DishCatalogImporter`

## Việc cần làm

1. Món thật, slug unique; cập nhật inventory nếu catalog yêu cầu.
2. JSON đúng shard role; facts dish_role committee.
3. Update manifest counts / quality_notes / kb_version patch.
4. Seed + report; smoke compose filter bac (soup ≥ 2 nếu thêm đủ).
5. Đánh WTE-S5-01 done.

## DoD

- [ ] bac soup pool lunch cook ≥ 2
- [ ] tay_nguyen tăng so baseline
- [ ] `skipped_sensitive=0`
- [ ] Tests catalog pass

## Không làm

- Không bịa calo/YHCT
