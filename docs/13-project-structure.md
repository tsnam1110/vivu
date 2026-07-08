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
├── resources/                # === PUBLIC SITE (Blade) ===
│   ├── views/
│   │   ├── layouts/
│   │   ├── components/       # <x-...> Blade components
│   │   ├── experiences/
│   │   ├── auth/
│   │   └── profile/
│   ├── js/                   # Alpine + JS công khai (map, reaction, share)
│   └── css/                  # Tailwind 4 (@theme)
│
├── lang/
│   └── vi/                   # chuỗi hiển thị tiếng Việt (không hard-code)
│
├── public/                   # webroot; ảnh build Vite; storage symlink
│
├── admin/                    # === ADMIN SPA (React 19 + AntD 6 + Vite 7) ===
│   ├── src/
│   │   ├── api/              # axios + hàm gọi API theo domain
│   │   ├── components/
│   │   ├── features/         # experiences/, categories/, users/, comments/...
│   │   ├── hooks/
│   │   ├── layouts/
│   │   ├── pages/
│   │   ├── types/            # type khớp API Resource
│   │   └── utils/
│   ├── .env                  # VITE_API_BASE_URL...
│   └── vite.config.ts
│
├── tests/
│   ├── Feature/
│   └── Unit/
│
├── config/                   # auth.php (2 guard), sanctum, filesystems...
└── storage/                  # logs, ảnh (disk local), cache
```

## 2. Quy tắc "đặt file mới ở đâu"
| Bạn đang viết… | Đặt tại |
|---|---|
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
