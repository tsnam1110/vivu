# What to Eat — Bộ quy tắc (Ruleset) & đánh giá mâm

> **Vai trò file này:** nguồn sự thật cho **logic gợi ý / đánh giá bữa**, tách khỏi
> UI popup và khỏi bảng dữ liệu món.  
> **Liên quan:** [`what-to-eat.md`](what-to-eat.md) (sản phẩm),
> [`what-to-eat-seed-and-kb.md`](what-to-eat-seed-and-kb.md) (chuẩn seed & fact),
> [`what-to-eat-dish-catalog.md`](what-to-eat-dish-catalog.md) (inventory món & vùng miền),
> [`../04-database-schema.md`](../04-database-schema.md),
> [`../10-security-privacy.md`](../10-security-privacy.md).

---

## 1. Nguyên tắc vàng

1. **Rule có id, có lớp, có hard/soft, có thông điệp tiếng Việt** — không “magic score”
   không giải thích được.
2. **Chỉ chấm / ràng field đã có giá trị xác thực.** Field `null` = *chưa biết* →
   **không** bịa, **không** dùng rule phụ thuộc field đó (trừ rule “thiếu data”).
3. **Random chỉ được phép sau hard-pass** — chọn giữa các mâm/món *đã hợp lệ*, không
   thay thế tri thức.
4. **Tách lớp tri thức** — một mâm có thể đạt Lớp B (cấu trúc) nhưng “không chấm” Lớp C
   vì thiếu hàn–nhiệt. Không gộp thành một câu “chuẩn y khoa”.
5. **Versioning:** mọi thay đổi rule = tăng `ruleset_version` (semver) và ghi changelog
   ngắn trong file này.
6. **Không claim chữa bệnh / chẩn đoán.** Output = tham khảo + giải thích rule.

**`ruleset_version` hiện tại:** `0.3.0`  
*(Code: `MealComposer` + `MealTemplateRegistry` + `WhatToEatSuggester` mode pick/compose/auto; soft diversity protein/fry; dine_out feast; YHCT soft opt-in.)*

---

## 2. Các lớp quy tắc (Layers)

| Layer | Mã | Tên | Bản chất | Claim UI cho phép |
|---|---|---|---|---|
| **A** | `nutrition` | Dinh dưỡng thực chứng (tham khảo) | EER, kcal bữa, nhóm TP, (sau) macro | “Ước lượng dinh dưỡng (tham khảo)” |
| **B** | `structure` | Cấu trúc mâm / suất | Vai trò món, template bữa Việt / one-bowl | “Cấu trúc bữa (canh–mặn–rau…)" |
| **C** | `thermal` | Tính hàn–nhiệt (dưỡng sinh / YHCT) | cold→hot | “Tham khảo dưỡng sinh/YHCT — không thay thế YHCT lâm sàng” |
| **D** | `five_element` | Ngũ hành | wood/fire/earth/metal/water + sinh–khắc | “Tham khảo văn hoá / ngũ hành — không phải tư vấn y khoa” |
| **E** | `sensory` | Cảm quan & chế biến | chiên, cay, trùng đạm… | “Cân bằng vị / cách chế biến” |
| **S** | `safety` | An toàn & sở thích user | dị ứng, blacklist, chay… | “Theo lựa chọn / hạn chế bạn đã khai” |

- **Lớp A + B + E + S:** nền ship được khi có data.  
- **Lớp C + D:** chỉ chạy khi **đủ fact đã xác thực** trên món; bật/tắt được (pref user
  hoặc config).  
- **Không** gọi C/D là “chuẩn y khoa hiện đại”.

---

## 3. Đơn vị đánh giá

| Đơn vị | Khi nào | Output |
|---|---|---|
| **Dish** (món đơn) | Mode `pick` (gợi ý option) | Score + reasons cấp món |
| **Plate** (mâm / thực đơn) | Mode `compose` | Pass/fail hard + score soft + reasons cấp mâm + theo slot |

**Quy ước:** `count` trong mode `compose` = số **slot trong mâm**, không phải “N option
ngẫu nhiên”.

---

## 4. Điều kiện áp dụng rule (data gate)

Mỗi rule khai báo `requires_fields` (trên dish hoặc trên plate).

| Tình huống | Hành vi engine |
|---|---|
| Field required của rule = `null` | Rule **SKIP** (không fail, không pass giả) |
| ≥1 rule hard của template bị skip vì thiếu data | Plate `data_incomplete: true`; UI: “Chưa đủ dữ liệu xác thực để chấm [lớp X]” |
| Hard rule có đủ data và vi phạm | Plate **reject** hoặc hạ template |
| Soft rule thiếu data | Bỏ qua soft đó |

**Cấm:** gán mặc định “đoán mò” (vd mọi canh = water, mọi cay = hot) chỉ để rule chạy.

---

## 5. Catalog rule (v0.1 — đặc tả)

Format mỗi rule:

```text
ID | Layer | Severity | Requires | Mô tả | Message VI (user-facing)
```

Severity: `hard` = loại / không hợp lệ · `soft` = trừ điểm · `info` = chỉ giải thích.

### 5.1 Lớp S — An toàn & preference

| ID | Sev | Requires | Mô tả |
|---|---|---|---|
| `S01_blacklist_dish` | hard | pref.disliked | Loại món user chặn |
| `S02_diet_vegetarian` | hard | pref.diet + dish flags/keywords **đã xác thực** | Chỉ món đánh dấu phù hợp chay (field/diet flag verified) |
| `S03_allergen` *(sau)* | hard | user allergens + dish allergens verified | Chưa implement data → không bật |

### 5.2 Lớp B — Cấu trúc mâm

| ID | Sev | Requires | Mô tả |
|---|---|---|---|
| `B01_template_roles` | hard | `dish_role` trên mọi slot | Đủ role theo `MealTemplate` (vd soup+main_protein+side_veg) |
| `B02_no_feast_in_home_plate` | hard | `dish_role` | Không nhét `share_feast` vào mâm nhà 1 người |
| `B03_no_double_one_bowl` | hard | `dish_role` | Mâm component không gồm `one_bowl` (trừ template standalone) |
| `B04_implicit_staple` | info | template | Ghi nhận cơm/bún ngầm + kcal **chỉ khi** có hằng số verified trong template config |
| `B05_slot_meal_flags` | hard | meal_slots, supports_* | Món thuộc bữa/mode đã chọn |

**Template tối thiểu (đặc tả):**

| Template id | Điều kiện | Roles (thứ tự) |
|---|---|---|
| `vn_home_3` | lunch/dinner · main · cook · count=3 | `soup`, `main_protein`, `side_veg` (+ staple implicit optional) |
| `vn_home_2_soup` | count=2 | `main_protein`, `soup` |
| `vn_home_2_veg` | count=2 | `main_protein`, `side_veg` |
| `standalone_1` | count=1 | `one_bowl` **hoặc** 1 `main_protein` nếu không có one_bowl |
| `light_1` | light | `dessert_light` \| `side_veg` \| `beverage` (ưu tiên field có data) |
| `dine_out_1` | dine_out | `one_bowl` / món supports_dine_out |

> Chỉ activate template khi **pool role** có món `published` với `dish_role` **verified**.
> Thiếu pool → partial / fallback template — **không** gán role tạm.

### 5.3 Lớp A — Dinh dưỡng (tham khảo)

| ID | Sev | Requires | Mô tả |
|---|---|---|---|
| `A01_meal_budget` | soft | user target + dish.calories_kcal | Gần `meal_budget` (món đơn) |
| `A02_plate_kcal_band` | soft→hard* | tổng kcal món **có** calories | Tổng mâm ∈ \[0.75, 1.15\] × budget (+ staple nếu config verified) |
| `A03_skip_null_kcal` | info | — | Món thiếu kcal không tham gia chấm A; plate có thể `nutrition_partial` |
| `A04_macro_band` *(sau)* | soft | protein_g, carb_g, fat_g verified | Chưa bật đến khi đủ data |
| `A05_single_deep_fry` | soft | `cooking_method` verified | Tránh ≥2 món `fry` trong một mâm |

\* `A02` hard chỉ khi **mọi** slot bắt buộc đều có `calories_kcal` verified; nếu thiếu → soft/info.

**Nguồn công thức EER / % bữa:** ghi trong seed-KB / code comment + changelog; không
“bịa %” theo cảm tính từng PR.

### 5.4 Lớp E — Cảm quan / đa dạng

| ID | Sev | Requires | Mô tả |
|---|---|---|---|
| `E01_protein_diversity` | soft | `protein_source` | Tránh 2 main cùng source nặng (meat+meat) nếu có lựa chọn khác |
| `E02_flavor_extreme` | soft | `flavor_tags` | Tránh 2× spicy high |
| `E03_recent_dish` | soft | logs | Trừ điểm món lặp 7 ngày |
| `E04_recent_pattern` | soft | logs | Trừ điểm lặp cùng signature mâm |

### 5.5 Lớp C — Hàn–nhiệt *(chỉ khi có data)*

| ID | Sev | Requires | Mô tả |
|---|---|---|---|
| `C01_no_all_hot` | soft | `thermal_nature` | Không 3 slot đều hot/warm cao |
| `C02_balance_cool` | soft | thermal + user pref “dễ nhiệt” (tự khai) | Ưu tiên thêm cool/cold/neutral |
| `C03_skip_if_missing` | info | — | Thiếu thermal → **không chấm** lớp C |

**Cấm** suy thermal từ tên món bằng heuristic trong production seed.

### 5.6 Lớp D — Ngũ hành *(chỉ khi có data)*

| ID | Sev | Requires | Mô tả |
|---|---|---|---|
| `D01_missing_boost` | soft | five_element + pref.balance_elements | Boost element thiếu 7 ngày (đã phác thảo code) |
| `D02_plate_diversity` | soft | five_element trên ≥2 slot | Thưởng mâm đa element |
| `D03_conflict_soft` *(sau)* | soft | bảng sinh–khắc versioned | Chỉ bật khi có bảng + gán element verified |
| `D04_skip_if_missing` | info | — | Element null → skip rule phụ thuộc element |

**Cấm** gán element “best-effort đoán” trong seed production (khác prototype dev có
cờ `SEED_ALLOW_UNVERIFIED=1` — xem seed-KB).

### 5.7 Random / đa dạng

| ID | Sev | Mô tả |
|---|---|---|
| `R01_jitter_after_pass` | — | Chỉ shuffle/jitter trong tập plate/dish **đã hard-pass** |
| `R02_forbid_jitter_fill` | hard (process) | Không dùng random để lấp slot thiếu role |

---

## 6. Pipeline engine (mục tiêu)

```
1. Input + suggest_mode (pick | compose)
2. Load dishes: status=published; chỉ dùng field non-null theo rule
3. Apply S (preference/safety)
4. compose?
     YES → ResolveTemplate → beam/search slots by role
           → evaluate B hard → E/A soft → C/D if data
     NO  → score single dishes (legacy pick) without claiming plate structure
5. Attach explanations[] = list { rule_id, layer, severity, message, skipped? }
6. Log: ruleset_version, template_id, composition, field_completeness
```

**Giải thích cho user:** ưu tiên 1–3 câu từ hard/info; soft chi tiết nằm panel “Vì sao”.

---

## 7. Bảng `explanations` (hợp đồng)

```json
{
  "rule_id": "B01_template_roles",
  "layer": "structure",
  "severity": "hard",
  "status": "pass",
  "message": "Đủ cấu trúc: canh · món chính · rau",
  "fields_used": ["dish_role"]
}
```

`status`: `pass` | `fail` | `soft_fail` | `skipped_missing_data`.

---

## 8. Changelog ruleset

| Version | Ngày | Thay đổi |
|---|---|---|
| `0.1.0-draft` | 2026-07-11 | Khởi tạo lớp A–E–S, template mâm, data-gate, cấm đoán field |
| `0.2.0` | 2026-07-11 | Implement compose: vn_home_3/2, standalone, light, dine_out_1; soft fry/kcal/thermal; auto mode; region filter |
| `0.3.0` | 2026-07-12 | Soft diversity protein (E01) + mạnh penalty double fry; template `dine_out_feast_1`; exclude plate signature reroll; thermal soft **chỉ** khi pref YHCT opt-in |
| `0.3.0` *(patch ops)* | 2026-07-12 | Soft-relax filter **không im lặng**: `meta.relaxations` + `meta.message` (region / budget / exclude / plate_signature). Không bump minor — hành vi minh bạch, hard rules giữ. |

Mọi PR đổi rule: **cập nhật bảng này + bump version**.

---

## 9. Kiểm thử rule (bắt buộc khi implement)

| Case | Kỳ vọng |
|---|---|
| Mâm 3 role đúng | `B01` pass |
| 3 one_bowl gọi là mâm vn_home_3 | `B01`/`B03` fail |
| Món thiếu `calories_kcal` | `A02` skip/partial, không bịa kcal |
| Món thiếu `thermal_nature` | Lớp C toàn skip |
| User blacklist | Không bao giờ ra món đó |
| Reroll | Signature khác; hard constraints vẫn giữ |

Chi tiết test code: [`../12-testing.md`](../12-testing.md) (bổ sung khi implement).

---

## 10. Việc Agent / dev khi sửa rule

1. Sửa catalog §5 + version §1/§8 **trước hoặc cùng** PR code.  
2. Không hard-code “số may mắn” trong Controller.  
3. Rule mới phải khai `requires_fields`.  
4. Không bật hard rule nếu seed chưa có đủ data verified (tránh partial vĩnh viễn).  
5. Seed: tuân [`what-to-eat-seed-and-kb.md`](what-to-eat-seed-and-kb.md).
