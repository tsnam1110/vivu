# 08 — Sổ tay cho AI Agent (Agent Playbook)

> Tài liệu này dành **riêng cho AI Agent** làm việc trên ViVu. Đọc kỹ trước khi hành động.
> Dev kế thừa cũng nên đọc để hiểu kỳ vọng.

## 1. Quy trình mỗi phiên làm việc

1. **Đọc [`../CLAUDE.md`](../CLAUDE.md)** — nắm ngữ cảnh & Golden Rules.
2. **Biết URL local** (chi tiết [`06-setup-development.md`](06-setup-development.md)):
   - Public (người dùng): http://127.0.0.1:8000/
   - Admin: http://localhost:5200/
   - Khởi chạy: `composer dev` (hoặc `npm run dev`) ở root
3. **Xác định loại việc** và mở đúng tài liệu:
   - Đụng schema/DB → [`04-database-schema.md`](04-database-schema.md)
   - Đụng API → [`05-api-conventions.md`](05-api-conventions.md)
   - **Đụng giao diện (Blade/React/layout/menu/form UI)** → [`15-design-system.md`](15-design-system.md) **trước**, rồi [`07-coding-standards.md`](07-coding-standards.md)
   - Làm 1 tính năng → file tương ứng trong [`features/`](features/)
   - Viết code BE/API → [`07-coding-standards.md`](07-coding-standards.md)
4. **Lập kế hoạch ngắn** trước khi sửa nhiều file.
5. **Thực hiện** theo chuẩn.
6. **Kiểm chứng** (test/chạy thử) — không tuyên bố xong nếu chưa kiểm.
7. **Cập nhật tài liệu** nếu thay đổi làm lệch tài liệu.

## 2. Nguyên tắc bất di bất dịch (nhắc lại Golden Rules)

1. Tài liệu là nguồn sự thật — code lệch thì sửa cho khớp, hoặc đổi hướng thì cập nhật
   tài liệu **cùng lúc**.
2. **User guard ≠ admin guard** — không trộn tài khoản, session, endpoint.
3. Không hard-code secret; mọi khoá qua `.env`.
4. Ngôn ngữ: tài liệu/giao tiếp tiếng Việt; code/định danh/commit tiếng Anh.
5. Validate ở server (Form Request) — luôn luôn.
6. Không phá vỡ hợp đồng API đã công bố nếu không version hoá.
7. Migration chỉ tiến; sai thì tạo migration mới, **không** sửa file đã chạy.
8. Test trước khi tuyên bố xong; fail thì báo rõ kèm output.

## 3. Việc PHẢI làm

- Bám sát schema trong `04-database-schema.md`; nếu thấy cần cột mới → cập nhật tài
  liệu trước, tạo migration mới sau.
- Đặt business logic vào **Service**, không nhồi vào Controller.
- Trả API qua **Resource** đúng envelope.
- Dùng **enum** cho status/type.
- Thêm/ cập nhật **test** cho phần mình đụng tới.
- Eager load để tránh N+1.
- Cập nhật checklist trạng thái trong `CLAUDE.md §6` khi hoàn thành một mảng lớn.

## 4. Việc KHÔNG được làm

- ❌ Sửa migration đã tồn tại/đã chạy để "tiện" — luôn tạo migration mới.
- ❌ Trộn logic admin vào route/guard của user hoặc ngược lại.
- ❌ Trả Eloquent model thô ra API (lộ field nhạy cảm như `password`, `email` người khác).
- ❌ Commit `.env`, khoá API, dump DB, ảnh test dung lượng lớn.
- ❌ Thêm dependency lớn khi framework/AntD đã có sẵn giải pháp — nếu cần, nêu lý do.
- ❌ Tự invent palette/layout public (bỏ floating nav, đổi home thành explore, v.v.)
  mà không cập nhật [`15-design-system.md`](15-design-system.md).
- ❌ `git push`, tạo PR, hay chạy lệnh phá huỷ dữ liệu (`migrate:fresh` trên non-local,
  `DROP`, xoá file hàng loạt) khi **không** được yêu cầu rõ ràng.
- ❌ Tuyên bố "đã xong/đã test" khi chưa thực sự chạy.

## 5. Khi không chắc chắn

- Nếu quyết định thay đổi kiến trúc/schema/hợp đồng API → **hỏi người dùng** hoặc ghi
  rõ giả định và đề xuất, không tự ý phá cấu trúc hiện có.
- Nếu tài liệu mâu thuẫn với code hiện có → nêu mâu thuẫn, đề xuất cách hoà giải,
  không im lặng chọn một bên.
- Nếu thiếu thông tin (vd khoá Google Maps) → nêu rõ chỗ chặn, không bịa giá trị.
- **What to Eat / seed món:** fact calo, ngũ hành, hàn–nhiệt, lợi/hại… **chỉ** ghi khi
  có nguồn xác thực. Chưa chắc → `null` (user đóng góp sau). **Cấm** đoán mò hay
  “best-effort” để cho đủ. Đọc
  [`features/what-to-eat-seed-and-kb.md`](features/what-to-eat-seed-and-kb.md),
  [`features/what-to-eat-ruleset.md`](features/what-to-eat-ruleset.md) và
  [`features/what-to-eat-dish-catalog.md`](features/what-to-eat-dish-catalog.md)
  (inventory món — cập nhật **trước** khi ghi JSON seed).

## 6. Khi thêm một tính năng mới (checklist)

1. Đọc/ tạo file đặc tả trong `features/` và liên kết từ `docs/README.md`.
2. Cập nhật `03-domain-model.md` & `04-database-schema.md` nếu có thực thể/cột mới.
3. Migration mới + Model + quan hệ + `$fillable` + `$casts` + enum.
4. Form Request (validate + authorize) + Policy nếu cần quyền sở hữu.
5. Service chứa logic; Controller mỏng; Resource cho output.
6. Route đúng không gian (`/api`, `/api/admin`, hoặc `web.php`).
7. Test feature cho luồng chính + biên.
8. FE: admin (AntD) và/hoặc public (Blade) tuỳ tính năng — **bám** [`15-design-system.md`](15-design-system.md)
   (token, shell, IA menu, component). Đổi design → cập nhật file 15 trong cùng thay đổi.
9. Cập nhật `11-glossary.md` nếu có thuật ngữ mới.
10. Cập nhật checklist trạng thái `CLAUDE.md §6`.

## 7. Định dạng báo cáo cho người dùng

- Trả lời **tiếng Việt**, súc tích, đi thẳng vào việc.
- Tham chiếu file dạng liên kết click được (`đường-dẫn:dòng`).
- Khi hoàn thành: nêu rõ **đã làm gì**, **đã kiểm chứng thế nào**, **còn lại gì**.
- Khi có lỗi/test fail: dán phần output liên quan, không giấu.

## 8. Ranh giới an toàn (thao tác khó đảo ngược)

Trước các thao tác hướng ra ngoài hoặc khó đảo ngược, **xác nhận trước** trừ khi đã
được cho phép rõ ràng:
- Gửi email thật, gọi API bên thứ ba tốn phí/ghi dữ liệu.
- Xoá/ghi đè dữ liệu, reset DB.
- Push code, tạo/đóng PR, thay đổi cấu hình production.
