import {
  Button,
  Form,
  Input,
  InputNumber,
  Modal,
  Select,
  Space,
  Switch,
  Table,
  Tag,
  message,
} from 'antd';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { createDish, deleteDish, listDishes, updateDish, type Dish } from '../api/resources';
import { useState } from 'react';
import ListTotalFooter from '../components/ListTotalFooter';
import { resolveListTotal, sttIdColumn } from '../utils/listTable';

const SLOT_OPTS = [
  { value: 'breakfast', label: 'Sáng' },
  { value: 'lunch', label: 'Trưa' },
  { value: 'dinner', label: 'Tối' },
];
const ELEMENT_OPTS = [
  { value: 'wood', label: 'Mộc' },
  { value: 'fire', label: 'Hoả' },
  { value: 'earth', label: 'Thổ' },
  { value: 'metal', label: 'Kim' },
  { value: 'water', label: 'Thuỷ' },
];

export default function DishesPage() {
  const qc = useQueryClient();
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<Dish | null>(null);
  const [status, setStatus] = useState<string | undefined>();
  const [q, setQ] = useState('');
  const [page, setPage] = useState(1);
  const [form] = Form.useForm();

  const { data, isLoading } = useQuery({
    queryKey: ['admin-dishes', status, q, page],
    queryFn: () => listDishes({ status, q: q || undefined, page }),
  });

  const closeModal = () => {
    setOpen(false);
    setEditing(null);
    form.resetFields();
  };

  const openCreate = () => {
    setEditing(null);
    form.resetFields();
    form.setFieldsValue({
      meal_slots: ['lunch'],
      supports_light: false,
      supports_main: true,
      supports_dine_out: true,
      supports_cook_home: true,
      status: 'published',
    });
    setOpen(true);
  };

  const openEdit = (row: Dish) => {
    setEditing(row);
    form.setFieldsValue(row);
    setOpen(true);
  };

  return (
    <>
      <div style={{ marginBottom: 8, color: '#666', fontSize: 13 }}>
        Kho món «Hôm nay ăn gì» — seed + chỉnh sửa. Người dùng đóng góp qua queue riêng.
      </div>
      <Space style={{ marginBottom: 16 }} wrap>
        <Button type="primary" onClick={openCreate}>
          Thêm món
        </Button>
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
            { value: 'published', label: 'Published' },
            { value: 'draft', label: 'Draft' },
            { value: 'hidden', label: 'Hidden' },
          ]}
        />
        <Input.Search
          placeholder="Tìm tên / slug"
          allowClear
          onSearch={(v) => {
            setQ(v);
            setPage(1);
          }}
          style={{ width: 220 }}
        />
      </Space>
      <Table
        rowKey="id"
        loading={isLoading}
        dataSource={data?.data}
        pagination={{
          current: data?.meta?.current_page ?? page,
          total: data?.meta?.total,
          pageSize: data?.meta?.per_page ?? 50,
          onChange: setPage,
        }}
        columns={[
          sttIdColumn<Dish>(data?.meta),
          { title: '', dataIndex: 'emoji', width: 48 },
          { title: 'Tên', dataIndex: 'name' },
          { title: 'Slug', dataIndex: 'slug', ellipsis: true },
          {
            title: 'Bữa',
            dataIndex: 'meal_slots',
            render: (slots: string[]) =>
              (slots ?? []).map((s) => (
                <Tag key={s}>{s}</Tag>
              )),
          },
          {
            title: 'Flags',
            render: (_: unknown, r: Dish) => (
              <span style={{ fontSize: 12 }}>
                {r.supports_light ? 'L ' : ''}
                {r.supports_main ? 'M ' : ''}
                {r.supports_dine_out ? 'Out ' : ''}
                {r.supports_cook_home ? 'Cook' : ''}
              </span>
            ),
          },
          { title: 'Element', dataIndex: 'five_element', width: 80 },
          { title: 'Status', dataIndex: 'status', width: 100 },
          { title: 'Suggest', dataIndex: 'suggest_count', width: 80 },
          {
            title: 'Thao tác',
            render: (_: unknown, r: Dish) => (
              <Space>
                <Button size="small" onClick={() => openEdit(r)}>
                  Sửa
                </Button>
                <Button
                  size="small"
                  danger
                  onClick={async () => {
                    await deleteDish(r.id);
                    message.success('Đã xoá');
                    qc.invalidateQueries({ queryKey: ['admin-dishes'] });
                  }}
                >
                  Xoá
                </Button>
              </Space>
            ),
          },
        ]}
      />
      <ListTotalFooter
        total={resolveListTotal(data?.meta?.total, data?.data?.length)}
        loading={isLoading}
      />
      <Modal
        title={editing ? 'Sửa món' : 'Thêm món'}
        open={open}
        onCancel={closeModal}
        onOk={() => form.submit()}
        width={640}
        destroyOnClose
      >
        <Form
          form={form}
          layout="vertical"
          onFinish={async (values) => {
            if (editing) {
              await updateDish(editing.id, values);
              message.success('Đã cập nhật');
            } else {
              await createDish(values);
              message.success('Đã tạo');
            }
            closeModal();
            qc.invalidateQueries({ queryKey: ['admin-dishes'] });
          }}
        >
          <Form.Item name="name" label="Tên" rules={[{ required: true }]}>
            <Input />
          </Form.Item>
          <Form.Item name="slug" label="Slug">
            <Input placeholder="Tự sinh nếu trống" />
          </Form.Item>
          <Form.Item name="emoji" label="Emoji">
            <Input style={{ width: 100 }} />
          </Form.Item>
          <Form.Item name="summary" label="Mô tả ngắn">
            <Input.TextArea rows={2} />
          </Form.Item>
          <Form.Item name="meal_slots" label="Bữa" rules={[{ required: true }]}>
            <Select mode="multiple" options={SLOT_OPTS} />
          </Form.Item>
          <Space wrap>
            <Form.Item name="supports_light" label="Ăn nhẹ" valuePropName="checked">
              <Switch />
            </Form.Item>
            <Form.Item name="supports_main" label="Ăn chính" valuePropName="checked">
              <Switch />
            </Form.Item>
            <Form.Item name="supports_dine_out" label="Ngoài" valuePropName="checked">
              <Switch />
            </Form.Item>
            <Form.Item name="supports_cook_home" label="Tự nấu" valuePropName="checked">
              <Switch />
            </Form.Item>
          </Space>
          <Form.Item name="five_element" label="Ngũ hành">
            <Select allowClear options={ELEMENT_OPTS} />
          </Form.Item>
          <Space wrap>
            <Form.Item
              name="calories_kcal"
              label="Kcal (cho khẩu phần)"
              tooltip="Lượng calo của serving_grams bên cạnh"
            >
              <InputNumber min={0} max={5000} />
            </Form.Item>
            <Form.Item
              name="serving_grams"
              label="Khối lượng cơ sở (g)"
              tooltip="calories_kcal tương ứng bao nhiêu gram"
            >
              <InputNumber min={1} max={2000} />
            </Form.Item>
            <Form.Item name="cook_minutes" label="Phút nấu">
              <InputNumber min={0} max={600} />
            </Form.Item>
          </Space>
          <Form.Item name="search_keywords" label="Keywords (ăn ngoài match)">
            <Input />
          </Form.Item>
          <Form.Item name="benefits" label="Có lợi">
            <Input.TextArea rows={2} />
          </Form.Item>
          <Form.Item name="harms" label="Lưu ý">
            <Input.TextArea rows={2} />
          </Form.Item>
          <Form.Item name="advice" label="Lời khuyên">
            <Input.TextArea rows={2} />
          </Form.Item>
          <Form.Item name="status" label="Trạng thái">
            <Select
              options={[
                { value: 'published', label: 'Published' },
                { value: 'draft', label: 'Draft' },
                { value: 'hidden', label: 'Hidden' },
              ]}
            />
          </Form.Item>
        </Form>
      </Modal>
    </>
  );
}
