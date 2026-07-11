import { Button, Space, Table, Tag, message } from 'antd';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { listUsers, updateUserStatus } from '../api/resources';
import { useState } from 'react';

export default function UsersPage() {
  const [page, setPage] = useState(1);
  const qc = useQueryClient();
  const { data, isLoading } = useQuery({
    queryKey: ['admin-users', page],
    queryFn: () => listUsers({ page }),
  });

  return (
    <Table
      rowKey="id"
      loading={isLoading}
      dataSource={data?.data}
      pagination={{
        current: data?.meta.current_page,
        total: data?.meta.total,
        pageSize: data?.meta.per_page,
        onChange: setPage,
      }}
      columns={[
        { title: 'ID', dataIndex: 'id', width: 70 },
        { title: 'Tên', dataIndex: 'name' },
        { title: 'Username', dataIndex: 'username' },
        {
          title: 'Trạng thái',
          dataIndex: 'status',
          render: (s: string) => <Tag color={s === 'active' ? 'green' : 'red'}>{s || '—'}</Tag>,
        },
        {
          title: 'Thao tác',
          render: (_, r) => (
            <Space>
              {r.status !== 'suspended' ? (
                <Button size="small" danger onClick={async () => {
                  await updateUserStatus(r.id, 'suspended');
                  message.success('Đã khoá');
                  qc.invalidateQueries({ queryKey: ['admin-users'] });
                }}>Khoá</Button>
              ) : (
                <Button size="small" type="primary" onClick={async () => {
                  await updateUserStatus(r.id, 'active');
                  message.success('Đã mở khoá');
                  qc.invalidateQueries({ queryKey: ['admin-users'] });
                }}>Mở khoá</Button>
              )}
            </Space>
          ),
        },
      ]}
    />
  );
}
