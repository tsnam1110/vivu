# Tính năng: Bản đồ & Định vị (Google Maps)

Liên quan: [`../02-tech-stack.md`](../02-tech-stack.md),
[`../10-security-privacy.md`](../10-security-privacy.md),
[`../04-database-schema.md`](../04-database-schema.md) (§6).

## 1. Mục tiêu
Gắn mỗi trải nghiệm với **vị trí thật trên bản đồ**: chọn địa điểm khi đăng, hiển thị
marker khi xem, và **tìm quanh đây**.

## 2. Dịch vụ Google dùng
| API | Dùng cho | Phía |
|---|---|---|
| Maps JavaScript API | Hiển thị bản đồ, marker | Client |
| Places Autocomplete | Gõ địa chỉ → gợi ý → chọn | Client |
| Geocoding | Đổi address ↔ toạ độ (khi cần backfill) | Server (tuỳ chọn) |

Key & bảo mật: xem [`../10-security-privacy.md`](../10-security-privacy.md) §5. Key
client giới hạn theo domain; key server (nếu có) giới hạn theo IP.

## 3. Luồng chọn vị trí khi đăng
1. Ô nhập địa chỉ có **Places Autocomplete**.
2. User chọn gợi ý → lấy `formatted_address`, `lat`, `lng`, `place_id` → điền vào
   `address`, `latitude`, `longitude`, `google_place_id`.
3. Bản đồ hiện marker; user **có thể kéo marker** để tinh chỉnh → cập nhật toạ độ.
4. Cho phép nhập tay địa chỉ nếu không dùng autocomplete (toạ độ có thể lấy qua
   Geocoding server-side khi lưu).

## 4. Lưu trữ
- `latitude`, `longitude`: `DECIMAL(10,7)` (độ chính xác ~1cm).
- `address`: chuỗi hiển thị. `place_name`: tên địa điểm. `google_place_id`: tham chiếu.
- **Bắt buộc toạ độ khi `status = published`.**

## 5. Hiển thị
- Trang chi tiết: bản đồ nhúng + marker + nút "Chỉ đường" (mở Google Maps với toạ độ).
- Trang danh sách/bản đồ: nhiều marker; cân nhắc cluster khi nhiều điểm.

## 6. Tìm quanh đây (nearby)
- Endpoint: `GET /api/experiences?lat=..&lng=..&radius_km=..`.
- v1: lọc bằng **bounding box** (tính min/max lat/lng từ tâm + bán kính) rồi sắp xếp
  theo khoảng cách Haversine trong Service. Index `(latitude, longitude)`.
- Khi dữ liệu lớn: cân nhắc cột `POINT` + `SPATIAL INDEX` và `ST_Distance_Sphere`
  (ghi ADR khi chuyển — xem [`../01-architecture.md`](../01-architecture.md) §7).

### Công thức Haversine (tham khảo)
Khoảng cách giữa 2 toạ độ để sắp xếp/kiểm bán kính; tính trong Service, không lặp query.

## 7. Quyền riêng tư
- Toạ độ lưu là của **địa điểm công cộng** (quán, điểm du lịch) — công khai được.
- **Không** lưu/hiển thị vị trí GPS chính xác của **người dùng**; nếu dùng "tìm quanh
  tôi", lấy vị trí trình duyệt tạm thời, **không** lưu.

## 8. Xử lý lỗi & giới hạn
- Autocomplete/map lỗi tải (mất mạng, key sai) → hiển thị fallback nhập địa chỉ tay,
  không chặn đăng bài (nếu không yêu cầu published ngay).
- Theo dõi hạn mức API để tránh phát sinh chi phí bất thường.

## 9. Test tối thiểu
- Lưu experience với toạ độ hợp lệ; toạ độ ngoài khoảng (-90..90 / -180..180) → 422.
- Nearby trả đúng các điểm trong bán kính, loại điểm ngoài bán kính.
- Published không toạ độ → 422.
