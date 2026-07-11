import { Button, Form, Input, Modal, Select, Table, Tag, message } from 'antd';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { createTasteTrait, listTasteTraits } from '../api/resources';
import { useState } from 'react';

export default function TasteTraitsPage() {
  const qc = useQueryClient();
  const [open, setOpen] = useState(false);
  const [form] = Form.useForm();
  const { data, isLoading } = useQuery({
    queryKey: ['admin-traits'],
    queryFn: listTasteTraits,
  });

  return (
    <>
      <Button type="primary" style={{ marginBottom: 16 }} onClick={() => setOpen(true)}>Thêm nhãn</Button>
      <Table
        rowKey="id"
        loading={isLoading}
        dataSource={data}
        columns={[
          { title: 'ID', dataIndex: 'id', width: 70 },
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
        <Form form={form} layout="vertical" onFinish={async (values) => {
          await createTasteTrait(values);
          message.success('Đã tạo');
          setOpen(false);
          form.resetFields();
          qc.invalidateQueries({ queryKey: ['admin-traits'] });
        }}>
          <Form.Item name="type" label="Loại" rules={[{ required: true }]} initialValue="interest">
            <Select options={[
              { value: 'personality', label: 'Personality' },
              { value: 'interest', label: 'Interest' },
            ]} />
          </Form.Item>
          <Form.Item name="name" label="Tên" rules={[{ required: true }]}><Input /></Form.Item>
        </Form>
      </Modal>
    </>
  );
}
