# WTE-S0-01 — Đồng bộ metadata manifest seed

**Plan:** [`../../features/what-to-eat-next-plan.md`](../../features/what-to-eat-next-plan.md)  
**Sóng:** S0 · **Owner:** Docs/Data

---

## Nhiệm vụ

Đồng bộ `database/data/what-to-eat/dishes_v1/manifest.json` (và status block doc) với **số món thực tế** sau seed (baseline ~182). Hiện `p0_count + p1_count + p2_count` có thể lệch `dish_count`.

## Đọc trước

- `database/data/what-to-eat/dishes_v1/manifest.json`
- Các shard `dishes_v1_*.json` (field `seed_phase` nếu có)
- `docs/features/what-to-eat.md` (khối trạng thái)
- `docs/features/what-to-eat-next-plan.md` §6 checklist

## Việc cần làm

1. Đếm món theo file / theo `seed_phase`.
2. Sửa `dish_count`, `p0_count`, `p1_count`, `p2_count` (hoặc bỏ count phase nếu không còn meaningful — ghi rõ trong `description`).
3. Cập nhật `docs/features/what-to-eat.md` § trạng thái cho khớp.
4. Đánh `WTE-S0-01` = done trong next-plan §6.
5. **Không** đổi nội dung món / fact.

## DoD

- [ ] Manifest số khớp JSON
- [ ] Doc status không mâu thuẫn
- [ ] Checklist plan cập nhật

## Không làm

- Không refactor importer
- Không thêm/xoá món
