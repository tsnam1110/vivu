import { Button, Input, Select, Space, Table, Tag, message } from 'antd';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import {
  listCategories,
  listExperiences,
  updateExperienceStatus,
  type Experience,
} from '../api/resources';
import { useState } from 'react';
import DatePresetSelect from '../components/DatePresetSelect';
import { DEFAULT_DATE_PRESET, type DatePreset } from '../utils/datePresets';
import { serverPagination, sttIdColumn } from '../utils/listTable';

export default function ExperiencesPage() {
  const [status, setStatus] = useState<string | undefined>();
  const [categoryId, setCategoryId] = useState<number | undefined>();
  const [q, setQ] = useState('');
  const [datePreset, setDatePreset] = useState<DatePreset>(DEFAULT_DATE_PRESET);
  const [page, setPage] = useState(1);
  const qc = useQueryClient();

  const { data: categories } = useQuery({
    queryKey: ['admin-categories'],
    queryFn: () => listCategories(),
  });

  const { data, isLoading } = useQuery({
    queryKey: ['admin-experiences', status, categoryId, q, datePreset, page],
    queryFn: () =>
      listExperiences({
        status,
        category_id: categoryId,
        q: q || undefined,
        date_preset: datePreset,
        page,
        per_page: 15,
      }),
  });

  const resetPage = () => setPage(1);

  return (
    <>
      <Space style={{ marginBottom: 16 }} wrap>
        <Input.Search
          placeholder="Tìm tiêu đề / địa điểm"
          allowClear
          onSearch={(v) => {
            setQ(v);
            resetPage();
          }}
          style={{ width: 220 }}
        />
        <Select
          allowClear
          placeholder="Trạng thái"
          style={{ width: 150 }}
          value={status}
          onChange={(v) => {
            setStatus(v);
            resetPage();
          }}
          options={[
            { value: 'published', label: 'Published' },
            { value: 'draft', label: 'Draft' },
            { value: 'pending', label: 'Pending' },
            { value: 'hidden', label: 'Hidden' },
          ]}
        />
        <Select
          allowClear
          placeholder="Danh mục"
          style={{ width: 180 }}
          value={categoryId}
          onChange={(v) => {
            setCategoryId(v);
            resetPage();
          }}
          options={(categories ?? []).map((c) => ({
            value: c.id,
            label: `${c.icon ? `${c.icon} ` : ''}${c.name}`,
          }))}
        />
        <DatePresetSelect
          value={datePreset}
          onChange={(v) => {
            setDatePreset(v);
            resetPage();
          }}
        />
      </Space>
      <Table
        rowKey="id"
        loading={isLoading}
        dataSource={data?.data}
        pagination={serverPagination(data?.meta, setPage)}
        columns={[
          sttIdColumn<Experience>(data?.meta),
          { title: 'Tiêu đề', dataIndex: 'title' },
          { title: 'Danh mục', render: (_, r) => r.category?.name },
          { title: 'Tác giả', render: (_, r) => r.user?.name },
          {
            title: 'Trạng thái',
            dataIndex: 'status',
            render: (s: string) => (
              <Tag color={s === 'published' ? 'green' : s === 'hidden' ? 'red' : 'default'}>{s}</Tag>
            ),
          },
          {
            title: 'Thao tác',
            render: (_, r) => (
              <Space>
                {r.status !== 'published' && (
                  <Button
                    size="small"
                    type="primary"
                    onClick={async () => {
                      await updateExperienceStatus(r.id, 'published');
                      message.success('Đã duyệt');
                      qc.invalidateQueries({ queryKey: ['admin-experiences'] });
                    }}
                  >
                    Duyệt
                  </Button>
                )}
                {r.status !== 'hidden' && (
                  <Button
                    size="small"
                    danger
                    onClick={async () => {
                      await updateExperienceStatus(r.id, 'hidden');
                      message.success('Đã ẩn');
                      qc.invalidateQueries({ queryKey: ['admin-experiences'] });
                    }}
                  >
                    Ẩn
                  </Button>
                )}
              </Space>
            ),
          },
        ]}
      />
    </>
  );
}
