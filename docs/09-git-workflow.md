# 09 — Quy trình Git & cộng tác

> Dự án hiện **chưa khởi tạo git** (`git init` khi bắt đầu code). Tài liệu này định
> nghĩa quy tắc áp dụng ngay khi có repo.

## 1. Nhánh (branching)
- `main` — luôn ở trạng thái chạy được (deployable). Không commit trực tiếp.
- `develop` — nhánh tích hợp (tuỳ chọn nếu team nhỏ có thể bỏ, PR thẳng vào `main`).
- Nhánh tính năng: `feature/<mô-tả-ngắn>` (vd `feature/experience-crud`).
- Sửa lỗi: `fix/<mô-tả>`. Tài liệu: `docs/<mô-tả>`.

## 2. Commit message (Conventional Commits)
Định dạng: `<type>(<scope>): <mô tả ngắn, tiếng Anh, thức mệnh lệnh>`

| type | Dùng khi |
|---|---|
| feat | thêm tính năng |
| fix | sửa lỗi |
| docs | tài liệu |
| refactor | tái cấu trúc, không đổi hành vi |
| test | thêm/sửa test |
| chore | cấu hình, build, dependency |
| perf | tối ưu hiệu năng |

Ví dụ:
```
feat(experience): add create endpoint with map coordinates
fix(reaction): prevent duplicate reaction per user
docs(schema): add taste_traits table
```

Cuối commit message do Agent tạo, thêm dòng:
```
Co-Authored-By: Claude Opus 4.8 <noreply@anthropic.com>
```

## 3. Kích thước & phạm vi commit
- Một commit = một ý thay đổi mạch lạc. Không gộp nhiều tính năng.
- Không commit code chết, `dd()`, `console.log`, file rác, `.env`.

## 4. Pull Request
- Tiêu đề rõ ràng; mô tả **cái gì / vì sao / kiểm chứng thế nào**.
- Liên kết tài liệu/tính năng liên quan.
- Checklist trước khi mở PR:
  - [ ] Test pass (`php artisan test`, lint FE).
  - [ ] Tài liệu cập nhật nếu đổi schema/API.
  - [ ] Không secret, không file rác.
- Body PR do Agent tạo kết thúc bằng:
  ```
  🤖 Generated with [Claude Code](https://claude.com/claude-code)
  ```

## 5. .gitignore (tối thiểu)
```
/vendor
/node_modules
/admin/node_modules
/admin/dist
/public/build
/public/storage
/storage/*.key
.env
.env.*
!.env.example
*.log
.DS_Store
Thumbs.db
```

## 6. Quy tắc cho Agent về Git
- **Chỉ** commit/push khi người dùng yêu cầu rõ ràng.
- Nếu đang ở `main`, tạo nhánh trước khi commit.
- **Không** `--no-verify`, không bỏ qua hook, không force-push trừ khi được yêu cầu.
- Không amend commit đã push; tạo commit mới.
- Trước thao tác phá huỷ (`reset --hard`, `push --force`) cân nhắc phương án an toàn hơn.

## 7. Migration & Git
- Mỗi migration là một file mới, timestamp tăng dần.
- **Không** sửa migration đã merge/đã chạy trên môi trường chung — tạo migration mới.
- Seeder cho dữ liệu mẫu (categories, taste_traits) đặt trong `database/seeders`.
