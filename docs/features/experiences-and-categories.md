# Tính năng: Trải nghiệm, Danh mục & Thẻ

Liên quan: [`../03-domain-model.md`](../03-domain-model.md),
[`../04-database-schema.md`](../04-database-schema.md),
[`maps-and-location.md`](maps-and-location.md).

## 1. Mục tiêu
Cho phép người dùng đăng **trải nghiệm** gắn địa điểm, phân loại theo **danh mục** và
gắn **thẻ** chi tiết; người khác duyệt/tìm kiếm theo danh mục, thẻ, vị trí.

## 2. Experience (trải nghiệm)

### Tạo/sửa
- Trường: `title`, `content`, `category_id` (một), `tags[]` (nhiều), `place_name`,
  `address`, `latitude`, `longitude`, `google_place_id`, ảnh (`media`).
- Chọn vị trí qua Google Maps (autocomplete điền `address` + toạ độ). Xem
  [`maps-and-location.md`](maps-and-location.md).
- Ảnh: upload nhiều, chọn 1 ảnh bìa (`is_cover`); resize qua queue.
- `slug` tự sinh từ `title` (spatie/sluggable), đảm bảo duy nhất.

### Trạng thái
`draft → pending → published → hidden` (xem
[`../03-domain-model.md`](../03-domain-model.md) §4). Quyết định v1: nếu **bỏ** kiểm
duyệt trước, cho `draft → published` trực tiếp, admin ẩn khi cần — ghi rõ khi hiện thực.

### Quy tắc
1. Một Experience thuộc **đúng một** Category, có **nhiều** Tag.
2. Khi `published`: bắt buộc có `latitude`, `longitude`, `published_at`.
3. Chỉ **chủ sở hữu** (hoặc admin) sửa/xoá — kiểm qua Policy.
4. Xoá = soft delete; nội dung ẩn khỏi công khai.
5. Cache `rating_avg`, `rating_count`, `reaction_count` cập nhật qua Observer.

### Hiển thị công khai (SEO)
- URL: `/experiences/{slug}` hoặc `/e/{slug}`.
- Có Open Graph, meta description, schema.org (`Place`/`LocalBusiness`).
- Đếm `view_count` (tránh tăng ảo — throttle theo session/IP).

## 3. Category (danh mục)
- Quản lý bởi admin (CRUD trong admin panel).
- Trường: `name`, `slug`, `icon`, `description`, `sort_order`, `is_active`.
- Ví dụ seed: Ăn, Uống, Cà phê, Du lịch, Lưu trú.
- Không xoá cứng danh mục còn Experience (FK `ON DELETE RESTRICT`) — vô hiệu hoá
  (`is_active = false`) thay vì xoá.

## 4. Tag (thẻ tuỳ chỉnh)
- Gắn theo danh mục: `category_id` trỏ danh mục, **hoặc** `null` = thẻ toàn cục.
- Ví dụ: "món Hàn", "món Nhật" (thuộc Ăn); "biển", "núi" (Du lịch); "yên tĩnh",
  "sống ảo" (toàn cục).
- Khi user gắn thẻ cho Experience: UI **NÊN** gợi ý thẻ thuộc danh mục đã chọn +
  thẻ toàn cục, và cho phép tạo thẻ mới (nếu bật) — thẻ mới chờ admin chuẩn hoá.
- `usage_count` cache để gợi ý thẻ phổ biến.
- Admin quản lý (gộp thẻ trùng, đổi tên, gán danh mục).

## 5. Duyệt & tìm kiếm
- Lọc: theo `category`, `tags[]`, `city`, tìm quanh đây (`lat/lng/radius`), text `q`.
- Sắp xếp: mới nhất (`-published_at`), đánh giá cao (`-rating_avg`), nhiều tương tác.
- Phân trang chuẩn envelope ([`../05-api-conventions.md`](../05-api-conventions.md)).

## 6. Endpoint chính
Xem [`../05-api-conventions.md`](../05-api-conventions.md) §8 (experiences, categories,
tags). Admin CRUD category/tag dưới `/api/admin/*`.

## 7. Test tối thiểu
- Tạo experience đầy đủ (category + tags + toạ độ) thành công.
- Published thiếu toạ độ → 422.
- Người khác sửa experience không phải của mình → 403.
- Lọc theo category/tag/vị trí trả đúng tập kết quả.
- Gắn thẻ ngoài danh mục hợp lệ theo quy tắc gợi ý.
