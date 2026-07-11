# WTE-S2-02 — Audit provenance món đã có calo

**Plan:** [`../../features/what-to-eat-next-plan.md`](../../features/what-to-eat-next-plan.md)  
**Sóng:** S2 · **Owner:** Data  
**Song song:** có thể chạy song song S2-01 nếu không đụng cùng slug

---

## Nhiệm vụ

Rà món đã có calo: siết provenance (FDC id / FCT VN cố định thay search URL yếu).

## Đọc trước

- `what-to-eat-fact-a-calories.md`
- `what-to-eat-seed-and-kb.md` §5
- Dataset + `facts_meta` sau seed

## Việc cần làm

1. Export list slug + method + source_ref + confidence.
2. Phân loại strong / weak.
3. Harden tối thiểu 20 weak (không đổi số nếu không có cơ sở).
4. Flag outlier kcal/100g (trừ trà/cà phê ~0).
5. Viết báo cáo ngắn trong `what-to-eat-fact-a-calories.md` hoặc file audit con.
6. Đánh WTE-S2-02 done.

## DoD

- [ ] Bảng audit + lô weak đã sửa
- [ ] Re-import sạch

## Không làm

- Không tăng coverage bằng số bịa
