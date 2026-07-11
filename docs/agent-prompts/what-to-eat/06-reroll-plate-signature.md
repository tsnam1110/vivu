# WTE-S1-04 — Gợi ý lại mâm (exclude signature)

**Plan:** [`../../features/what-to-eat-next-plan.md`](../../features/what-to-eat-next-plan.md)  
**Sóng:** S1 · **Owner:** BE+FE  
**Phụ thuộc gợi ý:** S1-02 xong giảm conflict modal

---

## Nhiệm vụ

«Gợi ý lại» mode compose: tránh lặp cùng mâm (`signature`) / reuse quá nhiều món khi pool cho phép.

## Đọc trước

- `app/Services/WhatToEatSuggester.php`
- `app/Services/MealComposer.php`
- Modal `suggest(reroll=true)`
- `SuggestWhatToEatRequest`

## Việc cần làm

1. Reroll gửi `exclude_ids` đủ mạnh; optional `exclude_plate_signatures`.
2. Server tôn trọng exclude khi compose; hết pool → nới + message.
3. Hard role constraints không bị random phá.
4. Test: 2 lần compose (pool đủ) không identical signature nếu có ≥2 combo.
5. Đánh WTE-S1-04 done.

## DoD

- [ ] Reroll đa dạng hơn trên seed hiện tại
- [ ] Tests pass

## Không làm

- Không bỏ data-gate / verified rules
