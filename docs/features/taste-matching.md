# Tính năng: Ghép "gu" người dùng (Taste-matching)

Liên quan: [`../03-domain-model.md`](../03-domain-model.md),
[`../04-database-schema.md`](../04-database-schema.md) (§3, §11),
[`../10-security-privacy.md`](../10-security-privacy.md).

## 1. Mục tiêu
Tìm **người có gu tương đồng** với bản thân — người có tính cách, sở thích, và (mở
rộng) hành vi trải nghiệm giống nhau — để theo dõi và tin tưởng gợi ý của họ.

Đây là **điểm khác biệt cốt lõi** của ViVu.

## 2. Dữ liệu đầu vào (taste signals)
1. **Personality** (tính cách) — mảng slug nhãn trong `user_profiles.personality`.
2. **Interests** (sở thích) — mảng slug nhãn trong `user_profiles.interests`.
3. (Mở rộng v1.1) **Hành vi**: danh mục/thẻ của experience user đã đăng/like/đánh giá cao.
4. (Tuỳ chọn) `location_city` — ưu tiên người cùng khu vực.

Nhãn chuẩn hoá trong bảng `taste_traits` (admin quản lý) để đảm bảo nhất quán khi so khớp.

## 3. Thuật toán v1 (đơn giản, minh bạch)

Nguyên tắc: **đơn giản trước, thông minh sau** — không dùng ML ở v1.

### Điểm tương đồng nhãn — Jaccard
Với hai tập nhãn A, B (gộp personality + interests):
```
jaccard(A, B) = |A ∩ B| / |A ∪ B|      (0 → 1)
```

### Công thức điểm tổng (đề xuất)
```
score = w1 * jaccard(personality) 
      + w2 * jaccard(interests)
      + w3 * jaccard(fav_categories)      # mở rộng: danh mục ưa thích suy từ hành vi
      + w4 * sameCityBonus                # 0 hoặc nhỏ, ưu tiên cùng thành phố
```
Trọng số đề xuất v1: `w1=0.4, w2=0.4, w3=0.15, w4=0.05` (tinh chỉnh sau).
Chỉ trả về ứng viên có `score ≥ ngưỡng` (vd 0.15) và **không phải chính mình**.

> Cách khác tương đương: biểu diễn nhãn thành vector nhị phân và dùng **cosine
> similarity**. Chọn một cách, ghi rõ trong hiện thực, giữ nhất quán.

## 4. Endpoint
```
GET /api/users/matches            # (auth:web) người cùng gu với tôi
GET /api/users/matches?trait=...  # lọc theo nhãn cụ thể
GET /api/users?personality[]=..&interests[]=..   # tìm người theo nhãn (khám phá)
```
Trả danh sách user (Resource công khai) kèm `match_score` và **các nhãn trùng** để
giải thích "vì sao gợi ý" (tăng minh bạch & tin cậy).

```json
{
  "data": [
    {
      "username": "an_nguyen",
      "avatar_url": "...",
      "match_score": 0.62,
      "shared_traits": ["phieu-luu", "am-thuc", "nhiep-anh"]
    }
  ]
}
```

## 5. Hiệu năng
- v1: tính trong Service. Với quy mô nhỏ, lọc ứng viên bằng **giao nhãn** ở DB
  (JSON contains / bảng phụ) rồi tính điểm trên tập rút gọn — tránh quét toàn bộ user.
- Khi lớn: cân nhắc bảng phụ `user_trait` (user_id, trait_id) để join nhanh, hoặc
  cache điểm qua queue. Ghi ADR khi chuyển.
- **Không** tạo quan hệ "friendship" cứng ở v1 — điểm là tính toán, không lưu lâu dài.

## 6. Quyền riêng tư
- User bật/tắt việc hồ sơ gu tham gia gợi ý (mặc định: tham gia, nhưng có thể ẩn
  chi tiết nhãn khỏi công khai).
- Không lộ email hay dữ liệu nhạy cảm trong kết quả match.
- Xem [`../10-security-privacy.md`](../10-security-privacy.md) §4.

## 7. Trải nghiệm người dùng
- Trang "Tìm người cùng gu": danh sách xếp theo `match_score`, hiển thị nhãn trùng.
- Cho phép **theo dõi (follow)** người cùng gu (tính năng follow có thể tách file
  riêng khi làm) để xem trải nghiệm mới của họ.
- Khuyến khích user hoàn thiện taste profile (thanh tiến độ) để match tốt hơn.

## 8. Test tối thiểu
- Hai user trùng nhiều nhãn → `match_score` cao hơn hai user ít trùng.
- Không tự match chính mình.
- User dưới ngưỡng không xuất hiện.
- `shared_traits` liệt kê đúng nhãn giao nhau.
- User ẩn hồ sơ gu không xuất hiện trong gợi ý của người khác (nếu chọn ẩn).
