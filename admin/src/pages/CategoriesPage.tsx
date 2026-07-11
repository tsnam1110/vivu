import { Button, Form, Input, InputNumber, Modal, Space, Switch, Table, message } from 'antd';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { createCategory, deleteCategory, listCategories, updateCategory } from '../api/resources';
import { useState } from 'react';

export default function CategoriesPage() {
  const qc = useQueryClient();
  const [open, setOpen] = useState(false);
  const [form] = Form.useForm();
  const { data, isLoading } = useQuery({
    queryKey: ['admin-categories'],
    queryFn: listCategories,
  });

  return (
    <>
      <Button type="primary" style={{ marginBottom: 16 }} onClick={() => setOpen(true)}>Thêm danh mục</Button>
      <Table
        rowKey="id"
        loading={isLoading}
        dataSource={data}
        columns={[
          { title: 'ID', dataIndex: 'id', width: 70 },
          { title: 'Icon', dataIndex: 'icon', width: 70 },
          { title: 'Tên', dataIndex: 'name' },
          { title: 'Slug', dataIndex: 'slug' },
          { title: 'Thứ tự', dataIndex: 'sort_order' },
          {
            title: 'Active',
            dataIndex: 'is_active',
            render: (v: boolean, r) => (
              <Switch checked={v} onChange={async (checked) => {
                await updateCategory(r.id, { is_active: checked });
                qc.invalidateQueries({ queryKey: ['admin-categories'] });
              }} />
            ),
          },
          {
            title: 'Thao tác',
            render: (_, r) => (
              <Space>
                <Button size="small" danger onClick={async () => {
                  try {
                    await deleteCategory(r.id);
                    message.success('Đã xoá');
                    qc.invalidateQueries({ queryKey: ['admin-categories'] });
                  } catch {
                    message.error('Không xoá được (còn trải nghiệm?)');
                  }
                }}>Xoá</Button>
              </Space>
            ),
          },
        ]}
      />
      <Modal
        title="Thêm danh mục"
        open={open}
        onCancel={() => setOpen(false)}
        onOk={() => form.submit()}
      >
        <Form form={form} layout="vertical" onFinish={async (values) => {
          await createCategory(values);
          message.success('Đã tạo');
          setOpen(false);
          form.resetFields();
          qc.invalidateQueries({ queryKey: ['admin-categories'] });
        }}>
          <Form.Item name="name" label="Tên" rules={[{ required: true }]}><Input /></Form.Item>
          <Form.Item name="icon" label="Icon"><Input placeholder="🍜" /></Form.Item>
          <Form.Item name="description" label="Mô tả"><Input /></Form.Item>
          <Form.Item name="sort_order" label="Thứ tự" initialValue={0}><InputNumber style={{ width: '100%' }} /></Form.Item>
        </Form>
      </Modal>
    </>
  );
}
