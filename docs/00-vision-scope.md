# 00 — Tầm nhìn & Phạm vi

## 1. Tuyên bố tầm nhìn

> ViVu là nơi mọi người **lưu giữ và chia sẻ trải nghiệm thực tế** về những địa điểm
> họ đã đến — quán ăn, quán uống, điểm du lịch, cà phê, homestay — và **tìm thấy
> những người có cùng gu** để khám phá thêm.

Khác với một danh bạ địa điểm thuần tuý, ViVu đặt **con người và cảm nhận** làm trung
tâm: mỗi trải nghiệm gắn với một người thật có tính cách, sở thích; và bạn có thể tìm
người "hợp gu" để tin tưởng gợi ý của họ.

### Định hướng UX (public)
- **Ưu tiên kho cá nhân** (lưu trữ trải nghiệm của tôi) hơn feed khám phá cộng đồng.
- Home `/` = kho (đã login) / landing (khách); khám phá nằm ở `/explore`.
- Giao diện public: cảm giác **mobile iOS** (menu nổi, bo góc lớn) — chi tiết
  [`15-design-system.md`](15-design-system.md).

## 2. Vấn đề giải quyết

| Vấn đề | Cách ViVu giải quyết |
|---|---|
| Review trên mạng chung chung, không biết người review có "gu" giống mình không | **Taste profile** + gợi ý người cùng gu |
| Trải nghiệm cá nhân bị phân mảnh (ảnh ở điện thoại, note ở nhiều app) | Nơi tập trung lưu trữ trải nghiệm có cấu trúc |
| Khó tìm địa điểm theo bối cảnh (món Hàn, quán yên tĩnh…) | **Danh mục + thẻ tuỳ chỉnh** + bản đồ |
| Muốn chia sẻ nhanh ra MXH | Nút **share** tích hợp |

## 3. Personas (đối tượng người dùng)

### P1 — Người khám phá (Explorer)
Muốn tìm địa điểm mới đáng tin. Đọc review, lọc theo danh mục/thẻ/bản đồ, theo dõi
người cùng gu.

### P2 — Người chia sẻ (Contributor)
Thích ghi lại và chia sẻ trải nghiệm. Đăng bài kèm ảnh, địa chỉ, đánh giá; nhận
tương tác (like/tim/bình luận).

### P3 — Người tìm bạn đồng điệu (Matcher)
Khai báo tính cách/sở thích, tìm người có trải nghiệm và gu tương đồng.

### P4 — Quản trị viên (Admin)
Quản lý nội dung, danh mục, thẻ, người dùng, kiểm duyệt; **tài khoản tách biệt hoàn
toàn** với người dùng thường. Dùng admin panel (React + Ant Design).

## 4. Phạm vi (Scope)

### Trong phạm vi (MVP → v1)
- Đăng ký / đăng nhập người dùng (guard `web`, tách khỏi admin).
- CRUD **Experience** (bài trải nghiệm) gắn địa chỉ + toạ độ bản đồ.
- **Danh mục** (du lịch, ăn, uống, cà phê…) — quản lý bởi admin.
- **Thẻ (tag)** tuỳ chỉnh theo danh mục (vd: "món Hàn", "yên tĩnh").
- **Bình luận & đánh giá** (rating sao) trên Experience.
- **Cảm xúc (reaction):** like, tim (mở rộng được).
- **Chia sẻ MXH** (Facebook, X/Twitter, sao chép link…).
- **Bản đồ Google Maps:** hiển thị vị trí, chọn vị trí khi đăng, tìm quanh đây.
- **Hồ sơ người dùng:** bio + **tính cách + sở thích** (taste profile).
- **Tìm người cùng gu** dựa trên taste profile.
- **Admin panel:** quản lý user, experience, danh mục, thẻ, kiểm duyệt bình luận.
- **Habit tracker (kho cá nhân):** thói quen daily/weekly, check-in, streak; riêng tư;
  tuỳ chọn gắn danh mục trải nghiệm.
- **Hôm nay ăn gì (M8, tính năng phụ):** nút trên trang Kho → **popup** chọn bữa /
  nhẹ–chính / ngoài–nấu / số lượng món → list gợi ý; bấm **Chi tiết** từng món để xem
  đầy đủ. Kho món hệ thống; (sau) đóng góp công thức & thông tin tham khảo.
  Xem [`features/what-to-eat.md`](features/what-to-eat.md).

### Ngoài phạm vi (chưa làm ở v1 — ghi để tránh phình scope)
- Nhắn tin trực tiếp giữa người dùng (chat).
- Đặt chỗ/booking, thanh toán.
- Ứng dụng mobile native (web responsive trước).
- Gợi ý bằng ML phức tạp (v1 dùng thuật toán tương đồng đơn giản — xem
  [`features/taste-matching.md`](features/taste-matching.md)).
- Đa ngôn ngữ (i18n) cho nội dung người dùng (giao diện có thể chuẩn bị sẵn khung).
- Ứng dụng dinh dưỡng y khoa / tính mệnh ngũ hành theo năm sinh (what-to-eat chỉ
  tham khảo cộng đồng + disclaimer).

## 5. Nguyên tắc sản phẩm

1. **Con người trước địa điểm** — mọi trải nghiệm có chủ thể rõ ràng.
2. **Tin cậy qua sự tương đồng** — gu giống nhau tạo niềm tin.
3. **Đơn giản trước, thông minh sau** — thuật toán match bắt đầu đơn giản, minh bạch.
4. **Dữ liệu người dùng là của người dùng** — quyền riêng tư mặc định an toàn (xem
   [`10-security-privacy.md`](10-security-privacy.md)).

## 6. Tiêu chí thành công (định hướng)

- Người dùng đăng được trải nghiệm hoàn chỉnh (địa chỉ + map + ảnh + đánh giá) < 3 phút.
- Tìm được ≥ 1 người "cùng gu" có ý nghĩa từ taste profile.
- Admin kiểm duyệt & quản lý nội dung không cần đụng vào DB thủ công.
