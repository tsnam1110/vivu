# WTE-S5-02 — Admin curator policy

**Plan:** [`../../features/what-to-eat-next-plan.md`](../../features/what-to-eat-next-plan.md)  
**Sóng:** S5 · **Owner:** Admin FE + Docs

---

## Nhiệm vụ

Siết UX/admin: sửa calo/YHCT = curator trusted; cảnh báo; SOP duyệt contribution.

## Đọc trước

- `admin/src/pages/DishesPage.tsx`
- `app/Http/Requests/Admin/StoreDishRequest.php`
- `what-to-eat-seed-and-kb.md`

## Việc cần làm

1. Banner/help: field nào curator vs seed provenance.
2. Optional: confirm khi clear `calories_kcal`.
3. Doc ngắn SOP admin (approve contribution → canonical).
4. Đánh WTE-S5-02 done.

## DoD

- [ ] Admin hiểu ranh giới seed vs hand-edit
- [ ] CRUD tests admin không vỡ

## Không làm

- Không chặn API trừ validate enum hợp lệ
