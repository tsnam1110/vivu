import {
  Button,
  ColorPicker,
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
import { useMemo, useState } from 'react';
import {
  createAvatarFrame,
  deleteAvatarFrame,
  listAvatarFrames,
  updateAvatarFrame,
  type AvatarFrame,
} from '../api/resources';
import AvatarFramePreview from '../components/AvatarFramePreview';

const EFFECT_OPTIONS = [
  { value: 'soft', label: 'Viền mềm' },
  { value: 'gradient', label: 'Gradient tĩnh' },
  { value: 'spin', label: 'Viền xoay' },
  { value: 'glow', label: 'Hào quang' },
  { value: 'holographic', label: 'Pha lê / holographic' },
];

type FormValues = {
  name: string;
  slug?: string;
  description?: string;
  effect_type: string;
  color1?: string;
  color2?: string;
  color3?: string;
  thickness?: number;
  speed_ms?: number;
  intensity?: number;
  is_premium?: boolean;
  show_badge?: boolean;
  sort_order?: number;
  is_active?: boolean;
};

function toPayload(values: FormValues) {
  const colors = [values.color1, values.color2, values.color3].filter(Boolean) as string[];
  return {
    name: values.name,
    slug: values.slug || undefined,
    description: values.description,
    effect_type: values.effect_type,
    effect_config: {
      colors: colors.length ? colors : ['#fbbf24', '#f59e0b', '#d97706'],
      thickness: values.thickness ?? 3,
      speed_ms: values.speed_ms ?? 3000,
      intensity: values.intensity ?? 0.7,
    },
    is_premium: values.is_premium ?? false,
    show_badge: values.show_badge ?? false,
    sort_order: values.sort_order ?? 0,
    is_active: values.is_active ?? true,
  };
}

function colorHex(v: unknown, fallback: string): string {
  if (typeof v === 'string') return v;
  if (v && typeof v === 'object' && 'toHexString' in v) {
    return (v as { toHexString: () => string }).toHexString();
  }
  return fallback;
}

export default function AvatarFramesPage() {
  const qc = useQueryClient();
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<AvatarFrame | null>(null);
  const [form] = Form.useForm<FormValues>();
  const watched = Form.useWatch([], form);

  const { data, isLoading } = useQuery({
    queryKey: ['admin-avatar-frames'],
    queryFn: listAvatarFrames,
  });

  const previewFrame = useMemo(() => {
    const colors = [
      colorHex(watched?.color1, '#fbbf24'),
      colorHex(watched?.color2, '#f59e0b'),
      colorHex(watched?.color3, '#d97706'),
    ];
    return {
      effect_type: watched?.effect_type || 'spin',
      effect_config: {
        colors,
        thickness: watched?.thickness ?? 3,
        speed_ms: watched?.speed_ms ?? 3000,
        intensity: watched?.intensity ?? 0.7,
      },
      show_badge: watched?.show_badge ?? false,
    };
  }, [watched]);

  const openCreate = () => {
    setEditing(null);
    form.setFieldsValue({
      name: '',
      slug: '',
      description: '',
      effect_type: 'spin',
      color1: '#fbbf24',
      color2: '#f59e0b',
      color3: '#d97706',
      thickness: 3,
      speed_ms: 3000,
      intensity: 0.7,
      is_premium: true,
      show_badge: true,
      sort_order: 100,
      is_active: true,
    });
    setOpen(true);
  };

  const openEdit = (row: AvatarFrame) => {
    setEditing(row);
    const colors = row.effect_config?.colors ?? [];
    form.setFieldsValue({
      name: row.name,
      slug: row.slug,
      description: row.description ?? '',
      effect_type: row.effect_type,
      color1: colors[0] ?? '#fbbf24',
      color2: colors[1] ?? '#f59e0b',
      color3: colors[2] ?? '#d97706',
      thickness: row.effect_config?.thickness ?? 3,
      speed_ms: row.effect_config?.speed_ms ?? 3000,
      intensity: row.effect_config?.intensity ?? 0.7,
      is_premium: row.is_premium,
      show_badge: row.show_badge,
      sort_order: row.sort_order,
      is_active: row.is_active,
    });
    setOpen(true);
  };

  return (
    <>
      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 16 }}>
        <div>
          <strong>Khung avatar</strong>
          <div style={{ color: '#78716c', fontSize: 13 }}>
            Catalog khung + engine hiệu ứng. Thêm khung mới không cần deploy code.
          </div>
        </div>
        <Button type="primary" onClick={openCreate}>
          Thêm khung
        </Button>
      </div>

      <Table
        rowKey="id"
        loading={isLoading}
        dataSource={data}
        columns={[
          {
            title: 'Xem trước',
            width: 100,
            render: (_, r) => <AvatarFramePreview frame={r} size="sm" />,
          },
          { title: 'Tên', dataIndex: 'name' },
          { title: 'Slug', dataIndex: 'slug' },
          {
            title: 'Hiệu ứng',
            dataIndex: 'effect_type',
            render: (v: string) => <Tag>{v}</Tag>,
          },
          {
            title: 'Premium',
            dataIndex: 'is_premium',
            render: (v: boolean) => (v ? <Tag color="gold">Premium</Tag> : <Tag>Free</Tag>),
          },
          {
            title: 'Active',
            dataIndex: 'is_active',
            render: (v: boolean, r) => (
              <Switch
                checked={v}
                onChange={async (checked) => {
                  await updateAvatarFrame(r.id, { is_active: checked });
                  qc.invalidateQueries({ queryKey: ['admin-avatar-frames'] });
                }}
              />
            ),
          },
          { title: 'Thứ tự', dataIndex: 'sort_order', width: 80 },
          {
            title: 'Thao tác',
            render: (_, r) => (
              <Space>
                <Button size="small" onClick={() => openEdit(r)}>
                  Sửa
                </Button>
                <Button
                  size="small"
                  danger
                  onClick={async () => {
                    try {
                      await deleteAvatarFrame(r.id);
                      message.success('Đã xoá');
                      qc.invalidateQueries({ queryKey: ['admin-avatar-frames'] });
                    } catch {
                      message.error('Không xoá được (còn user đang dùng?)');
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
        title={editing ? 'Sửa khung' : 'Thêm khung'}
        open={open}
        onCancel={() => setOpen(false)}
        onOk={() => form.submit()}
        width={720}
        destroyOnHidden
      >
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 180px', gap: 24 }}>
          <Form
            form={form}
            layout="vertical"
            onFinish={async (values) => {
              const payload = toPayload({
                ...values,
                color1: colorHex(values.color1, '#fbbf24'),
                color2: colorHex(values.color2, '#f59e0b'),
                color3: colorHex(values.color3, '#d97706'),
              });
              if (editing) {
                await updateAvatarFrame(editing.id, payload);
                message.success('Đã cập nhật');
              } else {
                await createAvatarFrame(payload);
                message.success('Đã tạo');
              }
              setOpen(false);
              qc.invalidateQueries({ queryKey: ['admin-avatar-frames'] });
            }}
          >
            <Form.Item name="name" label="Tên" rules={[{ required: true }]}>
              <Input />
            </Form.Item>
            <Form.Item name="slug" label="Slug">
              <Input placeholder="tự sinh nếu trống" />
            </Form.Item>
            <Form.Item name="description" label="Mô tả">
              <Input.TextArea rows={2} />
            </Form.Item>
            <Form.Item name="effect_type" label="Loại hiệu ứng" rules={[{ required: true }]}>
              <Select options={EFFECT_OPTIONS} />
            </Form.Item>
            <Space wrap>
              <Form.Item name="color1" label="Màu 1">
                <ColorPicker format="hex" />
              </Form.Item>
              <Form.Item name="color2" label="Màu 2">
                <ColorPicker format="hex" />
              </Form.Item>
              <Form.Item name="color3" label="Màu 3">
                <ColorPicker format="hex" />
              </Form.Item>
            </Space>
            <Space wrap style={{ width: '100%' }}>
              <Form.Item name="thickness" label="Độ dày (px)">
                <InputNumber min={1} max={8} />
              </Form.Item>
              <Form.Item name="speed_ms" label="Tốc độ (ms)">
                <InputNumber min={800} max={12000} step={100} />
              </Form.Item>
              <Form.Item name="intensity" label="Cường độ">
                <InputNumber min={0.1} max={1} step={0.05} />
              </Form.Item>
              <Form.Item name="sort_order" label="Thứ tự">
                <InputNumber min={0} />
              </Form.Item>
            </Space>
            <Space>
              <Form.Item name="is_premium" label="Premium" valuePropName="checked">
                <Switch />
              </Form.Item>
              <Form.Item name="show_badge" label="Huy hiệu ✦" valuePropName="checked">
                <Switch />
              </Form.Item>
              <Form.Item name="is_active" label="Active" valuePropName="checked">
                <Switch />
              </Form.Item>
            </Space>
          </Form>

          <div
            style={{
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'center',
              justifyContent: 'center',
              gap: 12,
              padding: 16,
              background: '#fafaf9',
              borderRadius: 12,
              border: '1px solid #e7e5e4',
            }}
          >
            <div style={{ fontSize: 12, color: '#78716c' }}>Xem trước trực tiếp</div>
            <AvatarFramePreview frame={previewFrame} size="lg" initials="VV" />
            <div style={{ fontSize: 12, fontWeight: 600 }}>{watched?.name || 'Tên khung'}</div>
          </div>
        </div>
      </Modal>
    </>
  );
}
