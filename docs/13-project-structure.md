# 13 — Cấu trúc thư mục dự án

Bản đồ nơi đặt từng loại mã. Giúp Agent/dev biết **file mới nên nằm ở đâu**. Cấu trúc
này định hình khi scaffold; cập nhật khi có thay đổi lớn.

## 1. Toàn cảnh
```
ViVu/
├── CLAUDE.md                 # điểm vào cho Agent
├── README.md
├── .env.example
├── docs/                     # TÀI LIỆU (thư mục này)
│
├── app/                      # === BACKEND LARAVEL ===
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/          # controller cho public API (auth:web / công khai)
│   │   │   └── Admin/        # controller cho /api/admin (auth:admin)
│   │   ├── Requests/         # Form Request (validate + authorize)
│   │   ├── Resources/        # API Resource (model → JSON)
│   │   └── Middleware/
│   ├── Models/               # User, Admin, Experience, Category, Tag, Comment...
│   ├── Services/             # business logic (ExperienceService, MatchService...)
│   ├── Policies/             # phân quyền sở hữu
│   ├── Observers/            # cập nhật cache rating/reaction
│   ├── Jobs/                 # queue: resize ảnh, mail, cache match
│   ├── Enums/                # ExperienceStatus, ReactionType, TraitType...
│   └── Providers/
│
├── routes/
│   ├── web.php               # public site (Blade) + auth user
│   ├── api.php               # /api (public + auth:web) và /api/admin (auth:admin)
│   └── console.php
│
├── database/
│   ├── migrations/           # 1 file/thay đổi, chỉ tiến (xem 04-database-schema.md)
│   ├── factories/            # cho test
│   └── seeders/              # categories, taste_traits, admin mặc định
│
├── resources/                # === PUBLIC SITE (Blade) — URL: :8000 hoặc vivu.test ===
│   ├── views/                # Design system: docs/15-design-system.md
│   │   ├── layouts/
│   │   │   └── app.blade.php # brand + sticky footer + floating iOS nav
│   │   ├── components/
│   │   │   └── password-input.blade.php
│   │   ├── home/             # me (kho), guest, explore
│   │   ├── experiences/
│   │   ├── auth/
│   │   ├── profile/
│   │   ├── matches/
│   │   └── pages/            # terms, privacy, community, cookies
│   ├── js/                   # Alpine + JS công khai (map, reaction, share)
│   └── css/app.css           # Tailwind 4 + [x-cloak]
│
├── lang/
│   └── vi/                   # chuỗi hiển thị tiếng Việt (không hard-code)
│
├── public/                   # webroot; ảnh build Vite; storage symlink
│   # Dev: php artisan serve :8000 · Vite HMR public :5201
│
├── admin/                    # === ADMIN SPA — URL: http://localhost:5200 ===
│   ├── src/
│   │   ├── api/              # axios + hàm gọi API theo domain
│   │   ├── layouts/
│   │   └── pages/            # Login, Experiences, Users, Comments, ...
│   ├── .env                  # VITE_API_BASE_URL=http://127.0.0.1:8000/api
│   ├── vite.config.ts        # server.port = 5200
│   └── package.json
│
├── package.json              # npm run dev = full stack (server+queue+vite+admin)
├── vite.config.js            # public Vite port 5201
│
├── tests/
│   ├── Feature/
│   └── Unit/
│
├── config/                   # auth.php (2 guard), sanctum, filesystems...
└── storage/                  # logs, ảnh (disk local), cache
```

> Khởi chạy local một lệnh: `composer dev` — xem [`06-setup-development.md`](06-setup-development.md).

## 2. Quy tắc "đặt file mới ở đâu"
| Bạn đang viết… | Đặt tại |
|---|---|
| **UI public (trang mới)** | `resources/views/{domain}/` + `@extends('layouts.app')` — xem [`15-design-system.md`](15-design-system.md) |
| **Component Blade tái dùng** | `resources/views/components/` (`x-...`) |
| **UI admin (trang mới)** | `admin/src/pages/` + route `App.tsx` + menu `AdminLayout` |
| Validate input | `app/Http/Requests/` (Form Request) |
| Logic nghiệp vụ | `app/Services/` |
| Biến đổi output API | `app/Http/Resources/` |
| Quyền sở hữu tài nguyên | `app/Policies/` |
| Cập nhật cache đếm | `app/Observers/` |
| Tác vụ chậm/nền | `app/Jobs/` |
| Hằng trạng thái/loại | `app/Enums/` |
| Endpoint public | Controller trong `app/Http/Controllers/Api/` + `routes/api.php` |
| Endpoint admin | Controller trong `app/Http/Controllers/Admin/` + `routes/api.php` (prefix admin) |
| Trang public (SEO) | `resources/views/` + `routes/web.php` |
| Màn admin | `admin/src/features/<domain>/` |
| Chuỗi hiển thị | `lang/vi/` (không hard-code trong code) |
| Migration | `database/migrations/` (file mới, không sửa file cũ) |

## 3. Ranh giới quan trọng
- **Admin và public không trộn:** controller, route, guard tách biệt (xem
  [`features/auth-and-accounts.md`](features/auth-and-accounts.md)).
- **Controller mỏng:** không đặt business logic ở controller — đưa vào Service.
- **Không trả model thô:** luôn qua Resource.
