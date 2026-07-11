import { Button, Select, Space, Table, Tag, message } from 'antd';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import {
  listDishContributions,
  updateDishContributionStatus,
  type DishContribution,
} from '../api/resources';
import { useState } from 'react';

export default function DishContributionsPage() {
  const qc = useQueryClient();
  const [status, setStatus] = useState<string>('pending');
  const [loadingId, setLoadingId] = useState<number | null>(null);

  const { data, isLoading } = useQuery({
    queryKey: ['admin-dish-contributions', status],
    queryFn: () => listDishContributions({ status: status === 'all' ? undefined : status }),
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
      <Select
        style={{ width: 160, marginBottom: 16 }}
        value={status}
        onChange={setStatus}
        options={[
          { value: 'pending', label: 'Chờ duyệt' },
          { value: 'approved', label: 'Đã duyệt' },
          { value: 'rejected', label: 'Từ chối' },
          { value: 'all', label: 'Tất cả' },
        ]}
      />
      <Table
        rowKey="id"
        loading={isLoading}
        dataSource={data?.data}
        columns={[
          { title: 'ID', dataIndex: 'id', width: 70 },
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
