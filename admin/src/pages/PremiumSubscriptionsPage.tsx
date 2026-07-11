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
import { useState } from 'react';
import {
  createPremiumSubscription,
  listPremiumSubscriptions,
  updatePremiumSubscription,
  type PremiumSubscription,
} from '../api/resources';
import ListTotalFooter from '../components/ListTotalFooter';
import { resolveListTotal, sttIdColumn } from '../utils/listTable';

function formatDate(iso?: string | null) {
  if (!iso) return 'Lifetime';
  try {
    return new Date(iso).toLocaleString('vi-VN');
  } catch {
    return iso;
  }
}

export default function PremiumSubscriptionsPage() {
  const qc = useQueryClient();
  const [page, setPage] = useState(1);
  const [status, setStatus] = useState<string | undefined>();
  const [q, setQ] = useState('');
  const [open, setOpen] = useState(false);
  const [form] = Form.useForm();

  const { data, isLoading } = useQuery({
    queryKey: ['admin-premium', page, status, q],
    queryFn: () =>
      listPremiumSubscriptions({
        page,
        status,
        q: q || undefined,
      }),
  });

  return (
    <>
      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 16, gap: 12, flexWrap: 'wrap' }}>
        <div>
          <strong>Đăng ký Premium</strong>
          <div style={{ color: '#78716c', fontSize: 13 }}>
            Quản lý thời hạn Premium (grant / gia hạn / huỷ).
          </div>
        </div>
        <Space wrap>
          <Input.Search
            placeholder="Tìm user…"
            allowClear
            onSearch={(v) => {
              setPage(1);
              setQ(v);
            }}
            style={{ width: 200 }}
          />
          <Select
            allowClear
            placeholder="Trạng thái"
            style={{ width: 140 }}
            value={status}
            onChange={(v) => {
              setPage(1);
              setStatus(v);
            }}
            options={[
              { value: 'active', label: 'Active' },
              { value: 'expired', label: 'Expired' },
              { value: 'cancelled', label: 'Cancelled' },
            ]}
          />
          <Button type="primary" onClick={() => setOpen(true)}>
            Cấp Premium
          </Button>
        </Space>
      </div>

      <Table
        rowKey="id"
        loading={isLoading}
        dataSource={data?.data}
        pagination={{
          current: data?.meta.current_page,
          total: data?.meta.total,
          pageSize: data?.meta.per_page,
          onChange: setPage,
        }}
        columns={[
          sttIdColumn<PremiumSubscription>(data?.meta),
          {
            title: 'User',
            render: (_, r: PremiumSubscription) =>
              r.user ? (
                <div>
                  <div>{r.user.name}</div>
                  <div style={{ fontSize: 12, color: '#78716c' }}>
                    @{r.user.username} · {r.user.email}
                  </div>
                </div>
              ) : (
                r.user_id
              ),
          },
          {
            title: 'Bắt đầu',
            dataIndex: 'starts_at',
            render: formatDate,
          },
          {
            title: 'Hết hạn',
            dataIndex: 'ends_at',
            render: (v: string | null, r) => (r.is_lifetime ? 'Lifetime' : formatDate(v)),
          },
          {
            title: 'Status',
            dataIndex: 'status',
            render: (s: string) => (
              <Tag color={s === 'active' ? 'green' : s === 'cancelled' ? 'red' : 'default'}>{s}</Tag>
            ),
          },
          {
            title: 'Nguồn',
            dataIndex: 'source',
            render: (s: string) => <Tag>{s}</Tag>,
          },
          {
            title: 'Ghi chú',
            dataIndex: 'notes',
            ellipsis: true,
          },
          {
            title: 'Thao tác',
            render: (_, r) => (
              <Space>
                {r.status === 'active' && (
                  <>
                    <Button
                      size="small"
                      onClick={async () => {
                        await updatePremiumSubscription(r.id, { action: 'extend', days: 30 });
                        message.success('Đã gia hạn +30 ngày');
                        qc.invalidateQueries({ queryKey: ['admin-premium'] });
                      }}
                    >
                      +30 ngày
                    </Button>
                    <Button
                      size="small"
                      danger
                      onClick={async () => {
                        await updatePremiumSubscription(r.id, { action: 'cancel' });
                        message.success('Đã huỷ');
                        qc.invalidateQueries({ queryKey: ['admin-premium'] });
                      }}
                    >
                      Huỷ
                    </Button>
                  </>
                )}
              </Space>
            ),
          },
        ]}
      />
      <ListTotalFooter
        total={resolveListTotal(data?.meta.total, data?.data?.length)}
        loading={isLoading}
      />

      <Modal
        title="Cấp Premium"
        open={open}
        onCancel={() => setOpen(false)}
        onOk={() => form.submit()}
        destroyOnHidden
      >
        <Form
          form={form}
          layout="vertical"
          initialValues={{ days: 30, lifetime: false }}
          onFinish={async (values) => {
            await createPremiumSubscription({
              username: values.username,
              days: values.lifetime ? undefined : values.days,
              lifetime: values.lifetime,
              notes: values.notes,
            });
            message.success('Đã cấp Premium');
            setOpen(false);
            form.resetFields();
            qc.invalidateQueries({ queryKey: ['admin-premium'] });
          }}
        >
          <Form.Item
            name="username"
            label="Username"
            rules={[{ required: true, message: 'Nhập username' }]}
          >
            <Input placeholder="vd: nguyenvana" />
          </Form.Item>
          <Form.Item name="lifetime" label="Lifetime" valuePropName="checked">
            <Switch />
          </Form.Item>
          <Form.Item noStyle shouldUpdate={(p, c) => p.lifetime !== c.lifetime}>
            {({ getFieldValue }) =>
              getFieldValue('lifetime') ? null : (
                <Form.Item name="days" label="Số ngày" rules={[{ required: true }]}>
                  <InputNumber min={1} max={3650} style={{ width: '100%' }} />
                </Form.Item>
              )
            }
          </Form.Item>
          <Form.Item name="notes" label="Ghi chú">
            <Input.TextArea rows={2} />
          </Form.Item>
        </Form>
      </Modal>
    </>
  );
}
