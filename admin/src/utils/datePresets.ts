/** Preset bộ lọc thời gian admin — khớp BE `AdminDateRange`. */
export type DatePreset =
  | 'today'
  | 'yesterday'
  | 'this_week'
  | 'this_month'
  | 'last_month'
  | 'all';

/** Mặc định khi trang có bộ lọc thời gian. */
export const DEFAULT_DATE_PRESET: DatePreset = 'this_month';

export const DATE_PRESET_OPTIONS: { value: DatePreset; label: string }[] = [
  { value: 'today', label: 'Hôm nay' },
  { value: 'yesterday', label: 'Hôm qua' },
  { value: 'this_week', label: 'Tuần này' },
  { value: 'this_month', label: 'Tháng này' },
  { value: 'last_month', label: 'Tháng trước' },
  { value: 'all', label: 'Tất cả' },
];
