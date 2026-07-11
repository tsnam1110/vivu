# ViVu

**Nền tảng chia sẻ & lưu trữ trải nghiệm cộng đồng** — quán ăn, quán uống, địa điểm du
lịch, cà phê… gắn với bản đồ, danh mục, thẻ, đánh giá và **tìm người cùng gu**.

## Tổng quan nhanh

| Thành phần | Công nghệ | URL local (mặc định) |
|---|---|---|
| **Giao diện người dùng (public)** | Blade + Tailwind 4 + Alpine | **http://127.0.0.1:8000/** |
| **Admin panel** | React 19 + Ant Design 6 + Vite + TS | **http://localhost:5200/** |
| Backend / API | Laravel 13 (PHP 8.3+), MySQL 8 | http://127.0.0.1:8000/api |
| Bản đồ | Google Maps JS API (tuỳ chọn key) | — |
| Auth | `web` session (user) · `admin` Sanctum Bearer | 2 guard tách biệt |

> Public site cũng có thể qua vhost Laragon: **http://vivu.test/**  
> Cổng Vite **5201** chỉ phục vụ CSS/JS dev — **không** phải trang người dùng.

## Tính năng chính

- Đăng trải nghiệm gắn địa chỉ + vị trí bản đồ
- Phân loại theo **danh mục** + **thẻ tuỳ chỉnh**
- **Bình luận + đánh giá sao**, **cảm xúc** (like/tim)
- **Chia sẻ** (Web Share / clipboard) + Open Graph
- **Hồ sơ gu** (tính cách + sở thích) và **tìm người cùng gu**
- **Admin panel** quản lý & kiểm duyệt

## Yêu cầu

- PHP 8.3+, Composer 2
- Node.js 20.19+, npm
- MySQL 8 (Laragon khuyến nghị)

## Cài đặt nhanh

```bash
composer install
cp .env.example .env
php artisan key:generate

# Tạo DB `vivu`, cấu hình DB_* trong .env, rồi:
php artisan migrate --seed
php artisan storage:link

npm install
cd admin && npm install && cp .env.example .env && cd ..
# admin/.env: VITE_API_BASE_URL=http://127.0.0.1:8000/api
```

## Khởi chạy local (một lệnh ở root)

```bash
composer dev
# hoặc: npm run dev
```

| Process | URL / vai trò |
|---|---|
| Public site + API | **http://127.0.0.1:8000/** |
| Admin SPA | **http://localhost:5200/** |
| Vite public (HMR assets) | http://localhost:5201/ (dev only) |
| Queue worker | nền |

Chi tiết đầy đủ, route, troubleshooting: **[`docs/06-setup-development.md`](docs/06-setup-development.md)**.

### Tài khoản mặc định (seeder)

| Vai trò | Email | Mật khẩu | Vào đâu |
|---|---|---|---|
| Admin | `admin@vivu.test` | `password` | http://localhost:5200/login |
| User | *(tự đăng ký)* | — | http://127.0.0.1:8000/register |

> Đổi ngay trên môi trường không phải local.

### Route public thường dùng

| Path | Mô tả |
|---|---|
| `/` | **Kho cá nhân** (đã login) / landing (khách) |
| `/explore` | Khám phá cộng đồng |
| `/login`, `/register` | Auth người dùng |
| `/experiences/{slug}` | Chi tiết trải nghiệm |
| `/u/{username}` | Hồ sơ công khai |
| `/matches` | Tìm người cùng gu (đã đăng nhập) |
| `/profile/edit` | Sửa hồ sơ + gu |

## Kiểm thử

```bash
php artisan test
```

Môi trường test: SQLite in-memory (`phpunit.xml`). CI: `.github/workflows/ci.yml`.

## API nhanh

| Prefix | Guard | Mô tả |
|---|---|---|
| `/api/*` | public / `auth:web` | API public + user |
| `/api/admin/*` | `auth:admin` | Admin SPA (Bearer token) |

Chi tiết: [`docs/05-api-conventions.md`](docs/05-api-conventions.md).

## Tài liệu

> 📚 Toàn bộ tài liệu: [`docs/`](docs/) — bắt đầu [`docs/README.md`](docs/README.md).  
> 🤖 **AI Agent:** [`CLAUDE.md`](CLAUDE.md) → [`docs/08-agent-playbook.md`](docs/08-agent-playbook.md).  
> 🤝 Đóng góp: [`CONTRIBUTING.md`](CONTRIBUTING.md).

## Trạng thái

Đã scaffold & hiện thực M1–M6 cốt lõi (backend + public + admin + tests).
Xem checklist [`CLAUDE.md` §6](CLAUDE.md).
