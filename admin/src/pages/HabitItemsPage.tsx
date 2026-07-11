import { Button, Form, Input, InputNumber, Modal, Select, Space, Switch, Table, message } from 'antd';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import {
  createHabitItem,
  deleteHabitItem,
  listHabitItems,
  updateHabitItem,
  type HabitItem,
} from '../api/resources';
import { useState } from 'react';
import { clientPagination, sttIdColumn } from '../utils/listTable';

export default function HabitItemsPage() {
  const qc = useQueryClient();
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<HabitItem | null>(null);
  const [q, setQ] = useState('');
  const [isActive, setIsActive] = useState<boolean | undefined>();
  const [form] = Form.useForm();

  const { data, isLoading } = useQuery({
    queryKey: ['admin-habit-items', q, isActive],
    queryFn: () =>
      listHabitItems({
        q: q || undefined,
        is_active: isActive === undefined ? undefined : isActive ? 1 : 0,
      }),
  });

  const closeModal = () => {
    setOpen(false);
    setEditing(null);
    form.resetFields();
  };

  const openCreate = () => {
    setEditing(null);
    form.resetFields();
    form.setFieldsValue({ sort_order: 0, is_active: true });
    setOpen(true);
  };

  const openEdit = (row: HabitItem) => {
    setEditing(row);
    form.setFieldsValue({
      name: row.name,
      icon: row.icon,
      description: row.description,
      sort_order: row.sort_order,
      is_active: row.is_active,
    });
    setOpen(true);
  };

  return (
    <>
      <div style={{ marginBottom: 8, color: '#666', fontSize: 13 }}>
        <strong>Mẫu gợi ý</strong> cho user chọn vào bảng cá nhân. Không phải dữ liệu user —
        user có thể tự tạo đầu mục riêng (không lưu vào đây).
      </div>
      <Space style={{ marginBottom: 16 }} wrap>
        <Button type="primary" onClick={openCreate}>
          Thêm mẫu
        </Button>
        <Input.Search
          placeholder="Tìm tên / slug"
          allowClear
          onSearch={setQ}
          style={{ width: 220 }}
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
          sttIdColumn<HabitItem>(),
          { title: 'Icon', dataIndex: 'icon', width: 70 },
          { title: 'Tên', dataIndex: 'name' },
          { title: 'Slug', dataIndex: 'slug' },
          { title: 'Thứ tự', dataIndex: 'sort_order', width: 90 },
          {
            title: 'Active',
            dataIndex: 'is_active',
            width: 90,
            render: (v: boolean, r: HabitItem) => (
              <Switch
                checked={v}
                onChange={async (checked) => {
                  await updateHabitItem(r.id, { is_active: checked });
                  qc.invalidateQueries({ queryKey: ['admin-habit-items'] });
                }}
              />
            ),
          },
          {
            title: 'Thao tác',
            render: (_: unknown, r: HabitItem) => (
              <Space>
                <Button size="small" onClick={() => openEdit(r)}>
                  Sửa
                </Button>
                <Button
                  size="small"
                  danger
                  onClick={async () => {
                    try {
                      await deleteHabitItem(r.id);
                      message.success('Đã xoá');
                      qc.invalidateQueries({ queryKey: ['admin-habit-items'] });
                    } catch {
                      message.error('Không xoá được');
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
        title={editing ? 'Sửa mẫu habit' : 'Thêm mẫu habit'}
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
              await updateHabitItem(editing.id, values);
              message.success('Đã cập nhật');
            } else {
              await createHabitItem(values);
              message.success('Đã tạo');
            }
            closeModal();
            qc.invalidateQueries({ queryKey: ['admin-habit-items'] });
          }}
        >
          <Form.Item name="name" label="Tên" rules={[{ required: true, message: 'Nhập tên đầu mục' }]}>
            <Input placeholder="Ví dụ: Tập thể dục" />
          </Form.Item>
          <Form.Item name="icon" label="Icon">
            <Input placeholder="💪" />
          </Form.Item>
          <Form.Item name="description" label="Mô tả">
            <Input.TextArea rows={2} placeholder="Gợi ý ngắn cho user" />
          </Form.Item>
          <Form.Item name="sort_order" label="Thứ tự" initialValue={0}>
            <InputNumber style={{ width: '100%' }} />
          </Form.Item>
          <Form.Item name="is_active" label="Đang bật" valuePropName="checked" initialValue={true}>
            <Switch />
          </Form.Item>
        </Form>
      </Modal>
    </>
  );
}
