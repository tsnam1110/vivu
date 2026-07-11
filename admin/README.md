# ViVu Admin SPA

Giao diện quản trị ViVu — **React 19 + Ant Design 6 + Vite + TypeScript**.

UI dùng component library **Ant Design** (tự dựng layout/pages, không dùng template admin Pro/CoreUI).

## URL local

| | |
|---|---|
| Admin panel | **http://localhost:5200/** |
| API (Laravel) | http://127.0.0.1:8000/api |
| Public site (người dùng) | http://127.0.0.1:8000/ |

Tài khoản seed: `admin@vivu.test` / `password`

## Khuyến nghị: chạy từ root monorepo

Ở thư mục gốc ViVu (không phải `admin/`):

```bash
composer dev
# hoặc: npm run dev
```

Lệnh đó khởi động API + queue + Vite public + **admin** cùng lúc.

Xem đầy đủ: [`../docs/06-setup-development.md`](../docs/06-setup-development.md).

## Chạy riêng (debug admin)

```bash
cp .env.example .env   # VITE_API_BASE_URL=http://127.0.0.1:8000/api
npm install
npm run dev            # http://localhost:5200  (port cố định trong vite.config.ts)
```

Cần Laravel API đang chạy (`php artisan serve` hoặc `composer dev` ở root).

## Script

| Lệnh | Mô tả |
|---|---|
| `npm run dev` | Dev server :5200 |
| `npm run build` | Build production |
| `npm run lint` | Oxlint |
| `npm run preview` | Preview build |

## Lưu ý Sanctum / 419

Admin dùng **Bearer token**, không dùng cookie session SPA.
**Không** thêm `localhost:5200` vào `SANCTUM_STATEFUL_DOMAINS` của Laravel (sẽ HTTP 419 CSRF).

## Design system

Quy ước layout AntD, form login (eye toggle), Table/Modal, và ranh giới với public UI:

→ [`../docs/15-design-system.md`](../docs/15-design-system.md) (đặc biệt §1, §7, §8).
