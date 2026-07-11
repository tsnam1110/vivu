# WTE-S3-02 — Template dine_out feast (share_feast)

**Plan:** [`../../features/what-to-eat-next-plan.md`](../../features/what-to-eat-next-plan.md)  
**Sóng:** S3 · **Owner:** BE (+ FE label)

---

## Nhiệm vụ

Template gợi ý ăn ngoài nhóm: ưu tiên `share_feast` khi `dine_out` và count phù hợp.

## Đọc trước

- `MealTemplateRegistry`, `MealComposer`, `SuggestMode`, `WhatToEatSuggester::resolveSuggestMode`
- `what-to-eat-ruleset.md` templates
- Seed: lẩu `cook_home=false`, `supports_dine_out=true`

## Việc cần làm

1. Template ví dụ `dine_out_feast_1` (1× share_feast) hoặc feast_2.
2. Auto: dine_out + count≥2 + pool đủ → feast; else pick.
3. Không đưa share_feast vào `vn_home_3`.
4. Lang + tests + modal hiện `template_label`.
5. Cập nhật ruleset doc; đánh WTE-S3-02 done.

## DoD

- [ ] dinner dine_out có path feast khi data đủ
- [ ] Tests pass

## Không làm

- Không seed hàng loạt trừ thiếu món demo
