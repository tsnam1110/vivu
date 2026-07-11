# WTE-S3-01 — Soft diversity protein / tránh 2× fry

**Plan:** [`../../features/what-to-eat-next-plan.md`](../../features/what-to-eat-next-plan.md)  
**Sóng:** S3 · **Owner:** BE

---

## Nhiệm vụ

Tận dụng `cooking_method` + `protein_source` (đã phủ gần 100%): soft diversity khi compose/pick; hard role giữ nguyên.

## Đọc trước

- `app/Services/MealComposer.php`
- `app/Services/WhatToEatSuggester.php`
- `docs/features/what-to-eat-ruleset.md` (A05, E01)
- `config/what_to_eat.php`

## Việc cần làm

1. Compose: penalty 2× fry; penalty trùng protein meat/seafood; reward đa dạng.
2. Pick: soft tương tự trong top band.
3. Null field → skip penalty (data-gate).
4. Unit tests rõ ràng.
5. Có thể bump version patch `0.2.1` (hoặc để S3-03) — nhất quán với plan.
6. Đánh WTE-S3-01 done.

## DoD

- [ ] Tests mới pass
- [ ] Compose vẫn đủ 3 role trên seed hiện tại
- [ ] Random chỉ sau hard-pass

## Không làm

- Không hard-reject mọi mâm có 1 món chiên
