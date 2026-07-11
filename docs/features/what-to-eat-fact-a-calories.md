# What to Eat — Fact-A: Calo verified

> **Vai trò:** quy tắc + danh sách **calo/khẩu phần đã gắn nguồn** (overlay), tách khỏi
> skeleton catalog.  
> **Liên quan:** [`what-to-eat-seed-and-kb.md`](what-to-eat-seed-and-kb.md),
> [`what-to-eat-dish-catalog.md`](what-to-eat-dish-catalog.md),
> [`what-to-eat-fct-vn.md`](what-to-eat-fct-vn.md),
> `database/data/what-to-eat/facts/calories_fact_a.json`.

---

## 1. Nguyên tắc Fact-A

| # | Rule |
|---|---|
| 1 | Chỉ món **map được** với FCT (USDA và/hoặc FCT VN 2007) hoặc `recipe_sum` có gram khóa. |
| 2 | **Không** gán calo cho phở/bún/cơm tấm từ “trung bình blog”. |
| 3 | `method`: `fct_table` hoặc `recipe_sum` (công thức định lượng / yield / vivu-standard-v1). |
| 4 | Luôn cặp `calories_kcal` + `serving_grams` + provenance (+ breakdown khi recipe_sum). |
| 5 | `confidence: high` chỉ khi dòng FCT chốt (FDC ID hoặc food_code VN + audit); multi-ingredient / yield → **medium**. |
| 6 | Overlay **không** tạo món mới — chỉ update slug đã có trong skeleton. |
| 7 | **Null có chủ đích** khi biến thiên quá lớn (lẩu share, buffet…). |

---

## 2. File & pipeline

```text
database/data/what-to-eat/facts/calories_fact_a.json   ← kb 2.3.0-fact-a
  → DishCatalogImporter::applyCalorieFactsOverlay()
  → DishSeeder / importDefault()
```

```bash
php database/data/what-to-eat/build_fact_overlays.php      # base USDA max-build (nếu rebuild từ đầu)
php database/data/what-to-eat/upgrade_fdc_high.php         # khóa FDC (lớp USDA)
php database/data/what-to-eat/build_fct_vn_phase_b.php     # FCT VN + yield + bowls → ghi đè pilot
php artisan db:seed --class=DishSeeder
php artisan what-to-eat:seed-report
```

> Phase B **ghi đè** một phần dòng đã FDC-lock bằng FCT VN (`fct_source: vn_2007`).  
> Số `fdc_id` / `high` **sau seed** thấp hơn lúc chỉ chạy `upgrade_fdc_high`.

---

## 3. Lô hiện tại (`2.4.0-fact-a`) — **~130 / 189** có calo

| Metric | Count | Ghi chú |
|---|---:|---|
| Có `calories_kcal` | **~130** | overlay by_slug; seed catalog ~189 (S5) |
| Null calo | còn lại | chủ đích (share feast, biến thiên cao…) |
| `confidence: high` | **9** | sau phase B |
| Còn `fdc_id` trên fact calo | **~13** | nhiều dòng VN ghi đè mất FDC |
| `fct_source: vn_2007` | tăng sau S2-01 | bowls + one_bowl mới |
| `method: recipe_sum` | **~113** | +14 one_bowl S2-01 |
| `method: fct_table` | **~17** | |

### 3.0 Phân tầng

| Nhóm | Ý |
|---|---|
| FCT đơn (USDA hoặc VN) | Trà, cà phê, sữa chua, khoai, pizza… |
| Yield gạo VN | `com-trang` / `com-gao-lut` / `xoi-trang` — medium |
| Home `recipe_sum` VN/USDA | Canh, xào, trứng, đậu… |
| **vivu-standard-v1** + FCT VN | Phở, bún, cơm tấm, xôi mặn… — medium + limitations |
| **Null** | Lẩu share, buffet, một số chè/smoothie |

### 3.1 `confidence: high` (đủ 9 slug)

| slug | kcal | g | nguồn |
|---|---:|---:|---|
| `sua-chua` | 92 | 150 | FCT VN 10004 |
| `khoai-lang-nuong` | 180 | 200 | USDA FDC |
| `nuoc-dua` | 48 | 250 | USDA FDC |
| `sua-dau-nanh` | 108 | 200 | USDA FDC |
| `ca-phe-den-da` | 2 | 240 | USDA FDC (đen, không đường) |
| `tra-da` | 2 | 250 | USDA FDC |
| `bun-tuoi` | 110 | 100 | FCT VN 1020 |
| `bap-cai-luoc` | 29 | 100 | FCT VN 4010 |
| `pizza` | 270 | 100 | USDA FDC |

### 3.2 Mẫu quan trọng (sau phase B)

| slug | kcal | g | conf | method | src |
|---|---:|---:|---|---|---|
| `com-trang` | **206** | 150 | medium | recipe_sum | VN yield 2.5 từ 1004 |
| `com-gao-lut` | 207 | 150 | medium | recipe_sum | VN yield 2.5 |
| `xoi-trang` | 235 | 150 | medium | recipe_sum | VN yield 2.2 |
| `trung-op-la` | 110 | 53 | medium | recipe_sum | VN |
| `trung-chien` | 211 | 105 | medium | recipe_sum | VN |
| `ga-luoc` | 299 | 150 | medium | fct_table | VN 7013 |
| `rau-muong-xao-toi` | 140 | 150 | medium | recipe_sum | VN |
| `dau-phu-sot-ca` | 251 | 250 | medium | recipe_sum | VN |
| `canh-cai-thit-bam` | 85 | 350 | medium | recipe_sum | VN (heo nạc) |
| `pho-bo` | 412 | 500 | medium | recipe_sum | standard + VN |
| `pho-ga` | 470 | 500 | medium | recipe_sum | standard + VN |
| `bun-cha` | 485 | 450 | medium | recipe_sum | standard + VN |
| `com-tam-suon` | 667 | 450 | medium | recipe_sum | standard + VN |
| `banh-mi-thit` | 332 | 180 | medium | recipe_sum | standard + VN |

Chi tiết đủ 116 dòng: `facts/calories_fact_a.json` → `by_slug`.  
UI: `calorie_source` (method, portion_note, limitations).

### 3.3 Config cơm ngầm (template B04)

```php
// config/what_to_eat.php
'implicit_rice_kcal' => 206,   // khớp com-trang 150 g
'implicit_rice_grams' => 150,
```

| | Cũ (USDA) | Hiện tại (FCT VN yield) |
|---|---:|---:|
| `com-trang` / implicit rice | 195 high | **206** medium |

### 3.4 Lịch sử (tham chiếu — không dùng làm số hiện tại)

- Lô 1.2: ~27 món USDA home.  
- FDC-lock đỉnh: ~29 `fdc_id`, ~14 pure `high` — **trước** phase B ghi đè.  
- `com-trang` từng = **195** (FDC 168878, 130×1.5).

---

## 4. Audit provenance (S2-02 — 2026-07-12)

| Nhóm | Đánh giá | Hành động |
|---|---|---|
| `method` ∈ {`fct_table`,`recipe_sum`} | strong | Giữ |
| `source_ref` dạng `vivu-standard-v1+vn:{slug}` + breakdown FCT | strong (medium conf) | Giữ |
| `source_ref` FDC id / food_code VN cố định | strong | Giữ high/medium |
| Proxy ingredient (miến→bún, cao lầu→bánh phở, ốc→heo) | weak-medium | Ghi `limitations`; **không** nâng high |
| Share-feast / lẩu | null | Không bịa |

**S2-01 thêm 14 one_bowl** (medium, recipe_sum, FCT VN components):  
`mi-quang`, `bo-kho`, `banh-xeo`, `bun-moc`, `bun-thang`, `mien-ga`, `banh-bao`,  
`banh-gio`, `com-ga-hoi-an`, `bun-dau-mam-tom`, `cao-lau`, `banh-canh-cua`,  
`bun-oc`, `com-ga-xoi-mo`.

Outlier check: trà/cà phê ~0–2 kcal/250ml — expected; bánh bao ~2.3 kcal/g — still medium street estimate.

## 5. Việc tiếp theo

1. Đo yield cơm/xôi thực tế → siết high cho gạo chín.  
2. Giữ / mở rộng `fdc_id` trên dòng **không** bị pilot VN thay.  
3. Thay proxy miến/ốc khi có food_code FCT.  
4. Inventory Status → `partial_facts` cho slug đã overlay.

---

## 6. Changelog

| Ngày | Thay đổi |
|---|---|
| 2026-07-11 | Ban hành Fact-A overlay 9 món USDA FCT; pipeline importer |
| 2026-07-11 | `1.1.0-fact-a` … `2.0.0-fact-a` max-build ~116 + standard bowls |
| 2026-07-11 | `2.1.0`: FDC lock ~29 / pure high ~14 (peak trước VN overwrite) |
| 2026-07-11 | `2.2.0`–`2.3.0`: FCT VN pilot + yield + bowls; **high còn 9**, vn=35 |
| 2026-07-12 | Sync doc + `implicit_rice_kcal=206`; bảng mẫu theo data 2.3.0 |
| 2026-07-12 | `2.4.0-fact-a`: +14 one_bowl S2-01; audit provenance S2-02 |
