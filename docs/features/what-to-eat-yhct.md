# YHCT — Hàn–nhiệt & ngũ hành (món)

> **Data:** `database/data/what-to-eat/facts/yhct_fact_a.json`  
> **Importer:** `DishCatalogImporter` overlay fields `thermal_nature`, `five_element`  
> **Method hợp lệ:** `tcm_text` \| `expert_tcm` \| `committee` (có biên bản)

## Nguyên tắc

1. **Không** gán từ keyword (cay → hỏa).  
2. **Không** claim chữa bệnh. UI disclaimer bắt buộc.  
3. Món phức tạp (phở, lẩu…) → **queue chuyên gia**, không bootstrap ồ ạt.  
4. Bootstrap chỉ món **đơn / gần đơn** với tính vị thực liệu phổ biến, `confidence: medium`, chờ `expert_tcm`.

## Trạng thái (audit S4-01 — 2026-07-12)

| Hạng mục | Status |
|---|---|
| Pipeline overlay | ✅ |
| Bootstrap **10** món đơn | ✅ giữ — `tcm_text` + `confidence: medium` + rationale |
| Method ∈ allowlist | ✅ `tcm_text` only (chưa `expert_tcm`) |
| Expert review production | ⏸ `expert_queue` trong JSON |
| Món phức tạp (phở, lẩu…) | **null** (không scale) |
| UI opt-in soft | ✅ pref `balance_elements` mặc định **off** (S4-02) |

### Pilot expert-ok (giữ)

| slug | thermal | element | method | conf |
|---|---|---|---|---|
| `com-trang` | neutral | earth | tcm_text | medium |
| `com-gao-lut` | neutral | earth | tcm_text | medium |
| `tra-da` | cool | wood | tcm_text | medium |
| `ca-phe-den-da` | warm | fire | tcm_text | medium |
| `khoai-lang-nuong` | neutral | earth | tcm_text | medium |
| `ga-luoc` | warm | earth | tcm_text | medium |
| `tom-rang-me` | neutral | water | tcm_text | medium |
| `dau-phu-sot-ca` | cool | metal | tcm_text | medium |
| `nuoc-dua` | cool | water | tcm_text | medium |
| `sua-chua` | cool | water | tcm_text | medium |

> **Không** thu hẹp null trong lô này: mỗi dòng có source_ref + notes/limitations;  
> claim UI = tham khảo dưỡng sinh, **không** y khoa / chữa bệnh.

## Việc chuyên gia

1. Review bootstrap 10 món → `expert_tcm` + high.  
2. Lần lượt `expert_queue` (phở, bún bò Huế, cơm tấm…).  
3. Có thể tách thermal (tứ tính) và element nếu không đồng thuận sách.
