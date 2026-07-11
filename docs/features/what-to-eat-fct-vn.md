# FCT Việt Nam — Map nguyên liệu

> **File data:** `database/data/what-to-eat/facts/fct_vn_ingredients.json`  
> **Pilot món:** `database/data/what-to-eat/facts/recipes_fct_vn_pilot_v1.json`  
> **Standard bowls:** `database/data/what-to-eat/facts/recipes_standard_v1.json`  
> **Plan tổng:** [`what-to-eat-fact-completion-plan.md`](what-to-eat-fact-completion-plan.md)

## Mục tiêu

1. Tính `recipe_sum` sát nguyên liệu nội địa (FCT VN 2007)  
2. Gắn `ingredient_id` / `food_code` vào breakdown  
3. Yield gạo chín khi bảng chỉ có gạo khô (`vivu-yield-v1`)  
4. Standard bowl (phở/bún/cơm tấm…) ưu tiên component VN  

## Nguồn

| Trường | Giá trị |
|---|---|
| Tài liệu | Bảng thành phần thực phẩm Việt Nam |
| Cơ quan | Viện Dinh dưỡng – Bộ Y tế / NXB Y học |
| Năm | **2007** |
| URL | https://www.fao.org/fileadmin/templates/food_composition/documents/pdf/VTN_FCT_2007.pdf |

PDF gốc **không commit**. Khóa dòng bằng `food_code` + `pdf_page`.

## Yield gạo (`vivu-yield-v1`)

FCT VN chỉ có **gạo khô**. Cơm/xôi suy ra:

| Derived id | Từ mã khô | yield (chín/khô) | ≈ kcal/100g chín |
|---|---|---:|---:|
| `vn-com-trang-chin` | 1004 (344) | **2.5** | 137.6 |
| `vn-com-gao-lut-chin` | 1005 (345) | **2.5** | 138.0 |
| `vn-xoi-nep-chin` | 1001 (344) | **2.2** | 156.4 |

- Method: `recipe_sum`, confidence **medium**  
- Có thể siết high sau khi đo 3 nồi thực tế  

## Trạng thái (`1.1.0-vn-fct`)

| Hạng mục | Số |
|---|---:|
| Nguyên liệu high (PDF line) | ~32 |
| Derived cooked (yield) | 3 |
| Pilot dishes (calories overlay) | **35** |
| Standard bowls FCT VN | **~18** (+ vài món còn mixed USDA) |

### Mã hay dùng

| id | food_code | kcal/100g | Ghi chú |
|---|---:|---:|---|
| `vn-gao-te-trang-kho` | 1004 | 344 | Gạo tẻ máy |
| `vn-bun` | 1020 | 110 | Bún ẩm |
| `vn-banh-pho` | 1013 | 143 | Bánh phở |
| `vn-banh-mi` | 1012 | 249 | Bánh mì |
| `vn-dau-phu` | 3025 | 95 | |
| `vn-ca-chua` | 4005 | 20 | |
| `vn-bap-cai` | 4010 | 29 | |
| `vn-cai-thia` | 4015 | 17 | |
| `vn-rau-muong` | 4083 | 25 | |
| `vn-rau-mong-toi` | 4080 | 14 | |
| `vn-dau-thao-moc` | 6002 | 897 | Dầu proxy |
| `vn-thit-bo-loai-1` | 7003 | 118 | |
| `vn-thit-ga-ta` | 7013 | 199 | raw |
| `vn-thit-heo-nac` | 7017 | 139 | |
| `vn-thit-heo-nua-nac` | 7018 | 260 | nướng/proxy |
| `vn-ca-qua` | 8022 | 97 | cá lóc |
| `vn-ca-thu` | 8026 | 166 | |
| `vn-tom-bien` | 8051 | 82 | |
| `vn-trung-ga-toan-phan` | 9001 | 166 | |
| `vn-sua-chua` | 10004 | 61 | |
| `vn-duong-trang` | 12013 | 390 | |

## Lệnh

```bash
# Rebuild phase B (ingredients + standard bowls + pilot → calories 2.3.0)
php database/data/what-to-eat/build_fct_vn_phase_b.php

# (Tuỳ chọn) chỉ merge pilot cũ phase A
php database/data/what-to-eat/apply_fct_vn_pilot.php

php artisan db:seed --class=DishSeeder
php artisan what-to-eat:seed-report
```

## Một số delta calo đáng chú ý (phase B)

| slug | Ý |
|---|---|
| `com-trang` | 195 USDA → **206** (yield 2.5 từ 1004); config `implicit_rice_kcal=206` |
| `xoi-trang` | 146 → **235** (xôi đặc yield 2.2) |
| `pho-bo` | 444 → **412** (bánh phở 143 + bò grade-I 118) |
| `pho-ga` | 383 → **470** (gà ta 199 raw) |
| `bun-bo-hue` | 504 → **382** (bỏ overcount, bò 118) |
| `xoi-man` / `xoi-ga` | Tăng theo xôi yield 2.2 |

## Việc tiếp theo

1. Đo yield cơm/xôi thực tế → siết high  
2. Chuyển nốt `banh-cuon` / `mi-xao` sang FCT VN khi có dòng mì  
3. Không claim đúng mọi quán — luôn `limitations` + medium cho món phức tạp  
