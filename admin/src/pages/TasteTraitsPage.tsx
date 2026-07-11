import { Button, Form, Input, Modal, Select, Space, Table, Tag, message } from 'antd';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { createTasteTrait, listTasteTraits, type TasteTrait } from '../api/resources';
import { useState } from 'react';
import { clientPagination, sttIdColumn } from '../utils/listTable';

export default function TasteTraitsPage() {
  const qc = useQueryClient();
  const [open, setOpen] = useState(false);
  const [type, setType] = useState<string | undefined>();
  const [isActive, setIsActive] = useState<boolean | undefined>();
  const [q, setQ] = useState('');
  const [form] = Form.useForm();

  const { data, isLoading } = useQuery({
    queryKey: ['admin-traits', type, isActive, q],
    queryFn: () =>
      listTasteTraits({
        type,
        q: q || undefined,
        is_active: isActive === undefined ? undefined : isActive ? 1 : 0,
      }),
  });

  return (
    <>
      <Space style={{ marginBottom: 16 }} wrap>
        <Button type="primary" onClick={() => setOpen(true)}>
          Thêm nhãn
        </Button>
        <Input.Search
          placeholder="Tìm tên / slug"
          allowClear
          onSearch={setQ}
          style={{ width: 200 }}
        />
        <Select
          allowClear
          placeholder="Loại"
          style={{ width: 150 }}
          value={type}
          onChange={setType}
          options={[
            { value: 'personality', label: 'Personality' },
            { value: 'interest', label: 'Interest' },
          ]}
        />
        <Select
          allowClear
          placeholder="Active"
          style={{ width: 120 }}
          value={isActive === undefined ? undefined : isActive ? '1' : '0'}
          onChange={(v) => setIsActive(v === undefined ? undefined : v === '1')}
          options={[
            { value: '1', label: 'Active' },
            { value: '0', label: 'Inactive' },
          ]}
        />
      </Space>
      <Table
        rowKey="id"
        loading={isLoading}
        dataSource={data}
        pagination={clientPagination()}
        columns={[
          sttIdColumn<TasteTrait>(),
          {
            title: 'Loại',
            dataIndex: 'type',
            render: (t: string) => <Tag color={t === 'personality' ? 'blue' : 'orange'}>{t}</Tag>,
          },
          { title: 'Tên', dataIndex: 'name' },
          { title: 'Slug', dataIndex: 'slug' },
          {
            title: 'Active',
            dataIndex: 'is_active',
            render: (v: boolean) => (v ? 'Yes' : 'No'),
          },
        ]}
      />
      <Modal title="Thêm nhãn gu" open={open} onCancel={() => setOpen(false)} onOk={() => form.submit()}>
        <Form
          form={form}
          layout="vertical"
          onFinish={async (values) => {
            await createTasteTrait(values);
            message.success('Đã tạo');
            setOpen(false);
            form.resetFields();
            qc.invalidateQueries({ queryKey: ['admin-traits'] });
          }}
        >
          <Form.Item name="type" label="Loại" rules={[{ required: true }]} initialValue="interest">
            <Select
              options={[
                { value: 'personality', label: 'Personality' },
                { value: 'interest', label: 'Interest' },
              ]}
            />
          </Form.Item>
          <Form.Item name="name" label="Tên" rules={[{ required: true }]}>
            <Input />
          </Form.Item>
        </Form>
      </Modal>
    </>
  );
}
