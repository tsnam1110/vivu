# 01 — Kiến trúc hệ thống

## 1. Tổng quan

ViVu gồm **ba mặt tiền (frontend)** chia sẻ **một backend Laravel** duy nhất:

```
                       ┌─────────────────────────────────────┐
                       │            NGƯỜI DÙNG CUỐI            │
                       └─────────────────────────────────────┘
              ┌──────────────────────┬───────────────────────┐
              ▼                      ▼                       ▼
   ┌────────────────────┐  ┌────────────────────┐  ┌──────────────────┐
   │  Public Site       │  │  Admin Panel       │  │  Google Maps JS  │
   │  Blade + Tailwind  │  │  React + AntD SPA  │  │  (client-side)   │
   │  + Alpine.js       │  │  (Vite)            │  └──────────────────┘
   │  (SEO, session)    │  │  (Sanctum token)   │
   └─────────┬──────────┘  └─────────┬──────────┘
             │ HTTP (session)        │ HTTP (Bearer token / API)
             ▼                       ▼
   ┌─────────────────────────────────────────────────────────────────┐
   │                     LARAVEL 11 BACKEND                            │
   │  Routes: web.php (public)  |  api.php (admin+public API)          │
   │  Guards: web (users)  |  admin (Sanctum)                         │
   │  Layers: Controller → Form Request → Service → Model → DB        │
   │  Async: Queue (mail, xử lý ảnh, tính taste-match)                │
   └───────────────┬──────────────────────────┬──────────────────────┘
                   ▼                          ▼
        ┌───────────────────┐      ┌─────────────────────┐
        │   MySQL 8         │      │  Storage (ảnh)      │
        │   (dữ liệu)       │      │  local → S3 (scale) │
        └───────────────────┘      └─────────────────────┘
```

## 2. Vì sao 2 công nghệ frontend khác nhau

| Mặt tiền | Công nghệ | Lý do |
|---|---|---|
| **Public site** | Blade + Tailwind + Alpine | Nội dung trải nghiệm/địa điểm cần **SEO** (Google index), tải nhanh, render server-side. Alpine đủ cho tương tác nhẹ (reaction, share). UX: **kho cá nhân trước**, menu floating iOS. |
| **Admin panel** | React 19 + Ant Design 6 | Giao diện quản trị nhiều bảng/biểu mẫu, "app-like", cần component phong phú của AntD. Không cần SEO. **Không** dùng template admin marketplace. |

> Design system / token / shell / component: [`15-design-system.md`](15-design-system.md).

> 💡 Nếu sau này muốn public site cũng là SPA/SSR (Next/Inertia), đây là quyết định
> lớn — cập nhật tài liệu này **và** design system trước.

## 3. Ranh giới & luồng xác thực

- **Public users** dùng **session guard `web`** (cookie), route trong `web.php`.
- **Admin** dùng **Sanctum token** (SPA gọi API), route trong `api.php` dưới prefix
  `/api/admin`, bảo vệ bởi middleware `auth:admin`.
- **Public API** (dùng bởi Alpine trên public site cho reaction/comment) nằm dưới
  `/api` bảo vệ bởi `auth:web` (session + CSRF) hoặc cho phép đọc công khai.

Hai loại tài khoản **không dùng chung bảng, session, hay endpoint**. Chi tiết:
[`features/auth-and-accounts.md`](features/auth-and-accounts.md).

## 4. Kiến trúc backend theo lớp

```
HTTP Request
   │
   ▼
Route (web.php / api.php)
   │
   ▼
Middleware (auth, throttle, CSRF, guard)
   │
   ▼
Controller  ── nhận request, trả response, KHÔNG chứa business logic nặng
   │
   ▼
Form Request  ── validate + authorize
   │
   ▼
Service (App\Services)  ── business logic, giao dịch DB, gọi queue
   │
   ▼
Model / Eloquent  ── truy cập dữ liệu, quan hệ, scope
   │
   ▼
MySQL
```

Nguyên tắc:
- **Controller mỏng, Service dày.** Logic nghiệp vụ phức tạp (tính taste-match, xử lý
  ảnh, gộp reaction) nằm trong Service.
- **Trả API qua API Resource** (`App\Http\Resources`) — không trả model thô.
- **Tác vụ chậm đẩy vào Queue:** gửi mail, resize ảnh, tính lại điểm tương đồng.

Chi tiết quy ước: [`07-coding-standards.md`](07-coding-standards.md).

## 5. Các luồng chính (sequence rút gọn)

### 5.1 Đăng một trải nghiệm (public user)
1. User điền form (Blade) → chọn vị trí trên Google Maps → toạ độ + address tự điền.
2. Submit → `POST /experiences` (session, CSRF) → Form Request validate.
3. Service tạo `Experience`, gắn `category`, `tags`, lưu `latitude/longitude`, đẩy
   job resize ảnh vào queue.
4. Redirect tới trang chi tiết (SEO-friendly URL, có slug).

### 5.2 Thả reaction (like/tim)
1. User bấm icon → Alpine gọi `POST /api/experiences/{id}/reactions` (session).
2. Service upsert reaction (1 user + 1 experience + 1 loại = 1 bản ghi), trả về tổng đếm.
3. Alpine cập nhật UI không reload.

### 5.3 Tìm người cùng gu
1. User mở trang "Tìm người cùng gu".
2. `GET /api/users/matches` → Service tính điểm tương đồng taste profile (xem
   [`features/taste-matching.md`](features/taste-matching.md)).
3. Trả danh sách user xếp theo điểm.

### 5.4 Admin duyệt nội dung
1. Admin đăng nhập SPA → nhận Sanctum token.
2. SPA gọi `GET /api/admin/experiences?status=pending`.
3. Admin duyệt/ẩn → `PATCH /api/admin/experiences/{id}` → cập nhật `status`.

## 6. Môi trường & triển khai

| Môi trường | Mô tả |
|---|---|
| **Local** | Laragon (Windows). Một lệnh: `composer dev` (hoặc `npm run dev`) ở root. |
| **Staging** | (định nghĩa sau) — mirror production, dữ liệu giả. |
| **Production** | (định nghĩa sau) — HTTPS bắt buộc, queue worker chạy nền, storage → S3. |

### 6.1 URL local (mặc định)

| Thành phần | URL | Ghi chú |
|---|---|---|
| Public site (người dùng) | http://127.0.0.1:8000/ | Blade; vhost tuỳ chọn `http://vivu.test/` |
| Admin SPA | http://localhost:5200/ | React + Ant Design; Bearer token |
| API | http://127.0.0.1:8000/api | Admin: `/api/admin/*` |
| Vite public (HMR) | http://localhost:5201/ | Chỉ assets dev, không phải UI user |

Setup chi tiết: [`06-setup-development.md`](06-setup-development.md).

Biến môi trường quan trọng: `APP_KEY`, `DB_*`, `GOOGLE_MAPS_API_KEY`,
`SANCTUM_STATEFUL_DOMAINS`, `MAIL_*`, `QUEUE_CONNECTION`. Không commit `.env`.

> Admin SPA dùng **Bearer token** — **không** đưa origin admin (`localhost:5200`) vào
> `SANCTUM_STATEFUL_DOMAINS` (sẽ gây HTTP 419 CSRF).

## 7. Quyết định kiến trúc (ADR tóm tắt)

| # | Quyết định | Trạng thái | Lý do ngắn |
|---|---|---|---|
| ADR-1 | Public = Blade, Admin = React/AntD | Chấp nhận | SEO cho public, richness cho admin |
| ADR-2 | 2 guard tách biệt (web/admin) | Chấp nhận | Bảo mật & phân tách rõ ràng |
| ADR-3 | Service layer bắt buộc cho logic nghiệp vụ | Chấp nhận | Controller mỏng, dễ test |
| ADR-4 | Toạ độ lưu `DECIMAL(10,7)/(10,7)` | Chấp nhận | Đủ chính xác ~1cm, xem schema |
| ADR-5 | Taste-match thuật toán đơn giản (Jaccard/cosine) ở v1 | Chấp nhận | Minh bạch, đủ tốt, không cần ML |

> Khi thêm quyết định lớn, thêm một dòng ADR ở đây và mô tả chi tiết ở tài liệu liên quan.
