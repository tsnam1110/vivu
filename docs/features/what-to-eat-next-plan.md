# What to Eat — Kế hoạch tiếp theo (sprint backlog + Agent)

> **Vai trò file này:** bản **kế hoạch vận hành** sau Phase A–D. Baseline ban hành =
> compose 0.2.0 + ~182 món; **hiện ship** compose **0.3.0** + **189** món (S0–S5 + P0).  
> Dùng để chia việc cho dev / AI Agent.  
> **Không** thay đặc tả sản phẩm ([`what-to-eat.md`](what-to-eat.md)) hay chuẩn data
> ([`what-to-eat-seed-and-kb.md`](what-to-eat-seed-and-kb.md)).  
> **Prompt copy-paste từng task:** [`../agent-prompts/what-to-eat/`](../agent-prompts/what-to-eat/).

**Ngày chốt baseline:** 2026-07-12  
**Baseline lúc ban hành plan:** ruleset `0.2.0`, seed ~182 (`1.2.1-fix`)  
**Sau ship S0–S5 + P0 (cùng ngày):** ruleset **`0.3.0`**, seed **`1.2.2-s5` / 189 món**, Fact-A **`2.4.0` / 130 calo**  
**Số live:** luôn chạy `php artisan what-to-eat:seed-report`.

---

## 1. Baseline đã xong (không làm lại)

| Hạng mục | Trạng thái | Ghi chú |
|---|---|---|
| Phase A–D (popup, catalog, UGC, history, prefs, Experience match) | ✅ | |
| `MealComposer` + template mâm (vn_home_3/2, standalone, light, dine_out) | ✅ | auto / compose / pick |
| Filter vùng miền public + admin | ✅ | `culinary_regions` / `region_tags` |
| Seed multi-file + verified-only importer | ✅ | `dishes_v1/manifest.json` |
| Role + region 100% món system | ✅ | |
| Ops `cooking_method` + `protein_source` | ✅ ~100% | soft rule có data |
| Fact-A calo + serving_grams | ⚠️ ~64% | còn null có chủ đích |
| Recipe text ingredients/steps | ⚠️ ~85% | ưu tiên cook_home |
| YHCT element + thermal | ⚠️ ~5% pilot | expert-gate |
| Phase E social / mệnh cá nhân | ❌ | backlog vision |

**Nguyên tắc giữ nguyên**

1. Verified-only: không bịa calo / YHCT.  
2. Null = chưa biết → rule skip / UI “chưa có dữ liệu”.  
3. Tính năng **phụ** trên Kho — không tab nav mới.  
4. Không claim chữa bệnh / tư vấn y khoa.

---

## 2. Mục tiêu giai sóng

| Sóng | Tên | Mục tiêu user-facing | DoD tổng |
|---|---|---|---|
| **S0** | Hygiene | Số liệu manifest/doc khớp seed; checklist release | Doc + lệnh xanh |
| **S1** | UX polish | User hiểu mâm vs list; tổng kcal; empty state | Copy + UI meta.composition |
| **S2** | Fact-A sâu | Nhiều one_bowl phổ biến có calo tin cậy hơn | Coverage ↑; provenance siết |
| **S3** | Engine soft | Mâm đa dạng đạm / ít chồng chiên; feast dine-out | ruleset ≥ 0.2.1 |
| **S4** | YHCT opt-in | Toggle tham khảo dưỡng sinh, mặc định off | Pref + soft only |
| **S5** | Vùng mỏng | Bắc soup / Tây Nguyên / (tuỳ) P3 skeleton | Pool compose mượt hơn |

```text
S0 ──► S1 ──► S2 ──► S3 ──► S4
              │
              └──► S5 (song song data, ít đụng code)
```

---

## 3. Backlog chi tiết (1 task = 1 Agent)

### S0 — Hygiene & vận hành

| ID | Task | Owner gợi ý | Phụ thuộc | Prompt |
|---|---|---|---|---|
| **WTE-S0-01** | Đồng bộ `manifest` dish_count / p0–p2 / mô tả phase với 182 món thực tế | Docs/Data | — | [`01-manifest-hygiene.md`](../agent-prompts/what-to-eat/01-manifest-hygiene.md) |
| **WTE-S0-02** | Checklist release (migrate, seed, report, test, manual) | Docs | — | [`02-release-checklist.md`](../agent-prompts/what-to-eat/02-release-checklist.md) |

### S1 — UX

| ID | Task | Owner gợi ý | Phụ thuộc | Prompt |
|---|---|---|---|---|
| **WTE-S1-01** | Copy disclaimer / không claim y khoa | FE | — | [`03-ux-disclaimer-copy.md`](../agent-prompts/what-to-eat/03-ux-disclaimer-copy.md) |
| **WTE-S1-02** | UI tổng kcal mâm + so `meal_budget` | FE | compose meta đã có | [`04-ux-plate-kcal.md`](../agent-prompts/what-to-eat/04-ux-plate-kcal.md) |
| **WTE-S1-03** | Empty state theo filter (vùng/mode/catalog) | FE | — | [`05-ux-empty-states.md`](../agent-prompts/what-to-eat/05-ux-empty-states.md) |
| **WTE-S1-04** | Gợi ý lại mâm — exclude signature / đa dạng | BE+FE | S1-02 optional | [`06-reroll-plate-signature.md`](../agent-prompts/what-to-eat/06-reroll-plate-signature.md) |

### S2 — Data Fact-A

| ID | Task | Owner gợi ý | Phụ thuộc | Prompt |
|---|---|---|---|---|
| **WTE-S2-01** | Bơm calo one_bowl phổ biến còn null (verified-only) | Data | fact-a + fct-vn docs | [`07-fact-a-one-bowl-calories.md`](../agent-prompts/what-to-eat/07-fact-a-one-bowl-calories.md) |
| **WTE-S2-02** | Audit provenance 116+ món đã có calo (siết ref) | Data | S2-01 có thể song song | [`08-fact-a-provenance-audit.md`](../agent-prompts/what-to-eat/08-fact-a-provenance-audit.md) |

### S3 — Engine

| ID | Task | Owner gợi ý | Phụ thuộc | Prompt |
|---|---|---|---|---|
| **WTE-S3-01** | Soft diversity: protein_source + tránh 2× fry | BE | ops 100% đã có | [`09-engine-soft-diversity.md`](../agent-prompts/what-to-eat/09-engine-soft-diversity.md) |
| **WTE-S3-02** | Template dine_out feast (`share_feast`) | BE (+ FE label) | S3-01 optional | [`10-engine-dine-out-feast.md`](../agent-prompts/what-to-eat/10-engine-dine-out-feast.md) |
| **WTE-S3-03** | Bump ruleset_version + contract test | BE/Docs | sau S3-01/02 | [`11-ruleset-version-bump.md`](../agent-prompts/what-to-eat/11-ruleset-version-bump.md) |

### S4 — YHCT

| ID | Task | Owner gợi ý | Phụ thuộc | Prompt |
|---|---|---|---|---|
| **WTE-S4-01** | Rà pilot 10 món YHCT; thu hẹp/null nếu yếu | Data+Docs | yhct.md | [`12-yhct-pilot-audit.md`](../agent-prompts/what-to-eat/12-yhct-pilot-audit.md) |
| **WTE-S4-02** | Opt-in soft YHCT trên UI/pref (mặc định off) | BE+FE | S4-01 | [`13-yhct-opt-in-ui.md`](../agent-prompts/what-to-eat/13-yhct-opt-in-ui.md) |

### S5 — Vùng / catalog mỏng

| ID | Task | Owner gợi ý | Phụ thuộc | Prompt |
|---|---|---|---|---|
| **WTE-S5-01** | + soup Bắc; + món Tây Nguyên (skeleton verified role/region) | Data | catalog inventory | [`14-seed-bac-tay-nguyen.md`](../agent-prompts/what-to-eat/14-seed-bac-tay-nguyen.md) |
| **WTE-S5-02** | Admin curator SOP / banner policy | Admin FE+Docs | — | [`15-admin-curator-policy.md`](../agent-prompts/what-to-eat/15-admin-curator-policy.md) |

---

## 4. Ma trận Agent (ai làm gì, tránh đụng file)

| Agent / làn | Tasks | File “nóng” (tránh 2 agent cùng lúc) |
|---|---|---|
| **Docs** | S0-01, S0-02 | `manifest.json`, `docs/**` |
| **FE/UX** | S1-01 → S1-03 | `what-to-eat-modal.blade.php`, `lang/vi/what_to_eat.php` |
| **FE+BE** | S1-04 | modal + `WhatToEatSuggester` |
| **Data Fact-A** | S2-01, S2-02 | `database/data/what-to-eat/**`, fact-a docs |
| **Data YHCT** | S4-01, (S5-01) | yhct docs + JSON facts |
| **BE engine** | S3-01 → S3-03, S4-02 | `MealComposer`, `WhatToEatSuggester`, `MealTemplateRegistry` |
| **Admin** | S5-02 | `admin/src/pages/DishesPage.tsx` |

**Song song an toàn**

```text
S0-01 ‖ S0-02 ‖ S1-01 ‖ S2-01 ‖ S3-01 ‖ S5-02
```

**Nối tiếp**

```text
S1-01 → S1-02 → S1-03
S2-01 → S2-02
S3-01 → S3-02 → S3-03
S4-01 → S4-02
S1-02 xong rồi mới S1-04 (ít conflict modal)
```

---

## 5. Definition of Done chung (mọi task)

1. Đọc `docs/08-agent-playbook.md` + file đặc tả liên quan.  
2. Code/docs tiếng Việt (UI/doc); identifier tiếng Anh.  
3. Không phá golden rules (verified-only, 2 guard, migration chỉ tiến).  
4. `php artisan test --filter="WhatToEat|MealComposer|DishCatalog|DishCalorie"` xanh (hoặc subset task chỉ định).  
5. Nếu đụng seed: `php artisan db:seed --class=DishSeeder` + `what-to-eat:seed-report`.  
6. Cập nhật **checklist §6** file này (status) khi task xong.  
7. Không `migrate:fresh` ngoài local; không commit `.env`.

---

## 6. Checklist tiến độ (cập nhật khi ship)

| ID | Status | Ghi chú |
|---|---|---|
| WTE-S0-01 | ✅ done | p2_count 41→51; sau S5: 189 món (P0=51 P1=80 P2=58) |
| WTE-S0-02 | ✅ done | Checklist §18 what-to-eat.md + link setup |
| WTE-S1-01 | ✅ done | Disclaimer + mâm vs list copy lang/modal |
| WTE-S1-02 | ✅ done | Tổng kcal + band + incomplete UI |
| WTE-S1-03 | ✅ done | Empty region/filter + hint actions |
| WTE-S1-04 | ✅ done | exclude_plate_signatures + retry compose |
| WTE-S2-01 | ✅ done | +14 one_bowl recipe_sum medium (2.4.0-fact-a) |
| WTE-S2-02 | ✅ done | Audit provenance trong fact-a doc §4 |
| WTE-S3-01 | ✅ done | E01 protein diversity + pick soft diversity |
| WTE-S3-02 | ✅ done | template dine_out_feast_1 |
| WTE-S3-03 | ✅ done | ruleset 0.3.0 + assert meta.ruleset_version |
| WTE-S4-01 | ✅ done | Pilot 10 món giữ; doc audit |
| WTE-S4-02 | ✅ done | softYhct via balance_elements default off |
| WTE-S5-01 | ✅ done | +4 soup Bắc; +3 Tây Nguyên |
| WTE-S5-02 | ✅ done | Admin curator banner + kcal validate |

**Status values:** `pending` · `in_progress` · `done` · `cancelled`

### 6.1 Follow-up sau ship S0–S5 (P0)

| ID | Status | Ghi chú |
|---|---|---|
| WTE-P0-01 | ✅ done | Soft-relax có `meta.relaxations` + message (không nới im lặng) |
| WTE-P0-02 | ✅ done | Doc status compose **0.3.0**; inventory S5 trong dish-catalog; edge-case + API meta |
| WTE-P1-01 | ⬜ pending | Toggle YHCT discoverable trong modal (không chỉ history) |
| WTE-P2-01 | ⬜ pending | Siết proxy calo one_bowl / harden provenance ≥20 weak |
| WTE-P2-02 | ⬜ pending | Manual smoke §18.2 trên browser |

---

## 7. Lệnh baseline (mọi Agent)

```bash
# Từ root repo
php artisan migrate
php artisan db:seed --class=DishSeeder
php artisan what-to-eat:seed-report
php artisan test --filter="WhatToEat|MealComposer|DishCatalog|DishCalorie|DailyCalorie"
```

Manual tối thiểu sau task UX/engine:

1. Login → Kho → «Hôm nay ăn gì?»  
2. Tự nấu · Ăn chính · count 3 · auto → mâm canh–mặn–rau  
3. Lọc vùng Trung / Bắc / Ngoại  
4. Chi tiết món có calo → slider gram  
5. History + đóng góp (smoke)

---

## 8. Liên kết tài liệu

| File | Vai trò |
|---|---|
| [`what-to-eat.md`](what-to-eat.md) | Đặc tả sản phẩm + trạng thái |
| [`what-to-eat-ruleset.md`](what-to-eat-ruleset.md) | Rule engine |
| [`what-to-eat-seed-and-kb.md`](what-to-eat-seed-and-kb.md) | Chuẩn seed |
| [`what-to-eat-dish-catalog.md`](what-to-eat-dish-catalog.md) | Inventory món |
| [`what-to-eat-fact-a-calories.md`](what-to-eat-fact-a-calories.md) | Fact calo |
| [`what-to-eat-fact-completion-plan.md`](what-to-eat-fact-completion-plan.md) | Tầng fact 0–6 (chiến lược data dài) |
| [`what-to-eat-yhct.md`](what-to-eat-yhct.md) | YHCT |
| [`../14-roadmap.md`](../14-roadmap.md) | Mốc M8 |
| [`../agent-prompts/what-to-eat/`](../agent-prompts/what-to-eat/) | **Prompt từng task** |

---

## 9. Ngoài kế hoạch này (cố ý)

- Phase E social feed «bạn bè ăn gì»  
- Mệnh / tử vi theo năm sinh  
- App dinh dưỡng full-page / tab nav  
- ML recommender  
- Macro/allergen cứng (tầng 5 fact plan) trước khi có cột + FCT đủ  

---

## 10. Changelog kế hoạch

| Ngày | Thay đổi |
|---|---|
| 2026-07-12 | Ban hành plan S0–S5, 15 task, link agent-prompts, baseline seed 182 |
| 2026-07-12 | Ship S0–S5: 15/15 done; ruleset 0.3.0; seed 189; Fact-A 2.4.0 |
| 2026-07-12 | P0 follow-up: soft-relax message + doc sync (status 0.3.0, inventory S5, API meta) |
