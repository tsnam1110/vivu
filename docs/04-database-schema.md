# 04 — Lược đồ cơ sở dữ liệu

> **Đây là nguồn sự thật duy nhất về schema.** Migration phải khớp file này. Đổi
> schema → cập nhật file này trong cùng thay đổi.

Quy ước chung:
- Engine: **InnoDB**, charset `utf8mb4`, collation `utf8mb4_unicode_ci`.
- Mọi bảng có `id` (BIGINT UNSIGNED, auto-increment), `created_at`, `updated_at`.
- Xoá mềm (`deleted_at`) cho `users`, `experiences`, `comments`.
- Khoá ngoại đặt tên `<table_singular>_id`, có `ON DELETE` rõ ràng.
- Timezone lưu UTC; hiển thị theo `Asia/Ho_Chi_Minh`.

---

## 1. `admins` — tài khoản quản trị
| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK | |
| name | VARCHAR(150) | NOT NULL | |
| email | VARCHAR(191) | UNIQUE, NOT NULL | |
| password | VARCHAR(255) | NOT NULL | hash bcrypt/argon |
| is_active | BOOLEAN | default true | |
| last_login_at | TIMESTAMP | nullable | |
| created_at / updated_at | TIMESTAMP | | |

Vai trò/quyền qua `spatie/laravel-permission` (bảng `roles`, `permissions`,
`model_has_roles` với `model_type = App\Models\Admin`).

---

## 2. `users` — người dùng cuối
| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK | |
| name | VARCHAR(150) | NOT NULL | tên hiển thị |
| username | VARCHAR(50) | UNIQUE, NOT NULL | dùng cho URL hồ sơ `/u/{username}` |
| email | VARCHAR(191) | UNIQUE, NOT NULL | |
| email_verified_at | TIMESTAMP | nullable | |
| password | VARCHAR(255) | NOT NULL | |
| avatar_path | VARCHAR(255) | nullable | upload storage; null nếu dùng sample |
| sample_avatar_id | BIGINT UNSIGNED | nullable, FK→sample_avatars SET NULL | avatar mẫu |
| avatar_frame_id | BIGINT UNSIGNED | nullable, FK→avatar_frames SET NULL | khung đang chọn |
| premium_expires_at | DATETIME | nullable | cache Premium active; null = không Premium |
| status | ENUM('active','suspended') | default 'active' | admin có thể khoá |
| created_at / updated_at / deleted_at | TIMESTAMP | | soft delete |

Index: `username`, `email`, `premium_expires_at`.

> **Avatar / Premium:** xem [`features/avatar-and-premium.md`](features/avatar-and-premium.md).
> `hasActivePremium()` ⇔ `premium_expires_at IS NOT NULL AND premium_expires_at > now()`.

---

## 3. `user_profiles` — hồ sơ gu (1-1 với users)
| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK | |
| user_id | BIGINT UNSIGNED | FK→users, UNIQUE, ON DELETE CASCADE | 1-1 |
| bio | TEXT | nullable | giới thiệu tự do |
| personality | JSON | nullable | mảng nhãn tính cách, vd `["huong_noi","phieu_luu"]` |
| interests | JSON | nullable | mảng nhãn sở thích, vd `["am_thuc","nhiep_anh"]` |
| location_city | VARCHAR(100) | nullable | thành phố (lọc/gợi ý theo vùng) |
| weight_kg | DECIMAL(5,2) | nullable | cân nặng — **riêng tư**; ước lượng calo ngày |
| height_cm | SMALLINT UNSIGNED | nullable | chiều cao cm — riêng tư |
| gender | VARCHAR(20) | nullable | male/female/other/prefer_not |
| birth_year | SMALLINT UNSIGNED | nullable | năm sinh (tính tuổi cho Mifflin) |
| activity_level | VARCHAR(20) | nullable | sedentary/light/moderate/active/very_active |
| is_matchable | BOOLEAN | default true | tham gia gợi ý "người cùng gu" |
| created_at / updated_at | TIMESTAMP | | |

> `personality` & `interests` lưu **JSON mảng slug nhãn**. Danh mục nhãn chuẩn hoá
> quản lý ở bảng `taste_traits` (§10) để tìm kiếm & gợi ý nhất quán.
>
> Thể trạng (cân/cao/giới tính…) **không** lộ public profile; chỉ dùng hồ sơ gu +
> «Hôm nay ăn gì» (mặc định calo ngày).

---

## 4. `categories` — danh mục
| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK | |
| name | VARCHAR(100) | NOT NULL | vd "Ăn", "Du lịch" |
| slug | VARCHAR(120) | UNIQUE, NOT NULL | |
| icon | VARCHAR(100) | nullable | tên icon/emoji |
| description | VARCHAR(255) | nullable | |
| sort_order | INT | default 0 | thứ tự hiển thị |
| is_active | BOOLEAN | default true | |
| created_at / updated_at | TIMESTAMP | | |

---

## 5. `tags` — thẻ tuỳ chỉnh
| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK | |
| category_id | BIGINT UNSIGNED | FK→categories, **nullable**, ON DELETE CASCADE | null = thẻ toàn cục |
| name | VARCHAR(80) | NOT NULL | vd "món Hàn" |
| slug | VARCHAR(100) | NOT NULL | |
| usage_count | INT | default 0 | cache số lần dùng (gợi ý phổ biến) |
| status | ENUM/VARCHAR `pending` \| `approved` | default `approved` | user tạo → `pending`; admin duyệt → `approved` |
| created_by | BIGINT UNSIGNED | nullable, FK→users SET NULL | user đề xuất thẻ; null = admin/seed |
| created_at / updated_at | TIMESTAMP | | |

Index/UNIQUE: `UNIQUE(category_id, slug)` (một slug không trùng trong cùng danh mục); index `status`.

**Hiển thị / lọc:**
- `approved`: mọi người thấy & lọc được.
- `pending`: chỉ **người tạo** (`created_by`) thấy (form đăng, chi tiết bài của mình); **không** vào bộ lọc public / API tags public.
- Admin: list + filter `status`, `PATCH /api/admin/tags/{id}/status` để duyệt.

---

## 6. `experiences` — bài trải nghiệm (trung tâm)
| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK | |
| user_id | BIGINT UNSIGNED | FK→users, ON DELETE CASCADE | tác giả |
| category_id | BIGINT UNSIGNED | FK→categories, ON DELETE RESTRICT | một danh mục |
| title | VARCHAR(180) | NOT NULL | |
| slug | VARCHAR(200) | UNIQUE, NOT NULL | URL SEO |
| content | LONGTEXT | nullable | nội dung trải nghiệm |
| place_name | VARCHAR(180) | nullable | tên địa điểm (vd "Quán A") |
| address | VARCHAR(255) | nullable | địa chỉ dạng chữ |
| latitude | DECIMAL(10,7) | nullable | vĩ độ |
| longitude | DECIMAL(10,7) | nullable | kinh độ |
| google_place_id | VARCHAR(255) | nullable | id Google Places (nếu chọn từ autocomplete) |
| author_rating | TINYINT UNSIGNED | nullable | điểm tác giả 1–10 = **5 sao × nửa sao** (1=½★ … 10=5★); khác rating cộng đồng |
| status | ENUM('draft','pending','published','hidden') | default 'draft' | xem vòng đời |
| rating_avg | DECIMAL(3,2) | default 0 | cache trung bình sao **cộng đồng** (comments) |
| rating_count | INT | default 0 | cache số đánh giá cộng đồng |
| reaction_count | INT | default 0 | cache tổng reaction |
| view_count | INT | default 0 | |
| published_at | TIMESTAMP | nullable | |
| created_at / updated_at / deleted_at | TIMESTAMP | | soft delete |

Index: `slug`, `status`, `category_id`, `(latitude, longitude)` (tìm theo vùng),
`published_at`.

> ⚠️ Khi `status = published`: `latitude`, `longitude`, `published_at` **BẮT BUỘC** có.
> Ràng buộc này áp ở tầng ứng dụng (Form Request / Service), không ở DB.

---

## 7. `experience_tag` — pivot Experience ↔ Tag
| Cột | Kiểu | Ràng buộc |
|---|---|---|
| experience_id | BIGINT UNSIGNED | FK→experiences, ON DELETE CASCADE |
| tag_id | BIGINT UNSIGNED | FK→tags, ON DELETE CASCADE |

PK tổ hợp: `(experience_id, tag_id)`. Không cần `id` riêng.

---

## 8. `media` — ảnh của experience
| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK | |
| experience_id | BIGINT UNSIGNED | FK→experiences, ON DELETE CASCADE | |
| disk | VARCHAR(30) | default 'public' | |
| path | VARCHAR(255) | NOT NULL | |
| width | INT | nullable | |
| height | INT | nullable | |
| is_cover | BOOLEAN | default false | ảnh bìa |
| sort_order | INT | default 0 | |
| created_at / updated_at | TIMESTAMP | | |

---

## 9. `comments` — bình luận + đánh giá
| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK | |
| experience_id | BIGINT UNSIGNED | FK→experiences, ON DELETE CASCADE | |
| user_id | BIGINT UNSIGNED | FK→users, ON DELETE CASCADE | |
| parent_id | BIGINT UNSIGNED | FK→comments, nullable, ON DELETE CASCADE | trả lời 1 cấp |
| body | TEXT | NOT NULL | |
| rating | TINYINT UNSIGNED | nullable | 1–5, null = không chấm |
| status | ENUM('visible','pending','hidden') | default 'visible' | kiểm duyệt |
| created_at / updated_at / deleted_at | TIMESTAMP | | soft delete |

Index: `experience_id`, `user_id`, `status`.

> Ràng buộc `rating BETWEEN 1 AND 5` khi không null — kiểm ở Form Request.

---

## 10. `reactions` — cảm xúc (polymorphic)
| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK | |
| user_id | BIGINT UNSIGNED | FK→users, ON DELETE CASCADE | |
| reactable_type | VARCHAR(255) | NOT NULL | vd `App\Models\Experience` |
| reactable_id | BIGINT UNSIGNED | NOT NULL | |
| type | ENUM('like','love') | NOT NULL | mở rộng được |
| created_at / updated_at | TIMESTAMP | | |

UNIQUE: `(user_id, reactable_type, reactable_id)` — **một user 1 reaction / đối tượng**.
Index: `(reactable_type, reactable_id)` để đếm nhanh.

---

## 11. `taste_traits` — từ điển nhãn tính cách/sở thích
Chuẩn hoá nhãn dùng trong `user_profiles.personality` & `.interests` để tìm kiếm/gợi ý.
| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK | |
| type | ENUM('personality','interest') | NOT NULL | phân loại nhãn |
| name | VARCHAR(80) | NOT NULL | vd "Thích phiêu lưu" |
| slug | VARCHAR(100) | NOT NULL | vd "phieu-luu" |
| is_active | BOOLEAN | default true | |

UNIQUE: `(type, slug)`. Quản lý bởi admin.

> 💡 Lưu nhãn trong profile bằng **slug** trỏ tới bảng này → tìm "người cùng gu" bằng
> giao tập nhãn. Xem [`features/taste-matching.md`](features/taste-matching.md).

---

## 12. `sample_avatars` — avatar mẫu (catalog)
| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK | |
| slug | VARCHAR(60) | UNIQUE, NOT NULL | |
| name | VARCHAR(100) | NOT NULL | |
| path | VARCHAR(255) | NOT NULL | relative `public/` (vd `images/sample-avatars/fox.svg`) |
| sort_order | INT | default 0 | |
| is_active | BOOLEAN | default true | |
| created_at / updated_at | TIMESTAMP | | |

## 13. `avatar_frames` — catalog khung avatar
| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK | |
| slug | VARCHAR(60) | UNIQUE, NOT NULL | |
| name | VARCHAR(100) | NOT NULL | |
| description | VARCHAR(255) | nullable | |
| effect_type | VARCHAR(32) | NOT NULL | engine: soft, gradient, spin, glow, holographic |
| effect_config | JSON | nullable | colors, thickness, speed_ms, intensity… |
| is_premium | BOOLEAN | default false | |
| show_badge | BOOLEAN | default false | huy hiệu ✦ |
| sort_order | INT | default 0 | |
| is_active | BOOLEAN | default true | |
| created_at / updated_at | TIMESTAMP | | |

Index: `(is_active, sort_order)`, `is_premium`.

## 14. `premium_subscriptions` — gói Premium (thời hạn)
| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK | |
| user_id | BIGINT UNSIGNED | FK→users CASCADE | |
| starts_at | DATETIME | NOT NULL | |
| ends_at | DATETIME | nullable | null = lifetime |
| status | ENUM('active','expired','cancelled') | default 'active' | |
| source | ENUM('admin','demo','payment') | default 'admin' | |
| notes | VARCHAR(500) | nullable | ghi chú admin |
| granted_by_admin_id | BIGINT UNSIGNED | nullable, FK→admins SET NULL | |
| created_at / updated_at | TIMESTAMP | | |

Index: `(user_id, status)`, `ends_at`.

> Khi grant/extend/cancel: sync `users.premium_expires_at` = max ends_at của các sub
> `active` còn hiệu lực (hoặc `+50 years` nếu lifetime — dùng DATETIME, không TIMESTAMP).

---

## 15. Bảng hệ thống (Laravel mặc định)
`password_reset_tokens`, `sessions` (guard web), `jobs` + `failed_jobs` (queue),
`personal_access_tokens` (Sanctum cho admin), `cache`, `migrations`.

---

## 16. `habit_items` — mẫu Habit (catalog admin)
| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK | |
| name | VARCHAR(120) | NOT NULL | tên mẫu gợi ý |
| slug | VARCHAR(140) | UNIQUE, NOT NULL | |
| description | VARCHAR(500) | nullable | |
| icon | VARCHAR(16) | nullable | emoji |
| sort_order | INT | default 0 | |
| is_active | BOOLEAN | default true | tắt → không cho user chọn mới |
| created_at / updated_at | TIMESTAMP | | |

> Chỉ là **template**. User adopt → copy sang `user_habit_items`, không dùng trực tiếp làm hàng bảng.

## 16b. `user_habit_items` — đầu mục cá nhân (hàng trên bảng user)
| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK | |
| user_id | BIGINT UNSIGNED | FK→users CASCADE | |
| template_habit_item_id | BIGINT UNSIGNED | nullable, FK→habit_items SET NULL | null = tự tạo text |
| name | VARCHAR(120) | NOT NULL | user có thể sửa |
| description | VARCHAR(500) | nullable | |
| icon | VARCHAR(16) | nullable | |
| sort_order | INT | default 0 | |
| is_active | BOOLEAN | default true | ẩn khỏi grid |
| created_at / updated_at | TIMESTAMP | | |

UNIQUE: `(user_id, template_habit_item_id)` — một template tối đa 1 lần/user (custom = null, MySQL cho nhiều null).

## 17. `habit_entries` — ô kết quả
| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK | |
| user_id | BIGINT UNSIGNED | FK→users CASCADE | |
| user_habit_item_id | BIGINT UNSIGNED | FK→user_habit_items CASCADE | |
| entry_date | DATE | NOT NULL | |
| status | ENUM('done','missed') | NOT NULL | ✓ / ✗ ; empty = không có row |
| created_at / updated_at | TIMESTAMP | | |

UNIQUE: `(user_id, user_habit_item_id, entry_date)`.

## 18. `habit_entry_histories` — lịch sử đổi ô
| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK | |
| user_id | BIGINT UNSIGNED | FK→users CASCADE | |
| user_habit_item_id | BIGINT UNSIGNED | FK→user_habit_items CASCADE | |
| entry_date | DATE | NOT NULL | |
| from_status | VARCHAR(16) | nullable | |
| to_status | VARCHAR(16) | nullable | |
| source | VARCHAR(32) | default `web` | |
| changed_at | TIMESTAMP | NOT NULL | |
| created_at / updated_at | TIMESTAMP | | |

> Đặc tả: [`features/habit-tracker.md`](features/habit-tracker.md).

---

## 19. `dishes` — kho món (Hôm nay ăn gì)

| Cột | Kiểu | Ràng buộc | Ghi chú |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK | |
| name | VARCHAR(150) | NOT NULL | |
| slug | VARCHAR(180) | UNIQUE, NOT NULL | |
| emoji | VARCHAR(16) | nullable | hiển thị list/popup |
| summary | VARCHAR(500) | nullable | |
| meal_slots | JSON | NOT NULL | `["breakfast","lunch","dinner"]` |
| supports_light | BOOLEAN | default false | ăn nhẹ |
| supports_main | BOOLEAN | default true | ăn chính |
| supports_dine_out | BOOLEAN | default true | ăn ngoài |
| supports_cook_home | BOOLEAN | default true | tự nấu |
| dish_role | VARCHAR(32) | nullable | soup/main_protein/side_veg/… — chỉ khi verified |
| culinary_regions | JSON | nullable | region_tags: `bac`/`trung`/`nam`/`tay_nguyen`/`quoc_gia`/`hoa_viet`/`ngoai` |
| five_element | VARCHAR(16) | nullable | wood/fire/earth/metal/water — verified only |
| thermal_nature | VARCHAR(16) | nullable | cold/cool/neutral/warm/hot — verified only |
| protein_source | VARCHAR(16) | nullable | meat/seafood/egg/plant/mixed/none |
| cooking_method | VARCHAR(16) | nullable | boil/steam/grill/fry/raw/braise/soup_base/mixed |
| flavor_tags | JSON | nullable | vd `["spicy","sour"]` |
| calories_kcal | SMALLINT UNSIGNED | nullable | kcal của **khẩu phần chuẩn** (verified) |
| serving_grams | SMALLINT UNSIGNED | nullable | khối lượng (g) tương ứng `calories_kcal` |
| cook_minutes | SMALLINT UNSIGNED | nullable | |
| ingredients | JSON | nullable | `[{name, amount}]` |
| steps | JSON | nullable | mảng bước nấu |
| benefits / harms / advice / notes | TEXT | nullable | tham khảo (disclaimer UI); null nếu chưa verified |
| search_keywords | VARCHAR(255) | nullable | |
| facts_meta | JSON | nullable | provenance / kb_version khi import seed |
| status | VARCHAR(20) | default `published` | draft/published/hidden |
| source | VARCHAR(20) | default `system` | system/user |
| created_by | FK users | nullable SET NULL | |
| suggest_count | INT UNSIGNED | default 0 | |
| created_at / updated_at / deleted_at | TIMESTAMP | | soft delete |

Index: `status`, `five_element`, `dish_role`, `thermal_nature`, `(supports_light, supports_main)`, `(supports_dine_out, supports_cook_home)`.

> Đặc tả: [`features/what-to-eat.md`](features/what-to-eat.md). UI: **popup trên Kho**.  
> Chuẩn seed/fact: [`features/what-to-eat-seed-and-kb.md`](features/what-to-eat-seed-and-kb.md)
> (verified-only; null = chưa xác thực). Rules:
> [`features/what-to-eat-ruleset.md`](features/what-to-eat-ruleset.md).

---

## 19b. `dish_contributions` — đóng góp tri thức món

| Cột | Kiểu | Ghi chú |
|---|---|---|
| dish_id | FK dishes CASCADE | |
| user_id | FK users SET NULL | |
| type | VARCHAR(32) | recipe/calories/harm/benefit/advice/note/five_element |
| payload | JSON | theo type |
| status | pending/approved/rejected | |
| is_canonical | BOOLEAN | bản chính sau duyệt |
| reviewed_by | FK admins nullable | |
| reviewed_at | TIMESTAMP nullable | |

## 19c. `meal_suggestion_logs` — lịch sử gợi ý (riêng tư)

| Cột | Kiểu | Ghi chú |
|---|---|---|
| user_id | FK users CASCADE | |
| meal_slot / meal_size / meal_mode | VARCHAR | context |
| filters_json | JSON | count, lat/lng… |
| suggested_dish_ids | JSON | |
| chosen_dish_id | FK dishes nullable | |
| outcome | suggested/chosen | |
| created_at | TIMESTAMP | không updated_at |

## 19d. `user_food_preferences` — sở thích ăn uống (1-1 user)

| Cột | Kiểu | Ghi chú |
|---|---|---|
| user_id | UNIQUE FK users | |
| diet_flags | JSON | vegetarian… |
| disliked_dish_ids | JSON | |
| preferred_elements | JSON | |
| default_meal_mode | VARCHAR nullable | |
| max_calories_default | SMALLINT nullable | |
| balance_elements | BOOLEAN | boost ngũ hành 7 ngày |

---

## 20. Sơ đồ khoá ngoại (tóm tắt)

```
users ──1:1── user_profiles
users ──1:*── experiences ──*:1── categories
                  │  ├──*:*── tags ──*:1── categories (nullable)
                  │  ├──1:*── media
                  │  ├──1:*── comments ──*:1── users
                  │  └──1:*── reactions (morph) ──*:1── users
users ──1:*── user_habit_items ──*:1── habit_items (optional template)
users ──1:*── habit_entries ──*:1── user_habit_items
users ──1:*── habit_entry_histories ──*:1── user_habit_items
users ──*:1── sample_avatars (optional)
users ──*:1── avatar_frames (optional)
users ──1:*── premium_subscriptions ──*:1── admins (granted_by)
dishes (catalog; optional created_by → users)
admins (guard admin, roles/permissions riêng)
taste_traits ── (tham chiếu logic từ user_profiles JSON)
```

## 21. Chiến lược index & hiệu năng
- Tìm quanh đây: index `(latitude, longitude)`; ở quy mô lớn cân nhắc spatial index
  (`POINT` + `SPATIAL INDEX`) — ghi ADR nếu chuyển.
- Feed công khai: index `(status, published_at)`.
- Đếm reaction/rating: dùng **cột cache** trên `experiences` (cập nhật qua Observer),
  tránh `COUNT` mỗi lần tải.
- Habit grid: nạp entries theo `(user_id, entry_date BETWEEN month)`; history phân trang
  theo `changed_at`.
- What-to-eat: filter `dishes` theo flags + `whereJsonContains(meal_slots)`; seed đủ ma trận.
