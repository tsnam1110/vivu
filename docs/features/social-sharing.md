# Tính năng: Chia sẻ mạng xã hội

Liên quan: [`../10-security-privacy.md`](../10-security-privacy.md),
[`experiences-and-categories.md`](experiences-and-categories.md).

## 1. Mục tiêu
Cho phép chia sẻ nhanh một trải nghiệm ra mạng xã hội và sao chép liên kết.

## 2. Kênh hỗ trợ (v1)
| Kênh | Cách |
|---|---|
| Facebook | `https://www.facebook.com/sharer/sharer.php?u={url}` |
| X / Twitter | `https://twitter.com/intent/tweet?url={url}&text={title}` |
| Zalo | Zalo share URL (nếu có SDK/endpoint phù hợp) |
| Sao chép link | Clipboard API (Alpine) |
| Web Share API | `navigator.share()` trên thiết bị hỗ trợ (mobile) |

> Ưu tiên **share qua URL công khai** (không cần SDK nặng). Dùng Web Share API khi
> khả dụng để có trải nghiệm native trên mobile.

## 3. Open Graph & Meta (bắt buộc để share đẹp)
Mỗi trang experience công khai phải có:
```html
<meta property="og:title" content="{title}">
<meta property="og:description" content="{mô tả ngắn}">
<meta property="og:image" content="{ảnh bìa tuyệt đối, https}">
<meta property="og:url" content="{url chính tắc}">
<meta property="og:type" content="article">
<meta name="twitter:card" content="summary_large_image">
```
- Ảnh bìa dùng `media` có `is_cover`; nếu không có, dùng ảnh mặc định.
- URL canonical dùng slug.

## 4. Quy tắc
1. Chỉ share **URL công khai**; không đính kèm dữ liệu cá nhân người dùng.
2. Nội dung ẩn/`hidden`/`draft` **không** có URL chia sẻ hoạt động (trả 404/redirect).
3. Nút share là client-side thuần; **không** cần gọi backend (trừ khi đếm lượt share
   — tuỳ chọn, qua endpoint riêng có rate limit).

## 5. (Tuỳ chọn) Đếm lượt share
- Nếu muốn thống kê: `POST /api/experiences/{id}/shares` (throttle), tăng bộ đếm.
- Không bắt buộc ở v1.

## 6. Test tối thiểu
- Trang experience công khai có đủ thẻ OG hợp lệ, ảnh tuyệt đối HTTPS.
- Link share Facebook/X sinh đúng URL encode.
- Sao chép link hoạt động; nội dung không công khai không share được.
