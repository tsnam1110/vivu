# 05 — Quy ước thiết kế API

Áp dụng cho mọi endpoint dưới `/api`. Public site (Blade) chủ yếu dùng route `web.php`,
nhưng các tương tác động (reaction, comment) gọi API theo chuẩn dưới đây.

## 1. Nguyên tắc chung
- **RESTful**, dùng danh từ số nhiều: `/experiences`, `/comments`.
- HTTP method đúng ngữ nghĩa: `GET` (đọc), `POST` (tạo), `PATCH` (sửa một phần),
  `PUT` (thay toàn bộ), `DELETE` (xoá).
- **Không** động từ trong URL (trừ hành động đặc biệt: `POST /experiences/{id}/publish`).
- JSON in/out, `Content-Type: application/json`.
- Thời gian trả về **ISO 8601 UTC** (`2026-07-06T09:00:00Z`).

## 2. Không gian route

| Prefix | Guard/Middleware | Dùng cho |
|---|---|---|
| `/api/admin/*` | `auth:admin` (Sanctum) | Admin panel (React) |
| `/api/*` (đã đăng nhập) | `auth:web` | Public user thao tác (reaction, comment) |
| `/api/*` (công khai) | throttle, không auth | Đọc dữ liệu công khai (list experience) |

> Admin và public **không dùng chung** endpoint. Endpoint admin luôn dưới `/api/admin`.

## 3. Envelope phản hồi (response chuẩn)

### Thành công — một tài nguyên
```json
{
  "data": { "id": 1, "title": "..." },
  "meta": {}
}
```

### Thành công — danh sách (phân trang)
```json
{
  "data": [ { "id": 1 }, { "id": 2 } ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 128,
    "last_page": 9
  },
  "links": { "next": "...", "prev": null }
}
```

Dùng **API Resource** + `->response()` / `ResourceCollection` của Laravel để tạo
envelope này. Không trả model thô.

## 4. Lỗi (error) chuẩn

HTTP status đúng ngữ nghĩa + body:
```json
{
  "message": "Dữ liệu không hợp lệ.",
  "errors": {
    "title": ["Tiêu đề là bắt buộc."],
    "latitude": ["Toạ độ không hợp lệ."]
  }
}
```

| Status | Khi nào |
|---|---|
| 200 | OK |
| 201 | Tạo thành công |
| 204 | Thành công, không nội dung (vd DELETE) |
| 401 | Chưa xác thực |
| 403 | Không đủ quyền |
| 404 | Không tìm thấy |
| 422 | Validate thất bại (dùng format `errors` ở trên) |
| 429 | Quá giới hạn (throttle) |
| 500 | Lỗi máy chủ (không lộ chi tiết ra client) |

Laravel tự trả 422 đúng format khi Form Request fail — **luôn dùng Form Request**.

## 5. Phân trang, lọc, sắp xếp
- Phân trang: `?page=1&per_page=15` (mặc định 15, tối đa 50).
- Lọc: `?category=an&tags[]=mon-han&city=da-nang`.
- Tìm quanh đây: `?lat=16.05&lng=108.2&radius_km=5`.
- Sắp xếp: `?sort=-published_at` (dấu `-` = giảm dần). Whitelist trường được sort.
- Tìm kiếm text: `?q=cà+phê`.

## 6. Xác thực
- **Admin (SPA):** `Authorization: Bearer <sanctum-token>`. Login: `POST /api/admin/login`.
- **Public user:** session cookie (`auth:web`) + CSRF cho request thay đổi dữ liệu.
- Rate limit login: 5 lần / phút / IP (throttle).

## 7. Versioning
- v1 **không** đặt version trong URL (giữ đơn giản).
- Khi có breaking change bắt buộc → chuyển sang `/api/v2/*`. **Không** phá vỡ hợp đồng
  đã công bố mà không version hoá (Golden Rule #6).

## 8. Endpoint chính (bản nháp — cập nhật khi hiện thực)

### Public / user
```
GET    /api/experiences                 # list (lọc, phân trang, tìm quanh đây)
GET    /api/experiences/{slug}          # chi tiết
POST   /api/experiences                 # tạo (auth:web)
PATCH  /api/experiences/{id}            # sửa (chủ sở hữu)
DELETE /api/experiences/{id}            # xoá mềm (chủ sở hữu)

GET    /api/experiences/{id}/comments   # danh sách bình luận
POST   /api/experiences/{id}/comments   # bình luận + rating (auth:web)
DELETE /api/comments/{id}               # xoá (chủ sở hữu / admin)

POST   /api/experiences/{id}/reactions  # thả/đổi cảm xúc (auth:web)
DELETE /api/experiences/{id}/reactions  # gỡ cảm xúc

GET    /api/categories                  # danh mục active
GET    /api/tags?category=an            # thẻ theo danh mục

GET    /api/users/{username}            # hồ sơ công khai
GET    /api/users/matches               # người cùng gu (auth:web)
GET    /api/me                          # hồ sơ của tôi (auth:web)
PATCH  /api/me/profile                  # cập nhật taste profile
```

### Admin
```
POST   /api/admin/login
POST   /api/admin/logout
GET    /api/admin/users
PATCH  /api/admin/users/{id}            # khoá/mở, đổi status
PATCH  /api/admin/users/{id}/premium    # grant Premium (days | lifetime)
GET    /api/admin/experiences?status=pending
PATCH  /api/admin/experiences/{id}      # duyệt/ẩn
CRUD   /api/admin/categories
CRUD   /api/admin/tags
CRUD   /api/admin/taste-traits
CRUD   /api/admin/avatar-frames         # catalog khung + preview data
CRUD   /api/admin/sample-avatars
GET    /api/admin/premium-subscriptions
POST   /api/admin/premium-subscriptions # grant
PATCH  /api/admin/premium-subscriptions/{id}  # cancel | extend
GET    /api/admin/comments?status=pending
PATCH  /api/admin/comments/{id}         # ẩn/hiện
```

## 9. Idempotency & an toàn
- Reaction dùng **upsert** → gọi lại không tạo bản ghi trùng (ràng buộc UNIQUE).
- `DELETE` gọi lại trên tài nguyên đã xoá → trả 204/404 nhất quán, không lỗi 500.

## 10. Quy ước đặt tên field JSON
- `snake_case` trong JSON API (khớp Laravel mặc định).
- Boolean có tiền tố `is_`/`has_` (`is_cover`, `has_rating`).
- Thời gian hậu tố `_at` (`published_at`).
