# Tính năng: Habit Tracker (bảng theo ngày + đầu mục cá nhân)

## 1. Mục tiêu

Bảng kiểu Excel theo **tháng/ngày**, dữ liệu **per user**:

| Thành phần | Nguồn |
|---|---|
| **Hàng** | **Đầu mục cá nhân** (`user_habit_items`) — user tự chọn/tạo |
| **Cột** | Ngày trong tháng |
| **Ô** | Trạng thái user: trống → ✓ → ✗ → trống |
| **Mẫu admin** | `habit_items` — **chỉ gợi ý**; không phải hàng trên bảng |

## 2. Đầu mục cá nhân vs mẫu admin

### Mẫu admin (`habit_items`)
- Admin CRUD trong Admin SPA (“Mẫu Habit”).
- User **chọn** mẫu → hệ thống **sao chép** name/icon/description vào `user_habit_items`
  (kèm `template_habit_item_id`).
- **Không** chia sẻ row admin; đổi mẫu admin sau **không** ghi đè bản user đã copy
  (trừ khi product quyết định sync — v1 không sync).

### Mẫu sẵn (starter)
- Lần đầu user mở `/habits` hoặc `/habits/items` (và kho khi load widget): nếu **chưa có**
  `user_habit_items` nào → tự copy toàn bộ `habit_items` active (admin) sang list cá nhân.
- Catalog admin trống → fallback `HabitService::STARTER_SAMPLES` (custom rows).
- User **xoá** được từng mẫu sẵn ở trang Đầu mục (nút Xoá). Xoá không re-seed tự động.
- Nút **Thêm mẫu sẵn** trên trang Đầu mục: bổ sung các mẫu còn thiếu (`applyStarterTemplates`).

### Tuỳ chỉnh user
- User nhập **text tự do** (tên, mô tả) + **chọn icon từ bảng preset**
  (`UserHabitItem::ICONS`, component `x-habit-icon-picker`) → `user_habit_items`
  với `template_habit_item_id = null`.
- Icon create: validate `Rule::in(ICONS)`. Edit: giữ icon từ mẫu admin nếu ngoài list.
- **Không** ghi vào `habit_items` / admin catalog.

### Quản lý
- User sửa tên/icon, ẩn (`is_active`), xoá đầu mục (cascade entries + history).
- Mỗi user + mỗi template tối đa 1 lần adopt (`UNIQUE user_id + template_habit_item_id`).

## 3. Trạng thái ô

| Lần ấn | Status | DB |
|---|---|---|
| 0 | trống | không có `habit_entries` |
| 1 | `done` ✓ | insert/update |
| 2 | `missed` ✗ | update |
| 3 | trống | delete entry |

Mọi cycle → `habit_entry_histories`.

## 4. Schema

- `habit_items` — template admin  
- `user_habit_items` — hàng của user  
- `habit_entries` — UNIQUE `(user_id, user_habit_item_id, entry_date)`  
- `habit_entry_histories` — audit  

## 5. Routes (web, auth:web)

| Path | Việc |
|---|---|
| `GET /habits` | Bảng tháng |
| `GET/POST /habits/items` | Quản lý đầu mục + thêm template/custom |
| `PUT/DELETE /habits/items/{userHabitItem}` | Sửa / xoá |
| `GET /habits/history` | Lịch sử |
| `POST /habits/cycle` | body: `user_habit_item_id`, `date` |

API tương ứng: `/api/habits/grid`, `/items`, `/cycle`, `/history`.

Admin:
- `/api/admin/habit-items` — templates only
- `/api/admin/users/{user}/habits/summary` — đầu mục + stats tháng của user
- `/api/admin/users/{user}/habits/history` — lịch sử cycle (phân trang)
- `/api/admin/users/{user}/habits/grid` — lưới tháng (read-only)
- Admin SPA **Người dùng** → nút **Lịch sử habit** (modal tổng quan + bảng history)

## 6. UI

- Empty grid → CTA “Thêm đầu mục”.
- Trang items: form text tuỳ chỉnh + list mẫu còn lại + list đang theo dõi (sửa/xoá).
- Grid Alpine cycle JSON.
- **Charts `/habits`:** (1) donut cơ cấu tháng live, (2) bar hiệu suất theo đầu mục live.
- **Charts kho `/`:** (1) cột 7 ngày gần nhất, (2) donut tháng + top đầu mục.

## 7. Test

- Adopt template; custom không vào admin; không adopt trùng; isolation per user;
  cycle 3 bước; rename/delete; admin template CRUD.
