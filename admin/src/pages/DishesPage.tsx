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
import { serverPagination, sttIdColumn } from '../utils/listTable';

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
const ROLE_OPTS = [
  { value: 'soup', label: 'Canh / súp' },
  { value: 'main_protein', label: 'Món chính (đạm)' },
  { value: 'side_veg', label: 'Rau / phụ xanh' },
  { value: 'side_extra', label: 'Món phụ' },
  { value: 'starch', label: 'Tinh bột' },
  { value: 'one_bowl', label: 'Suất trọn (1 tô)' },
  { value: 'dessert_light', label: 'Ăn nhẹ / tráng miệng' },
  { value: 'beverage', label: 'Đồ uống' },
  { value: 'share_feast', label: 'Chia sẻ (lẩu/nướng…)' },
];
const THERMAL_OPTS = [
  { value: 'cold', label: 'Hàn' },
  { value: 'cool', label: 'Lương (mát)' },
  { value: 'neutral', label: 'Bình' },
  { value: 'warm', label: 'Ôn (ấm)' },
  { value: 'hot', label: 'Nhiệt (nóng)' },
];
const PROTEIN_OPTS = [
  { value: 'meat', label: 'Thịt' },
  { value: 'seafood', label: 'Hải sản' },
  { value: 'egg', label: 'Trứng' },
  { value: 'plant', label: 'Thực vật' },
  { value: 'mixed', label: 'Hỗn hợp' },
  { value: 'none', label: 'Không đạm chính' },
];
const COOKING_OPTS = [
  { value: 'boil', label: 'Luộc/nấu' },
  { value: 'steam', label: 'Hấp' },
  { value: 'grill', label: 'Nướng' },
  { value: 'fry', label: 'Chiên' },
  { value: 'raw', label: 'Sống' },
  { value: 'braise', label: 'Kho/om' },
  { value: 'soup_base', label: 'Nước dùng' },
  { value: 'mixed', label: 'Hỗn hợp' },
];
const REGION_OPTS = [
  { value: 'bac', label: 'Miền Bắc' },
  { value: 'trung', label: 'Miền Trung' },
  { value: 'nam', label: 'Miền Nam' },
  { value: 'tay_nguyen', label: 'Tây Nguyên' },
  { value: 'quoc_gia', label: 'Phổ biến cả nước' },
  { value: 'hoa_viet', label: 'Hoa–Việt' },
  { value: 'ngoai', label: 'Món ngoại / quốc tế' },
];

export default function DishesPage() {
  const qc = useQueryClient();
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<Dish | null>(null);
  const [status, setStatus] = useState<string | undefined>();
  const [mealSlot, setMealSlot] = useState<string | undefined>();
  const [fiveElement, setFiveElement] = useState<string | undefined>();
  const [culinaryRegion, setCulinaryRegion] = useState<string | undefined>();
  const [dishRole, setDishRole] = useState<string | undefined>();
  const [q, setQ] = useState('');
  const [page, setPage] = useState(1);
  const [form] = Form.useForm();

  const { data, isLoading } = useQuery({
    queryKey: ['admin-dishes', status, mealSlot, fiveElement, culinaryRegion, dishRole, q, page],
    queryFn: () =>
      listDishes({
        status,
        meal_slot: mealSlot,
        five_element: fiveElement,
        culinary_region: culinaryRegion,
        dish_role: dishRole,
        q: q || undefined,
        page,
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
      <div style={{ marginBottom: 12, fontSize: 13, lineHeight: 1.5 }}>
        <p style={{ color: '#666', marginBottom: 6 }}>
          Kho món «Hôm nay ăn gì» — seed verified-only + chỉnh sửa admin (curator).
        </p>
        <p
          style={{
            margin: 0,
            padding: '8px 12px',
            background: '#fff7ed',
            border: '1px solid #ffedd5',
            borderRadius: 8,
            color: '#9a3412',
          }}
        >
          <strong>Curator policy:</strong> Field nhạy cảm (calo, serving_grams, ngũ hành, hàn–nhiệt,
          dish_role, recipe) khi lưu qua Admin = <em>curator trusted</em> (khác seed JSON bắt
          provenance). Chỉ điền khi chắc chắn — null = «chưa có dữ liệu xác thực». Không bịa số.
          Vùng miền: multi-tag <code>bac|trung|nam|tay_nguyen|…</code>. Duyệt contribution → set
          canonical khi đủ nguồn. SOP: docs/features/what-to-eat-seed-and-kb.md + § admin curator.
        </p>
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
        <Select
          allowClear
          placeholder="Bữa"
          style={{ width: 120 }}
          value={mealSlot}
          onChange={(v) => {
            setMealSlot(v);
            setPage(1);
          }}
          options={SLOT_OPTS}
        />
        <Select
          allowClear
          placeholder="Ngũ hành"
          style={{ width: 120 }}
          value={fiveElement}
          onChange={(v) => {
            setFiveElement(v);
            setPage(1);
          }}
          options={ELEMENT_OPTS}
        />
        <Select
          allowClear
          placeholder="Vùng miền"
          style={{ width: 160 }}
          value={culinaryRegion}
          onChange={(v) => {
            setCulinaryRegion(v);
            setPage(1);
          }}
          options={REGION_OPTS}
        />
        <Select
          allowClear
          placeholder="Vai trò mâm"
          style={{ width: 150 }}
          value={dishRole}
          onChange={(v) => {
            setDishRole(v);
            setPage(1);
          }}
          options={ROLE_OPTS}
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
        pagination={serverPagination(data?.meta, setPage)}
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
            title: 'Vùng',
            dataIndex: 'culinary_regions',
            width: 140,
            render: (regions: string[] | null | undefined) =>
              (regions ?? []).length
                ? (regions ?? []).map((r) => (
                    <Tag key={r}>{REGION_OPTS.find((o) => o.value === r)?.label ?? r}</Tag>
                  ))
                : '—',
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
          { title: 'Role', dataIndex: 'dish_role', width: 100, ellipsis: true },
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
          <Form.Item
            name="culinary_regions"
            label="Vùng miền ẩm thực"
            extra="Có thể chọn nhiều (vd: Bắc + toàn quốc). Để trống nếu chưa xác định."
          >
            <Select mode="multiple" allowClear options={REGION_OPTS} placeholder="Bắc / Trung / Nam…" />
          </Form.Item>
          <Space wrap style={{ width: '100%' }}>
            <Form.Item name="dish_role" label="Vai trò mâm (dish_role)">
              <Select allowClear options={ROLE_OPTS} style={{ minWidth: 180 }} />
            </Form.Item>
            <Form.Item name="five_element" label="Ngũ hành">
              <Select allowClear options={ELEMENT_OPTS} style={{ minWidth: 120 }} />
            </Form.Item>
            <Form.Item name="thermal_nature" label="Hàn–nhiệt">
              <Select allowClear options={THERMAL_OPTS} style={{ minWidth: 140 }} />
            </Form.Item>
          </Space>
          <Space wrap style={{ width: '100%' }}>
            <Form.Item name="protein_source" label="Nguồn đạm">
              <Select allowClear options={PROTEIN_OPTS} style={{ minWidth: 140 }} />
            </Form.Item>
            <Form.Item name="cooking_method" label="Cách chế biến">
              <Select allowClear options={COOKING_OPTS} style={{ minWidth: 140 }} />
            </Form.Item>
            <Form.Item name="flavor_tags" label="Vị (tags)">
              <Select mode="tags" tokenSeparators={[',']} style={{ minWidth: 180 }} placeholder="spicy, sour…" />
            </Form.Item>
          </Space>
          <Space wrap>
            <Form.Item
              name="calories_kcal"
              label="Kcal (cho khẩu phần)"
              tooltip="Lượng calo của serving_grams. Xóa = null (chưa verified) — không điền số đoán."
              rules={[
                {
                  validator: async (_, value) => {
                    if (value === 0) {
                      return Promise.reject(new Error('0 kcal hiếm — để trống nếu chưa có dữ liệu xác thực'));
                    }
                  },
                },
              ]}
            >
              <InputNumber min={0} max={5000} placeholder="null = chưa có" />
            </Form.Item>
            <Form.Item
              name="serving_grams"
              label="Khối lượng cơ sở (g)"
              tooltip="Phải cặp với calories_kcal. Không để một bên null một bên có số."
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
