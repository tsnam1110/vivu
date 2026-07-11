import { Button, Form, Input, InputNumber, Modal, Space, Switch, Table, message } from 'antd';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { createCategory, deleteCategory, listCategories, updateCategory, type Category } from '../api/resources';
import { useState } from 'react';

export default function CategoriesPage() {
  const qc = useQueryClient();
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<Category | null>(null);
  const [form] = Form.useForm();
  const { data, isLoading } = useQuery({
    queryKey: ['admin-categories'],
    queryFn: listCategories,
  });

  const closeModal = () => {
    setOpen(false);
    setEditing(null);
    form.resetFields();
  };

  const openCreate = () => {
    setEditing(null);
    form.resetFields();
    form.setFieldsValue({ sort_order: 0 });
    setOpen(true);
  };

  const openEdit = (row: Category) => {
    setEditing(row);
    form.setFieldsValue({
      name: row.name,
      icon: row.icon,
      description: row.description,
      sort_order: row.sort_order,
    });
    setOpen(true);
  };

  return (
    <>
      <Button type="primary" style={{ marginBottom: 16 }} onClick={openCreate}>
        Thêm danh mục
      </Button>
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
            render: (v: boolean, r: Category) => (
              <Switch
                checked={v}
                onChange={async (checked) => {
                  await updateCategory(r.id, { is_active: checked });
                  qc.invalidateQueries({ queryKey: ['admin-categories'] });
                }}
              />
            ),
          },
          {
            title: 'Thao tác',
            render: (_: unknown, r: Category) => (
              <Space>
                <Button size="small" onClick={() => openEdit(r)}>
                  Sửa
                </Button>
                <Button
                  size="small"
                  danger
                  onClick={async () => {
                    try {
                      await deleteCategory(r.id);
                      message.success('Đã xoá');
                      qc.invalidateQueries({ queryKey: ['admin-categories'] });
                    } catch {
                      message.error('Không xoá được (còn trải nghiệm?)');
                    }
                  }}
                >
                  Xoá
                </Button>
              </Space>
            ),
          },
        ]}
      />
      <Modal
        title={editing ? 'Sửa danh mục' : 'Thêm danh mục'}
        open={open}
        onCancel={closeModal}
        onOk={() => form.submit()}
        destroyOnClose
      >
        <Form
          form={form}
          layout="vertical"
          onFinish={async (values) => {
            if (editing) {
              await updateCategory(editing.id, values);
              message.success('Đã cập nhật');
            } else {
              await createCategory(values);
              message.success('Đã tạo');
            }
            closeModal();
            qc.invalidateQueries({ queryKey: ['admin-categories'] });
          }}
        >
          <Form.Item name="name" label="Tên" rules={[{ required: true, message: 'Nhập tên danh mục' }]}>
            <Input placeholder="Ví dụ: Ăn uống" />
          </Form.Item>
          <Form.Item name="icon" label="Icon">
            <Input placeholder="🍜" />
          </Form.Item>
          <Form.Item name="description" label="Mô tả">
            <Input />
          </Form.Item>
          <Form.Item name="sort_order" label="Thứ tự" initialValue={0}>
            <InputNumber style={{ width: '100%' }} />
          </Form.Item>
        </Form>
      </Modal>
    </>
  );
}
