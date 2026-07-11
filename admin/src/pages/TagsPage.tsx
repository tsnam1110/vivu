import { Button, Form, Input, Modal, Table, message } from 'antd';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { createTag, listTags } from '../api/resources';
import { useState } from 'react';

export default function TagsPage() {
  const qc = useQueryClient();
  const [open, setOpen] = useState(false);
  const [form] = Form.useForm();
  const { data, isLoading } = useQuery({
    queryKey: ['admin-tags'],
    queryFn: listTags,
  });

  return (
    <>
      <Button type="primary" style={{ marginBottom: 16 }} onClick={() => setOpen(true)}>Thêm thẻ</Button>
      <Table
        rowKey="id"
        loading={isLoading}
        dataSource={data?.data}
        columns={[
          { title: 'ID', dataIndex: 'id', width: 70 },
          { title: 'Tên', dataIndex: 'name' },
          { title: 'Slug', dataIndex: 'slug' },
          { title: 'Category ID', dataIndex: 'category_id' },
          { title: 'Usage', dataIndex: 'usage_count' },
        ]}
      />
      <Modal title="Thêm thẻ" open={open} onCancel={() => setOpen(false)} onOk={() => form.submit()}>
        <Form form={form} layout="vertical" onFinish={async (values) => {
          await createTag(values);
          message.success('Đã tạo');
          setOpen(false);
          form.resetFields();
          qc.invalidateQueries({ queryKey: ['admin-tags'] });
        }}>
          <Form.Item name="name" label="Tên" rules={[{ required: true }]}><Input /></Form.Item>
        </Form>
      </Modal>
    </>
  );
}
