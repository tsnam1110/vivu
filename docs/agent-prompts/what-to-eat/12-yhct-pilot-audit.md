# WTE-S4-01 — Audit pilot YHCT (10 món)

**Plan:** [`../../features/what-to-eat-next-plan.md`](../../features/what-to-eat-next-plan.md)  
**Sóng:** S4 · **Owner:** Data+Docs

---

## Nhiệm vụ

Rà pilot `five_element` + `thermal_nature`; siết expert-gate; **không** scale thêm nếu nguồn yếu.

## Đọc bắt buộc

- `docs/features/what-to-eat-yhct.md`
- `docs/features/what-to-eat-ruleset.md` lớp C/D
- `docs/features/what-to-eat-seed-and-kb.md` (method tcm_*)

## Việc cần làm

1. List món có element/thermal + method + source + confidence.
2. Method phải ∈ {tcm_text, expert_tcm, committee}; có rationale.
3. Yếu → set null + ghi doc (không giữ fact ảo).
4. Cập nhật `what-to-eat-yhct.md` trạng thái pilot.
5. Disclaimer: không phải y khoa hiện đại.
6. Đánh WTE-S4-01 done.

## DoD

- [ ] Pilot sạch hoặc thu hẹp “expert-ok”
- [ ] Doc khớp DB

## Không làm

- Không gán YHCT hàng loạt / LLM
- Không mệnh năm sinh
