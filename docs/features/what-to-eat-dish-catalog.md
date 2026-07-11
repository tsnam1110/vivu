# What to Eat — Danh mục món ứng viên (Catalog inventory)

> **Vai trò file này:** nguồn sự thật cho **danh sách món** sẽ đưa vào seed / KB —
> tên chuẩn, slug, `dish_role`, **vùng miền**, mức ưu tiên seed, trạng thái xác thực.  
> **Không** thay thế:
>
> | File | Nội dung |
> |---|---|
> | [`what-to-eat.md`](what-to-eat.md) | Sản phẩm, UI, phase |
> | [`what-to-eat-ruleset.md`](what-to-eat-ruleset.md) | Logic đánh giá mâm |
> | [`what-to-eat-seed-and-kb.md`](what-to-eat-seed-and-kb.md) | Chuẩn verified-only, provenance, pipeline import |
>
> **Dataset JSON thực tế:** `database/data/what-to-eat/dishes_v1/` (multi-file).  
> **Seed P0+P1+P2 (2026-07):** **172 món** skeleton multi-file `dishes_v1/` (`1.2.0-p2`) —
> catalog lô 1 đủ; fact III (calo/YHCT) vẫn `null`. **P3 (5 món) giữ `candidate`.**

---

## 1. Nguyên tắc quy mô lớn + độ chính xác tối đa

| # | Nguyên tắc | Hệ quả vận hành |
|---|---|---|
| 1 | **Chính xác > số lượng.** Có thể nhắm catalog lớn, nhưng **không** seed field nhạy (calo, ngũ hành, hàn–nhiệt, lợi/hại y khoa…) nếu chưa có nguồn + review. | Hàng trăm món skeleton OK; fact III có thể còn 0% trong thời gian dài. |
| 2 | **Danh sách ứng viên ≠ fact đã verified.** File này = inventory / backlog có kiểm soát. | Món trong bảng có thể `status: candidate` cho đến khi vào JSON. |
| 3 | **Skeleton (I+II) trước, fact (III) sau.** Tên, slug, bữa, mode, role, vùng — seed khi chắc ngữ cảnh; calo/YHCT null. | Xem seed-KB §3. |
| 4 | **Một slug = một món canonical.** Không nhân bản “Phở tái / nạm / gầu” thành 3 slug trừ khi product thật sự tách. | Biến thể topping = ghi chú, không phình catalog. |
| 5 | **Vùng miền là tag phụ, không thay `dish_role`.** Compose mâm vẫn theo role; filter/browse theo vùng. | `region_tags[]` trên record. |
| 6 | **Mọi thay đổi inventory:** cập nhật file này **trước hoặc cùng** PR dataset JSON. | Agent/dev không “tự thêm 50 món” vào JSON mà không ghi bảng. |
| 7 | **Cấm** copy blog/LLM gán calo–element “cho đủ”. | seed-KB §1, §5. |

**Slogan:** *Catalog rộng bằng skeleton sạch; tri thức sâu bằng provenance — không đảo ngược.*

### 1.1 Mục tiêu quy mô (có trần chất lượng)

| Mức | Unique món | Điều kiện chất lượng | Mục tiêu product |
|---|---:|---|---|
| Floor | ~50–60 | Mọi role core có pool; skeleton I+II review 4 mắt | Compose + pick chạy |
| **MVP seed** | **~110–130** | 100% có name/slug/slots/flags; role + region review | UX gợi ý ổn |
| **Catalog đầy** (lô 1 theo file này) | **~160–180** | Inventory chốt; skeleton vào JSON theo lô role | Đa dạng + vùng miền |
| **Scale lớn** | **200–400+** | Chỉ khi có curator + PR fact-gate + seed-report xanh | SEO / vùng sâu |
| **Trần không-curator** | **~250** | Vượt mức này **cấm** merge lô lớn nếu không có reviewer người | Tránh sụp chất lượng |

> **Quyết định sản phẩm (2026-07):** nhắm **quy mô lớn theo lộ trình** (→ 160+ rồi
> 250–400), nhưng **độ chính xác fact luôn ưu tiên tuyệt đối**. Thiếu data → `null`,
> không bịa.

### 1.2 Trạng thái từng món (`catalog_status`)

| Mã | Ý nghĩa | Được import production seed? |
|---|---|---|
| `candidate` | Có trong inventory; chưa vào JSON | Không |
| `skeleton_ready` | I+II + role + region đã review | Có (fact III null) |
| `partial_facts` | Một phần field III verified | Có |
| `full_facts` | Fact nhạy đủ cho use-case đã chốt | Có |
| `rejected` | Không seed (trùng / mơ hồ / out-of-scope) | Không |

Cột **Status** trong bảng dưới mặc định `candidate` cho đến khi lô JSON được merge.

---

## 2. Phân loại vùng miền (`region_tags`)

### 2.1 Giá trị chuẩn

| Tag | Label VI | Khi gán |
|---|---|---|
| `bac` | Miền Bắc | Gốc hoặc đặc trưng Bắc (phở Hà Nội, bún chả, chả cá…) |
| `trung` | Miền Trung | Huế / Quảng / Đà Nẵng / Nha Trang… (bún bò Huế, mì Quảng, cao lầu…) |
| `nam` | Miền Nam | Sài Gòn / Mekong (hủ tiếu, cơm tấm, bún mắm, canh chua…) |
| `tay_nguyen` | Tây Nguyên | Signature rõ (gà nướng cơm lam…) — dùng sparingly |
| `quoc_gia` | Phổ biến cả nước | Món “quốc dân” / mâm nhà generic (trứng chiên, canh rau, thịt kho…) |
| `hoa_viet` | Ảnh hưởng Hoa–Việt | Hoành thánh, mì vịt tiềm, cơm chiên dương châu… (tag **thêm**, không thay vùng) |

**Quy tắc gán:**

1. Có thể **nhiều tag** (vd phở bò: `bac` + `quoc_gia`).  
2. Signature địa phương rõ → tag vùng **chính** + optional `quoc_gia` nếu đã phổ biến toàn quốc.  
3. Mâm nhà generic → ưu tiên `quoc_gia` only.  
4. **Không** gán vùng “cho đủ” nếu không chắc → chỉ `quoc_gia` hoặc để `[]` đến khi review.  
5. Vùng **không** dùng làm trục tách file seed chính (xem §3).

### 2.2 Cân bằng mục tiêu (catalog đầy ~160–180)

| Vùng (tag chính / nổi bật) | Tỷ lệ gợi ý | Ghi chú |
|---|---:|---|
| `quoc_gia` (+ multi-tag) | ~40–50% | Nền gợi ý mọi user |
| `nam` | ~20–25% | One-bowl + canh chua + lẩu mắm… |
| `bac` | ~15–20% | Phở, bún chả, bún thang… |
| `trung` | ~12–18% | Huế / Quảng / Hội An… |
| `tay_nguyen` | ≤5% | Ít món hàng ngày |
| `hoa_viet` | tag phụ | Không đếm riêng pool |

---

## 3. Cấu trúc file seed (multi-file)

### 3.1 Tách theo `dish_role` (+ file chay + manifest)

Vùng miền = **field** `region_tags[]` trên từng món, **không** tách `dishes_v1_bac.json`
(tránh trùng slug khi món đa vùng).

```text
database/data/what-to-eat/
  README.md
  dishes_v1/
    manifest.json
    dishes_v1_one_bowl.json
    dishes_v1_soup.json
    dishes_v1_main_protein.json
    dishes_v1_side_veg.json
    dishes_v1_side_extra.json
    dishes_v1_starch.json
    dishes_v1_dessert_light.json
    dishes_v1_beverage.json
    dishes_v1_share_feast.json
    dishes_v1_chay.json          # món chay; mỗi record vẫn có dish_role
```

**`manifest.json` (khi implement import multi-file):**

```json
{
  "kb_version": "1.0.0",
  "ruleset_min": "0.1.0-draft",
  "split": "dish_role",
  "region_field": "region_tags",
  "inventory_doc": "docs/features/what-to-eat-dish-catalog.md",
  "files": [
    "dishes_v1_one_bowl.json",
    "dishes_v1_soup.json",
    "dishes_v1_main_protein.json",
    "dishes_v1_side_veg.json",
    "dishes_v1_side_extra.json",
    "dishes_v1_starch.json",
    "dishes_v1_dessert_light.json",
    "dishes_v1_beverage.json",
    "dishes_v1_share_feast.json",
    "dishes_v1_chay.json"
  ]
}
```

### 3.2 Record skeleton tối thiểu (khi vào JSON)

```json
{
  "slug": "pho-bo",
  "name": "Phở bò",
  "emoji": "🍜",
  "summary": "Món nước dùng bò, bánh phở — phổ biến bữa sáng/trưa.",
  "meal_slots": ["breakfast", "lunch", "dinner"],
  "supports_light": false,
  "supports_main": true,
  "supports_dine_out": true,
  "supports_cook_home": true,
  "dish_role": "one_bowl",
  "region_tags": ["bac", "quoc_gia"],
  "calories_kcal": null,
  "serving_grams": null,
  "five_element": null,
  "thermal_nature": null,
  "facts": [
    {
      "field": "dish_role",
      "method": "committee",
      "source_ref": "vivu-catalog-role-review",
      "confidence": "high",
      "reviewed_at": "YYYY-MM-DD"
    }
  ]
}
```

> **Đã implement:** cột DB `dishes.culinary_regions` (JSON) — cùng giá trị tag
> `bac|trung|nam|tay_nguyen|quoc_gia|hoa_viet|ngoai`. Dataset JSON chấp nhận key
> `culinary_regions` **hoặc** alias `region_tags`. Xem enum `CulinaryRegion`.
> **trước** migration.

### 3.3 Cột bảng inventory

| Cột | Ý nghĩa |
|---|---|
| # | STT ổn định trong file (không đổi khi reorder nhẹ) |
| Tên | Tiếng Việt canonical |
| slug | kebab-case, unique toàn catalog |
| role | `DishRole` |
| regions | `region_tags` (pipe-separated trong bảng) |
| slots | B=breakfast, L=lunch, D=dinner |
| L/M | light / main (• = true) |
| D/C | dine_out / cook_home |
| Prio | `P0` floor · `P1` MVP · `P2` full catalog · `P3` scale sau |
| Status | xem §1.2 |

---

## 4. Inventory — One bowl (`one_bowl`)

**File seed:** `dishes_v1_one_bowl.json`  
**Mục tiêu P1:** ≥ 35 · **P2:** ≥ 48

| # | Tên | slug | regions | slots | L/M | D/C | Prio | Status |
|---|---|---|---|---|---|---|---|---|
| 1 | Phở bò | `pho-bo` | bac\|quoc_gia | B,L,D | ·/• | •/• | P0 | skeleton_ready |
| 2 | Phở gà | `pho-ga` | bac\|quoc_gia | B,L,D | ·/• | •/• | P0 | skeleton_ready |
| 3 | Bún bò Huế | `bun-bo-hue` | trung\|quoc_gia | B,L,D | ·/• | •/• | P0 | skeleton_ready |
| 4 | Bún riêu cua | `bun-rieu-cua` | quoc_gia | B,L,D | ·/• | •/• | P0 | skeleton_ready |
| 5 | Bún chả | `bun-cha` | bac | L,D | ·/• | •/• | P0 | skeleton_ready |
| 6 | Bún thịt nướng | `bun-thit-nuong` | nam\|quoc_gia | L,D | ·/• | •/• | P0 | skeleton_ready |
| 7 | Bún mắm | `bun-mam` | nam | L,D | ·/• | •/△ | P1 | skeleton_ready |
| 8 | Bún ốc | `bun-oc` | bac | L,D | ·/• | •/△ | P1 | skeleton_ready |
| 9 | Bún mọc | `bun-moc` | bac | B,L | ·/• | •/• | P1 | skeleton_ready |
| 10 | Bún thang | `bun-thang` | bac | B,L | ·/• | •/△ | P1 | skeleton_ready |
| 11 | Bún bò Nam Bộ | `bun-bo-nam-bo` | nam | L,D | ·/• | •/• | P1 | skeleton_ready |
| 12 | Bún cá | `bun-ca` | bac\|trung | L,D | ·/• | •/△ | P2 | skeleton_ready |
| 13 | Hủ tiếu Nam Vang | `hu-tieu-nam-vang` | nam\|quoc_gia | B,L,D | ·/• | •/△ | P0 | skeleton_ready |
| 14 | Mì Quảng | `mi-quang` | trung | B,L,D | ·/• | •/△ | P0 | skeleton_ready |
| 15 | Cao lầu | `cao-lau` | trung | L,D | ·/• | •/△ | P1 | skeleton_ready |
| 16 | Bánh canh cua | `banh-canh-cua` | trung\|nam | B,L,D | ·/• | •/△ | P1 | skeleton_ready |
| 17 | Bánh canh giò heo | `banh-canh-gio-heo` | trung\|nam | B,L | ·/• | •/△ | P1 | skeleton_ready |
| 18 | Miến gà | `mien-ga` | quoc_gia | B,L,D | •/• | •/• | P1 | skeleton_ready |
| 19 | Miến lươn | `mien-luon` | bac | L,D | ·/• | •/△ | P2 | skeleton_ready |
| 20 | Bò kho | `bo-kho` | quoc_gia | B,L,D | ·/• | •/• | P0 | skeleton_ready |
| 21 | Cháo gà | `chao-ga` | quoc_gia | B,L,D | •/• | •/• | P0 | skeleton_ready |
| 22 | Cháo lòng | `chao-long` | quoc_gia | B,L | ·/• | •/△ | P1 | skeleton_ready |
| 23 | Cháo sườn | `chao-suon` | quoc_gia | B,L | ·/• | •/• | P1 | skeleton_ready |
| 24 | Bánh mì thịt | `banh-mi-thit` | quoc_gia | B,L | •/• | •/• | P0 | skeleton_ready |
| 25 | Bánh mì trứng | `banh-mi-trung` | quoc_gia | B | •/· | •/• | P0 | skeleton_ready |
| 26 | Bánh mì xíu mại | `banh-mi-xiu-mai` | nam\|quoc_gia | B,L | •/• | •/• | P1 | skeleton_ready |
| 27 | Xôi mặn | `xoi-man` | quoc_gia | B | •/• | •/• | P0 | skeleton_ready |
| 28 | Xôi gà | `xoi-ga` | quoc_gia | B,L | ·/• | •/• | P0 | skeleton_ready |
| 29 | Bánh cuốn | `banh-cuon` | bac\|quoc_gia | B,L | •/• | •/• | P0 | skeleton_ready |
| 30 | Bánh ướt | `banh-uot` | bac\|trung | B | •/· | •/△ | P1 | skeleton_ready |
| 31 | Bánh bèo | `banh-beo` | trung | B,L | •/· | •/△ | P1 | skeleton_ready |
| 32 | Bánh bột lọc | `banh-bot-loc` | trung | L | •/· | •/△ | P2 | skeleton_ready |
| 33 | Bánh xèo | `banh-xeo` | nam\|quoc_gia | L,D | ·/• | •/• | P0 | skeleton_ready |
| 34 | Bánh căn | `banh-can` | trung | B,L | •/• | •/△ | P2 | skeleton_ready |
| 35 | Cơm tấm sườn | `com-tam-suon` | nam\|quoc_gia | B,L,D | ·/• | •/• | P0 | skeleton_ready |
| 36 | Cơm tấm bì chả | `com-tam-bi-cha` | nam | B,L,D | ·/• | •/• | P1 | skeleton_ready |
| 37 | Cơm gà Hội An | `com-ga-hoi-an` | trung | L,D | ·/• | •/△ | P1 | skeleton_ready |
| 38 | Cơm gà xối mỡ | `com-ga-xoi-mo` | nam\|quoc_gia | L,D | ·/• | •/• | P1 | skeleton_ready |
| 39 | Cơm rang thập cẩm | `com-rang-thap-cam` | quoc_gia\|hoa_viet | L,D | ·/• | •/• | P1 | skeleton_ready |
| 40 | Bún đậu mắm tôm | `bun-dau-mam-tom` | bac | L,D | ·/• | •/△ | P1 | skeleton_ready |
| 41 | Gỏi cuốn | `goi-cuon` | nam\|quoc_gia | L,D | •/· | •/• | P0 | skeleton_ready |
| 42 | Nem nướng Nha Trang | `nem-nuong-nha-trang` | trung | L,D | ·/• | •/△ | P2 | skeleton_ready |
| 43 | Bột chiên | `bot-chien` | nam | B,L | •/• | •/△ | P1 | skeleton_ready |
| 44 | Bò né | `bo-ne` | nam\|hoa_viet | B,L | ·/• | •/△ | P1 | skeleton_ready |
| 45 | Chả cá Lã Vọng | `cha-ca-la-vong` | bac | L,D | ·/• | •/△ | P2 | skeleton_ready |
| 46 | Mì xào | `mi-xao` | quoc_gia\|hoa_viet | L,D | ·/• | •/• | P1 | skeleton_ready |
| 47 | Phở trộn | `pho-tron` | quoc_gia | L,D | ·/• | •/• | P2 | skeleton_ready |
| 48 | Cơm hến | `com-hen` | trung | L | •/• | •/△ | P2 | skeleton_ready |
| 49 | Bánh giò | `banh-gio` | bac\|quoc_gia | B | •/• | •/• | P1 | skeleton_ready |
| 50 | Bánh bao | `banh-bao` | quoc_gia\|hoa_viet | B | •/• | •/• | P1 | skeleton_ready |
| 51 | Bánh hỏi thịt nướng | `banh-hoi-thit-nuong` | trung\|nam | L,D | ·/• | •/• | P2 | skeleton_ready |
| 52 | Bánh khoái | `banh-khoai` | trung | L,D | ·/• | •/△ | P2 | skeleton_ready |
| 53 | Bánh tráng trộn | `banh-trang-tron` | nam | L | •/· | •/△ | P2 | skeleton_ready |
| 54 | Phở chua | `pho-chua` | bac | L | ·/• | •/△ | P3 | candidate |
| 55 | Hủ tiếu Mỹ Tho | `hu-tieu-my-tho` | nam | B,L | ·/• | •/△ | P3 | candidate |

> Ký hiệu D/C: `•` = true chắc; `△` = cook_home hạn chế / tuỳ biến (vẫn có thể true nhẹ khi skeleton).

---

## 5. Inventory — Soup (`soup`)

**File:** `dishes_v1_soup.json` · **P0 ≥ 6 · P1 ≥ 12 · P2 ≥ 14**

| # | Tên | slug | regions | slots | L/M | D/C | Prio | Status |
|---|---|---|---|---|---|---|---|---|
| 56 | Canh chua cá | `canh-chua-ca` | nam\|quoc_gia | L,D | •/· | •/• | P0 | skeleton_ready |
| 57 | Canh chua tôm | `canh-chua-tom` | nam | L,D | •/· | •/• | P1 | skeleton_ready |
| 58 | Canh chua cá lóc | `canh-chua-ca-loc` | nam | L,D | •/· | •/• | P2 | skeleton_ready |
| 59 | Canh khổ qua nhồi thịt | `canh-kho-qua-nhoi-thit` | nam\|quoc_gia | L,D | •/· | •/• | P0 | skeleton_ready |
| 60 | Canh bí đỏ | `canh-bi-do` | quoc_gia | L,D | •/· | •/• | P0 | skeleton_ready |
| 61 | Canh rau ngót thịt bằm | `canh-rau-ngot-thit-bam` | quoc_gia | L,D | •/· | •/• | P0 | skeleton_ready |
| 62 | Canh cải thịt bằm | `canh-cai-thit-bam` | quoc_gia | L,D | •/· | •/• | P0 | skeleton_ready |
| 63 | Canh mướp mọc | `canh-muop-moc` | quoc_gia | L,D | •/· | •/• | P1 | skeleton_ready |
| 64 | Canh gà lá giang | `canh-ga-la-giang` | nam\|trung | L,D | •/· | •/• | P1 | skeleton_ready |
| 65 | Canh cua rau đay | `canh-cua-rau-day` | bac | L,D | •/· | •/• | P1 | skeleton_ready |
| 66 | Canh bầu tôm | `canh-bau-tom` | quoc_gia | L,D | •/· | •/• | P1 | skeleton_ready |
| 67 | Canh xương rau củ | `canh-xuong-rau-cu` | quoc_gia | L,D | •/· | •/• | P0 | skeleton_ready |
| 68 | Súp cua | `sup-cua` | quoc_gia\|hoa_viet | B,L,D | •/· | •/• | P1 | skeleton_ready |
| 69 | Canh khoai mỡ | `canh-khoai-mo` | nam | L,D | •/· | •/• | P2 | skeleton_ready |
| 70 | Canh mồng tơi nấu tôm | `canh-mong-toi-nau-tom` | quoc_gia | L,D | •/· | •/• | P2 | skeleton_ready |
| 71 | Canh bí đao | `canh-bi-dao` | quoc_gia | L,D | •/· | •/• | P2 | skeleton_ready |

---

## 6. Inventory — Main protein (`main_protein`)

**File:** `dishes_v1_main_protein.json` · **P0 ≥ 8 · P1 ≥ 16 · P2 ≥ 20**

| # | Tên | slug | regions | slots | L/M | D/C | Prio | Status |
|---|---|---|---|---|---|---|---|---|
| 72 | Thịt kho tàu | `thit-kho-tau` | nam\|quoc_gia | L,D | ·/• | •/• | P0 | skeleton_ready |
| 73 | Thịt kho trứng | `thit-kho-trung` | quoc_gia | L,D | ·/• | •/• | P0 | skeleton_ready |
| 74 | Cá kho tộ | `ca-kho-to` | nam\|quoc_gia | L,D | ·/• | •/• | P0 | skeleton_ready |
| 75 | Cá kho riềng | `ca-kho-rieng` | bac | L,D | ·/• | •/• | P1 | skeleton_ready |
| 76 | Sườn ram mặn | `suon-ram-man` | quoc_gia | L,D | ·/• | •/• | P0 | skeleton_ready |
| 77 | Gà kho gừng | `ga-kho-gung` | quoc_gia | L,D | ·/• | •/• | P0 | skeleton_ready |
| 78 | Gà rang muối | `ga-rang-muoi` | quoc_gia | L,D | ·/• | •/• | P1 | skeleton_ready |
| 79 | Gà luộc | `ga-luoc` | quoc_gia | L,D | ·/• | •/• | P0 | skeleton_ready |
| 80 | Vịt om sấu | `vit-om-sau` | bac | L,D | ·/• | •/• | P2 | skeleton_ready |
| 81 | Tôm rang me | `tom-rang-me` | nam | L,D | ·/• | •/• | P1 | skeleton_ready |
| 82 | Mực xào chua ngọt | `muc-xao-chua-ngot` | quoc_gia | L,D | ·/• | •/• | P1 | skeleton_ready |
| 83 | Bò xào lúc lắc | `bo-xao-luc-lac` | quoc_gia | L,D | ·/• | •/• | P1 | skeleton_ready |
| 84 | Chả lá lốt | `cha-la-lot` | quoc_gia | L,D | ·/• | •/• | P1 | skeleton_ready |
| 85 | Chả trứng hấp | `cha-trung-hap` | quoc_gia | L,D | ·/• | •/• | P1 | skeleton_ready |
| 86 | Đậu phụ sốt cà | `dau-phu-sot-ca` | quoc_gia | L,D | •/• | •/• | P0 | skeleton_ready |
| 87 | Trứng chiên | `trung-chien` | quoc_gia | L,D | •/• | •/• | P0 | skeleton_ready |
| 88 | Cá chiên | `ca-chien` | quoc_gia | L,D | ·/• | •/• | P1 | skeleton_ready |
| 89 | Thịt xào nấm | `thit-xao-nam` | quoc_gia | L,D | ·/• | •/• | P1 | skeleton_ready |
| 90 | Heo quay | `heo-quay` | quoc_gia\|hoa_viet | L,D | ·/• | •/△ | P2 | skeleton_ready |
| 91 | Tôm chiên bột | `tom-chien-bot` | quoc_gia | L,D | ·/• | •/• | P2 | skeleton_ready |
| 92 | Sườn chua ngọt | `suon-chua-ngot` | quoc_gia | L,D | ·/• | •/• | P2 | skeleton_ready |

---

## 7. Inventory — Side veg (`side_veg`)

**File:** `dishes_v1_side_veg.json` · **P0 ≥ 6 · P1 ≥ 12 · P2 ≥ 16**

| # | Tên | slug | regions | slots | L/M | D/C | Prio | Status |
|---|---|---|---|---|---|---|---|---|
| 93 | Rau muống xào tỏi | `rau-muong-xao-toi` | quoc_gia | L,D | •/· | •/• | P0 | skeleton_ready |
| 94 | Cải xào tỏi | `cai-xao-toi` | quoc_gia | L,D | •/· | •/• | P0 | skeleton_ready |
| 95 | Su su xào tỏi | `su-su-xao-toi` | quoc_gia | L,D | •/· | •/• | P0 | skeleton_ready |
| 96 | Đậu que xào | `dau-que-xao` | quoc_gia | L,D | •/· | •/• | P1 | skeleton_ready |
| 97 | Bí đao xào | `bi-dao-xao` | quoc_gia | L,D | •/· | •/• | P1 | skeleton_ready |
| 98 | Bầu xào trứng | `bau-xao-trung` | quoc_gia | L,D | •/· | •/• | P1 | skeleton_ready |
| 99 | Mướp xào trứng | `muop-xao-trung` | quoc_gia | L,D | •/· | •/• | P1 | skeleton_ready |
| 100 | Rau luộc chấm kho quẹt | `rau-luoc-kho-quet` | nam | L,D | •/· | •/• | P0 | skeleton_ready |
| 101 | Rau mồng tơi xào | `rau-mong-toi-xao` | quoc_gia | L,D | •/· | •/• | P1 | skeleton_ready |
| 102 | Cải thìa xào | `cai-thia-xao` | quoc_gia | L,D | •/· | •/• | P1 | skeleton_ready |
| 103 | Gỏi đu đủ | `goi-du-du` | nam\|quoc_gia | L,D | •/· | •/• | P1 | skeleton_ready |
| 104 | Gỏi gà bắp cải | `goi-ga-bap-cai` | quoc_gia | L,D | •/· | •/• | P1 | skeleton_ready |
| 105 | Nộm hoa chuối | `nom-hoa-chuoi` | bac\|quoc_gia | L,D | •/· | •/• | P2 | skeleton_ready |
| 106 | Dưa món | `dua-mon` | quoc_gia | L,D | •/· | •/• | P0 | skeleton_ready |
| 107 | Rau lang xào | `rau-lang-xao` | quoc_gia | L,D | •/· | •/• | P2 | skeleton_ready |
| 108 | Đậu bắp xào | `dau-bap-xao` | quoc_gia | L,D | •/· | •/• | P2 | skeleton_ready |
| 109 | Nấm xào tỏi | `nam-xao-toi` | quoc_gia | L,D | •/· | •/• | P1 | skeleton_ready |
| 110 | Salad dưa leo cà chua | `salad-dua-leo-ca-chua` | quoc_gia | L,D | •/· | •/• | P1 | skeleton_ready |

---

## 8. Inventory — Side extra (`side_extra`)

**File:** `dishes_v1_side_extra.json` · **P1 ≥ 6 · P2 ≥ 10**

| # | Tên | slug | regions | slots | L/M | D/C | Prio | Status |
|---|---|---|---|---|---|---|---|---|
| 111 | Đậu phụ chiên | `dau-phu-chien` | quoc_gia | L,D | •/· | •/• | P0 | skeleton_ready |
| 112 | Nem rán | `nem-ran` | bac | L,D | •/• | •/• | P1 | skeleton_ready |
| 113 | Chả giò | `cha-gio` | nam | L,D | •/• | •/• | P1 | skeleton_ready |
| 114 | Trứng kho | `trung-kho` | quoc_gia | L,D | •/• | •/• | P1 | skeleton_ready |
| 115 | Nem chua | `nem-chua` | trung | L,D | •/· | •/△ | P2 | skeleton_ready |
| 116 | Tép rang | `tep-rang` | quoc_gia | L,D | •/· | •/• | P2 | skeleton_ready |
| 117 | Ruốc | `ruoc` | quoc_gia | B,L,D | •/· | •/• | P1 | skeleton_ready |
| 118 | Trứng ốp la | `trung-op-la` | quoc_gia | B,L | •/• | •/• | P0 | skeleton_ready |
| 119 | Chả lụa (miếng) | `cha-lua` | quoc_gia | B,L,D | •/· | •/△ | P2 | skeleton_ready |
| 120 | Tóp mỡ | `top-mo` | nam\|quoc_gia | L,D | •/· | •/△ | P3 | candidate |

---

## 9. Inventory — Starch (`starch`)

**File:** `dishes_v1_starch.json` · **P1 ≥ 3 · P2 ≥ 4**

| # | Tên | slug | regions | slots | L/M | D/C | Prio | Status |
|---|---|---|---|---|---|---|---|---|
| 121 | Cơm trắng | `com-trang` | quoc_gia | L,D | ·/• | •/• | P0 | skeleton_ready |
| 122 | Cơm gạo lứt | `com-gao-lut` | quoc_gia | L,D | ·/• | •/• | P1 | skeleton_ready |
| 123 | Xôi trắng | `xoi-trang` | quoc_gia | B,L | ·/• | •/• | P1 | skeleton_ready |
| 124 | Bún tươi | `bun-tuoi` | quoc_gia | L,D | ·/• | •/• | P2 | skeleton_ready |

> Template có thể dùng staple ngầm (ruleset B04) — vẫn seed `com-trang` để UI/compose
> tường minh khi cần.

---

## 10. Inventory — Dessert light (`dessert_light`)

**File:** `dishes_v1_dessert_light.json` · **P0 ≥ 4 · P1 ≥ 8 · P2 ≥ 14**

| # | Tên | slug | regions | slots | L/M | D/C | Prio | Status |
|---|---|---|---|---|---|---|---|---|
| 125 | Chè đậu xanh | `che-dau-xanh` | quoc_gia | B,L,D | •/· | •/• | P0 | skeleton_ready |
| 126 | Chè ba màu | `che-ba-mau` | nam | L,D | •/· | •/• | P1 | skeleton_ready |
| 127 | Chè Thái | `che-thai` | nam | L,D | •/· | •/△ | P1 | skeleton_ready |
| 128 | Chè chuối | `che-chuoi` | quoc_gia | L,D | •/· | •/• | P1 | skeleton_ready |
| 129 | Chè bắp | `che-bap` | nam | L,D | •/· | •/• | P2 | skeleton_ready |
| 130 | Bánh flan | `banh-flan` | quoc_gia | L,D | •/· | •/• | P1 | skeleton_ready |
| 131 | Sữa chua | `sua-chua` | quoc_gia | B,L,D | •/· | •/• | P0 | skeleton_ready |
| 132 | Chuối nếp nướng | `chuoi-nep-nuong` | nam | L,D | •/· | •/• | P1 | skeleton_ready |
| 133 | Khoai lang nướng | `khoai-lang-nuong` | quoc_gia | B,L,D | •/· | •/• | P0 | skeleton_ready |
| 134 | Bánh cam | `banh-cam` | quoc_gia | L,D | •/· | •/△ | P2 | skeleton_ready |
| 135 | Xôi đậu xanh | `xoi-dau-xanh` | quoc_gia | B | •/· | •/• | P1 | skeleton_ready |
| 136 | Trái cây dĩa | `trai-cay-dia` | quoc_gia | L,D | •/· | •/• | P0 | skeleton_ready |
| 137 | Sương sáo | `suong-sao` | quoc_gia | L,D | •/· | •/• | P2 | skeleton_ready |
| 138 | Bánh chuối nướng | `banh-chuoi-nuong` | quoc_gia | L,D | •/· | •/• | P2 | skeleton_ready |

---

## 11. Inventory — Beverage (`beverage`)

**File:** `dishes_v1_beverage.json` · **P0 ≥ 4 · P1 ≥ 8 · P2 ≥ 10**

| # | Tên | slug | regions | slots | L/M | D/C | Prio | Status |
|---|---|---|---|---|---|---|---|---|
| 139 | Cà phê sữa đá | `ca-phe-sua-da` | nam\|quoc_gia | B,L,D | •/· | •/• | P0 | skeleton_ready |
| 140 | Cà phê đen đá | `ca-phe-den-da` | quoc_gia | B,L,D | •/· | •/• | P0 | skeleton_ready |
| 141 | Trà đá | `tra-da` | quoc_gia | L,D | •/· | •/• | P0 | skeleton_ready |
| 142 | Nước mía | `nuoc-mia` | quoc_gia | L,D | •/· | •/△ | P1 | skeleton_ready |
| 143 | Sinh tố bơ | `sinh-to-bo` | nam | L,D | •/· | •/• | P1 | skeleton_ready |
| 144 | Nước chanh | `nuoc-chanh` | quoc_gia | L,D | •/· | •/• | P1 | skeleton_ready |
| 145 | Trà tắc | `tra-tac` | nam | L,D | •/· | •/△ | P1 | skeleton_ready |
| 146 | Sữa đậu nành | `sua-dau-nanh` | quoc_gia | B,L | •/· | •/• | P0 | skeleton_ready |
| 147 | Sinh tố xoài | `sinh-to-xoai` | nam\|quoc_gia | L,D | •/· | •/• | P2 | skeleton_ready |
| 148 | Nước dừa | `nuoc-dua` | nam\|quoc_gia | L,D | •/· | •/△ | P2 | skeleton_ready |

---

## 12. Inventory — Share feast (`share_feast`)

**File:** `dishes_v1_share_feast.json` · **P0 ≥ 3 · P1 ≥ 6 · P2 ≥ 12**  
**Lưu ý ruleset:** không nhét vào `vn_home_3` (B02).

| # | Tên | slug | regions | slots | L/M | D/C | Prio | Status |
|---|---|---|---|---|---|---|---|---|
| 149 | Lẩu thái | `lau-thai` | quoc_gia | L,D | ·/• | •/△ | P0 | skeleton_ready |
| 150 | Lẩu bò | `lau-bo` | quoc_gia | L,D | ·/• | •/△ | P0 | skeleton_ready |
| 151 | Lẩu hải sản | `lau-hai-san` | quoc_gia | L,D | ·/• | •/△ | P1 | skeleton_ready |
| 152 | Lẩu mắm | `lau-mam` | nam | L,D | ·/• | •/△ | P1 | skeleton_ready |
| 153 | Lẩu gà lá é | `lau-ga-la-e` | tay_nguyen\|trung | L,D | ·/• | •/△ | P2 | skeleton_ready |
| 154 | Lẩu nấm | `lau-nam` | quoc_gia | L,D | ·/• | •/• | P1 | skeleton_ready |
| 155 | Lẩu riêu cua bắp bò | `lau-rieu-cua-bap-bo` | quoc_gia | L,D | ·/• | •/△ | P2 | skeleton_ready |
| 156 | Nướng BBQ set | `nuong-bbq-set` | quoc_gia | L,D | ·/• | •/△ | P0 | skeleton_ready |
| 157 | Bò nướng lá lốt | `bo-nuong-la-lot` | nam\|quoc_gia | L,D | ·/• | •/• | P1 | skeleton_ready |
| 158 | Hải sản nướng mọi | `hai-san-nuong-moi` | trung\|nam | L,D | ·/• | •/△ | P2 | skeleton_ready |
| 159 | Gà nướng cơm lam | `ga-nuong-com-lam` | tay_nguyen\|trung | L,D | ·/• | •/△ | P2 | skeleton_ready |
| 160 | Ốc xào | `oc-xao` | quoc_gia | L,D | •/• | •/△ | P1 | skeleton_ready |
| 161 | Lẩu cá kèo | `lau-ca-keo` | nam | L,D | ·/• | •/△ | P3 | candidate |
| 162 | Dê nướng | `de-nuong` | quoc_gia | L,D | ·/• | •/△ | P3 | candidate |

---

## 13. Inventory — Chay (`dishes_v1_chay.json`)

Món chay **có `dish_role` riêng** + `flavor_tags` / diet flag `vegetarian` khi implement.  
**Không** duplicate slug với bản mặn.

| # | Tên | slug | role | regions | slots | L/M | D/C | Prio | Status |
|---|---|---|---|---|---|---|---|---|---|
| 163 | Phở chay | `pho-chay` | one_bowl | quoc_gia | B,L,D | ·/• | •/• | P1 | skeleton_ready |
| 164 | Bún chay | `bun-chay` | one_bowl | quoc_gia | L,D | ·/• | •/• | P1 | skeleton_ready |
| 165 | Hủ tiếu chay | `hu-tieu-chay` | one_bowl | nam | B,L | ·/• | •/• | P2 | skeleton_ready |
| 166 | Cơm chay thập cẩm | `com-chay-thap-cam` | one_bowl | quoc_gia | L,D | ·/• | •/• | P1 | skeleton_ready |
| 167 | Cơm tấm chay | `com-tam-chay` | one_bowl | nam | L,D | ·/• | •/• | P2 | skeleton_ready |
| 168 | Xôi chay | `xoi-chay` | one_bowl | quoc_gia | B | •/• | •/• | P1 | skeleton_ready |
| 169 | Miến xào chay | `mien-xao-chay` | one_bowl | quoc_gia | L,D | ·/• | •/• | P2 | skeleton_ready |
| 170 | Đậu hũ kho nấm | `dau-hu-kho-nam` | main_protein | quoc_gia | L,D | ·/• | •/• | P1 | skeleton_ready |
| 171 | Nấm kho tiêu | `nam-kho-tieu` | main_protein | quoc_gia | L,D | ·/• | •/• | P1 | skeleton_ready |
| 172 | Canh rau củ chay | `canh-rau-cu-chay` | soup | quoc_gia | L,D | •/· | •/• | P1 | skeleton_ready |
| 173 | Canh bí đỏ chay | `canh-bi-do-chay` | soup | quoc_gia | L,D | •/· | •/• | P1 | skeleton_ready |
| 174 | Rau củ luộc chấm tương | `rau-cu-luoc-cham-tuong` | side_veg | quoc_gia | L,D | •/· | •/• | P1 | skeleton_ready |
| 175 | Gỏi cuốn chay | `goi-cuon-chay` | side_extra | quoc_gia | L,D | •/· | •/• | P1 | skeleton_ready |
| 176 | Nem chay rán | `nem-chay-ran` | side_extra | quoc_gia | L,D | •/· | •/• | P2 | skeleton_ready |
| 177 | Chè thập cẩm chay | `che-thap-cam-chay` | dessert_light | quoc_gia | L,D | •/· | •/• | P2 | skeleton_ready |

---

## 14. Tổng hợp số lượng

| Nhóm (file) | # range | Count | P0+P1 (ước) | P2 full |
|---|---|---:|---:|---:|
| one_bowl | 1–55 | 55 | ~40 | 55 |
| soup | 56–71 | 16 | ~12 | 16 |
| main_protein | 72–92 | 21 | ~16 | 21 |
| side_veg | 93–110 | 18 | ~14 | 18 |
| side_extra | 111–120 | 10 | ~7 | 10 |
| starch | 121–124 | 4 | ~3 | 4 |
| dessert_light | 125–138 | 14 | ~10 | 14 |
| beverage | 139–148 | 10 | ~8 | 10 |
| share_feast | 149–162 | 14 | ~8 | 14 |
| chay | 163–177 | 15 | ~12 | 15 |
| **Tổng unique** | | **177** | **~130** | **~170** |

P3 (scale sau, không liệt kê hết trong file này): biến thể topping, chè theo loại,
món vùng hẹp — chỉ thêm khi có curator + dòng inventory mới.

### 14.1 Theo vùng (ước tag xuất hiện, multi-tag được đếm nhiều lần)

| Tag | ~món có tag | Ghi chú |
|---|---:|---|
| `quoc_gia` | ~110+ | Nền |
| `nam` | ~45+ | |
| `bac` | ~30+ | |
| `trung` | ~28+ | |
| `tay_nguyen` | ~2–4 | |
| `hoa_viet` | ~8 | tag phụ |

---

## 15. Lộ trình seed từ inventory

| Phase | Scope | ~món | Fact III | DoD |
|---|---|---:|---|---|
| **Inv-0** | File này (inventory) | 177 listed | — | ✅ |
| **Seed-P0** | Prio P0 | **51** | null | ✅ trong `1.2.0-p2` |
| **Seed-P1** | + P1 (+ chay) | **80** | null | ✅ |
| **Seed-P2** | + P2 curated | **41** → **172 total** | null | ✅ `1.2.0-p2` catalog lô 1 |
| **P3** | scale later | **5** inventory only | — | `candidate` (không seed) |
| **Fact-A** | FCT + recipe_sum + standard bowl v1 | **~116** / 182 | medium–high | ✅ `2.0.0-fact-a` |
| **Ops-A** | cooking_method + protein_source | **182** / 182 | committee | ✅ |
| **Fact-C/D** | thermal / element | 0 | verified YHCT | Chưa — **không bịa** |

**Thứ tự file khi seed P0:**  
`soup` + `main_protein` + `side_veg` + `one_bowl` (breakfast) + `starch` + vài `beverage`/`dessert_light` + 3 `share_feast`.

---

## 16. Quy trình thêm / sửa món trong inventory

1. Thêm dòng vào đúng bảng §4–§13 (slug unique toàn file).  
2. Gán `role`, `regions`, `Prio`, `Status=candidate`.  
3. PR doc-only được phép (chưa JSON).  
4. Khi seed: copy sang shard JSON skeleton; đổi Status → `skeleton_ready` sau merge.  
5. Fact III: chỉ khi có provenance — cập nhật Status `partial_facts` / `full_facts`.  
6. Reject: `Status=rejected` + ghi chú lý do (không xoá dòng — giữ audit).

**Agent AI:**

- Được: đề xuất dòng inventory, soạn skeleton I+II theo bảng.  
- **Không được:** tự verified calo/element/thermal; không seed JSON vượt inventory đã chốt.

---

## 17. Ngoài phạm vi inventory lô 1

- Biến thể phở theo topping (tái / nạm / gầu / gân) — 1 slug `pho-bo` đủ.  
- Đồ uống có cồn (trừ khi product chốt sau).  
- Món dân tộc / vùng cực hẹp không “hàng ngày”.  
- Fusion / K-food / Nhật trừ khi đã thành phổ biến VN và có dòng review.  
- Claim chữa bệnh trong `summary` / benefits.

---

## 18. Changelog

| Ngày | Thay đổi |
|---|---|
| 2026-07-11 | Ban hành inventory ~177 món; split multi-file theo role; `region_tags`; P0–P3; ưu tiên chính xác tuyệt đối khi scale lớn |
| 2026-07-11 | **Seed-P0:** 51 món skeleton multi-file (`dishes_v1/`), status P0 → `skeleton_ready`; fact III null |
| 2026-07-11 | **Seed-P1:** +80 món → **131** total (`1.1.0-p1`); thêm `dishes_v1_chay.json`; P1 → `skeleton_ready` |
| 2026-07-11 | **Seed-P2 + quality:** +41 → **172** (`1.2.0-p2`); QA multi-agent; P3 giữ candidate; fact III vẫn 0; sửa copy/role/cook_home |
| 2026-07-11 | **Fact-A:** 9 món calo USDA FCT overlay (`calories_fact_a.json`); phở/bún vẫn null |
| 2026-07-11 | **Fact-A 1.1:** + recipe_sum → ~17 món; UI `calorie_source`; `implicit_rice_kcal=195` (cũ) |
| 2026-07-12 | `implicit_rice_kcal=206` khớp `com-trang` FCT VN yield (`2.3.0-fact-a`) |
| 2026-07-11 | **Hoàn thiện plan (multi-agent):** Fact-A 27; Ops-A 23; S02 hygiene; UI explanations/prefs; catalog 182 |

---

## 19. Liên kết

- Seed / provenance: [`what-to-eat-seed-and-kb.md`](what-to-eat-seed-and-kb.md)  
- Ruleset / role template: [`what-to-eat-ruleset.md`](what-to-eat-ruleset.md)  
- Schema DB: [`../04-database-schema.md`](../04-database-schema.md)  
- Dataset path: `database/data/what-to-eat/`
