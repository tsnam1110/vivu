# ViVu

**Nền tảng chia sẻ & lưu trữ trải nghiệm cộng đồng** — quán ăn, quán uống, địa điểm du
lịch, cà phê… gắn với bản đồ, danh mục, thẻ, đánh giá và **tìm người cùng gu**.

## Tổng quan nhanh
- **Backend:** Laravel 13 (PHP 8.3+, khuyến nghị 8.4), MySQL 8
- **Admin panel:** React 19 + Ant Design 6 + Vite 7 + TypeScript
- **Trang người dùng:** Blade + Tailwind CSS 4 + Alpine.js (SEO)
- **Bản đồ:** Google Maps JS API (Places + Geocoding)
- **Xác thực:** 2 guard tách biệt — `web` (người dùng) & `admin` (Sanctum)

## Tính năng chính
- Đăng trải nghiệm gắn địa chỉ + vị trí bản đồ
- Phân loại theo **danh mục** + **thẻ tuỳ chỉnh** (vd "món Hàn")
- **Bình luận + đánh giá sao**, **cảm xúc** (like/tim)
- **Chia sẻ** ra mạng xã hội
- **Hồ sơ gu** (tính cách + sở thích) và **tìm người cùng gu**
- **Admin panel** quản lý & kiểm duyệt

## Tài liệu
> 📚 Toàn bộ tài liệu nằm trong [`docs/`](docs/). Bắt đầu ở [`docs/README.md`](docs/README.md).
>
> 🤖 **AI Agent:** đọc [`CLAUDE.md`](CLAUDE.md) trước tiên, rồi
> [`docs/08-agent-playbook.md`](docs/08-agent-playbook.md).

| Tài liệu | Nội dung |
|---|---|
| [CLAUDE.md](CLAUDE.md) | Điểm vào & Golden Rules |
| [docs/00-vision-scope.md](docs/00-vision-scope.md) | Tầm nhìn & phạm vi |
| [docs/01-architecture.md](docs/01-architecture.md) | Kiến trúc hệ thống |
| [docs/03-domain-model.md](docs/03-domain-model.md) | Mô hình miền |
| [docs/04-database-schema.md](docs/04-database-schema.md) | Lược đồ CSDL |
| [docs/05-api-conventions.md](docs/05-api-conventions.md) | Chuẩn API |
| [docs/features/](docs/features/) | Đặc tả tính năng |

## Bắt đầu phát triển (sau khi scaffold)
```bash
composer install
cp .env.example .env && php artisan key:generate
# cấu hình DB & GOOGLE_MAPS_API_KEY trong .env
php artisan migrate --seed
php artisan serve

# Admin panel
cd admin && npm install && npm run dev
```

## Trạng thái
Dự án đang ở giai đoạn **thiết lập tài liệu & quy tắc**. Xem checklist ở
[CLAUDE.md §6](CLAUDE.md).
