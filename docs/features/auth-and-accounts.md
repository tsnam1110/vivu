# Tính năng: Xác thực & Hai loại tài khoản

Liên quan: [`../03-domain-model.md`](../03-domain-model.md),
[`../10-security-privacy.md`](../10-security-privacy.md),
[`../05-api-conventions.md`](../05-api-conventions.md).

## 1. Mục tiêu
Tách bạch hoàn toàn **người dùng cuối** và **quản trị viên**: khác bảng, khác guard,
khác luồng đăng nhập, khác giao diện.

## 2. Hai guard

| | User | Admin |
|---|---|---|
| Guard | `web` | `admin` |
| Bảng | `users` | `admins` |
| Cơ chế | Session + cookie | Sanctum token |
| Giao diện | Public site (Blade) | Admin panel (React/AntD) |
| Route | `web.php` + `/api` (auth:web) | `/api/admin` (auth:admin) |
| Provider | `App\Models\User` | `App\Models\Admin` |

Cấu hình trong `config/auth.php`: thêm guard `admin` và provider `admins`.

## 3. Luồng người dùng (User)

### Đăng ký
- Trang Blade `GET /register` → form (name, username, email, password).
- `POST /register` → Form Request validate (username unique, email unique, password
  ≥ 8 ký tự + confirm).
- Tạo `User` + `UserProfile` rỗng (qua Service/observer).
- Đăng nhập tự động, gửi mail xác thực (queue). Redirect về trang chủ.

### Đăng nhập / đăng xuất
- Trang public (local): **http://127.0.0.1:8000/login** (hoặc `http://vivu.test/login`).
- `GET /login`, `POST /login` (throttle 5/phút/IP), `POST /logout`.
- User **không** seed sẵn — đăng ký tại `/register`.
- "Quên mật khẩu": luồng reset password mặc định Laravel (nếu bật).

### Hồ sơ
- URL công khai: `/u/{username}` (hiển thị bio, taste công khai, experiences).
- `GET/PATCH /api/me`, `PATCH /api/me/profile` cho chủ tài khoản.

## 4. Luồng quản trị (Admin)

- **Không** đăng ký công khai. Admin tạo bởi seeder hoặc bởi `super-admin`.
- Giao diện SPA: **http://localhost:5200/** (dev). Public user: **http://127.0.0.1:8000/**.
- `POST /api/admin/login` → trả Sanctum token → SPA lưu localStorage và gắn
  `Authorization: Bearer`.
- `POST /api/admin/logout` → thu hồi token.
- Role qua `spatie/laravel-permission`: `super-admin` (toàn quyền), `moderator`
  (kiểm duyệt nội dung, không quản admin khác).
- Seed mặc định: `admin@vivu.test` / `password` (đổi ngay ngoài local).
- Admin dùng Bearer — **không** đưa `localhost:5200` vào `SANCTUM_STATEFUL_DOMAINS`
  (tránh HTTP 419).

## 5. Quy tắc nghiệp vụ
1. Một email **có thể** tồn tại ở cả `users` và `admins` (hai không gian độc lập) —
   nhưng UNIQUE trong từng bảng.
2. User bị `suspended` không đăng nhập được (kiểm tại middleware/attempt).
3. Admin `is_active = false` không đăng nhập được.
4. Không có endpoint nào cho phép "nâng cấp" user thành admin qua API công khai.

## 6. Bảo mật
- Mật khẩu hash, rate limit login, CSRF cho form web, Sanctum stateful domain cho SPA.
- Session cookie `httpOnly`, `secure` (prod), `SameSite=Lax`.
- Xem [`../10-security-privacy.md`](../10-security-privacy.md).

## 7. Test tối thiểu
- User đăng ký/đăng nhập/đăng xuất thành công & thất bại (sai mật khẩu, bị khoá).
- Admin đăng nhập nhận token; token sai/hết hạn bị 401.
- User **không** truy cập được `/api/admin/*` (403/401).
- Admin token **không** dùng được cho hành động của user guard.
