# 02 — Ngăn xếp công nghệ

Danh sách công nghệ, phiên bản mục tiêu và lý do. Khi nâng cấp phiên bản lớn, cập
nhật bảng này.

## 1. Backend

| Hạng mục | Lựa chọn | Phiên bản mục tiêu | Ghi chú |
|---|---|---|---|
| Ngôn ngữ | PHP | **8.3+** (khuyến nghị 8.4) | Laravel 13 yêu cầu tối thiểu PHP 8.3 |
| Framework | Laravel | **13.x** | Ra mắt 03/2026, latest stable |
| CSDL | MySQL | **8.0+** | charset `utf8mb4`, collation `utf8mb4_unicode_ci` |
| Xác thực API | Laravel Sanctum | mới nhất (đi kèm Laravel 13) | Token cho admin SPA |
| Queue | database driver → Redis | — | v1 dùng `database`; scale thì Redis |
| Cache | file → Redis | — | |
| Storage | local (`public` disk) → S3 | — | Ảnh trải nghiệm |
| Ảnh | Intervention Image | 3.x | Resize/optimize trong queue |
| Slug | `spatie/laravel-sluggable` | mới nhất | URL SEO cho experience |
| Quyền | `spatie/laravel-permission` | mới nhất | Vai trò admin (super, moderator…) |

## 2. Frontend — Admin panel

| Hạng mục | Lựa chọn | Phiên bản mục tiêu | Ghi chú |
|---|---|---|---|
| Framework | React | **19.x** | AntD 6 hỗ trợ React 18+; dùng React 19 stable |
| Ngôn ngữ | TypeScript | 5.x | `strict: true` |
| UI kit | Ant Design | **6.x** | Ra mắt 11/2025; tương thích ngược v5, bỏ hỗ trợ IE |
| Build tool | Vite | **8.x** | Admin SPA port **5200** |
| HTTP client | Axios | mới nhất | Interceptor gắn Bearer token |
| State/Data | TanStack Query (React Query) | 5.x | Cache & sync dữ liệu server |
| Router | React Router | 7.x | |
| Form | AntD Form | — | `Input.Password` có eye toggle |
| UI template | **Không** (custom layout) | — | Ant Design components, không Pro/CoreUI |

Thư mục: `admin/` (dự án Vite riêng). Dev: http://localhost:5200 · API Bearer.

> Hướng dẫn dùng AntD / layout admin: [`15-design-system.md`](15-design-system.md) §7.

## 3. Frontend — Public site

| Hạng mục | Lựa chọn | Ghi chú |
|---|---|---|
| Template engine | Blade | Render server-side, SEO |
| CSS | Tailwind CSS 4.x | Utility-first; CSS-first (`@theme`); palette **teal + stone** |
| JS tương tác | Alpine.js 3.x | Password toggle, reaction, share, UI nhẹ |
| Build | Vite 8 (Laravel plugin) | Public HMR port **5201** |
| Shell UI | Floating iOS tab bar + sticky footer | `layouts/app.blade.php` |
| Bản đồ | Google Maps JS API | Places Autocomplete + Geocoding |

> Design system public (token, IA, component): [`15-design-system.md`](15-design-system.md).

## 4. Bản đồ & dịch vụ ngoài

| Dịch vụ | Dùng cho | Khoá |
|---|---|---|
| Google Maps JavaScript API | Hiển thị bản đồ, marker | `GOOGLE_MAPS_API_KEY` |
| Google Places API | Autocomplete địa chỉ | (cùng key, bật API) |
| Google Geocoding API | Chuyển address ↔ toạ độ | (cùng key) |

> ⚠️ Giới hạn key theo domain/HTTP referrer (client) và IP (server-side geocoding).
> Không lộ key server-side ra client. Xem [`features/maps-and-location.md`](features/maps-and-location.md).

## 5. Công cụ chất lượng

| Công cụ | Mục đích |
|---|---|
| Pint (Laravel) | Format PHP theo chuẩn |
| Larastan / PHPStan | Phân tích tĩnh PHP |
| Pest hoặc PHPUnit | Test backend |
| ESLint + Prettier | Lint/format TS/React |
| Vitest | Test admin (tuỳ chọn) |

## 6. Yêu cầu môi trường dev (Laragon/Windows)

- Laragon (đã có Apache/Nginx + MySQL + PHP).
- **PHP 8.3+** (khuyến nghị 8.4; kiểm tra `php -v`). Laragon có thể cần tải thêm bản
  PHP 8.3/8.4 và chọn qua *Menu → PHP → Version*.
- Composer 2.x.
- **Node.js 20 LTS trở lên** (Vite 8: Node 20.19+/22.12+) + npm.
- MySQL 8 (Laragon mặc định có; đảm bảo version ≥ 8).

> Chi tiết cài đặt, **một lệnh `composer dev`**, URL public/admin:
> [`06-setup-development.md`](06-setup-development.md).

## 7. Nguyên tắc chọn & nâng cấp thư viện

1. **Ưu tiên thư viện đã có trong hệ sinh thái Laravel/AntD** trước khi thêm mới.
2. Mỗi dependency mới **phải có lý do** ghi trong PR.
3. Không thêm thư viện chỉ để dùng 1 hàm nhỏ.
4. Nâng cấp major version → đọc changelog, chạy full test, cập nhật tài liệu này.
