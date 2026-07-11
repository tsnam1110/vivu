# WTE-S1-03 — Empty state theo filter

**Plan:** [`../../features/what-to-eat-next-plan.md`](../../features/what-to-eat-next-plan.md)  
**Sóng:** S1 · **Owner:** FE

---

## Nhiệm vụ

Empty / partial state rõ khi suggest rỗng hoặc filter (vùng, bữa, mode) không có món.

## Đọc trước

- `WhatToEatController@suggest` (`message`, `catalog_empty`, `partial`)
- `what-to-eat-modal.blade.php`
- `lang/vi/what_to_eat.php`

## Việc cần làm

1. Phân biệt: catalog trống · không khớp filter · partial compose (ép compose).
2. Message + gợi ý action (đổi vùng, mode, bỏ filter, thử pick).
3. A11y: đủ text.
4. Đánh WTE-S1-03 done.

## DoD

- [ ] User hiểu vì sao trống và bước tiếp
- [ ] Alpine không crash khi `dishes=[]` / `composition=null`

## Không làm

- Không nới filter server (trừ bug)
