# 14 — Lộ trình & Cột mốc (Roadmap)

Chia công việc thành các cột mốc giao được (deliverable). Mỗi cột mốc nên có tính năng
chạy được end-to-end. Cập nhật trạng thái khi tiến triển; đồng bộ với checklist
[`../CLAUDE.md`](../CLAUDE.md) §6.

## M0 — Tài liệu & quy tắc ✅ (hoàn thành)
- Bộ tài liệu `docs/`, CLAUDE.md, quy tắc, đặc tả tính năng.

## M1 — Nền tảng kỹ thuật
- Scaffold Laravel 13, cấu hình `.env`, timezone, MySQL.
- `config/auth.php`: **2 guard** (`web`, `admin`), provider `admins`.
- Migration toàn bộ bảng theo [`04-database-schema.md`](04-database-schema.md).
- Model + quan hệ + `$fillable` + `$casts` + Enums.
- Seeder: categories, taste_traits, admin mặc định.
- Bộ khung Resource/Service/Policy/Observer.
- **DoD:** `php artisan migrate --seed` chạy sạch; test khung xanh.

## M2 — Tài khoản & xác thực
- Đăng ký/đăng nhập user (public, Blade).
- Đăng nhập admin (Sanctum) + khung Admin SPA (React 19 + AntD 6).
- Phân quyền admin (role/permission).
- **DoD:** test auth (M2) xanh; user không truy cập `/api/admin`.

## M3 — Trải nghiệm, danh mục, thẻ (lõi sản phẩm)
- CRUD Experience (public) + upload ảnh (queue resize) + slug SEO.
- Danh mục & thẻ: admin CRUD; gắn thẻ theo danh mục.
- Tích hợp **Google Maps** chọn vị trí + hiển thị + nearby.
- Trang danh sách/chi tiết public có SEO + Open Graph.
- **DoD:** đăng 1 trải nghiệm hoàn chỉnh < 3 phút; test M3 xanh.

## M4 — Tương tác xã hội
- Bình luận + đánh giá sao (cache `rating_avg`).
- Reaction like/tim (cache `reaction_count`).
- Chia sẻ MXH + Open Graph hoàn chỉnh.
- Kiểm duyệt nội dung (admin ẩn/hiện).
- **DoD:** test M4 xanh; kiểm duyệt hoạt động.

## M5 — Hồ sơ gu & tìm người cùng gu (điểm khác biệt)
- Taste profile (personality/interests) + trang chỉnh sửa.
- Thuật toán taste-match v1 (Jaccard/cosine) + trang gợi ý + `shared_traits`.
- (Tuỳ chọn) theo dõi (follow) người cùng gu.
- **DoD:** gợi ý có ý nghĩa + minh bạch lý do; test M5 xanh.

## M6 — Hoàn thiện & sẵn sàng production
- Rà bảo mật ([`10-security-privacy.md`](10-security-privacy.md)), rate limit, CORS.
- Tối ưu hiệu năng (index, eager load, cache đếm).
- CI (lint + phpstan + test), cấu hình staging/prod, storage → S3, HTTPS.
- Trang tĩnh (điều khoản, quyền riêng tư), xử lý lỗi 404/500 thân thiện.
- **DoD:** checklist bảo mật đủ; CI xanh; deploy staging thành công.

## Ngoài phạm vi v1 (backlog)
Chat, booking/thanh toán, mobile native, gợi ý ML nâng cao, i18n nội dung. Xem
[`00-vision-scope.md`](00-vision-scope.md) §4.

> Thứ tự M1→M6 có thể điều chỉnh, nhưng **M1 (nền tảng) và M2 (auth 2 guard) nên làm
> trước** vì mọi thứ khác phụ thuộc.
