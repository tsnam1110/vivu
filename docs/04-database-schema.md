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
| avatar_path | VARCHAR(255) | nullable | |
| status | ENUM('active','suspended') | default 'active' | admin có thể khoá |
| created_at / updated_at / deleted_at | TIMESTAMP | | soft delete |

Index: `username`, `email`.

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
| is_matchable | BOOLEAN | default true | tham gia gợi ý "người cùng gu" |
| created_at / updated_at | TIMESTAMP | | |

> `personality` & `interests` lưu **JSON mảng slug nhãn**. Danh mục nhãn chuẩn hoá
> quản lý ở bảng `taste_traits` (§10) để tìm kiếm & gợi ý nhất quán.

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
| created_at / updated_at | TIMESTAMP | | |

Index/UNIQUE: `UNIQUE(category_id, slug)` (một slug không trùng trong cùng danh mục).

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
| status | ENUM('draft','pending','published','hidden') | default 'draft' | xem vòng đời |
| rating_avg | DECIMAL(3,2) | default 0 | cache trung bình sao |
| rating_count | INT | default 0 | cache số đánh giá |
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

## 12. Bảng hệ thống (Laravel mặc định)
`password_reset_tokens`, `sessions` (guard web), `jobs` + `failed_jobs` (queue),
`personal_access_tokens` (Sanctum cho admin), `cache`, `migrations`.

---

## 13. Sơ đồ khoá ngoại (tóm tắt)

```
users ──1:1── user_profiles
users ──1:*── experiences ──*:1── categories
                  │  ├──*:*── tags ──*:1── categories (nullable)
                  │  ├──1:*── media
                  │  ├──1:*── comments ──*:1── users
                  │  └──1:*── reactions (morph) ──*:1── users
admins (guard admin, roles/permissions riêng)
taste_traits ── (tham chiếu logic từ user_profiles JSON)
```

## 14. Chiến lược index & hiệu năng
- Tìm quanh đây: index `(latitude, longitude)`; ở quy mô lớn cân nhắc spatial index
  (`POINT` + `SPATIAL INDEX`) — ghi ADR nếu chuyển.
- Feed công khai: index `(status, published_at)`.
- Đếm reaction/rating: dùng **cột cache** trên `experiences` (cập nhật qua Observer),
  tránh `COUNT` mỗi lần tải.
