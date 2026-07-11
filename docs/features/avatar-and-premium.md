# Avatar, khung & Premium

## 1. Mục tiêu

Cho phép người dùng:
- Đổi **ảnh đại diện** (tải lên **hoặc** chọn **avatar mẫu**).
- Chọn **khung avatar** (kiểu Discord / LoL) — khung premium chỉ khi còn Premium active.
- Admin quản lý **đăng ký Premium** (thời hạn) và **catalog khung** (có xem trước hiệu ứng).

## 2. Thực thể

| Thực thể | Mô tả |
|---|---|
| **SampleAvatar** | Ảnh mẫu có sẵn; user chọn thay vì upload |
| **AvatarFrame** | Catalog khung (effect engine + config JSON) |
| **PremiumSubscription** | Lịch sử/gói Premium của user (starts_at, ends_at) |
| **User** | `avatar_path`, `sample_avatar_id`, `avatar_frame_id`, `premium_expires_at` (cache) |

## 3. Quy tắc nghiệp vụ

1. **Avatar hiển thị:** ưu tiên `avatar_path` (upload); nếu null và có `sample_avatar_id` → dùng sample; không thì initials.
2. **Chọn sample:** gán `sample_avatar_id`, **xoá** file upload cũ (nếu có) và clear `avatar_path`.
3. **Upload:** lưu crop 400×400 JPEG, clear `sample_avatar_id`.
4. **Khung free** (`is_premium = false`) — mọi user active.
5. **Khung premium** — chỉ khi `user->hasActivePremium()` (`premium_expires_at > now()` hoặc subscription lifetime).
6. Hết hạn Premium: khung premium **không render** (fallback none/soft free); user có thể chọn lại free.
7. Admin **grant/extend/cancel** Premium → ghi `premium_subscriptions` + sync `users.premium_expires_at`.
8. **Không** inject CSS tùy ý từ admin: chỉ `effect_type` (whitelist engine) + `effect_config` (màu, tốc độ…).

## 4. Effect engines (tối ưu scale khung)

Thêm khung = **insert row**, không sửa code. Thêm **loại hiệu ứng mới** = thêm engine CSS + enum (hiếm).

| effect_type | Mô tả |
|---|---|
| `soft` | Viền tĩnh nhẹ |
| `gradient` | Vòng gradient tĩnh |
| `spin` | Viền gradient xoay (Discord-like) |
| `glow` | Hào quang nhịp (pulse) |
| `holographic` | Shimmer / iridescent |

Config mẫu (`effect_config` JSON):
```json
{
  "colors": ["#fbbf24", "#f59e0b", "#d97706"],
  "thickness": 3,
  "speed_ms": 3000,
  "intensity": 0.7
}
```

## 5. Seed ban đầu (~5 khung)

| slug | is_premium | effect_type | Tên |
|---|---|---|---|
| soft | false | soft | Viền mềm |
| gold | true | spin | Hoàng kim |
| aurora | true | spin | Cực quang |
| crystal | true | holographic | Pha lê |
| ember | true | glow | Than hồng |

## 6. API / Routes

### Public (web session)
```
PATCH  /profile/account     # name, username, avatar|sample_avatar_id, avatar_frame_id, remove_avatar
POST   /profile/premium-demo  # (tuỳ chọn) bật demo Premium N ngày — chỉ local/dev
```

### Admin
```
GET|POST       /api/admin/avatar-frames
GET|PATCH|DELETE /api/admin/avatar-frames/{id}
GET|POST       /api/admin/premium-subscriptions
PATCH          /api/admin/premium-subscriptions/{id}   # cancel / extend
GET|POST|PATCH|DELETE /api/admin/sample-avatars
PATCH          /api/admin/users/{user}/premium          # grant nhanh từ user
```

## 7. Admin UI

1. **Khung avatar** — CRUD + **live preview** (render engine + config).
2. **Premium** — danh sách subscription, grant theo user/email, thời hạn (ngày/tháng/lifetime), huỷ/gia hạn.
3. **Users** — cột Premium + nút grant nhanh.

## 8. Hiệu năng

- Catalog frames/samples: cache `Cache::remember('avatar_frames:active', …)` invalidate khi admin CRUD.
- `premium_expires_at` denormalized trên `users` — O(1) khi render avatar (không join mỗi request).
- Eager load `avatarFrame`, `sampleAvatar` khi list user có avatar.
- CSS engines dùng CSS variables từ config — một stylesheet dùng chung mọi khung.
