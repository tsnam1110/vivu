# WTE-S1-01 — Copy disclaimer / không claim y khoa

**Plan:** [`../../features/what-to-eat-next-plan.md`](../../features/what-to-eat-next-plan.md)  
**Sóng:** S1 · **Owner:** FE/UX

---

## Nhiệm vụ

Chuẩn hoá copy UI «Hôm nay ăn gì» để user hiểu: **tham khảo / ước lượng**, không phải tư vấn y khoa hay dinh dưỡng lâm sàng.

## Đọc trước

- `docs/features/what-to-eat.md`
- `docs/features/what-to-eat-ruleset.md` (disclaimer, tách lớp)
- `lang/vi/what_to_eat.php`
- `resources/views/components/what-to-eat-modal.blade.php`
- (tuỳ) `resources/views/what-to-eat/history.blade.php`

## Việc cần làm

1. Rà chuỗi user-facing (form, kết quả mâm/list, detail, empty).
2. Thêm/sửa:
   - Disclaimer ngắn form + kết quả
   - Phân biệt «Mâm có cấu trúc» vs «Danh sách lựa chọn»
   - Thiếu fact: «Chưa có dữ liệu xác thực»
3. Wire key lang vào Blade.
4. Tone tiếng Việt ngắn, không hù doạ.
5. Đánh WTE-S1-01 done.

## DoD

- [ ] Surface fact sức khoẻ có disclaimer nhất quán
- [ ] Không câu dễ hiểu nhầm “chuẩn y khoa”
- [ ] `php artisan test --filter=WhatToEat` pass (cập nhật assertSee nếu cần)

## Không làm

- Không đổi MealComposer / seed
