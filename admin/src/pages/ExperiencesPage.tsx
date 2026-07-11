import { Button, Select, Space, Table, Tag, message } from 'antd';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { listExperiences, updateExperienceStatus } from '../api/resources';
import { useState } from 'react';

export default function ExperiencesPage() {
  const [status, setStatus] = useState<string | undefined>();
  const [page, setPage] = useState(1);
  const qc = useQueryClient();

  const { data, isLoading } = useQuery({
    queryKey: ['admin-experiences', status, page],
    queryFn: () => listExperiences({ status, page, per_page: 15 }),
  });

  return (
    <>
      <Space style={{ marginBottom: 16 }}>
        <Select
          allowClear
          placeholder="Lọc trạng thái"
          style={{ width: 180 }}
          value={status}
          onChange={(v) => { setStatus(v); setPage(1); }}
          options={[
            { value: 'published', label: 'Published' },
            { value: 'draft', label: 'Draft' },
            { value: 'pending', label: 'Pending' },
            { value: 'hidden', label: 'Hidden' },
          ]}
        />
      </Space>
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
          { title: 'Tiêu đề', dataIndex: 'title' },
          { title: 'Danh mục', render: (_, r) => r.category?.name },
          { title: 'Tác giả', render: (_, r) => r.user?.name },
          {
            title: 'Trạng thái',
            dataIndex: 'status',
            render: (s: string) => <Tag color={s === 'published' ? 'green' : s === 'hidden' ? 'red' : 'default'}>{s}</Tag>,
          },
          {
            title: 'Thao tác',
            render: (_, r) => (
              <Space>
                {r.status !== 'published' && (
                  <Button size="small" type="primary" onClick={async () => {
                    await updateExperienceStatus(r.id, 'published');
                    message.success('Đã duyệt');
                    qc.invalidateQueries({ queryKey: ['admin-experiences'] });
                  }}>Duyệt</Button>
                )}
                {r.status !== 'hidden' && (
                  <Button size="small" danger onClick={async () => {
                    await updateExperienceStatus(r.id, 'hidden');
                    message.success('Đã ẩn');
                    qc.invalidateQueries({ queryKey: ['admin-experiences'] });
                  }}>Ẩn</Button>
                )}
              </Space>
            ),
          },
        ]}
      />
    </>
  );
}
