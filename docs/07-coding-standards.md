# 07 — Quy ước code

Quy ước bắt buộc cho cả Backend (Laravel/PHP) và Frontend (React/TS + Blade). Mục tiêu:
code mới **đọc giống** code xung quanh.

## A. Nguyên tắc chung
1. **Ngôn ngữ:** code, tên định danh, comment kỹ thuật, commit → **tiếng Anh**. Chuỗi
   hiển thị cho người dùng → tiếng Việt, đặt trong file lang (`lang/vi/`), không hard-code.
2. **Không magic number/string** — đặt hằng hoặc enum.
3. **Fail loud ở dev, an toàn ở prod** — không nuốt exception im lặng.
4. **Một hàm làm một việc.** Hàm > ~40 dòng hoặc > 3 cấp lồng → cân nhắc tách.
5. **Không comment cái hiển nhiên**; comment cái "tại sao", không phải "cái gì".

---

## B. Backend — Laravel / PHP

### B.1 Cấu trúc & lớp
- `app/Http/Controllers` — **mỏng**, chỉ điều phối.
- `app/Http/Requests` — **mọi** validate + authorize (Form Request bắt buộc cho input).
- `app/Http/Resources` — biến đổi model → JSON (không trả model thô ra API).
- `app/Services` — business logic, giao dịch, gọi queue.
- `app/Models` — Eloquent: quan hệ, scope, cast; **không** chứa logic nghiệp vụ nặng.
- `app/Observers` — cập nhật cache (`rating_avg`, `reaction_count`).
- `app/Jobs` — tác vụ queue (resize ảnh, gửi mail).
- `app/Policies` — phân quyền chi tiết (chủ sở hữu experience, v.v.).

### B.2 Quy ước đặt tên
| Loại | Quy ước | Ví dụ |
|---|---|---|
| Class | PascalCase | `ExperienceService` |
| Method/biến | camelCase | `createExperience()` |
| Bảng | snake_case số nhiều | `experiences` |
| Cột | snake_case | `rating_avg` |
| Route name | dot.notation | `experiences.store` |
| Enum PHP | PascalCase + case PascalCase | `ExperienceStatus::Published` |

### B.3 Chuẩn code
- Tuân **PSR-12**, format bằng **Laravel Pint** trước khi commit.
- Khai báo kiểu chặt: `declare(strict_types=1);`, type-hint tham số & return.
- Dùng **Eloquent** thay query builder thô khi có thể; tránh N+1 → `with()` eager load.
- Bọc thao tác đa bảng trong `DB::transaction()`.
- Trạng thái/loại cố định dùng **PHP enum** (`ExperienceStatus`, `ReactionType`).
- Cast JSON columns (`personality`, `interests`) qua `$casts` → `array`.

### B.4 Validate (ví dụ khung Form Request)
```php
public function rules(): array
{
    return [
        'title'       => ['required', 'string', 'max:180'],
        'category_id' => ['required', 'exists:categories,id'],
        'tags'        => ['array', 'max:10'],
        'tags.*'      => ['integer', 'exists:tags,id'],
        'latitude'    => ['nullable', 'numeric', 'between:-90,90'],
        'longitude'   => ['nullable', 'numeric', 'between:-180,180'],
        'rating'      => ['nullable', 'integer', 'between:1,5'],
    ];
}
```

### B.5 Bảo mật BE (xem thêm [`10-security-privacy.md`](10-security-privacy.md))
- Không bao giờ tin input client — validate lại toàn bộ.
- Dùng Policy/Gate cho quyền sở hữu; không kiểm quyền bằng if rải rác.
- Mass assignment: khai báo `$fillable` rõ ràng, không `$guarded = []`.

---

## C. Frontend — Admin (React + TypeScript + AntD)

> **Design system chi tiết (layout, AntD, auth form):** [`15-design-system.md`](15-design-system.md) §7.

### C.1 Cấu trúc `admin/src`
```
admin/src/
  api/          # axios instance + hàm gọi API theo domain (experiences.ts...)
  components/    # component tái dùng
  features/      # theo domain: experiences/, categories/, users/...
  hooks/         # custom hooks
  layouts/       # AdminLayout, AuthLayout
  pages/         # route pages
  types/         # TypeScript types khớp API Resource
  utils/
```

### C.2 Quy ước
- **TypeScript `strict`** — không `any` (dùng `unknown` + thu hẹp kiểu nếu cần).
- Component: **function component + hooks**; PascalCase, một component/file.
- Dữ liệu server qua **TanStack Query** (`useQuery`/`useMutation`) — không tự quản
  loading/cache thủ công.
- Gọi API qua lớp `api/` (axios có interceptor gắn token, xử lý 401 → logout).
- Dùng component AntD sẵn có (Table, Form, Modal) trước khi tự viết.
- Type API **khớp** envelope ở [`05-api-conventions.md`](05-api-conventions.md).
- ESLint + Prettier phải pass.

### C.3 Ví dụ lớp API
```ts
// api/experiences.ts
export const listExperiences = (params: ListParams) =>
  http.get<Paginated<Experience>>('/admin/experiences', { params });
```

---

## D. Frontend — Public (Blade + Tailwind + Alpine)

> **Design system chi tiết (token, menu iOS, shell, component):** [`15-design-system.md`](15-design-system.md) §2–§6.

- Blade: component hoá bằng `<x-...>` (Blade components), layout kế thừa `@extends('layouts.app')`.
- Tailwind: utility trong markup; palette **teal + stone**; bo góc `rounded-2xl` / `rounded-full`.
  Gom pattern lặp thành Blade component — **không** invent CSS rải rác.
- Alpine cho tương tác nhẹ (`x-password-input`, toggle); luôn có `[x-cloak]` trong `app.css`.
- **Shell bắt buộc:** brand chip + floating tab bar + sticky footer — xem design system §5.
- **IA:** `/` = kho cá nhân (auth); `/explore` = khám phá — không đổi mặc định về explore nếu chưa cập nhật docs.
- **SEO bắt buộc:** mỗi trang experience có `<title>`, meta description, Open Graph
  (og:title, og:image, og:description), URL có slug, dữ liệu có cấu trúc
  (schema.org `LocalBusiness`/`Place` nếu phù hợp).
- Không gọi Google Maps API bằng key server-side; dùng key client có giới hạn referrer.

---

## E. Test
- Backend: **Feature test** cho mỗi endpoint quan trọng (auth, tạo experience, reaction,
  comment, taste-match). Unit test cho Service phức tạp (thuật toán match).
- Chạy `php artisan test` xanh trước khi coi là xong.
- FE admin: test luồng chính (đăng nhập, CRUD) nếu có Vitest.

## F. Checklist trước khi coi một thay đổi là "xong"
- [ ] Pint/PHPStan (BE) hoặc ESLint/tsc (FE) pass.
- [ ] Test liên quan pass.
- [ ] Tài liệu cập nhật nếu đổi schema/API/kiến trúc.
- [ ] Không còn secret/log debug.
- [ ] Xử lý lỗi & trạng thái rỗng/loading.
