# WTE-S0-02 — Checklist release What to Eat

**Plan:** [`../../features/what-to-eat-next-plan.md`](../../features/what-to-eat-next-plan.md)  
**Sóng:** S0 · **Owner:** Docs

---

## Nhiệm vụ

Viết checklist release/local verify cho What to Eat (dev copy-paste được).

## Đọc trước

- `docs/06-setup-development.md`
- `docs/features/what-to-eat.md`
- `docs/features/what-to-eat-next-plan.md` §7

## Việc cần làm

1. Thêm section checklist vào `what-to-eat.md` **hoặc** `06-setup-development.md` (ưu tiên what-to-eat § riêng + link từ setup).
2. Nội dung tối thiểu:
   - `php artisan migrate`
   - `php artisan db:seed --class=DishSeeder`
   - `php artisan what-to-eat:seed-report` (ghi ngưỡng: role 100%, region 100%)
   - `php artisan test --filter="WhatToEat|MealComposer|DishCatalog|DishCalorie"`
   - Manual: compose dinner cook; filter trung/bac/ngoai; chi tiết calo
   - Cấm `migrate:fresh` ngoài local
3. Link từ `docs/README.md` nếu thiếu.
4. Đánh WTE-S0-02 done trên next-plan.

## DoD

- [ ] Checklist tiếng Việt, lệnh chạy được từ root
- [ ] Có liên kết chéo

## Không làm

- Không đổi code runtime
