import { Button, Select, Space, Table, Tag, message } from 'antd';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import {
  listDishContributions,
  updateDishContributionStatus,
  type DishContribution,
} from '../api/resources';
import { useState } from 'react';
import DatePresetSelect from '../components/DatePresetSelect';
import { DEFAULT_DATE_PRESET, type DatePreset } from '../utils/datePresets';
import { serverPagination, sttIdColumn } from '../utils/listTable';

export default function DishContributionsPage() {
  const qc = useQueryClient();
  const [status, setStatus] = useState<string>('pending');
  const [type, setType] = useState<string | undefined>();
  const [datePreset, setDatePreset] = useState<DatePreset>(DEFAULT_DATE_PRESET);
  const [page, setPage] = useState(1);
  const [loadingId, setLoadingId] = useState<number | null>(null);

  const { data, isLoading } = useQuery({
    queryKey: ['admin-dish-contributions', status, type, datePreset, page],
    queryFn: () =>
      listDishContributions({
        status: status === 'all' ? undefined : status,
        type,
        date_preset: datePreset,
        page,
      }),
  });

  const act = async (row: DishContribution, next: 'approved' | 'rejected') => {
    setLoadingId(row.id);
    try {
      await updateDishContributionStatus(row.id, {
        status: next,
        set_canonical: next === 'approved',
      });
      message.success(next === 'approved' ? 'Đã duyệt & đồng bộ món' : 'Đã từ chối');
      qc.invalidateQueries({ queryKey: ['admin-dish-contributions'] });
    } catch {
      message.error('Không cập nhật được');
    } finally {
      setLoadingId(null);
    }
  };

  return (
    <>
      <div style={{ marginBottom: 8, color: '#666', fontSize: 13 }}>
        Duyệt đóng góp user (công thức, calo, lợi/hại, ngũ hành…). Approve canonical → cập nhật dish.
      </div>
      <Space style={{ marginBottom: 16 }} wrap>
        <Select
          style={{ width: 150 }}
          value={status}
          onChange={(v) => {
            setStatus(v);
            setPage(1);
          }}
          options={[
            { value: 'pending', label: 'Chờ duyệt' },
            { value: 'approved', label: 'Đã duyệt' },
            { value: 'rejected', label: 'Từ chối' },
            { value: 'all', label: 'Tất cả' },
          ]}
        />
        <Select
          allowClear
          placeholder="Loại đóng góp"
          style={{ width: 160 }}
          value={type}
          onChange={(v) => {
            setType(v);
            setPage(1);
          }}
          options={[
            { value: 'recipe', label: 'Công thức' },
            { value: 'calories', label: 'Calo' },
            { value: 'harm', label: 'Lưu ý' },
            { value: 'benefit', label: 'Có lợi' },
            { value: 'advice', label: 'Lời khuyên' },
            { value: 'note', label: 'Ghi chú' },
            { value: 'five_element', label: 'Ngũ hành' },
          ]}
        />
        <DatePresetSelect
          value={datePreset}
          onChange={(v) => {
            setDatePreset(v);
            setPage(1);
          }}
        />
      </Space>
      <Table
        rowKey="id"
        loading={isLoading}
        dataSource={data?.data}
        pagination={serverPagination(data?.meta, setPage)}
        columns={[
          sttIdColumn<DishContribution>(data?.meta),
          {
            title: 'Món',
            render: (_: unknown, r: DishContribution) =>
              r.dish ? `${r.dish.emoji ?? ''} ${r.dish.name}` : r.dish_id,
          },
          {
            title: 'Loại',
            dataIndex: 'type',
            render: (t: string, r: DishContribution) => r.type_label || t,
          },
          {
            title: 'User',
            render: (_: unknown, r: DishContribution) => r.user?.username ?? '—',
          },
          {
            title: 'Payload',
            dataIndex: 'payload',
            ellipsis: true,
            render: (p: Record<string, unknown>) => (
              <code style={{ fontSize: 11 }}>{JSON.stringify(p).slice(0, 120)}</code>
            ),
          },
          {
            title: 'Status',
            dataIndex: 'status',
            render: (s: string) => (
              <Tag color={s === 'approved' ? 'green' : s === 'pending' ? 'gold' : 'red'}>{s}</Tag>
            ),
          },
          {
            title: 'Thao tác',
            render: (_: unknown, r: DishContribution) => (
              <Space>
                <Button
                  size="small"
                  type="primary"
                  loading={loadingId === r.id}
                  disabled={r.status === 'approved'}
                  onClick={() => act(r, 'approved')}
                >
                  Duyệt
                </Button>
                <Button
                  size="small"
                  danger
                  loading={loadingId === r.id}
                  disabled={r.status === 'rejected'}
                  onClick={() => act(r, 'rejected')}
                >
                  Từ chối
                </Button>
              </Space>
            ),
          },
        ]}
      />
    </>
  );
}
