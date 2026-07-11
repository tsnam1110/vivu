# What to Eat — Chuẩn seed, tri thức món (KB) & đóng góp

> **Vai trò file này:** quy định **dữ liệu nào được đưa vào seed/catalog hệ thống**,
> cách bổ sung số lượng lớn món, và chỗ nào **bắt buộc để trống** để user/admin đóng góp
> sau.  
> **Liên quan:** [`what-to-eat.md`](what-to-eat.md),
> [`what-to-eat-ruleset.md`](what-to-eat-ruleset.md),
> [`what-to-eat-dish-catalog.md`](what-to-eat-dish-catalog.md) (**danh sách món / vùng miền / prio**),
> [`../04-database-schema.md`](../04-database-schema.md),
> [`../07-coding-standards.md`](../07-coding-standards.md),
> [`../08-agent-playbook.md`](../08-agent-playbook.md).

---

## 1. Nguyên tắc bất di bất dịch

| # | Nguyên tắc |
|---|---|
| 1 | **Seed chỉ chứa fact đã xác thực** (verified). Không “ước lượng cho đủ”, không
  copy mạng không nguồn, không gán ngũ hành/hàn–nhiệt/calo “cho vui”. |
| 2 | **Chưa xác thực → `null` / bỏ qua field** (hoặc không ghi key). UI + engine coi là
  *chưa có dữ liệu* — mời đóng góp (contribution) hoặc admin bổ sung sau. |
| 3 | **Không suy diễn hàng loạt bằng heuristic** trong seeder production (vd mọi canh =
  `water`, mọi món cay = `hot`). |
| 4 | **Tách “định danh món” và “fact dinh dưỡng/dưỡng sinh”.** Có thể seed tên + slug +
  cờ bữa/mode khi chắc, trong khi calo/element/thermal vẫn null. |
| 5 | **Mỗi fact verified gắn provenance** (nguồn + ngày + người/review). Không provenance
  = không được đánh verified. |
| 6 | **UGC không tự động thành truth hệ thống** — chỉ sau duyệt admin (`approved` +
  `is_canonical` khi phù hợp). |
| 7 | **Cấm claim chữa bệnh** trong seed và contribution (xem `what-to-eat.md` §11). |

**Slogan vận hành:** *Thiếu data trong sạch còn hơn data đủ mà bịa.*

---

## 2. Phân tầng độ tin cậy (trust)

| Trust | Ký hiệu | Ý nghĩa | Được dùng trong |
|---|---|---|---|
| **Verified** | `verified` | Có nguồn + (nên) review người; đủ tin cho hard/soft rule phụ thuộc field | Seed system, cache dish canonical |
| **Provisional** | `provisional` | Nội bộ staging / dev only — **cấm** `php artisan db:seed` production-like | Local experiment (`SEED_ALLOW_UNVERIFIED`) |
| **Unverified / empty** | `null` | Chưa biết | Contribution queue, UI “Chưa có thông tin” |
| **Community** | `community` | Contribution đã approve nhưng chưa nâng canonical | Hiển thị tab cộng đồng; **không** ghi đè seed system nếu conflict trừ admin chọn |

Seed **production path** chỉ chấp nhận **Verified** hoặc **null**.  
`provisional` nếu lọt production = **bug quy trình**.

---

## 3. Nhóm field trên `dishes` — seed được gì / để trống gì

> Cột có thể mở rộng theo migration sau; nguyên tắc trust giữ nguyên.

### 3.1 Nhóm I — Định danh & hiển thị (thường seed được nếu “món có thật”)

| Field | Seed khi | Ghi chú |
|---|---|---|
| `name`, `slug` | Tên món phổ biến, chính tả chuẩn | `slug` ổn định; đổi slug = cẩn thận log/URL |
| `emoji` | Tuỳ chọn | Không ảnh hưởng rule |
| `summary` | Chỉ câu mô tả **trung tính** (không lợi/hại y khoa) | VD: “Phở bò — món nước dùng phổ biến VN” |
| `status` | `published` chỉ khi món đủ **tối thiểu vận hành** (§4) | |
| `source` | `system` | |

### 3.2 Nhóm II — Trục gợi ý vận hành (seed khi chắc chắn ngữ cảnh)

| Field | Seed khi chắc | Nếu không chắc |
|---|---|---|
| `meal_slots` | Món **thực tế** hay dùng bữa đó | Chỉ gán slot chắc; không gán cả 3 “cho rộng” nếu sai |
| `supports_light` / `supports_main` | Vai trò no rõ | `false` mặc định an toàn hơn gán bừa `true` cả hai |
| `supports_dine_out` / `supports_cook_home` | Chắc chắn | |
| `culinary_regions` | Vùng miền (`region_tags`: bac/trung/nam/tay_nguyen/quoc_gia/hoa_viet/ngoai) | `null`/`[]` nếu chưa chắc; multi-label OK; xem dish-catalog §2 |
| `dish_role` *(khi có cột)* | Có định nghĩa trong ruleset + review | **`null`** — món đó **không** vào template compose cần role |
| `search_keywords` | Từ khoá match Experience (trung tính) | null ok |
| `cook_minutes` | Chỉ khi có công thức verified / nguồn thời gian rõ | null |
| `ingredients` / `steps` | Công thức có nguồn hoặc nội bộ chuẩn hoá | **null** → user contribute `recipe` |

### 3.3 Nhóm III — Fact “nhạy” (mặc định null trừ khi verified)

| Field | Chỉ seed khi | Nguồn chấp nhận (ví dụ) |
|---|---|---|
| `calories_kcal` + `serving_grams` | Cùng cặp khẩu phần; có phương pháp đo/ước **có nguồn** | Bảng thành phần TP + định lượng nguyên liệu; nhãn SP; lab; tài liệu viện/dinh dưỡng được phép dùng; **không** “đoán 500 kcal” |
| `protein_g` / `carb_g` / `fat_g` *(nếu có)* | Cùng khẩu phần với kcal | Như trên |
| `five_element` | Có rationale + nguồn YHCT/văn bản được duyệt | Giáo trình/chuyên gia review — **không** best-effort dev |
| `thermal_nature` *(khi có)* | Bảng tứ tính + nguồn | Như trên |
| `protein_source`, `cooking_method`, `flavor_tags` | Quan sát công thức chuẩn rõ (vd “hấp”, “chiên”) | null nếu món đa biến thể |
| `benefits` / `harms` / `advice` | Text **thận trọng**, không chữa bệnh, có cơ sở hoặc để null | Ưu tiên **null** + contribution; seed chỉ generic an toàn (“ăn kèm rau”) |
| `notes` | Ghi chú kỹ thuật nội bộ ngắn | Không nhồi claim sức khoẻ |

### 3.4 Ma trận nhanh: “Không biết thì để trống”

```
Có nguồn + review? ──yes──► Ghi field + provenance
         │
         no
         ▼
      null / bỏ field
         │
         ▼
  User/admin contribution sau (pending → approve → optional canonical)
```

---

## 4. Điều kiện tối thiểu để món `published` trong seed

Món **được** đưa vào seeder `published` khi **tối thiểu**:

1. `name`, `slug` hợp lệ, không trùng.  
2. Ít nhất một `meal_slots[]`.  
3. Ít nhất một trong `supports_light` / `supports_main` = true.  
4. Ít nhất một trong `supports_dine_out` / `supports_cook_home` = true.  
5. **Không** bắt buộc: calo, element, thermal, recipe, benefits/harms.

Món chỉ có tên + cờ bữa vẫn có thể:

- hiện trong mode **pick** (filter flags),  
- **không** tham gia rule Lớp A/C/D,  
- **không** vào compose template nếu thiếu `dish_role`.

Muốn vào **`vn_home_3`**: cần thêm `dish_role` ∈ {soup, main_protein, side_veg} **verified**.

---

## 5. Provenance — bắt buộc với fact verified

### 5.1 Hình thức (khuyến nghị)

Cho đến khi có cột DB riêng, provenance seed lưu:

- File sidecar JSON/YAML cạnh seeder, **hoặc**
- Mảng trong seeder: `'provenance' => [...]` **không** map thẳng DB nếu chưa có cột,
  dùng để review PR;  
- Khi có migration: bảng `dish_fact_sources` hoặc JSON `facts_meta` trên `dishes`.

**Bản ghi provenance tối thiểu:**

```yaml
field: calories_kcal
value: 450
serving_grams: 500
method: "recipe_sum"   # recipe_sum | label | fct_table | lab | expert_panel
source_title: "..."
source_ref: "URL hoặc ISBN hoặc mã nội bộ"
source_date: "2024-01-01"
reviewed_by: "tên hoặc role"
reviewed_at: "2026-07-11"
confidence: high       # high | medium  (low = không seed)
notes: "Khẩu phần 1 tô chuẩn X"
```

`confidence: low` → **không** seed field đó.

### 5.2 Method hợp lệ (calo / macro)

| method | Ý nghĩa | Ghi chú |
|---|---|---|
| `fct_table` | Thành phần nguyên liệu × khối lượng từ Food Composition Table | Ghi rõ bảng (quốc gia/năm) |
| `recipe_sum` | Cộng từ FCT theo công thức định lượng | Công thức phải frozen version |
| `label` | Nhãn sản phẩm đóng gói | Ghi brand/SKU nếu có |
| `lab` | Phân tích lab | Kèm báo cáo |
| `expert_panel` | Hội đồng nội bộ ký | Biên bản ngày |

**Không hợp lệ:** `guess`, `similar_dish`, `chatgpt`, `average_internet`.

### 5.3 Method hợp lệ (ngũ hành / hàn–nhiệt)

| method | Ý nghĩa |
|---|---|
| `tcm_text` | Trích giáo trình / dược thư / tài liệu YHCT được phép |
| `expert_tcm` | Chuyên gia YHCT review từng món hoặc nhóm |
| `committee` | Hội đồng nội bộ + biên bản |

**Không hợp lệ:** dev gán theo cảm tính, vote không moderation, map tự động từ keyword cay→hoả.

---

## 6. Quy trình bổ sung **số lượng lớn** món vào seed

> **Inventory bắt buộc:** mọi món mới phải có dòng trong
> [`what-to-eat-dish-catalog.md`](what-to-eat-dish-catalog.md) (slug, role, region_tags,
> prio) **trước hoặc cùng** PR dataset JSON. Catalog quy mô lớn được phép; fact III
> vẫn verified-only.

### 6.1 Không làm

- Copy list 200 món từ blog + bịa calo/element.  
- Generate hàng loạt bằng LLM **rồi commit như verified**.  
- Sửa tay `DishSeeder` 500 dòng không file nguồn.  
- `updateOrCreate` ghi đè fact verified bằng giá trị rỗng/guess khi re-seed.

### 6.2 Làm (pipeline)

```
[1] Thu thập danh sách ứng viên (CSV/JSON)
        ↓
[2] Chuẩn hoá tên + slug + flags bữa/mode (review 4 mắt hoặc checklist)
        ↓
[3] Với TỪNG field Nhóm III: chỉ điền nếu có provenance
        ↓
[4] File dataset versioned: database/data/what-to-eat/dishes_v{N}.json
        ↓
[5] PR: dataset + provenance + (optional) diff coverage role
        ↓
[6] Review: “fact gate” — reject field không nguồn
        ↓
[7] Seeder đọc JSON → updateOrCreate theo policy merge (§7)
        ↓
[8] Chạy coverage: template pool / % null facts (báo cáo, không fail vì null)
```

### 6.3 Schema file dataset (đề xuất)

Đường dẫn gợi ý: `database/data/what-to-eat/dishes_v1.json`

```json
{
  "kb_version": "1.0.0",
  "ruleset_min": "0.1.0-draft",
  "dishes": [
    {
      "slug": "pho-bo",
      "name": "Phở bò",
      "emoji": "🍜",
      "summary": "Món nước dùng bò, bánh phở — phổ biến bữa sáng/trưa.",
      "meal_slots": ["breakfast", "lunch"],
      "supports_light": true,
      "supports_main": true,
      "supports_dine_out": true,
      "supports_cook_home": true,
      "dish_role": null,
      "calories_kcal": null,
      "serving_grams": null,
      "five_element": null,
      "thermal_nature": null,
      "ingredients": null,
      "steps": null,
      "benefits": null,
      "harms": null,
      "advice": null,
      "search_keywords": "pho bo",
      "facts": []
    }
  ]
}
```

Khi một field verified:

```json
"calories_kcal": 450,
"serving_grams": 500,
"facts": [
  {
    "field": "calories_kcal",
    "method": "recipe_sum",
    "source_title": "…",
    "source_ref": "…",
    "reviewed_by": "…",
    "reviewed_at": "2026-07-11",
    "confidence": "high"
  }
]
```

### 6.4 Vai trò CSV import (admin / ops)

- Template CSV cột = field §3.  
- Cột `calories_kcal` trống = null.  
- Cột `provenance_calories` bắt buộc **nếu** calories không trống (validate import).  
- Import fail cả dòng nếu có fact mà thiếu provenance (strict mode).

### 6.5 Batch “chỉ skeleton”

Cho phép import 100+ món **chỉ Nhóm I+II** trong một PR:

- Mục tiêu: phủ tên món + filter bữa.  
- Compose / calo / YHCT: phase sau từng lô verified.  
- Báo cáo: `published=N`, `with_role=R`, `with_kcal=K`, `with_element=E`.

---

## 7. Policy merge khi re-seed

`updateOrCreate` theo `slug`:

| Tình huống | Hành vi |
|---|---|
| Seed có giá trị verified mới | Cập nhật field + provenance |
| Seed để `null`, DB đang có giá trị **canonical từ contribution/admin** | **Không ghi đè** bằng null (giữ truth đã duyệt) |
| Seed verified, DB có community non-canonical | Seed thắng cho cache system fields; community giữ tab |
| Local provisional | Chỉ khi `SEED_ALLOW_UNVERIFIED=true` |

Tránh: mỗi lần `migrate:fresh --seed` **xoá** tri thức user đã được admin duyệt — thiết kế
merge phải tôn trọng canonical.

*Đã implement: `App\Services\DishCatalogImporter` — null seed không ghi đè field sensitive đang có trên DB.*

---

## 8. Đóng góp người dùng (UGC) — lấp chỗ trống đúng chỗ

### 8.1 Khi field null trên dish

UI chi tiết món:

- Hiển thị: **“Chưa có thông tin xác thực”** (theo loại: calo, công thức, ngũ hành…).  
- CTA: **Đóng góp** → `DishContribution` đúng `type`.  
- Không hiện số bịa, không hiện “— kcal” như 0.

### 8.2 Mapping contribution → field

| Contribution `type` | Có thể nâng canonical lên dish |
|---|---|
| `recipe` | `ingredients`, `steps`, `cook_minutes` |
| `calories` | `calories_kcal`, `serving_grams` (+ macro nếu payload đủ) |
| `five_element` | `five_element` |
| `harm` / `benefit` / `advice` / `note` | text tương ứng (thận trọng review) |
| *(sau)* `thermal` | `thermal_nature` |

### 8.3 Điều kiện admin approve → canonical

1. Nội dung không claim chữa bệnh.  
2. Calo: có khẩu phần (gram hoặc mô tả quy đổi được); số liệu hợp lý biên.  
3. Element/thermal: có rationale; ưu tiên reject nếu chỉ 1 từ không nguồn.  
4. Recipe: steps/ingredients không rỗng, không spam.  
5. Ghi `reviewed_by`, `reviewed_at`.

Sau approve canonical: đồng bộ cache dish (đã có hướng Phase B).

### 8.4 Seed vs UGC — ưu tiên

| Nguồn | Ưu tiên |
|---|---|
| Seed system verified | Cao — baseline |
| Admin edit verified | Cao nhất — sửa sai seed |
| UGC canonical | Thay/ bổ sung field null hoặc cập nhật có chủ đích |
| UGC non-canonical | Chỉ hiển thị cộng đồng |

---

## 9. Trách nhiệm & review

| Vai trò | Việc |
|---|---|
| **Dev / Agent** | Skeleton I+II; **không** tự verified III; PR dataset; test seeder |
| **Data curator** | Provenance, FCT, biên soạn lô |
| **Reviewer PR** | Fact gate: reject guess |
| **Admin app** | Duyệt contribution |
| **Chuyên gia dinh dưỡng** (khi có) | Review Lớp A |
| **Chuyên gia YHCT** (khi bật C/D) | Review element/thermal |

**Agent AI:**  
- Được: soạn skeleton, tool import, doc, test null-safety.  
- **Không được:** tuyên bố field III là verified nếu user không cung cấp nguồn.  
- Nếu user yêu cầu “seed full calo/element”: **từ chối đoán** — để null + nêu cần nguồn.

### 9.1 SOP admin curator (S5-02)

1. **Seed JSON** = verified-only + provenance; Admin hand-edit = **curator trusted** (bạn chịu trách nhiệm).  
2. Chỉ điền calo khi có nguồn (FCT / recipe_sum / label); **luôn cặp** `serving_grams`.  
3. Xóa calo → để `null` (không ghi 0 giả). UI admin cảnh báo.  
4. YHCT / thermal: chỉ khi có biên bản `expert_tcm` / `tcm_text` — mặc định null.  
5. Duyệt contribution: kiểm claim chữa bệnh → reject; approve → set `is_canonical` khi đủ.  
6. Banner policy trên `admin` DishesPage.

---

## 10. Kiểm tra chất lượng seed (DoD mỗi lô)

Checklist PR thêm món:

- [ ] Mọi slug unique, name tiếng Việt chuẩn.  
- [ ] Flags bữa/mode không “bật hết cho chắc” nếu sai thực tế.  
- [ ] Mọi field Nhóm III non-null đều có provenance `confidence` ≥ medium.  
- [ ] Không có benefits/harms kiểu chữa bệnh.  
- [ ] Báo cáo đếm: total / null_kcal / null_element / null_role.  
- [ ] (Khi có compose) coverage template: không fail vì null — chỉ báo pool.  
- [ ] Seeder idempotent (`updateOrCreate` + merge policy).  
- [ ] Không phụ thuộc `SEED_ALLOW_UNVERIFIED` để pass CI.

Lệnh gợi ý (khi implement):

```bash
php artisan what-to-eat:seed-report
# output: counts + list field III missing provenance (error)
```

---

## 11. Trạng thái seed hiện tại

> **Số live:** `php artisan what-to-eat:seed-report` (bảng dưới = chốt 2026-07-12 sau S5 + Fact-A 2.4.0).

| Hạng mục | Trạng thái |
|---|---|
| Món best-effort cũ trong seeder PHP | **Đã gỡ** |
| Dataset multi-file | `dishes_v1/manifest.json` (`1.2.2-s5`) + shards (+ `chay`) |
| Seed P0+P1+P2+S5 | **189 món** skeleton; role + region 100% |
| P3 | 5 món inventory `candidate` — chưa seed |
| Import | `importDefault()` = skeleton + calo + ops (+ recipe/YHCT overlays) |
| Báo cáo | `php artisan what-to-eat:seed-report` |
| Fact-A calo | **130** / 189 — `facts/calories_fact_a.json` (`2.4.0-fact-a`) |
| Ops-A | **189** / 189 cooking_method + protein_source |
| Recipe text | **155** / 189 |
| Fact YHCT | **10** pilot medium (`yhct_fact_a.json`) — không scale; UI opt-in |
| Ruleset min (manifest) | `0.3.0` |
| Plan hoàn thiện fact dài | [`what-to-eat-fact-completion-plan.md`](what-to-eat-fact-completion-plan.md) |
| Sprint S0–S5 | [`what-to-eat-next-plan.md`](what-to-eat-next-plan.md) |

`SEED_ALLOW_UNVERIFIED=true` chỉ cho local thử nghiệm — **không** dùng production.

---

## 12. Ví dụ đúng / sai

### Đúng

```php
// Skeleton verified vận hành; fact dinh dưỡng chưa có nguồn
[
  'slug' => 'canh-bi-do',
  'name' => 'Canh bí đỏ',
  'meal_slots' => ['lunch', 'dinner'],
  'supports_light' => true,
  'supports_main' => false,
  'supports_dine_out' => true,
  'supports_cook_home' => true,
  'dish_role' => 'soup', // chỉ khi đã chốt định nghĩa role + review
  'calories_kcal' => null,
  'serving_grams' => null,
  'five_element' => null,
  'ingredients' => null,
  'benefits' => null,
  'harms' => null,
]
```

### Sai

```php
// Đoán mò để rule/UI “đẹp”
'calories_kcal' => 200,          // không nguồn
'five_element' => 'water',       // vì là canh
'benefits' => 'Chữa mất ngủ',    // claim y khoa
'harms' => 'Nên tránh nếu mệnh Hoả', // bịa
```

---

## 13. Liên kết ruleset

- Rule **không** chạy trên field null → xem data-gate trong
  [`what-to-eat-ruleset.md`](what-to-eat-ruleset.md) §4.  
- Bổ sung món không đồng nghĩa bật hard rule mới — tránh partial vĩnh viễn.  
- Muốn hard `A02` plate kcal: cần tỷ lệ món trong pool có kcal verified đủ cao.

---

## 14. Việc Agent khi được bảo “thêm món vào seed”

1. Đọc file này + ruleset.  
2. Hỏi / yêu cầu **nguồn** cho mọi field Nhóm III; không có → null.  
3. Thêm vào dataset versioned (hoặc seeder skeleton-only).  
4. Không “làm đầy” element/calo/thermal bằng model AI.  
5. Cập nhật báo cáo đếm null; cập nhật doc nếu đổi quy ước.  
6. Test: món mới published; contribution vẫn submit được trên field null.

---

## 15. Changelog doc

| Ngày | Thay đổi |
|---|---|
| 2026-07-11 | Ban hành chuẩn verified-only seed, null-for-unknown, pipeline lô lớn, provenance, nợ DishSeeder cũ |
