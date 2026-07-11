<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Vùng miền / vùng ẩm thực (region_tags trên inventory).
 *
 * @see docs/features/what-to-eat-dish-catalog.md §2
 */
enum CulinaryRegion: string
{
    /** Miền Bắc */
    case Bac = 'bac';

    /** Miền Trung */
    case Trung = 'trung';

    /** Miền Nam */
    case Nam = 'nam';

    /** Tây Nguyên */
    case TayNguyen = 'tay_nguyen';

    /** Phổ biến cả nước / mâm nhà generic */
    case QuocGia = 'quoc_gia';

    /** Ảnh hưởng Hoa–Việt (tag thêm, không thay vùng địa lý) */
    case HoaViet = 'hoa_viet';

    /** Món ngoại / quốc tế / fusion không đặc trưng VN */
    case Ngoai = 'ngoai';

    public function label(): string
    {
        return match ($this) {
            self::Bac => __('what_to_eat.region_bac'),
            self::Trung => __('what_to_eat.region_trung'),
            self::Nam => __('what_to_eat.region_nam'),
            self::TayNguyen => __('what_to_eat.region_tay_nguyen'),
            self::QuocGia => __('what_to_eat.region_quoc_gia'),
            self::HoaViet => __('what_to_eat.region_hoa_viet'),
            self::Ngoai => __('what_to_eat.region_ngoai'),
        };
    }
}
