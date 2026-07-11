# 06 — Cài đặt & Môi trường phát triển

Hướng dẫn dựng môi trường dev trên **Laragon (Windows)** và chạy local.
Phiên bản stack: [`02-tech-stack.md`](02-tech-stack.md).

> 💡 **Người mới vào dự án:** làm lần lượt mục 1 → 6, rồi mở bảng **“Truy cập giao diện”** ở mục 6.

---

## 1. Yêu cầu

| Thành phần | Phiên bản / ghi chú |
|---|---|
| Laragon | Apache/Nginx + MySQL + PHP (khuyến nghị) |
| PHP | **8.3+** (khuyến nghị 8.4). Laragon: *Menu → PHP → Version* |
| Composer | 2.x |
| Node.js | **≥ 20.19** + npm (Vite 8) |
| MySQL | 8+ |
| Google Maps API | Tuỳ chọn — xem [`features/maps-and-location.md`](features/maps-and-location.md) |

---

## 2. Lấy mã nguồn & cài phụ thuộc

```bash
# Thư mục gốc dự án (ví dụ)
# D:\laragon\www\vivu   hoặc   F:\laragon\www\My_Project\ViVu

composer install
cp .env.example .env
php artisan key:generate

# JS — public (Blade assets) + admin SPA
npm install
cd admin
npm install
cp .env.example .env
cd ..
```

`admin/.env` mặc định:

```dotenv
VITE_API_BASE_URL=http://127.0.0.1:8000/api
```

---

## 3. Cấu hình `.env` (root)

Điền tối thiểu (đủ biến xem [`.env.example`](../.env.example)):

```dotenv
APP_NAME=ViVu
APP_URL=http://127.0.0.1:8000
# Nếu dùng vhost Laragon: APP_URL=http://vivu.test
APP_TIMEZONE=Asia/Ho_Chi_Minh

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vivu
DB_USERNAME=root
DB_PASSWORD=

GOOGLE_MAPS_API_KEY=
GOOGLE_MAPS_SERVER_KEY=

# Chỉ domain dùng session/cookie (public site).
# Admin SPA dùng Bearer token — KHÔNG thêm localhost:5200 vào đây (gây HTTP 419).
SANCTUM_STATEFUL_DOMAINS=vivu.test,localhost,127.0.0.1,127.0.0.1:8000
```

---

## 4. Tạo CSDL & migrate

```bash
# Tạo database `vivu` (HeidiSQL / phpMyAdmin / CLI), rồi:
php artisan migrate --seed
php artisan storage:link
```

Seeder tạo: categories, tags, taste traits, **admin mặc định**.

> `php artisan migrate:fresh --seed` **chỉ** dùng ở local — xoá sạch dữ liệu.

---

## 5. Khởi chạy local (một lệnh ở root)

```bash
composer dev
# tương đương:
npm run dev
```

Một lệnh chạy đồng thời:

| Process | Cổng / vai trò |
|---|---|
| `php artisan serve` | **8000** — API + **giao diện người dùng (public Blade)** |
| Queue worker | job ảnh, mail, … |
| Vite public | **5201** — HMR CSS/JS cho public (không mở để “vào site”) |
| Admin SPA (Vite) | **5200** — giao diện quản trị React |

Dừng: `Ctrl+C` trong terminal đó.

### Chạy riêng (khi debug)

```bash
npm run dev:vite     # chỉ public Vite :5201
npm run dev:admin    # chỉ admin :5200
php artisan serve --host=127.0.0.1 --port=8000
php artisan queue:work
```

### Build production assets

```bash
npm run build          # public
npm run build:admin    # admin
npm run build:all      # cả hai
```

> `php artisan pail` (log realtime) cần extension `pcntl` — **không** có trên Windows PHP thông thường, nên không gắn vào `composer dev`.

---

## 6. Truy cập giao diện

### Bản đồ URL (local mặc định)

| Giao diện | URL | Công nghệ | Ghi chú |
|---|---|---|---|
| **Người dùng (public)** | **http://127.0.0.1:8000/** | Blade + Tailwind + Alpine | Site chính, SEO |
| Public (Laragon vhost) | **http://vivu.test/** | như trên | Cần bật Auto virtual hosts |
| **Admin panel** | **http://localhost:5200/** | React + Ant Design | SPA riêng, Bearer token |
| API | http://127.0.0.1:8000/api/… | Laravel | Admin gọi qua `VITE_API_BASE_URL` |
| Vite public (dev only) | http://localhost:5201/ | Vite | Chỉ phục vụ asset HMR, **không** phải trang user |

> ⚠️ Đừng nhầm: cổng **5201** không phải giao diện người dùng. User vào **8000** (hoặc `vivu.test`).

### Route public (người dùng) — quan trọng

| URL | Mô tả | Auth |
|---|---|---|
| `/` | **Kho cá nhân** (đã đăng nhập) hoặc landing (khách) — mặc định | Guest / User |
| `/explore` | Khám phá trải nghiệm công khai | Công khai |
| `/experiences/{slug}` | Chi tiết trải nghiệm | Công khai |
| `/u/{username}` | Hồ sơ công khai | Công khai |
| `/login` | Đăng nhập user | Guest |
| `/register` | Đăng ký user | Guest |
| `/experiences/create/new` | Đăng trải nghiệm mới | User (`web`) |
| `/profile/edit` | Sửa hồ sơ + taste profile (+ đăng xuất) | User |
| `/matches` | Tìm người cùng gu | User |
| `/terms`, `/privacy` | Điều khoản / bảo mật | Công khai |

> Ưu tiên sản phẩm: **lưu trữ cá nhân** trước, khám phá là phụ. Menu floating iOS ở cuối màn hình.

Source routes: `routes/web.php`.

### Route admin (SPA)

| URL (trên :5200) | Mô tả |
|---|---|
| `/login` | Đăng nhập admin |
| `/experiences` | Duyệt / ẩn trải nghiệm |
| `/comments` | Kiểm duyệt bình luận |
| `/users` | Quản lý user |
| `/categories`, `/tags`, `/taste-traits` | Danh mục / thẻ / nhãn gu |

API admin: `POST /api/admin/login`, các endpoint `/api/admin/*` (Bearer).

### Tài khoản mặc định (seeder)

| Vai trò | Email | Mật khẩu | Đăng nhập ở |
|---|---|---|---|
| **Admin** | `admin@vivu.test` | `password` | http://localhost:5200/login |
| User public | *(không seed)* | — | Tự đăng ký tại `/register` |

> Đổi mật khẩu admin ngay trên môi trường không phải local.

### Virtual host Laragon (tuỳ chọn)

1. Bật *Auto virtual hosts*; domain `vivu.test` trỏ `public/`.
2. Reload Laragon.
3. Đặt `APP_URL=http://vivu.test` và cập nhật `SANCTUM_STATEFUL_DOMAINS` nếu cần.
4. Public site: **http://vivu.test/** — Admin vẫn thường chạy **http://localhost:5200** (Vite).
5. Khi dùng vhost thay vì `artisan serve`, chỉnh `admin/.env` → `VITE_API_BASE_URL=http://vivu.test/api` và đảm bảo API CORS/Sanctum phù hợp.

---

## 7. Lệnh hữu ích

```bash
php artisan migrate:status
php artisan tinker
php artisan pint               # format PHP
php artisan test               # test backend
php artisan optimize:clear     # xoá cache config/route/view
php artisan storage:link       # symlink storage → public/storage
```

Admin SPA:

```bash
cd admin
npm run lint
npx tsc --noEmit
```

---

## 8. Sự cố thường gặp

| Triệu chứng | Cách xử lý |
|---|---|
| Admin: “Email hoặc mật khẩu không đúng” nhưng credential đúng | API chưa chạy — chạy `composer dev`. Form cũ gộp lỗi mạng thành sai mật khẩu. |
| Admin: **HTTP 419** | **Không** thêm `localhost:5200` vào `SANCTUM_STATEFUL_DOMAINS`. `php artisan config:clear`. |
| Không kết nối API (admin) | Kiểm `admin/.env` → `VITE_API_BASE_URL=http://127.0.0.1:8000/api` và process server :8000. |
| Port 8000 / 5200 / 5201 đã dùng | Tắt process cũ, hoặc đổi port trong `vite.config.*` / `artisan serve --port`. |
| Public CSS/JS không hot-reload | Cần Vite public (:5201) đang chạy trong `composer dev`. |
| Ảnh không hiện | `php artisan storage:link` |
| Queue không chạy job | `composer dev` đã gồm queue; hoặc `php artisan queue:work` |
| Lỗi PHP version | Chọn PHP 8.3/8.4 trong Laragon; `php -v` |
| Vite / Node lỗi | Node ≥ 20.19 |
| Map không hiện | `GOOGLE_MAPS_API_KEY` + whitelist referrer |
| `php artisan pail` fail trên Windows | Bình thường (thiếu `pcntl`) — không dùng trong stack mặc định |

---

## 9. Sơ đồ nhanh (local)

```
Trình duyệt
  │
  ├─ http://127.0.0.1:8000/     → Public site (Blade) + /api/*
  │                                 assets dev ← Vite :5201
  │
  └─ http://localhost:5200/     → Admin SPA (React)
                                    API Bearer → http://127.0.0.1:8000/api/admin/*
```
