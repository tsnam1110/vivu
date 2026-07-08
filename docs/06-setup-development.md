# 06 — Cài đặt & Môi trường phát triển

Hướng dẫn dựng môi trường dev trên **Laragon (Windows)**. Xem phiên bản chuẩn ở
[`02-tech-stack.md`](02-tech-stack.md).

## 1. Yêu cầu
- Laragon (Apache/Nginx + MySQL + PHP).
- **PHP 8.3+** (khuyến nghị 8.4). Laragon: *Menu → PHP → Version* để chọn/tải.
- Composer 2.x.
- **Node.js ≥ 20.19** (Vite 7) + npm.
- MySQL 8+.
- Khoá Google Maps API (xem [`features/maps-and-location.md`](features/maps-and-location.md)).

## 2. Lấy mã nguồn & cài phụ thuộc
```bash
# Thư mục gốc: F:\laragon\www\My_Project\ViVu
composer install
cp .env.example .env
php artisan key:generate
```

## 3. Cấu hình `.env`
Điền tối thiểu (xem [`../.env.example`](../.env.example) để biết đủ biến):
```dotenv
APP_NAME=ViVu
APP_URL=http://vivu.test
APP_TIMEZONE=Asia/Ho_Chi_Minh

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vivu
DB_USERNAME=root
DB_PASSWORD=

GOOGLE_MAPS_API_KEY=your_client_key
GOOGLE_MAPS_SERVER_KEY=your_server_key   # nếu dùng geocoding server-side

SANCTUM_STATEFUL_DOMAINS=vivu.test,localhost:5173
```

## 4. Tạo CSDL & migrate
```bash
# Tạo database 'vivu' (qua HeidiSQL của Laragon hoặc lệnh)
php artisan migrate --seed     # seed: categories, taste_traits, admin mặc định
```

> `migrate:fresh --seed` **chỉ** dùng ở local — nó xoá sạch dữ liệu.

## 5. Chạy backend + public site
```bash
php artisan serve              # hoặc dùng vhost Laragon: http://vivu.test
npm install                    # phụ thuộc build Blade (Tailwind 4 + Alpine + Vite)
npm run dev                    # watch assets public
php artisan queue:work         # worker cho job (ảnh, mail, taste-match cache)
```

## 6. Chạy Admin panel (SPA)
```bash
cd admin
npm install
npm run dev                    # thường http://localhost:5173
```
Cấu hình base URL API của admin qua biến môi trường Vite (`VITE_API_BASE_URL`) trong
`admin/.env`, trỏ tới `http://vivu.test/api`.

## 7. Virtual host Laragon (khuyến nghị)
- Bật *Auto virtual hosts*; domain mặc định `vivu.test` trỏ tới `public/`.
- Reload Laragon sau khi tạo project. Nếu không dùng vhost, dùng `php artisan serve`.

## 8. Tài khoản mặc định (từ seeder)
- **Admin:** email/mật khẩu định nghĩa trong `AdminSeeder` (đổi ngay ở prod).
- Không tạo user public qua seeder trừ dữ liệu demo (đánh dấu rõ).

## 9. Lệnh hữu ích
```bash
php artisan migrate:status
php artisan tinker
php artisan pint               # format code PHP
./vendor/bin/phpstan analyse   # phân tích tĩnh (nếu cài Larastan)
php artisan test               # test backend
php artisan optimize:clear     # xoá cache config/route/view khi lỗi lạ
```

## 10. Sự cố thường gặp
| Triệu chứng | Cách xử lý |
|---|---|
| 419 Page Expired ở admin SPA | Kiểm `SANCTUM_STATEFUL_DOMAINS` + cấu hình CORS/cookie |
| Lỗi PHP version | Chọn đúng PHP 8.3/8.4 trong Laragon; `php -v` |
| Vite lỗi Node | Nâng Node ≥ 20.19 |
| Map không hiện | Sai/không có `GOOGLE_MAPS_API_KEY`, hoặc referrer chưa whitelist |
| Ảnh không hiển thị | Chạy `php artisan storage:link` |
| Queue không chạy job | Bật `php artisan queue:work`; kiểm `QUEUE_CONNECTION` |
