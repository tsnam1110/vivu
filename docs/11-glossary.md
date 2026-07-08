# 11 — Bảng thuật ngữ (Glossary)

Thuật ngữ thống nhất toàn dự án. Khi gặp/đưa ra khái niệm mới, thêm vào đây.

| Thuật ngữ | Định nghĩa |
|---|---|
| **ViVu** | Tên dự án — nền tảng chia sẻ trải nghiệm cộng đồng. |
| **Experience (Trải nghiệm)** | Bài chia sẻ của người dùng về một địa điểm/trải nghiệm, gắn địa chỉ + toạ độ + danh mục + thẻ. Thực thể trung tâm. |
| **User (Người dùng)** | Tài khoản công khai của người chia sẻ/khám phá. Guard `web`. Khác admin. |
| **Admin** | Tài khoản quản trị, guard `admin`, đăng nhập admin panel (React/AntD). Bảng riêng. |
| **Guard** | Cơ chế xác thực của Laravel. Dự án có 2 guard tách biệt: `web` và `admin`. |
| **Category (Danh mục)** | Phân loại cấp cao: Ăn, Uống, Du lịch, Cà phê… Một Experience thuộc một Category. |
| **Tag (Thẻ)** | Nhãn chi tiết gắn theo danh mục (vd "món Hàn") hoặc toàn cục. Một Experience có nhiều Tag. |
| **Reaction (Cảm xúc)** | Biểu cảm thả lên Experience: `like`, `love` (tim). Polymorphic, mỗi user 1 reaction/đối tượng. |
| **Comment (Bình luận)** | Ý kiến của user trên Experience, có thể kèm **rating** sao 1–5. |
| **Rating (Đánh giá sao)** | Điểm 1–5 tuỳ chọn kèm comment. Gộp thành `rating_avg` trên Experience. |
| **Taste profile (Hồ sơ gu)** | Bộ dữ liệu `bio` + `personality` + `interests` của user, nền tảng tìm người cùng gu. |
| **Personality (Tính cách)** | Tập nhãn mô tả tính cách user (vd hướng nội, thích phiêu lưu). |
| **Interests (Sở thích)** | Tập nhãn mô tả sở thích user (vd ẩm thực, nhiếp ảnh). |
| **Taste-matching (Ghép gu)** | Tính độ tương đồng taste profile để gợi ý "người cùng gu". |
| **Taste trait** | Nhãn chuẩn hoá (personality/interest) trong bảng `taste_traits`, admin quản lý. |
| **Media** | Ảnh gắn với Experience. |
| **Slug** | Chuỗi thân thiện URL (vd `quan-ca-phe-abc`) cho SEO. |
| **Guard `web`** | Xác thực session/cookie cho người dùng công khai. |
| **Guard `admin`** | Xác thực Sanctum token cho admin panel. |
| **Envelope** | Cấu trúc phản hồi API chuẩn (`data`/`meta`/`links`). Xem `05-api-conventions.md`. |
| **Form Request** | Lớp Laravel validate + authorize input. Bắt buộc cho mọi input. |
| **Service layer** | Lớp chứa business logic, giữa Controller và Model. |
| **Resource** | Lớp Laravel biến model → JSON cho API. |
| **Observer** | Lớp Laravel cập nhật cache (`rating_avg`, `reaction_count`) khi dữ liệu đổi. |
| **Policy** | Lớp phân quyền chi tiết (quyền sở hữu tài nguyên). |
| **Public site** | Mặt tiền người dùng: Blade + Tailwind + Alpine (SEO). |
| **Admin panel** | Mặt tiền quản trị: React + Ant Design SPA. |
| **ADR** | Architecture Decision Record — ghi quyết định kiến trúc (xem `01-architecture.md §7`). |
