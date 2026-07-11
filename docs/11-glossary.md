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
| **Public site** | Mặt tiền người dùng: Blade + Tailwind + Alpine (SEO). Shell: brand chip + floating nav + sticky footer. |
| **Admin panel** | Mặt tiền quản trị: React + Ant Design SPA (Bearer token). |
| **Kho cá nhân** | Màn hình mặc định khi user đã đăng nhập (`/`) — danh sách trải nghiệm của chính họ (mọi status). |
| **Khám phá (`/explore`)** | Lưới trải nghiệm công khai cộng đồng — không phải home mặc định. |
| **Floating tab bar** | Menu nổi dạng pill iOS ở đáy màn hình public (`layouts/app.blade.php`). |
| **Design system** | Quy ước UI/token/component — xem `15-design-system.md`. |
| **Avatar Premium** | Gói Premium có thời hạn; mở khoá khung cao cấp + huy hiệu. |
| **Avatar frame** | Khung trang trí quanh avatar — catalog DB `avatar_frames` + effect engine. |
| **Sample avatar** | Ảnh đại diện mẫu trong catalog (`sample_avatars`) — chọn thay vì upload. |
| **Effect engine** | Loại hiệu ứng CSS cố định (`spin`, `glow`…) đọc `effect_config` JSON. |
| **ADR** | Architecture Decision Record — ghi quyết định kiến trúc (xem `01-architecture.md §7`). |
| **Habit Tracker** | Bảng Excel theo tháng: hàng = đầu mục **cá nhân**, cột = ngày, ô = ✓/✗. |
| **HabitItem** | Mẫu gợi ý admin (`habit_items`) — user chọn copy, không phải hàng bảng. |
| **UserHabitItem** | Đầu mục cá nhân (từ mẫu hoặc text tự tạo); không lưu custom vào admin. |
| **HabitEntry** | Ô kết quả user × UserHabitItem × ngày: `done` / `missed`. |
| **HabitEntryHistory** | Lịch sử mỗi lần cycle trạng thái ô. |
| **What to Eat (Hôm nay ăn gì)** | Tính năng **phụ**: nút trên Kho → popup chọn bữa / nhẹ–chính / ngoài–nấu / số lượng món → list gợi ý; Chi tiết từng món khi user bấm. Xem `features/what-to-eat.md`. |
| **Dish (Món ăn)** | Bản ghi trong **kho món hệ thống** — tri thức món dùng chung, khác Experience (trải nghiệm tại địa điểm). |
| **DishContribution** | Đóng góp UGC về món: công thức, calo, hại, lợi, lời khuyên, ghi chú, ngũ hành — có kiểm duyệt. |
| **Meal slot** | Bữa: `breakfast` \| `lunch` \| `dinner` (và tuỳ chọn `snack`). |
| **Meal size** | Vai trò bữa: `light` (ăn nhẹ) \| `main` (ăn chính). |
| **Meal mode** | Hình thức: `dine_out` (ăn ngoài) \| `cook_home` (tự nấu). |
| **Five element (Ngũ hành)** | Phân loại món: Mộc / Hoả / Thổ / Kim / Thuỷ — tham khảo văn hoá, không phải tư vấn y khoa. Chỉ seed khi **verified** (có nguồn); chưa chắc → `null`. |
| **Verified fact** | Fact món (calo, element, thermal…) có provenance + confidence đủ — mới được seed/canonical. Xem `features/what-to-eat-seed-and-kb.md`. |
| **Provenance** | Nguồn + phương pháp + người review gắn với một field fact. Không provenance = không verified. |
| **Ruleset (What to Eat)** | Bộ rule có id/lớp (dinh dưỡng, cấu trúc mâm, hàn–nhiệt, ngũ hành…) — `features/what-to-eat-ruleset.md`. |
| **Data-gate** | Rule **bỏ qua** nếu field required đang `null` — không bịa giá trị để chấm. |
| **dish_role** | Vai trò món trong mâm: soup / main_protein / side_veg / one_bowl… (cột `dishes.dish_role`, enum `DishRole`). |
| **culinary_regions** | Vùng miền ẩm thực (JSON, multi-label) — đồng bộ inventory `region_tags`: `bac` / `trung` / `nam` / `tay_nguyen` / `quoc_gia` / `hoa_viet` / `ngoai`. |
| **region_tags** | Tag vùng món: `bac` / `trung` / `nam` / `tay_nguyen` / `quoc_gia` (+ phụ `hoa_viet`). Inventory: `features/what-to-eat-dish-catalog.md`. |
| **Dish catalog inventory** | Danh sách món ứng viên seed (chưa đồng nghĩa fact verified) — `features/what-to-eat-dish-catalog.md`. |
| **MealSuggestionLog** | Lịch sử gợi ý/chọn món **riêng tư** của user (tránh lặp, thống kê). |
