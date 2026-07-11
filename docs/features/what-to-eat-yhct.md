# YHCT — Hàn–nhiệt & ngũ hành (món)

> **Data:** `database/data/what-to-eat/facts/yhct_fact_a.json`  
> **Importer:** `DishCatalogImporter` overlay fields `thermal_nature`, `five_element`  
> **Method hợp lệ:** `tcm_text` \| `expert_tcm` \| `committee` (có biên bản)

## Nguyên tắc

1. **Không** gán từ keyword (cay → hỏa).  
2. **Không** claim chữa bệnh. UI disclaimer bắt buộc.  
3. Món phức tạp (phở, lẩu…) → **queue chuyên gia**, không bootstrap ồ ạt.  
4. Bootstrap chỉ món **đơn / gần đơn** với tính vị thực liệu phổ biến, `confidence: medium`, chờ `expert_tcm`.

## Trạng thái

| Hạng mục | Status |
|---|---|
| Pipeline overlay | ✅ |
| Bootstrap ~10 món đơn | ✅ medium (`tcm_text`) |
| Expert review production | ⏸ `expert_queue` trong JSON |
| Món phức tạp | null |

## Việc chuyên gia

1. Review bootstrap 10 món → `expert_tcm` + high.  
2. Lần lượt `expert_queue` (phở, bún bò Huế, cơm tấm…).  
3. Có thể tách thermal (tứ tính) và element nếu không đồng thuận sách.
