# Tài liệu dự án ViVu

Đây là bộ tài liệu chính thức của **ViVu** — nền tảng chia sẻ trải nghiệm cộng đồng.

> **Đối tượng đọc chính:** AI Agent làm việc trên codebase.
> **Đối tượng phụ:** Dev kế thừa/tham gia dự án.

Điểm vào ngắn gọn nằm ở [`../CLAUDE.md`](../CLAUDE.md). Thư mục này chứa tài liệu
chi tiết.

---

## Mục lục

### Nền tảng (đọc theo thứ tự nếu mới vào dự án)
1. [`00-vision-scope.md`](00-vision-scope.md) — Tầm nhìn, phạm vi, personas, out-of-scope
2. [`01-architecture.md`](01-architecture.md) — Kiến trúc hệ thống & luồng dữ liệu
3. [`02-tech-stack.md`](02-tech-stack.md) — Ngăn xếp công nghệ & phiên bản
4. [`03-domain-model.md`](03-domain-model.md) — Mô hình miền nghiệp vụ (entities + ERD)
5. [`04-database-schema.md`](04-database-schema.md) — Lược đồ CSDL chi tiết
6. [`06-setup-development.md`](06-setup-development.md) — Cài đặt & môi trường dev (Laragon)

### Quy ước kỹ thuật
7. [`05-api-conventions.md`](05-api-conventions.md) — Chuẩn thiết kế REST API
8. [`07-coding-standards.md`](07-coding-standards.md) — Quy ước code Backend & Frontend
9. [`12-testing.md`](12-testing.md) — Chiến lược kiểm thử
10. [`13-project-structure.md`](13-project-structure.md) — Cấu trúc thư mục & nơi đặt file
11. [`09-git-workflow.md`](09-git-workflow.md) — Quy trình Git, commit, PR
12. [`10-security-privacy.md`](10-security-privacy.md) — Bảo mật & quyền riêng tư

### Quản trị dự án
- [`14-roadmap.md`](14-roadmap.md) — Lộ trình & cột mốc (M0–M6)

### Đặc tả tính năng — [`features/`](features/)
- [`auth-and-accounts.md`](features/auth-and-accounts.md) — Xác thực & 2 loại tài khoản
- [`experiences-and-categories.md`](features/experiences-and-categories.md) — Trải nghiệm, danh mục, thẻ
- [`reviews-comments-reactions.md`](features/reviews-comments-reactions.md) — Đánh giá, bình luận, cảm xúc
- [`maps-and-location.md`](features/maps-and-location.md) — Bản đồ & định vị Google Maps
- [`social-sharing.md`](features/social-sharing.md) — Chia sẻ mạng xã hội
- [`taste-matching.md`](features/taste-matching.md) — Ghép "gu" người dùng

### Dành cho Agent
- [`08-agent-playbook.md`](08-agent-playbook.md) — **Quy tắc & quy trình bắt buộc cho AI Agent**

### Tham chiếu
- [`11-glossary.md`](11-glossary.md) — Bảng thuật ngữ thống nhất

---

## Quy ước của tài liệu

- **Ngôn ngữ:** Nội dung tiếng Việt; thuật ngữ kỹ thuật, tên bảng/cột/route/biến giữ
  nguyên tiếng Anh.
- **Từ khoá mức độ:** Tuân theo [RFC 2119](https://www.rfc-editor.org/rfc/rfc2119):
  **BẮT BUỘC** (MUST), **NÊN** (SHOULD), **CÓ THỂ** (MAY).
- **Trạng thái block:** Các khối đánh dấu `> ⚠️` là cảnh báo quan trọng; `> 💡` là gợi ý.
- **Tính đơn nguồn:** Một sự thật chỉ mô tả ở **một** nơi; nơi khác **liên kết** tới,
  không sao chép. Ví dụ: schema chi tiết chỉ nằm ở `04-database-schema.md`.
- **Khi sửa code làm lệch tài liệu:** cập nhật tài liệu trong cùng thay đổi.

## Cách bảo trì tài liệu

1. Thay đổi kiến trúc/schema/API → cập nhật tài liệu tương ứng **trước hoặc cùng lúc**.
2. Thêm tính năng mới → thêm file trong `features/` và liên kết từ `README.md` này.
3. Thêm thuật ngữ mới → bổ sung vào `11-glossary.md`.
4. Không để tài liệu "mồ côi" — mọi file phải được liên kết từ mục lục này.
