import { Button, Input, Select, Space, Table, Tag, message } from 'antd';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { listComments, updateCommentStatus, type CommentRow } from '../api/resources';
import { useState } from 'react';
import DatePresetSelect from '../components/DatePresetSelect';
import { DEFAULT_DATE_PRESET, type DatePreset } from '../utils/datePresets';
import { serverPagination, sttIdColumn } from '../utils/listTable';

export default function CommentsPage() {
  const [page, setPage] = useState(1);
  const [status, setStatus] = useState<string | undefined>();
  const [q, setQ] = useState('');
  const [datePreset, setDatePreset] = useState<DatePreset>(DEFAULT_DATE_PRESET);
  const qc = useQueryClient();

  const { data, isLoading } = useQuery({
    queryKey: ['admin-comments', page, status, q, datePreset],
    queryFn: () =>
      listComments({
        page,
        status,
        q: q || undefined,
        date_preset: datePreset,
      }),
  });

  return (
    <>
      <Space style={{ marginBottom: 16 }} wrap>
        <Input.Search
          placeholder="Tìm nội dung"
          allowClear
          onSearch={(v) => {
            setQ(v);
            setPage(1);
          }}
          style={{ width: 220 }}
        />
        <Select
          allowClear
          placeholder="Trạng thái"
          style={{ width: 140 }}
          value={status}
          onChange={(v) => {
            setStatus(v);
            setPage(1);
          }}
          options={[
            { value: 'visible', label: 'Visible' },
            { value: 'pending', label: 'Pending' },
            { value: 'hidden', label: 'Hidden' },
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
          sttIdColumn<CommentRow>(data?.meta),
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
                <Button
                  size="small"
                  onClick={async () => {
                    await updateCommentStatus(r.id, 'visible');
                    message.success('Hiện');
                    qc.invalidateQueries({ queryKey: ['admin-comments'] });
                  }}
                >
                  Hiện
                </Button>
                <Button
                  size="small"
                  danger
                  onClick={async () => {
                    await updateCommentStatus(r.id, 'hidden');
                    message.success('Ẩn');
                    qc.invalidateQueries({ queryKey: ['admin-comments'] });
                  }}
                >
                  Ẩn
                </Button>
              </Space>
            ),
          },
        ]}
      />
    </>
  );
}
