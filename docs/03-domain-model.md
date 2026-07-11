# 03 — Mô hình miền nghiệp vụ (Domain Model)

Tài liệu này mô tả **các thực thể nghiệp vụ và quan hệ**, không đi vào chi tiết cột
SQL (xem [`04-database-schema.md`](04-database-schema.md)).

## 1. Sơ đồ quan hệ (ERD dạng text)

```
                    ┌──────────────┐
                    │    Admin     │  (guard: admin, Sanctum)
                    └──────────────┘
                          │ 1
                          │ manages / moderates
                          ▼ *
┌──────────────┐   *  ┌────────────────┐  *   ┌──────────────┐
│    User      │──────│   Experience   │──────│   Category   │
│ (guard: web) │ 1  * │  (bài trải     │ *  1 │ (danh mục)   │
│              │      │   nghiệm)      │      └──────────────┘
│  + Profile   │      └───────┬────────┘             │ 1
│  (taste)     │              │                       │ * (tag thuộc category)
└──────┬───────┘              │                       ▼
       │                      │  * ── belongsToMany ──┐
       │                      │                       │
       │ 1                    │                 ┌──────────────┐
       │                      │                 │     Tag      │
       │ * (reactions,        │                 │ (thẻ)        │
       │    comments)         │                 └──────────────┘
       ▼                      ▼
┌──────────────┐      ┌────────────────┐
│  Reaction    │      │   Comment      │
│ (like/tim)   │      │ (bình luận +   │
│ polymorphic  │      │  rating)       │
└──────────────┘      └────────────────┘

Ảnh:  Experience 1 ──── * Media (ảnh)
Theo dõi gu: User * ──── * User (qua taste-match, tính toán; không lưu quan hệ cứng ở v1)
```

## 2. Danh sách thực thể

### 2.1 User (người dùng cuối)
Tài khoản công khai của người chia sẻ/khám phá trải nghiệm.
- Thuộc guard `web`. **Không** phải admin.
- Có **UserProfile** (1-1) chứa: `bio`, `personality` (tính cách), `interests`
  (sở thích) — nền tảng cho taste-matching.
- Sở hữu nhiều **Experience**, **Comment**, **Reaction**.

### 2.2 Admin (quản trị viên)
Tài khoản quản trị, đăng nhập admin panel.
- Thuộc guard `admin`, xác thực Sanctum.
- Có **role** (spatie/permission): `super-admin`, `moderator`, …
- Quản lý User, Experience, Category, Tag; kiểm duyệt Comment.
- **Bảng riêng** (`admins`), không trộn với `users`.

### 2.3 Experience (trải nghiệm) — thực thể trung tâm
Một bài chia sẻ về một địa điểm/trải nghiệm.
- `belongsTo` **User** (tác giả).
- `belongsTo` **Category** (một danh mục chính).
- `belongsToMany` **Tag** (nhiều thẻ).
- `hasMany` **Media** (ảnh).
- `hasMany` **Comment**, `morphMany` **Reaction**.
- Chứa **địa chỉ** (`address`) + **toạ độ** (`latitude`, `longitude`) + `place_name`.
- Có `status`: `draft`, `published`, `pending`, `hidden` (kiểm duyệt).
- Có `slug` cho URL SEO.

### 2.4 Category (danh mục)
Phân loại cấp cao: Du lịch, Ăn, Uống, Cà phê, Lưu trú…
- `hasMany` **Experience**.
- `hasMany` **Tag** (thẻ thuộc danh mục — xem quy tắc bên dưới).
- Quản lý bởi Admin. Có `slug`, `icon`, `sort_order`, `is_active`.

### 2.5 Tag (thẻ tuỳ chỉnh)
Nhãn chi tiết, **gắn theo danh mục**: "món Hàn", "món Nhật" (thuộc Ăn); "biển",
"núi" (thuộc Du lịch); "yên tĩnh", "sống ảo" (dùng chung).
- `belongsTo` **Category** *hoặc* `category_id = null` cho **thẻ toàn cục** (dùng
  mọi danh mục). Xem quy tắc §3.
- `belongsToMany` **Experience**.

### 2.6 Comment (bình luận + đánh giá)
Ý kiến của User trên một Experience, **kèm rating sao (1–5) tuỳ chọn**.
- `belongsTo` **User**, `belongsTo` **Experience**.
- `rating` nullable (bình luận có thể không kèm sao).
- Hỗ trợ **trả lời lồng nhau** 1 cấp (`parent_id`) — tuỳ chọn ở v1.
- Có `status` để kiểm duyệt: `visible`, `hidden`, `pending`.

### 2.7 Reaction (cảm xúc: like, tim)
Thả cảm xúc lên Experience (và có thể mở rộng lên Comment).
- **Polymorphic** (`reactable_type`, `reactable_id`) để tái sử dụng.
- `type`: `like`, `love` (tim) — enum mở rộng được.
- **Ràng buộc duy nhất:** một User chỉ có **một** reaction cho mỗi (đối tượng) →
  đổi cảm xúc = update, bỏ = delete.

### 2.8 UserProfile (hồ sơ gu)
1-1 với User, chứa dữ liệu taste-matching.
- `bio` (giới thiệu tự do).
- `personality` (tính cách) — tập nhãn (vd: hướng nội, thích phiêu lưu…).
- `interests` (sở thích) — tập nhãn (vd: ẩm thực, nhiếp ảnh, leo núi…).
- Có thể liên kết với **Tag/danh mục** ưa thích để tính tương đồng.

### 2.9 Media (ảnh)
Ảnh của Experience.
- `belongsTo` **Experience**.
- `path`, `disk`, `width`, `height`, `sort_order`, `is_cover`.

### 2.10 SampleAvatar (avatar mẫu)
Catalog ảnh đại diện có sẵn để user chọn (không bắt buộc upload).
- `slug`, `name`, `path` (file tĩnh trong `public/`), `sort_order`, `is_active`.

### 2.11 AvatarFrame (khung avatar)
Catalog khung trang trí quanh avatar (kiểu Discord/LoL).
- `effect_type` + `effect_config` (JSON) — engine CSS cố định, config tham số hoá.
- `is_premium`, `show_badge`, `sort_order`, `is_active`.
- Scale: thêm khung = thêm row; không hard-code enum khung.

### 2.12 PremiumSubscription (gói Premium)
Đăng ký Premium theo **thời hạn**.
- `starts_at`, `ends_at` (null = lifetime), `status`, `source`.
- User có `premium_expires_at` denormalized để check O(1).
- Admin grant / extend / cancel.

## 3. Quy tắc miền quan trọng (business rules)

1. **User ≠ Admin:** hai loại tài khoản tách bảng, tách guard, tách endpoint.
2. **Thẻ theo danh mục:** mỗi Tag thuộc một Category, **hoặc** là thẻ toàn cục
   (`category_id = null`). Khi gắn thẻ cho Experience, UI **NÊN** chỉ gợi ý thẻ thuộc
   danh mục đã chọn + thẻ toàn cục.
3. **Một Experience có đúng một Category** nhưng **nhiều Tag**.
4. **Reaction duy nhất:** mỗi (User, đối tượng) chỉ 1 reaction; đổi loại = update.
5. **Rating gộp:** điểm trung bình của Experience = trung bình `rating` các Comment có
   rating; lưu cache `rating_avg`, `rating_count` trên Experience (cập nhật khi
   comment thay đổi — qua event/observer).
6. **Kiểm duyệt:** Experience và Comment có `status`; nội dung `hidden`/`pending`
   không hiển thị công khai.
7. **Toạ độ bắt buộc khi published:** Experience `published` phải có `latitude` &
   `longitude` hợp lệ.
8. **Taste-match không lưu quan hệ cứng ở v1:** điểm tương đồng tính khi cần (hoặc
   cache tạm), không tạo bảng "friendship". Xem
   [`features/taste-matching.md`](features/taste-matching.md).
9. **Khung premium chỉ khi Premium còn hạn:** `premium_expires_at > now()`. Hết hạn
   → fallback khung free / none. Xem [`features/avatar-and-premium.md`](features/avatar-and-premium.md).

## 4. Vòng đời trạng thái Experience

```
draft ──(user submit)──► pending ──(admin duyệt)──► published
  ▲                          │                          │
  └──────(user sửa)──────────┘                          │
                                                (admin ẩn) ▼
                                                        hidden
```

> Nếu bỏ kiểm duyệt trước ở v1, có thể cho `draft → published` trực tiếp và admin ẩn
> khi cần. Ghi rõ lựa chọn trong tài liệu tính năng.

## 5. Bảng thuật ngữ liên quan
Xem [`11-glossary.md`](11-glossary.md) cho: Experience, Taste profile, Reaction, Guard…
