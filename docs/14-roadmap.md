# 14 — Lộ trình & Cột mốc (Roadmap)

Chia công việc thành các cột mốc giao được (deliverable). Mỗi cột mốc nên có tính năng
chạy được end-to-end. Cập nhật trạng thái khi tiến triển; đồng bộ với checklist
[`../CLAUDE.md`](../CLAUDE.md) §6.

## M0 — Tài liệu & quy tắc ✅ (hoàn thành)
- Bộ tài liệu `docs/`, CLAUDE.md, quy tắc, đặc tả tính năng.

## M1 — Nền tảng kỹ thuật ✅
- Scaffold Laravel 13, cấu hình `.env`, timezone, MySQL.
- `config/auth.php`: **2 guard** (`web`, `admin`), provider `admins`.
- Migration toàn bộ bảng theo [`04-database-schema.md`](04-database-schema.md).
- Model + quan hệ + `$fillable` + `$casts` + Enums.
- Seeder: categories, taste_traits, admin mặc định.
- Bộ khung Resource/Service/Policy.
- **DoD:** `php artisan migrate --seed` chạy sạch; test khung xanh.

## M2 — Tài khoản & xác thực ✅
- Đăng ký/đăng nhập user (public, Blade).
- Đăng nhập admin (Sanctum) + khung Admin SPA (React + AntD).
- Phân quyền admin (role/permission Spatie: super-admin, moderator).
- **DoD:** test auth (M2) xanh; user không truy cập `/api/admin`.

## M3 — Trải nghiệm, danh mục, thẻ (lõi sản phẩm) ✅
- CRUD Experience (public + API) + upload ảnh (queue resize) + slug SEO.
- Danh mục & thẻ: admin CRUD; gắn thẻ theo danh mục.
- Nearby filter (bounding box + Haversine); Google Maps hiển thị khi có key.
- Trang danh sách/chi tiết public có SEO + Open Graph.
- **DoD:** test M3 xanh.

## M4 — Tương tác xã hội ✅
- Bình luận + đánh giá sao (cache `rating_avg`).
- Reaction like/tim (cache `reaction_count`, toggle/upsert).
- Chia sẻ MXH + Open Graph.
- Kiểm duyệt nội dung (admin ẩn/hiện experience & comment).
- **DoD:** test M4 xanh.

## M5 — Hồ sơ gu & tìm người cùng gu (điểm khác biệt) ✅
- Taste profile (personality/interests) + trang chỉnh sửa.
- Thuật toán taste-match v1 (Jaccard + trọng số) + trang gợi ý + `shared_traits`.
- Ẩn match qua `is_matchable`.
- **DoD:** test M5 xanh.

## M6 — Hoàn thiện & sẵn sàng production ✅ (local)
- Rate limit login; middleware `EnsureAdmin`; trang 404/500; terms/privacy.
- CI (test + admin build); eager load; cột cache đếm.
- Còn lại khi deploy thật: staging/prod, storage S3, HTTPS, PHPStan nghiêm ngặt.


## Ngoài phạm vi v1 (backlog)
Chat, booking/thanh toán, mobile native, gợi ý ML nâng cao, i18n nội dung. Xem
[`00-vision-scope.md`](00-vision-scope.md) §4.

> Thứ tự M1→M6 có thể điều chỉnh, nhưng **M1 (nền tảng) và M2 (auth 2 guard) nên làm
> trước** vì mọi thứ khác phụ thuộc.
