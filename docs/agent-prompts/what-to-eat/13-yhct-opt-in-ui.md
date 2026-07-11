# WTE-S4-02 — Opt-in soft YHCT (mặc định off)

**Plan:** [`../../features/what-to-eat-next-plan.md`](../../features/what-to-eat-next-plan.md)  
**Sóng:** S4 · **Owner:** BE+FE  
**Phụ thuộc:** S4-01

---

## Nhiệm vụ

User bật opt-in mới thấy soft explanation hàn–nhiệt/ngũ hành trên mâm; không hard block.

## Đọc trước

- `UserFoodPreference` / history preferences
- `MealComposer::evaluatePlateSoft`
- Modal + `what-to-eat-yhct.md`

## Việc cần làm

1. Pref/toggle (mặc định off) + disclaimer.
2. Truyền flag vào suggest/composer; chỉ soft C/D khi opt-in **và** có data.
3. Tests: off → không ép thermal; on + data → có thể có explanation.
4. Đánh WTE-S4-02 done.

## DoD

- [ ] Default off
- [ ] Không claim chữa bệnh
- [ ] Tests pass

## Không làm

- Không seed YHCT hàng loạt
