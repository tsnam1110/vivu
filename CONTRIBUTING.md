# Hướng dẫn đóng góp — ViVu

Cảm ơn bạn tham gia phát triển ViVu. Tài liệu này tóm tắt quy trình; chi tiết nằm
trong [`docs/`](docs/).

## Trước khi bắt đầu
1. Đọc [`CLAUDE.md`](CLAUDE.md) — tổng quan & **Golden Rules**.
2. Dựng môi trường theo [`docs/06-setup-development.md`](docs/06-setup-development.md).
3. Khởi chạy local (một lệnh ở root):
   ```bash
   composer dev
   # Public:  http://127.0.0.1:8000/
   # Admin:   http://localhost:5200/
   ```
4. Nếu là **AI Agent**: đọc thêm [`docs/08-agent-playbook.md`](docs/08-agent-playbook.md).

## Quy tắc cốt lõi (bắt buộc)
- **Tài liệu là nguồn sự thật** — đổi schema/API/kiến trúc thì cập nhật tài liệu cùng lúc.
- **User guard ≠ admin guard** — không trộn tài khoản/session/endpoint.
- **Validate ở server** (Form Request) cho mọi input.
- **Không commit secret** (`.env`, khoá API).
- **Migration chỉ tiến** — sai thì tạo file mới.
- Ngôn ngữ: tài liệu/giao tiếp tiếng Việt; code/định danh/commit tiếng Anh.

## Quy trình
1. Tạo nhánh: `feature/...`, `fix/...`, hoặc `docs/...`
   (xem [`docs/09-git-workflow.md`](docs/09-git-workflow.md)).
2. Viết code theo [`docs/07-coding-standards.md`](docs/07-coding-standards.md) và đặt
   file đúng chỗ theo [`docs/13-project-structure.md`](docs/13-project-structure.md).
3. Thêm/cập nhật test ([`docs/12-testing.md`](docs/12-testing.md)).
4. Chạy kiểm tra:
   ```bash
   php artisan pint
   php artisan test
   # admin: npm run lint && npx tsc --noEmit
   ```
5. Commit theo Conventional Commits; mở PR mô tả **cái gì / vì sao / kiểm chứng thế nào**.

## Definition of Done
- [ ] Test liên quan xanh.
- [ ] Lint/format/phân tích tĩnh pass.
- [ ] Tài liệu cập nhật nếu đổi schema/API/kiến trúc.
- [ ] Không secret, không code/log rác.
- [ ] Đã xử lý lỗi + trạng thái rỗng/loading (nếu có UI).
