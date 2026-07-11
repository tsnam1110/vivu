import { Button, Space, Table, Tag, message } from 'antd';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { listComments, updateCommentStatus } from '../api/resources';
import { useState } from 'react';

export default function CommentsPage() {
  const [page, setPage] = useState(1);
  const qc = useQueryClient();
  const { data, isLoading } = useQuery({
    queryKey: ['admin-comments', page],
    queryFn: () => listComments({ page }),
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
        { title: 'Nội dung', dataIndex: 'body', ellipsis: true },
        { title: 'User', render: (_, r) => r.user?.name },
        { title: 'Sao', dataIndex: 'rating' },
        {
          title: 'Trạng thái',
          dataIndex: 'status',
          render: (s: string) => <Tag>{s}</Tag>,
        },
        {
          title: 'Thao tác',
          render: (_, r) => (
            <Space>
              <Button size="small" onClick={async () => {
                await updateCommentStatus(r.id, 'visible');
                message.success('Hiện');
                qc.invalidateQueries({ queryKey: ['admin-comments'] });
              }}>Hiện</Button>
              <Button size="small" danger onClick={async () => {
                await updateCommentStatus(r.id, 'hidden');
                message.success('Ẩn');
                qc.invalidateQueries({ queryKey: ['admin-comments'] });
              }}>Ẩn</Button>
            </Space>
          ),
        },
      ]}
    />
  );
}
