# Tính năng: Hôm nay ăn gì (What to Eat)

Liên quan: [`../03-domain-model.md`](../03-domain-model.md),
[`../04-database-schema.md`](../04-database-schema.md),
[`experiences-and-categories.md`](experiences-and-categories.md),
[`maps-and-location.md`](maps-and-location.md),
[`taste-matching.md`](taste-matching.md),
[`habit-tracker.md`](habit-tracker.md),
[`../15-design-system.md`](../15-design-system.md),
[`../10-security-privacy.md`](../10-security-privacy.md).

**Tài liệu con (bắt buộc khi đụng rule / seed / fact món):**

| File | Nội dung |
|---|---|
| [`what-to-eat-ruleset.md`](what-to-eat-ruleset.md) | Lớp rule A–E–S, template mâm, data-gate, version ruleset |
| [`what-to-eat-seed-and-kb.md`](what-to-eat-seed-and-kb.md) | **Chuẩn seed verified-only**, null khi chưa xác thực, pipeline thêm món hàng loạt, provenance, UGC lấp trống |
| [`what-to-eat-dish-catalog.md`](what-to-eat-dish-catalog.md) | **Danh mục món ứng viên** (inventory): slug, role, **vùng miền**, ưu tiên seed P0–P3 — chốt trước khi ghi JSON |
| [`what-to-eat-fact-a-calories.md`](what-to-eat-fact-a-calories.md) | **Fact-A** calo verified (FCT + recipe_sum + vivu-standard-v1) |
| [`what-to-eat-fact-completion-plan.md`](what-to-eat-fact-completion-plan.md) | **Kế hoạch hoàn thiện** fact (tầng 0–6, FCT VN, DoD) |
| [`what-to-eat-fct-vn.md`](what-to-eat-fct-vn.md) | Map nguyên liệu FCT Việt Nam (bootstrap) |
| [`what-to-eat-yhct.md`](what-to-eat-yhct.md) | YHCT hàn–nhiệt / ngũ hành (expert-gated) |

> **Trạng thái:** Phase **A–D** + **engine compose 0.2.0** đã implement (popup, catalog,
> UGC + admin, history/prefs, match Experience, **MealComposer** mâm canh–mặn–rau,
> filter vùng miền). Calo gắn **serving_grams** khi có fact. Form: bữa · nhẹ/chính ·
> ngoài/nấu · **kiểu gợi ý** · **vùng** · count · mục tiêu kcal/ngày. Phase E social — backlog.  
> **Chuẩn dữ liệu:** verified-only seed — [`what-to-eat-seed-and-kb.md`](what-to-eat-seed-and-kb.md).  
> **Seed P0+P1+P2+fix:** **182** món skeleton (`1.2.1-fix`) + role + region.  
> **Fact-A:** **116**/182 calo (`2.3.0-fact-a`; null ~66; high **9**; FCT VN pilot **35**; `com-trang`/implicit rice **206**).  
> **Ops-A:** **182** `cooking_method` + `protein_source`.  
> **Recipe text:** **155** cook_home ingredients/steps.  
> **YHCT:** **10** medium `tcm_text` (chờ expert); queue món phức tạp.  
> **FCT VN:** ~35 nguyên liệu + yield; standard bowls `1.1.0-vn` (bún/phở/cơm tấm…).  
> Plan: [`what-to-eat-fact-completion-plan.md`](what-to-eat-fact-completion-plan.md).  
> **Ruleset:** `0.2.0` — [`what-to-eat-ruleset.md`](what-to-eat-ruleset.md).  
> **Nguyên tắc:** không claim chữa bệnh; không bịa fact.  
> **Vị trí sản phẩm:** **Tính năng phụ (utility)** — popup trên Kho.

---

## 1. Mục tiêu sản phẩm

Giúp người dùng **quyết định nhanh “hôm nay ăn gì”** ngay từ **trang chính (Kho)**,
qua **popup**, không rời luồng lưu trữ trải nghiệm.

| Mục tiêu | Không phải mục tiêu (v1–v1.2) |
|---|---|
| Nút trên **Kho** → **popup** chọn bối cảnh + **số lượng món** gợi ý | Tab nav / màn hình “app ăn uống” độc lập làm home thứ hai |
| Gợi ý theo **bữa** + **nhẹ/chính** + **ngoài/tự nấu** | Thay thế chuyên gia dinh dưỡng / y khoa |
| Danh sách món trong popup; **Chi tiết** từng món khi user chủ động mở | Ép đọc full công thức ngay trên kết quả |
| **Kho món** hệ thống + seed; (sau) đóng góp UGC | Ứng dụng đặt món / giao hàng |
| (Mở rộng) nối **ăn ngoài** → Experience / map | Social feed “hôm nay mình ăn” công khai |

### Vì sao là tính năng phụ?

ViVu ưu tiên **kho trải nghiệm**. “Hôm nay ăn gì” chỉ là **helper quyết định** —
mở nhanh, đóng nhanh, không chiếm floating nav, không thay home.

```
[Kho `/` — user đã login]
    └─ Nút «Hôm nay ăn gì?»
         └─ Popup (Alpine modal)
              ├─ Form: bữa · nhẹ/chính · ngoài/nấu · số lượng món (count)
              ├─ [Gợi ý] → danh sách N món (tóm tắt)
              │     └─ mỗi món: nút [Chi tiết] → panel/trang chi tiết
              └─ [Gợi ý lại] / đóng popup
```

Khác Habit Tracker: Habit = theo dõi thói quen (trang riêng `/habits`); What-to-eat =
**popup quyết định theo phiên** + tri thức món dùng chung.

---

## 2. Personas & use case

| Persona | Nhu cầu chính |
|---|---|
| User bận rộn (chính) | Từ Kho → 1 nút → chọn nhanh → N món gợi ý trong popup |
| P1 Explorer | (Phase sau) từ chi tiết món → quán quanh đây |
| P2 Contributor | Đóng góp công thức / calo / ngũ hành (phase B; có thể từ trang chi tiết) |
| User quan tâm sức khoẻ / văn hoá | Chỉ khi bấm **Chi tiết** mới xem calo, lợi/hại, ngũ hành (+ disclaimer) |

### Luồng chính (happy path) — v1

1. User đăng nhập, ở **trang chính Kho** (`/`, `home/me`).
2. Bấm nút **«Hôm nay ăn gì?»** (cùng cụm quick-action với Habit / Hồ sơ gu).
3. **Popup** mở (không điều hướng full-page bắt buộc):
   - Chọn **bữa**: sáng / trưa / tối  
   - Chọn **ăn nhẹ / ăn chính**  
   - Chọn **ăn ngoài / tự nấu**  
   - Chọn **số lượng món** gợi ý (`count`, vd 1–5, mặc định 3)
4. Bấm **Gợi ý** → engine trả đúng (hoặc gần) `count` món; hiển thị **danh sách tóm tắt**
   trong popup (tên, icon/element, 1 dòng lý do).
5. Muốn biết thêm → bấm **Chi tiết** trên **từng** món → mở chi tiết (drawer trong
   popup **hoặc** route `/what-to-eat/dishes/{slug}` — xem §6.3).
6. Có thể **Gợi ý lại** (reroll, exclude món vừa hiện), hoặc **đóng** popup → về Kho.

> Guest / landing: **không** bắt buộc có nút (Kho chỉ khi auth). Có thể bổ sung sau
> trên landing nếu cần marketing — **không** là DoD Phase A.

---

## 3. Phân rã miền — các trục lọc (dimensions)

Đây là “ma trận quyết định”. Mọi Dish **BẮT BUỘC** gắn được ít nhất một giá trị
hợp lệ trên từng trục dùng để gợi ý.

### 3.1 Bữa (`meal_slot`)

| Giá trị | Label VI | Ghi chú |
|---|---|---|
| `breakfast` | Bữa sáng | Phở, bánh mì, xôi, trứng… |
| `lunch` | Bữa trưa | Cơm văn phòng, bún, cơm tấm… |
| `dinner` | Bữa tối | Tương tự trưa; có thể ưu tiên món “nặng” hơn nếu `main` |
| `snack` | Ăn vặt / giữa bữa | **CÓ THỂ** map với `meal_size = light`; không bắt buộc UI v1 |

> **Quyết định v1:** UI chỉ 3 bữa (sáng/trưa/tối). `snack` dùng nội bộ khi
> `meal_size = light` và slot không khớp — hoặc bỏ hẳn cho đến khi có nhu cầu.

Một món có **nhiều** slot (vd bánh mì: breakfast + snack).

### 3.2 Độ no / vai trò bữa (`meal_size`)

| Giá trị | Label VI | Ý nghĩa gợi ý |
|---|---|---|
| `light` | Ăn nhẹ | Ít no, snack, gọn; ưu tiên calo thấp hơn nếu có |
| `main` | Ăn chính | Bữa chính đủ no |

### 3.3 Hình thức (`meal_mode`)

| Giá trị | Label VI | Hệ quả logic |
|---|---|---|
| `dine_out` | Ăn ngoài | Ưu tiên món có liên kết quán / Experience; **không** bắt buộc có công thức |
| `cook_home` | Tự nấu | Ưu tiên món có **công thức đã duyệt**; hiện bước nấu, thời gian, calo |

### 3.4 Ngũ hành (`five_element`)

| Giá trị | Label VI | Ghi chú |
|---|---|---|
| `wood` | Mộc | |
| `fire` | Hoả | |
| `earth` | Thổ | |
| `metal` | Kim | |
| `water` | Thuỷ | |
| `null` / unknown | Chưa xác định | Không loại khỏi gợi ý mặc định |

> ⚠️ **Disclaimer sản phẩm (BẮT BUỘC trên UI chi tiết món / trang ngũ hành):**  
> Thông tin ngũ hành, calo, lợi/hại mang tính **tham khảo cộng đồng / văn hoá**,
> **không** thay thế tư vấn y tế hay chuyên gia dinh dưỡng.

**Phạm vi logic ngũ hành (chia phase):**

| Phase | Hành vi |
|---|---|
| v1.0 catalog | Gắn 1 `five_element` chính / món (admin seed + contribution) |
| v1.1 | Lọc “muốn món hệ X”; hiển thị badge |
| v1.2+ (tuỳ chọn) | Gợi ý “cân bằng” theo lịch sử 7 ngày (đếm element đã chọn) — **không** làm tử vi / mệnh người dùng trừ khi có đặc tả riêng sau |

**Không làm v1:** nạp mệnh theo năm sinh, giờ ăn theo can chi, chữa bệnh bằng ngũ hành.

### 3.5 Trục phụ (optional filters — không chặn wizard cơ bản)

| Trục | Nguồn | Phase |
|---|---|---|
| `cuisine` / region | Tag hoặc `dish_tags` (Việt, Hàn, chay…) | v1.0 tag đơn giản |
| `max_calories` | Từ fact calo đã duyệt | v1.1 |
| `max_cook_minutes` | Từ recipe | v1.1 cook_home |
| `diet` (chay, không hải sản…) | Flag trên dish / tag | v1.1 |
| `nearby` radius | GPS + Experience | v1.2 dine_out |
| Dị ứng / blacklist user | `user_food_preferences` | v1.1 |

---

## 4. Mô hình thực thể (đề xuất)

### 4.1 Sơ đồ quan hệ

```
User 1 ── * DishContribution (UGC, moderated)
User 1 ── * MealSuggestionLog (private history)
User 1 ── 0..1 UserFoodPreference (private)

Dish (kho món hệ thống)
  ├── * meal_slots (JSON hoặc pivot)
  ├── meal_sizes  (JSON flags light/main)
  ├── meal_modes  (JSON flags dine_out/cook_home)
  ├── five_element?
  ├── * DishContribution  (recipe, calories, harm, benefit, advice, note, element_vote…)
  ├── * DishTag / Tag tái dùng?
  └── * (optional) Experience gợi ý qua tag/category — soft link, không FK cứng v1

Admin ── moderates Dish + DishContribution
```

### 4.2 Dish (món ăn — catalog)

Thực thể **dùng chung**, khác Experience (Experience = trải nghiệm tại **địa điểm**
của một user).

| Khái niệm | Mô tả |
|---|---|
| Định danh | `name`, `slug`, `name_en?` |
| Mô tả ngắn | `summary` |
| Ảnh | `cover_path` (seed/admin; user contribution ảnh phase sau) |
| Trục gợi ý | `meal_slots[]`, `supports_light`, `supports_main`, `supports_dine_out`, `supports_cook_home` |
| Ngũ hành | `five_element` nullable (canonical sau duyệt) |
| Trạng thái | `draft` \| `published` \| `hidden` |
| Nguồn | `source`: `system` \| `user` — món user đề xuất cần duyệt |
| Cache | `calories_kcal?`, `cook_minutes?`, `contribution_count`, `suggest_count` |
| SEO | trang `/what-to-eat/dishes/{slug}` |

**Quy tắc:**
1. Gợi ý **chỉ** lấy `status = published`.
2. Món `supports_cook_home = true` **NÊN** có ít nhất 1 contribution `type=recipe` đã `approved` trước khi ưu tiên cao (vẫn có thể gợi ý nếu chỉ có tên — điểm thấp hơn).
3. Không xoá cứng nếu đã có contribution / log — soft hide.

### 4.3 DishContribution (đóng góp thông tin)

Một bản ghi = **một gói đóng góp** của user (hoặc admin) về một Dish.

| Trường ý nghĩa | Kiểu logic |
|---|---|
| `type` | `recipe` \| `calories` \| `harm` \| `benefit` \| `advice` \| `note` \| `five_element` |
| `payload` | JSON theo type (schema bên dưới) |
| `status` | `pending` \| `approved` \| `rejected` |
| `user_id` | Tác giả (null nếu admin seed) |
| `reviewed_by` / `reviewed_at` | Admin |
| `is_canonical` | bool — bản được chọn hiển thị chính (1 recipe canonical / dish…) |

#### Schema `payload` theo type

```json
// recipe
{
  "ingredients": [{ "name": "Gạo", "amount": "2 chén" }],
  "steps": ["Vo gạo", "Nấu 20 phút"],
  "cook_minutes": 30,
  "servings": 2,
  "difficulty": "easy"   // easy|medium|hard
}

// calories
{
  "kcal_per_serving": 450,
  "serving_size": "1 tô ~400g",
  "protein_g": 20,
  "carb_g": 55,
  "fat_g": 12
}

// harm | benefit | advice | note
{
  "title": "optional",
  "body": "Markdown/plain ngắn",
  "severity": ["dạ dày yếu"]   // optional, cho harm
}

// five_element
{
  "element": "earth",
  "rationale": "Ngũ cốc, vị ngọt…"
}
```

**Quy tắc moderation (bám pattern Tag):**
1. User submit → `pending`; **chỉ** author + admin thấy pending.
2. Admin `approved` → hiển thị public; có thể set `is_canonical`.
3. Khi approve `calories` / `five_element` / `recipe` canonical → **đồng bộ cache** lên `dishes` (`calories_kcal`, `five_element`, `cook_minutes`).
4. Nhiều contribution cùng type: hiển thị canonical + tab “Đóng góp cộng đồng”.
5. Validate độ dài, cấm HTML thô; sanitize.
6. Rate limit submit (vd 10/giờ/user).

### 4.4 MealSuggestionLog (lịch sử gợi ý — riêng tư)

Ghi mỗi lần hệ thống gợi ý / user chọn, để **tránh lặp** và thống kê sau.

| Trường | Ý nghĩa |
|---|---|
| `user_id` | Chủ (bắt buộc auth để lưu; guest chỉ gợi ý không log — hoặc log session TTL ngắn) |
| `meal_slot`, `meal_size`, `meal_mode` | Context wizard |
| `filters_json` | Lọc phụ lúc đó |
| `suggested_dish_ids` | JSON mảng id trả về |
| `chosen_dish_id` | null cho đến khi user bấm “Chọn món này” |
| `outcome` | `suggested` \| `chosen` \| `skipped` \| `rerolled` |

**Quyền riêng tư:** chỉ chủ sở hữu; **không** public profile. Admin không list nội dung log user trừ tooling nội bộ (không làm v1).

### 4.5 UserFoodPreference (tuỳ chọn — v1.1)

| Trường | Ý nghĩa |
|---|---|
| `diet_flags` | JSON: `["vegetarian","no_seafood"]` |
| `disliked_dish_ids` / blacklist tags | Tránh gợi ý |
| `preferred_elements` | JSON ngũ hành ưa |
| `default_meal_mode` | cook / dine_out |
| `max_calories_default` | |

### 4.6 Liên kết Experience (ăn ngoài) — soft

**Không** tạo bảng pivot bắt buộc v1.

Chiến lược v1.2:
1. Dish có `search_keywords` / tags (`pho`, `bun-bo`…).
2. Khi `meal_mode = dine_out`: query Experience `published` + category slug `an` (Ăn) +
   fulltext/title/tag overlap + nearby nếu có lat/lng.
3. (Sau) pivot `dish_experience` do admin/user gắn tay.

---

## 5. Thuật toán gợi ý (v1 — rule-based, minh bạch)

Nguyên tắc giống taste-matching: **không ML** ở v1.

### 5.1 Pipeline

```
1. Input: meal_slot, meal_size, meal_mode, count (1..max), optional filters, user_id?
2. Validate count: MIN=1, MAX=5 (v1), default=3
3. Base query: dishes.published
4. Filter cứng (MUST):
   - meal_slot ∈ dish.meal_slots
   - meal_size: light → supports_light; main → supports_main
   - meal_mode: dine_out → supports_dine_out; cook_home → supports_cook_home
   - blacklist / diet (nếu có pref)
   - max_calories nếu filter + dish.calories_kcal NOT NULL (món thiếu calo: giữ nhưng score−)
5. Score (0–100) = tổng trọng số:
   + has_canonical_recipe          (+20 nếu cook_home)
   + has_calories                  (+5)
   + five_element match filter     (+10 nếu user lọc element)
   + not in last N chosen/suggested (+15 nếu không trùng 7 ngày)
   + popularity (log suggest/choose) (+0..10, log scale)
   + random jitter                 (+0..5) để đa dạng
   + dine_out: có Experience gần   (+15 nếu có kết quả nearby)
6. Lấy top K với K = count (user chọn); nếu pool < count → trả hết pool + cờ `partial: true`
7. Ghi MealSuggestionLog (filters_json gồm count)
8. Trả về kèm `reasons[]` tiếng Việt ngắn (1 dòng — đủ cho list trong popup)
```

### 5.2 Edge cases

| Tình huống | Xử lý |
|---|---|
| `count` ngoài 1–5 | Clamp hoặc 422 Form Request |
| Pool < count | Trả số món có được; UI: “Chỉ tìm thấy N món phù hợp” |
| Không còn món sau filter | Nới lần lượt: bỏ max_calories → bỏ element → cho phép món thiếu recipe (cook) → empty state trong popup |
| User spam “Gợi ý lại” | Exclude `suggested_dish_ids` của phiên popup hiện tại; hết pool thì reset + báo |
| Guest | Phase A: nút chỉ trên Kho (auth). Endpoint suggest CÓ THỂ public cho sau |
| Món hidden giữa chừng | Bỏ khỏi gợi ý; chi tiết 404/410 |

### 5.3 “Cân bằng ngũ hành” (v1.2 optional)

```
recent = elements of chosen dishes last 7 days
missing = five_elements - recent
boost dishes whose five_element ∈ missing
```

Chỉ **boost**, không ép; luôn cho user tắt.

---

## 6. UI / IA (public) — popup-first

### 6.1 Entry & nav (chốt theo yêu cầu sản phẩm)

| Khía cạnh | Quyết định |
|---|---|
| **Vai trò** | Tính năng **phụ** — helper, không module IA chính |
| **Entry chính** | **1 nút / thẻ** trên **trang chính user (Kho)** `resources/views/home/me.blade.php` |
| Hành vi nút | Mở **popup (modal)** — **không** điều hướng full-page làm luồng mặc định |
| Floating nav | **Không** thêm tab |
| Profile / Habit | Không bắt buộc shortcut riêng v1 (tránh loãng); có thể thêm icon nhỏ sau |
| Auth | Nút trên Kho ⇒ user đã login. Đóng góp / lịch sử: auth |
| Style | Modal `rounded-3xl`, overlay, teal/stone — bám [`15-design-system.md`](../15-design-system.md) |

**Vị trí nút gợi ý trên Kho** (cùng cụm quick-action hiện có: Thói quen, Hồ sơ gu):

- Thẻ/grid slot: icon 🍜 + label **«Hôm nay ăn gì?»** + subtitle ngắn  
- Hoặc nút secondary cạnh «Đăng trải nghiệm» — **ưu tiên thẻ grid** cho đồng bộ Habit.

### 6.2 Cấu trúc popup (2 tầng trong 1 modal)

```
┌─────────────────────────────────────────┐
│  Hôm nay ăn gì?                    [✕]  │
├─────────────────────────────────────────┤
│  TẦNG A — Form gợi ý                    │
│  • Bữa:     ( ) Sáng ( ) Trưa ( ) Tối   │
│  • Loại:    ( ) Ăn nhẹ  ( ) Ăn chính    │
│  • Hình thức: ( ) Ngoài ( ) Tự nấu      │
│  • Số món:  [ 1 ] [ 2 ] [ 3 ] [ 4 ] [ 5]│
│              (stepper hoặc chip; default 3)
│  [ Gợi ý món ]                          │
├─────────────────────────────────────────┤
│  TẦNG B — Kết quả (sau khi gợi ý)       │
│  ┌───────────────────────────────────┐  │
│  │ 🍜 Phở bò          [Chi tiết]     │  │
│  │    Phù hợp bữa sáng · ăn chính    │  │
│  └───────────────────────────────────┘  │
│  ┌───────────────────────────────────┐  │
│  │ 🥖 Bánh mì         [Chi tiết]     │  │
│  │    …                              │  │
│  └───────────────────────────────────┘  │
│  [ Gợi ý lại ]     [Đóng]               │
└─────────────────────────────────────────┘
```

**Nguyên tắc list vs chi tiết:**

| Surface | Nội dung hiển thị |
|---|---|
| **List trong popup** | Tên món, ảnh/icon nhỏ (nếu có), badge ngũ hành (optional), **1 dòng** reason. **Không** dump công thức / calo đầy đủ |
| **Nút Chi tiết** (mỗi món) | Mở full thông tin: summary, recipe, calo, lợi/hại, lời khuyên, ngũ hành, disclaimer |
| **Gợi ý lại** | Gọi lại engine với cùng filter + exclude id đang hiện |

### 6.3 Cách mở «Chi tiết» (chốt kỹ thuật v1)

| Cách | Ưu | Nhược | Khuyến nghị |
|---|---|---|---|
| **A. Panel lồng trong modal** (stack: list → detail → back) | Không rời popup; UX “phụ” rõ | Modal dài trên mobile | **Khuyến nghị Phase A** |
| **B. Modal thứ hai / bottom sheet** | Tách tầng rõ | Focus trap / z-index phức tạp hơn | OK nếu A chật |
| **C. Điều hướng trang** `/what-to-eat/dishes/{slug}` | SEO, share link, form đóng góp dễ | Rời Kho — chỉ khi user **chủ động** bấm Chi tiết | Dùng cho **deep link / Phase B+** |

**Phase A:** A (panel trong popup) **hoặc** C nếu muốn tái dùng Blade đơn giản.  
**BẮT BUỘC:** user không bị ép xem chi tiết — list gợi ý là đủ để “chọn nhanh”.

### 6.4 Route & component (đề xuất)

| Thành phần | Mô tả |
|---|---|
| Nút + modal trên `home/me` | `x-what-to-eat-trigger` + `x-what-to-eat-modal` (Blade + Alpine) |
| `POST /what-to-eat/suggest` | JSON/partial: nhận filter + `count`, trả list món tóm tắt |
| `GET /what-to-eat/dishes/{slug}` | Trang/partial **chi tiết** (khi chọn cách C, hoặc SEO) |
| `GET /what-to-eat/dishes/{slug}/fragment` | (Tuỳ chọn) HTML partial nhúng panel Chi tiết trong modal |
| Catalog full `/what-to-eat/dishes` | **Không** DoD Phase A — phase sau nếu cần SEO kho món |
| `GET /what-to-eat` full-page wizard | **Không** làm luồng chính; có thể redirect/alias mở Kho + `?what_to_eat=1` sau |
| Admin SPA | CRUD Dish + duyệt Contribution |

### 6.5 A11y & mobile

- Modal: focus trap, `Escape` đóng, `aria-modal`, nút ✕ đủ touch (≥44px).
- Scroll: form + list cuộn trong body modal; footer actions sticky trong modal nếu cần.
- Không block scroll body khi mở (`overflow-hidden` trên `body` khi open).
- Safe area / không đè floating nav: modal center hoặc sheet từ giữa-trên, `z-index` > nav.

### 6.6 Wire số lượng món (`count`)

| Rule | Giá trị |
|---|---|
| Default | `3` |
| Min / Max v1 | `1` / `5` |
| UI | Chip chọn số hoặc stepper − / + |
| API field | `count` (int) |
| Log | Lưu trong `filters_json.count` |

---

## 7. Admin

| Chức năng | Mô tả |
|---|---|
| Dish CRUD | Tạo/sửa/ẩn, gán slot/size/mode/element, cover |
| Seed import | CSV/JSON món VN phổ biến (seeder dev) |
| Duyệt contribution | List pending, approve/reject, set canonical |
| (Tuỳ chọn) Báo cáo | Flag nội dung sai lệch y khoa / spam |

Policy: chỉ `admin` guard; user không sửa dish canonical trừ qua contribution.

---

## 8. Schema đề xuất (tóm tắt — chi tiết ghi vào `04` khi implement)

> Khi implement: cập nhật [`04-database-schema.md`](../04-database-schema.md) **trước**
> migration. Dưới đây là bản thiết kế.

### 8.1 `dishes`

| Cột | Kiểu | Ghi chú |
|---|---|---|
| id | BIGINT PK | |
| name | VARCHAR(150) | |
| slug | VARCHAR(180) UNIQUE | |
| summary | VARCHAR(500) nullable | |
| cover_path | VARCHAR(255) nullable | |
| meal_slots | JSON | `["breakfast","lunch"]` |
| supports_light | BOOLEAN | |
| supports_main | BOOLEAN | |
| supports_dine_out | BOOLEAN | |
| supports_cook_home | BOOLEAN | |
| five_element | ENUM wood/fire/earth/metal/water nullable | |
| calories_kcal | SMALLINT UNSIGNED nullable | cache |
| cook_minutes | SMALLINT UNSIGNED nullable | cache |
| search_keywords | VARCHAR(255) nullable | cho dine_out match |
| status | ENUM draft/published/hidden | |
| source | ENUM system/user | |
| created_by | FK users nullable | |
| suggest_count | INT default 0 | |
| choose_count | INT default 0 | |
| timestamps + soft deletes | | |

Index: `status`, `five_element`, fulltext name+keywords (MySQL).

### 8.2 `dish_contributions`

| Cột | Kiểu | Ghi chú |
|---|---|---|
| id | BIGINT PK | |
| dish_id | FK dishes CASCADE | |
| user_id | FK users SET NULL | |
| type | VARCHAR/ENUM | recipe, calories, … |
| payload | JSON | |
| status | pending/approved/rejected | |
| is_canonical | BOOLEAN default false | |
| review_note | VARCHAR(255) nullable | |
| reviewed_by | FK admins nullable | |
| reviewed_at | TIMESTAMP nullable | |
| timestamps + soft deletes | | |

Index: `(dish_id, type, status)`, `status`.

### 8.3 `meal_suggestion_logs`

| Cột | Kiểu | Ghi chú |
|---|---|---|
| id | BIGINT PK | |
| user_id | FK users CASCADE | |
| meal_slot | VARCHAR | |
| meal_size | VARCHAR | |
| meal_mode | VARCHAR | |
| filters_json | JSON nullable | gồm `count` và filter phụ |
| suggested_dish_ids | JSON | đúng batch vừa gợi ý |
| chosen_dish_id | FK dishes SET NULL | |
| outcome | VARCHAR | |
| created_at | | (updated_at optional) |

Index: `(user_id, created_at)`.

### 8.4 `user_food_preferences` (v1.1)

1-1 `user_id`, JSON prefs như §4.5.

### 8.5 Enums (code)

- `MealSlot`, `MealSize`, `MealMode`, `FiveElement`, `DishStatus`, `ContributionType`, `ContributionStatus`

---

## 9. API / Route (định hướng)

### Public web (session `web`) — ưu tiên v1 (popup-first)

UI chính **không** cần trang riêng: modal gắn trên Kho. Backend endpoint mỏng cho Alpine.

| Method | Path | Auth | Mô tả |
|---|---|---|---|
| POST | `/what-to-eat/suggest` | auth (v1) | Body: slot, size, mode, `count` → JSON list tóm tắt |
| GET | `/what-to-eat/dishes/{slug}` | auth hoặc public | Chi tiết đầy đủ (panel fetch hoặc full page) |
| GET | `/what-to-eat/dishes/{slug}/fragment` | auth | (Tuỳ chọn) HTML partial cho panel Chi tiết |
| POST | `/what-to-eat/choose` | auth | (Phase C) Ghi món đã chọn |
| POST | `/what-to-eat/dishes/{dish}/contributions` | auth | (Phase B) Đóng góp |
| GET | `/what-to-eat/history` | auth | (Phase C) Lịch sử — trang phụ, không phải entry chính |

**Không** coi `GET /what-to-eat` full-page wizard là DoD.

### Admin API

```
/api/admin/dishes
/api/admin/dishes/{id}
/api/admin/dish-contributions
/api/admin/dish-contributions/{id}/status
```

Envelope theo [`05-api-conventions.md`](../05-api-conventions.md).

---

## 10. Ranh giới với module hiện có

| Module | Tách biệt | Điểm nối |
|---|---|---|
| **Experience** | Experience = địa điểm + cảm nhận user; Dish = tri thức món | dine_out: search Experience theo keyword/tag |
| **Category/Tag** | Giữ category Ăn cho Experience | Dish **không** bắt buộc `category_id`; có thể tái dùng Tag hoặc `dish` tags riêng để khỏi làm bẩn tag trải nghiệm |
| **Taste profile** | Gu người ≠ gu món | v2: boost món phổ biến trong nhóm match |
| **Habit** | Thói quen lặp | Template habit “Nấu ăn X lần/tuần”; auto check-in khi `choose` cook_home (tuỳ chọn, off mặc định) |
| **Premium** | Không khoá gợi ý cơ bản | CÓ THỂ: lịch sử dài, filter nâng cao — không làm v1 |

**Quyết định thẻ:** v1 dùng cột/JSON trên `dishes` + `search_keywords`; **không** nhét món vào bảng `tags` trải nghiệm để tránh lẫn “món Hàn” (tag explore) với catalog dinh dưỡng. v1.1 có thể `dish_tag` riêng nếu cần.

---

## 11. Bảo mật, pháp lý, nội dung nhạy cảm

1. **Disclaimer** calo / lợi / hại / ngũ hành trên mọi surface hiển thị fact sức khoẻ.
2. Contribution: cấm claim chữa bệnh tuyệt đối (filter từ khoá cơ bản + review admin).
3. CSRF + auth cho POST; Policy sở hữu contribution (sửa pending của mình).
4. Rate limit; chặn spam SEO.
5. Ảnh upload contribution (nếu có): resize queue, MIME whitelist — phase sau.
6. Không lộ `user_id` public trên contribution nếu user opt-out (mặc định hiện display name — giống comment).

---

## 12. Lộ trình triển khai đề xuất

### Phase A — MVP popup trên Kho (ưu tiên)

- Migration `dishes` + seeder ~40–80 món VN (slot/size/mode/element cơ bản).
- Service `WhatToEatSuggester` (nhận `count`).
- **Nút trên Kho** + **modal Alpine**: form bữa / nhẹ–chính / ngoài–nấu / số lượng.
- List kết quả trong popup + **nút Chi tiết** từng món (panel hoặc trang slug).
- Gợi ý lại (exclude batch hiện tại).
- Chưa UGC; field chi tiết từ seed.
- Test: ma trận filter; `count` 1–5; partial pool; hidden không ra; modal a11y cơ bản.

### Phase B — Đóng góp cộng đồng

- `dish_contributions` + form + admin duyệt.
- Canonical sync cache.
- Rate limit + validation payload theo type.

### Phase C — Cá nhân hoá nhẹ

- `meal_suggestion_logs` + tránh lặp 7 ngày.
- `user_food_preferences` (diet, blacklist).
- Lịch sử `/what-to-eat/history`.

### Phase D — Ăn ngoài × bản đồ × Experience

- Match keyword → Experience nearby.
- UI block “Quán gợi ý quanh bạn”.
- (Optional) gắn dish khi user đăng experience category Ăn.

### Phase E — Ngũ hành nâng cao / social (backlog)

- Boost cân bằng 7 ngày.
- Feed “bạn bè ăn gì” — **out of scope** đến khi có follow social rõ.

---

## 13. Seed & tri thức món

**Chuẩn bắt buộc:** [`what-to-eat-seed-and-kb.md`](what-to-eat-seed-and-kb.md).

Tóm tắt:

1. Seed **verified-only** cho fact nhạy (calo, ngũ hành, hàn–nhiệt, lợi/hại y khoa…).  
2. **Chưa xác thực → `null`** — UI mời đóng góp; rule **skip** field thiếu
   ([`what-to-eat-ruleset.md`](what-to-eat-ruleset.md) §4).  
3. Thêm lô lớn: dataset versioned (`database/data/what-to-eat/…`) + provenance + PR fact-gate.  
4. Được seed skeleton: tên, slug, `meal_slots`, flags light/main/dine/cook; recipe/calo/element
   chỉ khi có nguồn.

### Gợi ý nhóm món (phủ filter — không đồng nghĩa fact III đầy)

| Nhóm | Ví dụ | Slot | Size | Mode |
|---|---|---|---|---|
| Sáng Việt | Phở, bún bò, bánh mì, xôi, cháo | breakfast | main/light | both |
| Trưa/tối cơm | Cơm tấm, cơm gà, canh+cá | lunch, dinner | main | both |
| Bún/miến | Bún chả, bún riêu, miến | lunch, dinner | main | both |
| Ăn nhẹ | Sữa chua, trái cây, bánh tráng | all | light | both |
| Tự nấu đơn giản | Trứng chiên, canh rau, ức gà | lunch, dinner | main/light | cook |
| Ăn ngoài điển hình | Lẩu, nướng, buffet | dinner | main | dine_out |
| Chay | Phở chay, cơm chay | all | main/light | both |

`summary`: mô tả trung tính. **`five_element` / calo: không best-effort** — null nếu chưa verified.

---

## 14. Kiểm thử tối thiểu

### Unit
- `WhatToEatSuggester`: ma trận 3×2×2 trả món hợp lệ; score ưu tiên recipe khi cook_home.
- Nới filter khi empty.
- Exclude recent chosen.

### Feature
- User auth: POST suggest với count=3 → đúng số (hoặc partial).
- count=0 hoặc 99 → 422 / clamp theo Form Request.
- Contribute (B) → pending; user khác không thấy.
- Admin approve → public + cache dish cập nhật.
- Dish hidden → không suggest; chi tiết 404.
- Guest không thấy nút trên landing (Phase A).

### UI
- Nút trên Kho mở/đóng popup; list không auto-expand chi tiết.
- Nút Chi tiết từng món mở đúng nội dung món đó.
- Không thêm tab floating nav; disclaimer trên màn chi tiết.

---

## 15. Quyết định còn mở (cần chốt trước khi code lớn)

| # | Câu hỏi | Gợi ý mặc định |
|---|---|---|
| Q1 | Chi tiết món: panel trong popup hay trang riêng? | **Panel trong popup (A)**; URL slug cho share/Phase B |
| Q2 | `count` max? | **5** (default 3) |
| Q3 | Một contribution = một type hay multi-field? | **Một type / request** (Phase B) |
| Q4 | Tái dùng `tags` hay keywords trên Dish? | **Keywords/JSON riêng** v1 |
| Q5 | Ngũ hành bắt buộc trên seed? | **NÊN** có; null vẫn publish |
| Q6 | Catalog SEO full-page Phase A? | **Không** — chỉ chi tiết khi bấm Chi tiết |
| Q7 | Auto habit khi chọn món? | **Không** mặc định |
| Q8 | Tên code | `WhatToEat`, `Dish`, `DishContribution` |

---

## 16. Tóm tắt rủi ro scope

| Rủi ro | Mitigation |
|---|---|
| Phình thành app dinh dưỡng / “app thứ hai” | **Popup-only** trên Kho; không tab; không full-page wizard làm entry |
| UGC sai lệch sức khoẻ | Moderation + disclaimer + reject claim tuyệt đối |
| Trùng Experience | Tách entity Dish; soft-link khi dine_out |
| Ma trận filter trống | Seeder đủ phủ; nới filter; partial khi pool < count |
| Nav / IA rối | 1 nút trên Kho; không thêm tab |
| Modal quá tải thông tin | List tóm tắt; chi tiết **chỉ** khi bấm nút Chi tiết |
| Ngũ hành gây tranh cãi | Optional; không mệnh cá nhân v1 |

---

## 17. Việc Agent làm khi được lệnh implement

1. Chốt Q1–Q7 với user nếu lệch mặc định.
2. Cập nhật `03-domain-model`, `04-database-schema`, `14-roadmap` (mốc M8), `11-glossary`, `15-design-system` §IA, `00-vision-scope` trong phạm vi.
3. Migration + Enum + Model + `WhatToEatSuggester` + seeder.
4. Web routes + Blade + Alpine wizard.
5. Admin API + SPA pages (Phase B có thể cùng hoặc sau A).
6. Feature/Unit tests xanh.
7. Cập nhật `CLAUDE.md` §6 checklist.
8. **Seed / rule / fact món:** tuân
   [`what-to-eat-seed-and-kb.md`](what-to-eat-seed-and-kb.md) +
   [`what-to-eat-ruleset.md`](what-to-eat-ruleset.md) — không đoán field nhạy cảm.
