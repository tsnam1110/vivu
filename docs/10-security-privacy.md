# 10 — Bảo mật & Quyền riêng tư

## 1. Nguyên tắc nền tảng
1. **Không tin input client** — validate lại toàn bộ ở server (Form Request).
2. **Đặc quyền tối thiểu** — user chỉ thao tác tài nguyên của mình; admin theo role.
3. **An toàn mặc định** — dữ liệu nhạy cảm private trừ khi cố ý công khai.
4. **Không lộ bí mật** — secret qua `.env`, không log, không trả ra client.

## 2. Xác thực & phân quyền
- **Hai guard tách biệt:** `web` (user, session) và `admin` (Sanctum token). Không
  dùng chung bảng/endpoint. Xem [`features/auth-and-accounts.md`](features/auth-and-accounts.md).
- Mật khẩu hash bằng bcrypt/argon (Laravel mặc định). Không bao giờ lưu plaintext.
- **Policy/Gate** cho quyền sở hữu (sửa/xoá experience, comment). Không kiểm quyền
  bằng `if` rải rác trong controller.
- Admin phân quyền qua `spatie/laravel-permission` (role: `super-admin`, `moderator`).
- Rate limit đăng nhập (5/phút/IP) và các endpoint ghi (throttle).

## 3. Bảo vệ web thường gặp
| Nguy cơ | Biện pháp |
|---|---|
| CSRF | Middleware CSRF cho form web & API session; SPA admin dùng Sanctum + `SANCTUM_STATEFUL_DOMAINS` đúng. |
| XSS | Blade auto-escape (`{{ }}`); tránh `{!! !!}` với dữ liệu người dùng; sanitize nội dung rich text. |
| SQL Injection | Dùng Eloquent/binding, **không** nối chuỗi query. |
| Mass assignment | Khai báo `$fillable`, không `$guarded = []`. |
| IDOR | Kiểm quyền sở hữu qua Policy trước mọi thao tác trên tài nguyên theo `id`. |
| Upload độc hại | Validate mime/size ảnh; lưu ngoài webroot/qua disk; đổi tên file; resize lại (loại metadata). |
| Rò rỉ dữ liệu qua API | Dùng Resource, whitelist field; không trả `email`/`password` người khác. |

## 4. Dữ liệu cá nhân & quyền riêng tư người dùng

### 4.0 Văn bản pháp lý công khai (public site)
| Route | Nội dung | Căn cứ chính (tham chiếu) |
|---|---|---|
| `/terms` | Điều khoản sử dụng dịch vụ | Luật Giao dịch điện tử 2023, An ninh mạng 2018, BLDS 2015, Luật BVQLNTD 2023… |
| `/privacy` | Chính sách bảo vệ dữ liệu cá nhân | **Nghị định 13/2023/NĐ-CP**, An ninh mạng, ATTTM… |
| `/community` | Quy tắc cộng đồng / nội dung UGC | An ninh mạng, quy định nội dung bị cấm |
| `/cookies` | Cookie & lưu trữ trên thiết bị | NĐ 13/2023, minh bạch xử lý dữ liệu |

> Cập nhật email/MST/địa chỉ chủ thể vận hành trước production. Nội dung là khung vận hành —
> rà soát với tư vấn pháp lý khi thương mại hoá.

- **Taste profile (tính cách/sở thích), email, vị trí** là dữ liệu cá nhân.
  - `email` **không** hiển thị công khai trên hồ sơ.
  - `personality`/`interests` dùng cho taste-match: cho phép user bật/tắt hiển thị
    công khai (mặc định: dùng để match nhưng không phô bày chi tiết nếu user chọn ẩn).
  - `location_city` ở mức thành phố, **không** lưu vị trí GPS chính xác của người dùng
    (khác với toạ độ **địa điểm** trong Experience — đó là địa điểm công cộng).
- Cho phép user **xoá tài khoản** (soft delete + quy trình ẩn/xoá nội dung).
- Không bán/chia sẻ dữ liệu cá nhân cho bên thứ ba.

## 5. Google Maps API key
- Key **client** (Maps JS, Places Autocomplete): giới hạn theo **HTTP referrer**
  (domain), chỉ bật API cần thiết.
- Geocoding **server-side** (nếu dùng): key riêng giới hạn theo **IP server**, gọi từ
  backend, **không** lộ ra client.
- Không commit key. Đặt trong `.env`: `GOOGLE_MAPS_API_KEY`,
  `GOOGLE_MAPS_SERVER_KEY` (nếu tách).
- Theo dõi hạn mức để tránh lạm dụng phát sinh chi phí.

## 6. Chia sẻ MXH
- Nút share chỉ mở URL công khai của experience; **không** đính kèm dữ liệu cá nhân.
- Open Graph tags dùng dữ liệu công khai (title, ảnh bìa, mô tả).

## 7. Nội dung & kiểm duyệt
- Experience và Comment có `status` để admin ẩn nội dung vi phạm.
- Cân nhắc lọc từ khoá/spam ở tầng Service khi tạo comment (v1 tối thiểu: rate limit).

## 8. Bí mật & cấu hình
- `.env` không commit; cung cấp `.env.example` không chứa giá trị thật.
- `APP_DEBUG=false` ở production; không hiển thị stack trace ra người dùng.
- HTTPS bắt buộc ở production; cookie `secure` + `httpOnly` + `SameSite`.
- Log không chứa mật khẩu, token, PII.

## 9. Checklist bảo mật khi thêm tính năng
- [ ] Có validate server không?
- [ ] Có kiểm quyền sở hữu/role không?
- [ ] Output có đi qua Resource (không lộ field thừa) không?
- [ ] Có rate limit cho endpoint ghi không?
- [ ] Input file/URL/HTML có được sanitize không?
- [ ] Có log/trả secret ra ngoài không? (phải: không)
