# CLAUDE.md — Điểm vào cho AI Agent & Dev

> **Đối tượng đọc chính: AI Agent.** Đối tượng phụ: dev kế thừa dự án.
> File này là **bản đồ điều hướng**. Đọc file này đầu tiên trong mọi phiên làm việc,
> sau đó mở tài liệu chi tiết trong `docs/` theo nhu cầu.

---

## 1. Dự án là gì

**ViVu** là nền tảng chia sẻ & lưu trữ **trải nghiệm** của cộng đồng: quán ăn, quán
uống, địa điểm du lịch, cà phê, homestay… Người dùng đăng trải nghiệm gắn với **địa
chỉ + vị trí trên bản đồ**, phân loại theo **danh mục** và **thẻ (tag)**, cho phép
người khác **bình luận / đánh giá / thả cảm xúc (like, tim) / chia sẻ MXH**.

Điểm khác biệt cốt lõi: mỗi người dùng khai báo **tính cách & sở thích (taste
profile)** để hệ thống **gợi ý người có cùng "gu"** — tìm người có trải nghiệm tương
đồng với bản thân.

Chi tiết tầm nhìn & phạm vi: [`docs/00-vision-scope.md`](docs/00-vision-scope.md).

---

## 2. Kiến trúc & Stack (tóm tắt)

| Thành phần | Công nghệ | Ghi chú |
|---|---|---|
| Backend / API | **Laravel 13**, PHP 8.3+ (khuyến nghị 8.4) | REST API + Blade cho public site |
| CSDL | **MySQL 8** | UTF-8mb4, timezone `Asia/Ho_Chi_Minh` |
| Admin panel | **React 19 + Ant Design 6 + Vite 7 + TypeScript** | SPA riêng, gọi REST API |
| Trang người dùng (public) | **Blade + Tailwind CSS 4 + Alpine.js** | Ưu tiên SEO |
| Auth | **Laravel Sanctum** (admin SPA) + **session guard `web`** (public) | 2 guard tách biệt |
| Bản đồ | **Google Maps JS API** (+ Places, Geocoding) | |
| Bất đồng bộ | **Laravel Queue** (database driver → Redis khi scale) | Gửi mail, xử lý ảnh |

Chi tiết: [`docs/01-architecture.md`](docs/01-architecture.md),
[`docs/02-tech-stack.md`](docs/02-tech-stack.md).

> ⚠️ Các lựa chọn trên là **mặc định có chủ đích**, không phải bất biến. Nếu cần đổi
> (ví dụ dùng Inertia thay vì SPA tách rời), **cập nhật tài liệu trước, rồi mới code**.

---

## 3. Bản đồ tài liệu (`docs/`)

| File | Nội dung | Khi nào đọc |
|---|---|---|
| [`docs/README.md`](docs/README.md) | Mục lục & quy ước tài liệu | Bắt đầu |
| [`00-vision-scope.md`](docs/00-vision-scope.md) | Tầm nhìn, phạm vi, personas | Hiểu "tại sao" |
| [`01-architecture.md`](docs/01-architecture.md) | Kiến trúc hệ thống, luồng request | Hiểu "cấu trúc" |
| [`02-tech-stack.md`](docs/02-tech-stack.md) | Stack, phiên bản, lý do chọn | Setup / nâng cấp |
| [`03-domain-model.md`](docs/03-domain-model.md) | Thực thể nghiệp vụ, quan hệ, ERD | Trước khi model hoá |
| [`04-database-schema.md`](docs/04-database-schema.md) | Bảng, cột, index, migration | Viết migration/query |
| [`05-api-conventions.md`](docs/05-api-conventions.md) | Chuẩn REST, envelope, lỗi, phân trang | Viết/gọi API |
| [`06-setup-development.md`](docs/06-setup-development.md) | Cài đặt & môi trường dev (Laragon) | Dựng máy dev |
| [`features/`](docs/features/) | Đặc tả từng tính năng | Làm 1 tính năng cụ thể |
| [`07-coding-standards.md`](docs/07-coding-standards.md) | Quy ước code BE + FE | Mọi lúc viết code |
| [`08-agent-playbook.md`](docs/08-agent-playbook.md) | **Quy tắc dành cho Agent** | **Bắt buộc cho Agent** |
| [`09-git-workflow.md`](docs/09-git-workflow.md) | Nhánh, commit, PR, review | Trước khi commit |
| [`10-security-privacy.md`](docs/10-security-privacy.md) | Bảo mật & quyền riêng tư | Xử lý dữ liệu nhạy cảm |
| [`11-glossary.md`](docs/11-glossary.md) | Thuật ngữ thống nhất | Khi gặp từ lạ |
| [`12-testing.md`](docs/12-testing.md) | Chiến lược & ma trận kiểm thử | Viết/chạy test |
| [`13-project-structure.md`](docs/13-project-structure.md) | Cấu trúc thư mục, nơi đặt file | Tạo file mới |
| [`14-roadmap.md`](docs/14-roadmap.md) | Lộ trình & cột mốc M0–M6 | Lập kế hoạch |

---

## 4. Golden Rules (bắt buộc — không vi phạm)

1. **Tài liệu là nguồn sự thật.** Khi code khác tài liệu → sửa cho khớp; nếu quyết
   định đổi hướng, cập nhật tài liệu trong **cùng** một thay đổi.
2. **Tách bạch 2 loại tài khoản.** Admin (`admin` guard) và Người dùng (`web` guard)
   **không** dùng chung bảng, chung session, hay chung endpoint. Xem
   [`features/auth-and-accounts.md`](docs/features/auth-and-accounts.md).
3. **Không hard-code secret.** Mọi khoá (Google Maps, DB, mail) qua `.env`. Không
   commit `.env`.
4. **Ngôn ngữ:** Tài liệu & giao tiếp: **tiếng Việt**. Code, tên biến, comment kỹ
   thuật, commit message, tên bảng/cột/route: **tiếng Anh**.
5. **Validate ở server.** FE validate là UX; **BE luôn validate lại** qua Form Request.
6. **Không phá vỡ hợp đồng API** đã công bố mà không version hoá — xem
   [`05-api-conventions.md`](docs/05-api-conventions.md).
7. **Migration chỉ tiến, không sửa file cũ đã chạy.** Sai thì tạo migration mới.
8. **Test trước khi tuyên bố xong.** Chạy test/thử thực tế; nếu fail, nói rõ.

Quy tắc chi tiết cho Agent: [`docs/08-agent-playbook.md`](docs/08-agent-playbook.md).

---

## 5. Lệnh thường dùng (sẽ có sau khi scaffold code)

```bash
# Backend (Laravel) — chạy trong thư mục gốc
php artisan serve                 # dev server
php artisan migrate               # chạy migration
php artisan migrate:fresh --seed  # reset DB + seed (CHỈ ở local)
php artisan test                  # chạy test
php artisan queue:work            # chạy queue worker

# Admin SPA (React + Vite) — trong thư mục admin/
npm install
npm run dev                       # dev server admin
npm run build                     # build production

# Public assets (Blade + Vite)
npm run dev                       # watch tailwind/js
npm run build
```

> Laragon (Windows): thư mục gốc `F:\laragon\www\My_Project\ViVu`. Truy cập qua
> `http://vivu.test` nếu bật virtual host, hoặc `php artisan serve`.

---

## 6. Trạng thái hiện tại

- [x] Bộ tài liệu & quy tắc (thư mục này)
- [ ] Scaffold Laravel + cấu hình `.env`
- [ ] Migration theo [`04-database-schema.md`](docs/04-database-schema.md)
- [ ] Auth 2 guard
- [ ] Admin SPA (antd)
- [ ] Public site (Blade)
- [ ] Tính năng review / reaction / tag / map / share / taste-matching

> Cập nhật checklist này khi hoàn thành từng phần.
