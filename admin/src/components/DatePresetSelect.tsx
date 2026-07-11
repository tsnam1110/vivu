import { Select } from 'antd';
import {
  DATE_PRESET_OPTIONS,
  type DatePreset,
} from '../utils/datePresets';

type Props = {
  value: DatePreset;
  onChange: (value: DatePreset) => void;
  style?: React.CSSProperties;
  allowClear?: boolean;
};

/** Select preset thời gian: hôm nay / hôm qua / tuần này / tháng này / tháng trước. */
export default function DatePresetSelect({
  value,
  onChange,
  style,
  allowClear = false,
}: Props) {
  return (
    <Select
      value={value}
      onChange={onChange}
      options={DATE_PRESET_OPTIONS}
      style={{ width: 150, ...style }}
      allowClear={allowClear}
      placeholder="Thời gian"
    />
  );
}
