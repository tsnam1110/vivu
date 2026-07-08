# 12 — Chiến lược kiểm thử (Testing)

Bổ sung chi tiết cho phần Test ở [`07-coding-standards.md`](07-coding-standards.md) §E.

## 1. Triết lý
- **Test hành vi, không test hiện thực.** Ưu tiên Feature test qua HTTP.
- **Kim tự tháp test:** nhiều Feature/Integration test cho luồng chính, Unit test cho
  logic thuần (thuật toán taste-match, tính khoảng cách), ít E2E.
- Mỗi bug được sửa → thêm test tái hiện để không tái phát.
- **Không tuyên bố "xong" nếu test chưa xanh** (Golden Rule #8).

## 2. Backend (Laravel — Pest hoặc PHPUnit)

### Cấu trúc
```
tests/
  Feature/     # test qua HTTP: auth, experiences, comments, reactions, matches
  Unit/        # test Service/thuật toán thuần
```

### Nguyên tắc
- Dùng `RefreshDatabase` + **factory** cho mỗi model.
- Dùng SQLite in-memory hoặc DB test riêng (`DB_DATABASE=vivu_test`) — **không** chạy
  test trên DB dev.
- Mỗi endpoint quan trọng: kiểm **thành công**, **validate lỗi (422)**, **phân quyền
  (401/403)**.
- Mock dịch vụ ngoài (Google Geocoding) — không gọi API thật trong test.
- Dùng `Queue::fake()`, `Mail::fake()`, `Storage::fake()` khi test job/mail/upload.

### Ma trận test tối thiểu (theo tính năng)
| Khu vực | Ca bắt buộc |
|---|---|
| Auth | user đăng ký/đăng nhập/khoá; admin login token; user không vào được `/api/admin` |
| Experience | tạo đủ trường; published thiếu toạ độ → 422; người lạ sửa → 403; lọc theo category/tag/nearby |
| Comment/Rating | có/không rating; rating ngoài 1–5 → 422; `rating_avg` cập nhật đúng |
| Reaction | không tạo trùng (UNIQUE); đổi loại = update; `reaction_count` đúng; khách → 401 |
| Tag/Category | admin CRUD; không xoá category còn experience |
| Taste-match | trùng nhiều nhãn điểm cao hơn; không tự match; dưới ngưỡng bị loại; `shared_traits` đúng |
| Map | toạ độ ngoài khoảng → 422; nearby trả đúng bán kính |

## 3. Frontend Admin (React + Vitest — tuỳ chọn)
- Test component quan trọng (form CRUD) và hàm util.
- Test luồng: đăng nhập → nhận token → gọi API (mock axios/MSW).
- Không cần độ phủ cao ở v1; ưu tiên luồng nghiệp vụ chính.

## 4. Chất lượng tĩnh (chạy như một phần "test")
- **Pint** (format) + **Larastan/PHPStan** (phân tích tĩnh) cho BE.
- **ESLint** + **tsc --noEmit** cho admin.
- Các lệnh này phải pass trước khi coi thay đổi là xong.

## 5. Lệnh
```bash
php artisan test                     # toàn bộ
php artisan test --filter=Experience # lọc
php artisan test --parallel          # chạy song song
# admin
cd admin && npm run test && npm run lint && npx tsc --noEmit
```

## 6. CI (khi thiết lập repo)
Pipeline tối thiểu: cài phụ thuộc → Pint check → PHPStan → `php artisan test` →
(admin) lint + tsc + build. Chặn merge nếu đỏ. Xem
[`09-git-workflow.md`](09-git-workflow.md).

## 7. Dữ liệu test
- Dùng **factory**; tránh phụ thuộc dữ liệu seeder production.
- Seeder demo (nếu có) tách riêng, đánh dấu rõ, không chạy ở prod.
