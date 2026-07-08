# Tính năng: Đánh giá, Bình luận & Cảm xúc

Liên quan: [`../04-database-schema.md`](../04-database-schema.md) (§9, §10),
[`../03-domain-model.md`](../03-domain-model.md).

## 1. Mục tiêu
Cho phép người dùng **bình luận + chấm sao**, **trả lời** nhau, và **thả cảm xúc**
(like/tim) trên trải nghiệm.

## 2. Comment (bình luận + rating)

### Hành vi
- User đã đăng nhập bình luận trên Experience: `POST /api/experiences/{id}/comments`.
- Trường: `body` (bắt buộc), `rating` (1–5, tuỳ chọn), `parent_id` (trả lời 1 cấp,
  tuỳ chọn).
- Xoá bình luận: chủ sở hữu hoặc admin (`DELETE /api/comments/{id}`), soft delete.
- `status`: `visible` (mặc định) / `pending` / `hidden` (kiểm duyệt).

### Quy tắc
1. `rating` nếu có phải trong 1–5 (Form Request `between:1,5`).
2. Một user **có thể** bình luận nhiều lần; nhưng khi tính `rating_avg`, cân nhắc
   chỉ lấy **rating mới nhất** của mỗi user cho mỗi experience (quyết định v1: lấy
   trung bình tất cả rating hiện có — ghi rõ khi hiện thực, giữ nhất quán).
3. Trả lời lồng **1 cấp** (không lồng vô hạn) ở v1.
4. Nội dung hiển thị escape (chống XSS). Rate limit tạo comment (chống spam).

### Rating tổng hợp
- `experiences.rating_avg` & `rating_count` là **cột cache**, cập nhật qua
  **Observer** khi comment có rating được tạo/sửa/xoá.
- Không `COUNT/AVG` trực tiếp mỗi lần render trang.

## 3. Reaction (cảm xúc: like, tim)

### Hành vi
- Thả/đổi cảm xúc: `POST /api/experiences/{id}/reactions` với `type` (`like`|`love`).
- Gỡ: `DELETE /api/experiences/{id}/reactions`.
- Polymorphic (`reactable_type`/`reactable_id`) → tái dùng cho Comment sau này.

### Quy tắc
1. **Một user có đúng một reaction cho mỗi đối tượng** (UNIQUE
   `user_id + reactable_type + reactable_id`). Đổi từ like sang love = **update**
   (không tạo bản ghi mới).
2. Thả lại cùng loại đang có = gỡ (toggle) — hoặc giữ nguyên; chọn hành vi **toggle**
   cho like đơn giản, và **thay thế** giữa các loại. Ghi rõ khi hiện thực FE.
3. `experiences.reaction_count` là cột cache, cập nhật qua Observer.
4. Phải đăng nhập mới reaction (auth:web). Khách xem thấy số đếm.

### Trả về
```json
{ "data": { "type": "love", "counts": { "like": 12, "love": 30 }, "total": 42 } }
```

## 4. Kiểm duyệt (admin)
- Admin xem comment `pending`/bị báo cáo: `GET /api/admin/comments?status=pending`.
- Ẩn/hiện: `PATCH /api/admin/comments/{id}` đổi `status`.

## 5. Hiệu năng
- Eager load `comments.user`, đếm reaction qua cột cache.
- Phân trang comment (`per_page`), tải thêm khi cuộn (public site: Alpine + API).

## 6. Test tối thiểu
- Bình luận có/không rating; rating ngoài 1–5 → 422.
- Reaction lần 2 khác loại → update, không tạo trùng (kiểm UNIQUE).
- `rating_avg`/`reaction_count` cập nhật đúng sau khi thêm/xoá.
- Khách (chưa đăng nhập) không thả được reaction/comment (401).
- Admin ẩn được comment vi phạm.
