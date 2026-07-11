# 15 — Design system & hướng dẫn giao diện

> **Đối tượng:** AI Agent + dev kế thừa.  
> **Mục đích:** một nơi mô tả **dùng UI thế nào** — token, layout, component, IA, anti-pattern.  
> **Khi làm UI:** đọc file này **trước** khi sửa Blade/React.

Liên quan: [`02-tech-stack.md`](02-tech-stack.md) · [`07-coding-standards.md`](07-coding-standards.md) ·
[`13-project-structure.md`](13-project-structure.md) · [`06-setup-development.md`](06-setup-development.md).

---

## 1. Hai bề mặt UI (không trộn)

| Bề mặt | Người dùng | Stack | URL local | Mã nguồn |
|---|---|---|---|---|
| **Public** | Người dùng cuối | Blade + Tailwind 4 + Alpine.js | http://127.0.0.1:8000 | `resources/views`, `resources/css`, `resources/js` |
| **Admin** | Quản trị / kiểm duyệt | React 19 + Ant Design 6 + TS | http://localhost:5200 | `admin/src` |

### Quy tắc cứng

1. **Không** nhúng Ant Design vào public; **không** copy layout admin sang Blade.
2. **Không** dùng session cookie SPA cho admin — admin = **Bearer token** (xem auth docs).
3. Chuỗi hiển thị: **tiếng Việt**. Class/HTML/component name: **tiếng Anh** (trừ copy UI).
4. Public ưu tiên **SEO + mobile**; Admin ưu tiên **bảng, form, mật độ thông tin**.

---

## 2. Nguyên tắc sản phẩm (định hướng UX public)

| # | Nguyên tắc | Ý nghĩa UI |
|---|---|---|
| P1 | **Kho cá nhân trước, khám phá sau** | Route `/` = kho (auth) hoặc landing (guest). Khám phá ở `/explore`, không phải home mặc định. |
| P2 | **Mobile-first, cảm giác iOS** | Menu nổi pill + glass; bo góc lớn; nền xám iOS (`#f2f2f7`). |
| P3 | **Ít chrome, nhiều nội dung** | Brand chip nhỏ trên cùng; nav cố định đáy; footer sticky. |
| P4 | **An toàn & pháp lý rõ** | Footer + đăng ký link Điều khoản / Dữ liệu / Cộng đồng / Cookie. |
| P5 | **Tương tác nhẹ** | Alpine cho toggle/UI nhỏ; tránh SPA hoá public. |

---

## 3. Public — Information architecture (IA)

### 3.1 Route & vai trò màn hình

| Route | Màn hình | Ai | Ghi chú UI |
|---|---|---|---|
| `/` | Kho của tôi / Landing | Auth / Guest | Mặc định sau login |
| `/explore` | Khám phá cộng đồng | Mọi người | Filter + lưới card |
| `/experiences/create/new` | Đăng trải nghiệm | Auth | Form |
| `/experiences/{slug}` | Chi tiết | Mọi người | SEO, OG |
| `/experiences/{id}/edit` | Sửa | Chủ sở hữu | Form |
| `/matches` | Người cùng gu | Auth | Danh sách |
| `/habits` | **Habit Tracker** (bảng Excel theo tháng) | Auth | Entry từ kho/profile — **không** tab nav |
| `/habits/items` | Chọn mẫu / tự tạo / sửa đầu mục cá nhân | Auth | |
| `/habits/history` | Lịch sử đổi ô | Auth | Riêng tư per user |
| `/` (Kho) + modal | **Hôm nay ăn gì** (tính năng **phụ**) | Auth | Nút trên Kho → **popup** chọn bữa/số món → list; **Chi tiết** từng món |
| `/what-to-eat/dishes/{slug}` | Chi tiết món (khi user bấm Chi tiết / deep link) | Auth hoặc public | Disclaimer calo/ngũ hành; không phải entry chính |
| `/what-to-eat/history` | Lịch sử gợi ý (phase sau) | Auth | Riêng tư; không tab nav |
| `/profile` | **Hồ sơ** (avatar, khung, tên/username, mật khẩu) | Auth | Tab “Profile” |
| `/profile/edit` | Hồ sơ chi tiết (bio, thể trạng, gu) | Auth | Từ Profile / Kho |
| `/u/{username}` | Hồ sơ công khai | Mọi người | Avatar + experiences published |
| `/login`, `/register` | Auth | Guest | `x-password-input` |
| `/terms`, `/privacy`, `/community`, `/cookies` | Pháp lý | Mọi người | Layout legal (card + mục lục) |

### 3.2 Menu nổi (floating tab bar) — layout `layouts/app.blade.php`

**Vị trí:** `fixed` đáy màn hình, pill bo `rounded-[1.75rem]`, `backdrop-blur`, viền trắng mờ, shadow mềm.

**Auth:**

| Tab | Label | Route | Icon |
|---|---|---|---|
| 1 | Của tôi | `home` (`/`) | Nhà |
| 2 | Khám phá | `explore` | Kính lúp |
| 3 | **+** (nổi) | `experiences.create` | Dấu cộng tròn teal |
| 4 | Cùng gu | `matches.index` | Nhóm người |
| 5 | Profile | `profile.me` (`/profile`) | User |

> **Không** thêm tab thứ 6 cho Habit hay What-to-eat. Habit: thẻ trên Kho → trang
> `/habits`. What-to-eat: **nút trên Kho → popup** (không full-page làm luồng chính).
> Giữ menu 5 mục (cảm giác iOS).

**Guest:** Trang chủ · Khám phá · Đăng nhập · Đăng ký (CTA teal).

**Trạng thái active:** nền `bg-stone-900 text-white`, item inactive `text-stone-500`.  
Helper PHP trong layout: `$navItem($active)` — **tái dùng**, không hard-code class rải rác.

**Safe area:** `pb-[max(0.75rem,env(safe-area-inset-bottom))]` cho notch/home indicator.

### 3.3 Brand chip

- Fixed top center, pill nhỏ: logo text **ViVu**, `bg-white/70 backdrop-blur`.
- Click → `route('home')`.

---

## 4. Public — Token thiết kế

### 4.1 Màu (Tailwind utility — không invent palette mới)

| Vai trò | Token gợi ý | Dùng cho |
|---|---|---|
| Nền app | `bg-[#f2f2f7]` | Body (iOS system gray) |
| Surface | `bg-white`, `bg-white/90` | Card, form, menu glass |
| Primary | `teal-600` / `teal-700` | CTA, link nhấn, brand |
| Primary soft | `teal-50` text `teal-800` | Badge, chip active |
| Text / UI | `stone-900` … `stone-400` | Typography, border `stone-200` |
| Danger | `red-600` / `red-50` | Lỗi, đăng xuất hover |
| Warning | `amber-50` / `amber-800` | Badge “Chờ duyệt” |
| Success soft | `teal-50` | Flash success |

> **BẮT BUỘC** giữ **teal + stone** làm trục. Không đổi sang blue/indigo “mặc định admin” trên public.

### 4.2 Bo góc

| Thành phần | Gợi ý class |
|---|---|
| Card / panel | `rounded-2xl` hoặc `rounded-3xl` |
| Input / button form | `rounded-xl` hoặc `rounded-2xl` |
| Chip / badge / CTA nhỏ | `rounded-full` |
| Menu nổi container | `rounded-[1.75rem]` |
| Tab item trong menu | `rounded-2xl` |

### 4.3 Shadow & glass

- Card: `shadow-sm`, hover `shadow-md` + nhẹ `-translate-y-0.5`.
- Menu / brand: `shadow-[0_8px_32px_rgba(0,0,0,0.12)]` + `backdrop-blur-xl` / `backdrop-blur-2xl`.
- Border glass: `border-white/60`–`border-white/70` hoặc `border-stone-200/80`.

### 4.4 Typography

- Font: system stack (`@theme --font-sans` trong `resources/css/app.css`).
- Tiêu đề trang: `text-2xl`–`text-3xl font-bold tracking-tight`.
- Body: `text-sm` / `text-[15px]` leading-relaxed (trang pháp lý).
- Meta / caption: `text-xs text-stone-500`.

### 4.5 Spacing layout

| Vùng | Quy ước |
|---|---|
| Max content width | `max-w-6xl` (app), `max-w-3xl` (legal), `max-w-2xl` (auth/form hẹp) |
| Padding ngang | `px-4` |
| Cột shell | `pt-16` (chừa brand) + `pb-[calc(5.5rem+safe-area)]` (chừa menu) |
| Footer sticky | Xem §5 |

### 4.6 Global CSS

File: `resources/css/app.css`

- Tailwind 4: `@import 'tailwindcss'` + `@source` Blade/JS.
- `[x-cloak] { display: none !important; }` — **bắt buộc** khi dùng Alpine `x-show` (tránh flash icon).

---

## 5. Public — Shell layout (sticky footer + menu)

File: `resources/views/layouts/app.blade.php`

```
body (min-h-dvh flex flex-col, bg #f2f2f7)
├── brand chip (fixed top)
├── shell (flex-1 flex flex-col, pt-16, pb cho menu)
│   ├── flash success / errors
│   ├── main (flex-1, max-w-6xl)
│   └── footer (mt-auto)  ← dính đáy viewport nếu trang ngắn
└── nav floating (fixed bottom)
```

**Quy tắc:**

- Mọi trang public `@extends('layouts.app')` — **không** tự invent layout full-page trừ lỗi đặc biệt.
- Nội dung trang **không** thêm `pb-32` trùng shell (đã chừa chỗ menu).
- Footer luôn cuối trang; trang ngắn → cuối màn hình (trên menu).

---

## 6. Public — Component & pattern

### 6.1 Blade components (tái dùng)

| Component | Path | Khi dùng |
|---|---|---|
| `x-password-input` | `resources/views/components/password-input.blade.php` | Mọi ô mật khẩu public |
| `x-user-avatar` | `resources/views/components/user-avatar.blade.php` | Avatar + khung premium (sm/md/lg/xl) |
| `x-experience-card` | `resources/views/components/experience-card.blade.php` | Card trải nghiệm (explore / kho / profile) |
| `x-star-rating` | `resources/views/components/star-rating.blade.php` | Hiển thị 5 sao (nửa sao; scale 10 hoặc 5) |
| `x-empty-state` | `resources/views/components/empty-state.blade.php` | Empty state dashed + CTA |

**Utility CSS** (`resources/css/app.css`): `.vivu-input`, `.vivu-btn-primary`, `.vivu-btn-secondary`, `.vivu-page-enter`.

**`x-password-input` — API:**

| Prop | Mặc định | Ý nghĩa |
|---|---|---|
| `name` | `password` | `name` input |
| `label` | `Mật khẩu` | Nhãn |
| `required` | `true` | |
| `autocomplete` | `current-password` | |
| `toggle` | `true` | Hiện nút mắt |
| `scope` | `true` | Tự bọc `x-data="{ showPassword }"` |

**Đăng ký (2 ô, 1 mắt chung):**

```blade
<div x-data="{ showPassword: false }" class="space-y-4">
    <x-password-input name="password" autocomplete="new-password" :scope="false" :toggle="true" />
    <x-password-input name="password_confirmation" label="Xác nhận mật khẩu"
                      autocomplete="new-password" :scope="false" :toggle="false" />
</div>
```

**A11y / UX mắt:**

- Nút mắt: `tabindex="-1"` — **Tab không dừng** ở mắt.
- `@mousedown.prevent` — click mắt không cướp focus khỏi input.

### 6.2 Card trải nghiệm (lưới)

Pattern lặp lại (home/explore/me):

- Container: `rounded-2xl border border-stone-200 bg-white shadow-sm`
- Ảnh: `aspect-[16/10]`, fallback emoji category
- Title `line-clamp-2`, place `line-clamp-1 text-sm text-stone-500`
- Meta: rating / reaction / badge status

**Badge trạng thái (kho cá nhân):**

| Status | Class gợi ý | Label VI |
|---|---|---|
| published | `bg-teal-50 text-teal-800` | Công khai |
| pending | `bg-amber-50 text-amber-800` | Chờ duyệt |
| draft | `bg-stone-100 text-stone-600` | Nháp |
| hidden | `bg-red-50 text-red-700` | Đã ẩn |

### 6.3 Form controls public

```
label: mb-1 block text-sm font-medium
input: w-full rounded-xl border border-stone-300 px-3 py-2
       focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200
button primary: rounded-xl|full bg-teal-600 py-2.5 font-semibold text-white hover:bg-teal-700
```

### 6.4 Flash / lỗi

- Success: `border-teal-200 bg-teal-50 text-teal-800 rounded-2xl`
- Errors: `border-red-200 bg-red-50 text-red-800` + list `errors->all()`
- Nằm trong shell layout (không fixed che menu).

### 6.5 Trang pháp lý

Thư mục: `resources/views/pages/`

- Header: nhãn “Văn bản pháp lý” + H1 + ngày hiệu lực.
- Mục lục (nếu dài): card `rounded-2xl` + anchor `scroll-margin`.
- Mỗi mục: card trắng riêng (`space-y-8`).
- Cross-link 4 trang: terms · privacy · community · cookies.
- Partial: `pages/_legal-styles.blade.php` (scroll-margin h2).

**Không** rút gọn nội dung pháp lý chỉ để “đẹp”; chỉnh layout, không xoá nghĩa vụ pháp lý.

### 6.6 Empty state

- Border dashed, icon lớn, 1 câu giải thích, **một** CTA primary (vd. “Đăng trải nghiệm”).

### 6.7 Avatar & Avatar Premium

| Khái niệm | Chi tiết |
|---|---|
| Component | `<x-user-avatar :user="$user" size="md" />` |
| Size | `sm` `md` `lg` `xl` |
| Ảnh | Upload 400×400 **hoặc** chọn sample (`sample_avatars`) |
| Khung | Catalog `avatar_frames` — `effect_type` + CSS vars từ `effect_config` |
| Engines | `soft`, `gradient`, `spin`, `glow`, `holographic` (file `avatar-frames.css`) |
| Premium | `user->hasActivePremium()`; khung `is_premium` bị fallback nếu hết hạn |
| Badge | Huy hiệu ✦ khi frame `show_badge` |
| UX `/profile` | Dưới avatar: 2 nút **Avatar** / **Khung** → popup bottom-sheet (mobile) / modal (desktop) |
| Đặc tả | [`features/avatar-and-premium.md`](features/avatar-and-premium.md) |

Hiển thị bắt buộc dùng component — **không** hard-code `<img>` avatar rời rạc.

---


## 7. Admin — Design system (Ant Design)

### 7.1 Nguyên tắc

- **Ant Design 6** là UI kit chính — ưu tiên `Table`, `Form`, `Modal`, `Layout`, `Menu`, `Tag`, `message`.
- **Không** cài template admin Pro/CoreUI; layout tự viết trong `AdminLayout.tsx`.
- Locale: `ConfigProvider` + `antd/locale/vi_VN` trong `App.tsx`.
- Icon: `@ant-design/icons` — đồng bộ bộ icon outline.

### 7.2 Layout admin

```
Layout (min-h-screen)
├── Sider (dark Menu, breakpoint lg)
│   └── items → React Router Link
├── Layout
│   ├── Header (title + Logout)
│   └── Content (padding 24, card trắng radius 12)
```

File: `admin/src/layouts/AdminLayout.tsx`.

### 7.3 Auth admin

- `LoginPage`: `Card` center, `Form` vertical, `Input.Password` với **eye toggle**.
- Eye: `visibilityToggle` + `iconRender`; icon bọc `tabIndex={-1}`, `onMouseDown preventDefault` (Tab bỏ qua mắt).
- Lỗi mạng / 419 / 422: message phân biệt (không gộp mọi lỗi thành “sai mật khẩu”).

### 7.4 Data pages

- List: `Table` + actions `Space` + `Button` size small.
- Create/Edit: `Modal` + `Form` **hoặc** trang riêng nếu form dài.
- Feedback: `message.success` / `message.error` (AntD).
- Fetch: **TanStack Query** + `admin/src/api/*` (axios Bearer).

### 7.5 Những gì không làm ở admin

- Không hard-code màu Tailwind public vào admin.
- Không đưa origin admin (`localhost:5200`) vào `SANCTUM_STATEFUL_DOMAINS`.
- Không dùng session cookie flow cho login admin.

---

## 8. Checklist khi Agent/dev thêm UI

### Public (Blade)

- [ ] `@extends('layouts.app')`
- [ ] Palette teal/stone; bo góc theo §4.2
- [ ] Không che menu nổi / brand (shell đã chừa padding)
- [ ] Footer không bị “trôi giữa trang” (nhờ shell flex)
- [ ] Mật khẩu → `x-password-input` (đúng props scope/toggle)
- [ ] Chuỗi UI tiếng Việt; route name tiếng Anh
- [ ] Mobile: kiểm tra tab bar + safe area
- [ ] Trang mới có entry IA (§3) nếu đổi nav — **cập nhật file này**

### Admin (React)

- [ ] Dùng AntD có sẵn trước khi custom CSS
- [ ] TypeScript strict; type khớp API Resource
- [ ] `useQuery` / `useMutation`; token qua `http` interceptor
- [ ] Trang nằm `admin/src/pages`, route trong `App.tsx`
- [ ] Menu item mới trong `AdminLayout` nếu cần

### Cấm

- [ ] ❌ Đổi home mặc định thành explore mà không cập nhật docs
- [ ] ❌ Menu top sticky kiểu cũ thay floating iOS (trừ khi đổi design có chủ đích + cập nhật docs)
- [ ] ❌ Copy-paste class dài > 2 lần mà không component hoá
- [ ] ❌ `prose` plugin nếu chưa cài — dùng card pattern legal hiện có

---

## 9. Bản đồ file UI (tra nhanh)

```
resources/
  css/app.css                 # Tailwind 4 + x-cloak
  js/app.js                   # Alpine.start()
  views/
    layouts/app.blade.php     # Shell: brand + sticky footer + floating nav
    components/
      password-input.blade.php
    home/
      me.blade.php            # Kho cá nhân (auth home)
      guest.blade.php         # Landing
      explore.blade.php       # Khám phá
    auth/                     # login, register
    experiences/              # show, create, edit
    profile/                  # show, edit (+ logout)
    matches/
    pages/                    # terms, privacy, community, cookies
    errors/

admin/src/
  App.tsx                     # Router + ConfigProvider vi_VN
  layouts/AdminLayout.tsx
  pages/*.tsx
  api/http.ts                 # Axios + Bearer
```

---

## 10. Khi nào cập nhật tài liệu này

Cập nhật **cùng PR** nếu thay đổi:

1. Màu / bo góc / font / shell layout / menu IA  
2. Component public dùng chung (props API)  
3. Pattern form/auth (mắt mật khẩu, empty state)  
4. Thêm bề mặt UI mới (vd. PWA, email template)

Changelog ngắn ghi ở đầu file hoặc commit message `docs(ui): …`.

---

## 11. Tham chiếu nhanh URL (local)

| Việc | URL |
|---|---|
| Kho / landing | http://127.0.0.1:8000/ |
| Khám phá | http://127.0.0.1:8000/explore |
| Admin | http://localhost:5200/ |
| Điều khoản | http://127.0.0.1:8000/terms |

Chạy full stack: `composer dev` (xem [`06-setup-development.md`](06-setup-development.md)).
