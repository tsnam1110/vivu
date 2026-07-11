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
  Tabs,
  Tag,
  Typography,
  message,
} from 'antd';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import {
  getUserHabitSummary,
  grantUserPremium,
  listUserHabitHistory,
  listUsers,
  updateUserStatus,
  type UserRow,
} from '../api/resources';
import { useState } from 'react';
import DatePresetSelect from '../components/DatePresetSelect';
import { DEFAULT_DATE_PRESET, type DatePreset } from '../utils/datePresets';
import { serverPagination, sttIdColumn } from '../utils/listTable';

function statusTag(status: string | null) {
  if (status === 'done') return <Tag color="green">Đạt ✓</Tag>;
  if (status === 'missed') return <Tag color="red">Không đạt ✗</Tag>;
  return <Tag>Trống</Tag>;
}

export default function UsersPage() {
  const [page, setPage] = useState(1);
  const [q, setQ] = useState('');
  const [status, setStatus] = useState<string | undefined>();
  const [premium, setPremium] = useState<string | undefined>();
  const [datePreset, setDatePreset] = useState<DatePreset>(DEFAULT_DATE_PRESET);
  const [premiumUser, setPremiumUser] = useState<UserRow | null>(null);
  const [habitUser, setHabitUser] = useState<UserRow | null>(null);
  const [historyPage, setHistoryPage] = useState(1);
  const [form] = Form.useForm();
  const qc = useQueryClient();

  const { data, isLoading } = useQuery({
    queryKey: ['admin-users', page, q, status, premium, datePreset],
    queryFn: () =>
      listUsers({
        page,
        q: q || undefined,
        status,
        premium,
        date_preset: datePreset,
      }),
  });

  const { data: habitSummary, isLoading: summaryLoading } = useQuery({
    queryKey: ['admin-user-habit-summary', habitUser?.id],
    queryFn: () => getUserHabitSummary(habitUser!.id),
    enabled: !!habitUser,
  });

  const { data: habitHistory, isLoading: historyLoading } = useQuery({
    queryKey: ['admin-user-habit-history', habitUser?.id, historyPage],
    queryFn: () => listUserHabitHistory(habitUser!.id, { page: historyPage, per_page: 15 }),
    enabled: !!habitUser,
  });

  const openHabitHistory = (user: UserRow) => {
    setHistoryPage(1);
    setHabitUser(user);
  };

  const resetPage = () => setPage(1);

  return (
    <>
      <Space style={{ marginBottom: 16 }} wrap>
        <Input.Search
          placeholder="Tìm tên / username / email"
          allowClear
          onSearch={(v) => {
            setQ(v);
            resetPage();
          }}
          style={{ width: 240 }}
        />
        <Select
          allowClear
          placeholder="Trạng thái"
          style={{ width: 140 }}
          value={status}
          onChange={(v) => {
            setStatus(v);
            resetPage();
          }}
          options={[
            { value: 'active', label: 'Active' },
            { value: 'suspended', label: 'Suspended' },
          ]}
        />
        <Select
          allowClear
          placeholder="Premium"
          style={{ width: 140 }}
          value={premium}
          onChange={(v) => {
            setPremium(v);
            resetPage();
          }}
          options={[
            { value: 'active', label: 'Đang Premium' },
            { value: 'none', label: 'Free' },
          ]}
        />
        <DatePresetSelect
          value={datePreset}
          onChange={(v) => {
            setDatePreset(v);
            resetPage();
          }}
        />
      </Space>

      <Table
        rowKey="id"
        loading={isLoading}
        dataSource={data?.data}
        pagination={serverPagination(data?.meta, setPage)}
        columns={[
          sttIdColumn<UserRow>(data?.meta),
          { title: 'Tên', dataIndex: 'name' },
          { title: 'Username', dataIndex: 'username' },
          {
            title: 'Premium',
            render: (_, r) =>
              r.has_active_premium ? (
                <Tag color="gold">
                  đến{' '}
                  {r.premium_expires_at
                    ? new Date(r.premium_expires_at).toLocaleDateString('vi-VN')
                    : '—'}
                </Tag>
              ) : (
                <Tag>Free</Tag>
              ),
          },
          {
            title: 'Trạng thái',
            dataIndex: 'status',
            render: (s: string) => (
              <Tag color={s === 'active' ? 'green' : 'red'}>{s || '—'}</Tag>
            ),
          },
          {
            title: 'Thao tác',
            width: 320,
            render: (_, r) => (
              <Space wrap>
                <Button size="small" onClick={() => openHabitHistory(r)}>
                  Lịch sử habit
                </Button>
                <Button
                  size="small"
                  onClick={() => {
                    setPremiumUser(r);
                    form.setFieldsValue({ days: 30, lifetime: false });
                  }}
                >
                  Premium
                </Button>
                {r.status !== 'suspended' ? (
                  <Button
                    size="small"
                    danger
                    onClick={async () => {
                      await updateUserStatus(r.id, 'suspended');
                      message.success('Đã khoá');
                      qc.invalidateQueries({ queryKey: ['admin-users'] });
                    }}
                  >
                    Khoá
                  </Button>
                ) : (
                  <Button
                    size="small"
                    type="primary"
                    onClick={async () => {
                      await updateUserStatus(r.id, 'active');
                      message.success('Đã mở khoá');
                      qc.invalidateQueries({ queryKey: ['admin-users'] });
                    }}
                  >
                    Mở khoá
                  </Button>
                )}
              </Space>
            ),
          },
        ]}
      />

      <Modal
        title={premiumUser ? `Cấp Premium — @${premiumUser.username}` : 'Cấp Premium'}
        open={!!premiumUser}
        onCancel={() => setPremiumUser(null)}
        onOk={() => form.submit()}
        destroyOnHidden
      >
        <Form
          form={form}
          layout="vertical"
          onFinish={async (values) => {
            if (!premiumUser) return;
            await grantUserPremium(premiumUser.id, {
              days: values.lifetime ? undefined : values.days,
              lifetime: values.lifetime,
            });
            message.success('Đã cấp Premium');
            setPremiumUser(null);
            qc.invalidateQueries({ queryKey: ['admin-users'] });
          }}
        >
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
        </Form>
      </Modal>

      <Modal
        title={
          habitUser
            ? `Habit — ${habitUser.name} (@${habitUser.username})`
            : 'Lịch sử habit'
        }
        open={!!habitUser}
        onCancel={() => setHabitUser(null)}
        footer={[
          <Button key="close" onClick={() => setHabitUser(null)}>
            Đóng
          </Button>,
        ]}
        width={860}
        destroyOnHidden
      >
        <Tabs
          items={[
            {
              key: 'summary',
              label: 'Tổng quan',
              children: (
                <div>
                  {summaryLoading ? (
                    <Typography.Text type="secondary">Đang tải…</Typography.Text>
                  ) : habitSummary ? (
                    <Space direction="vertical" size="middle" style={{ width: '100%' }}>
                      <Space wrap size="large">
                        <div>
                          <Typography.Text type="secondary">Tháng </Typography.Text>
                          <Typography.Text strong>{habitSummary.month.month_label}</Typography.Text>
                        </div>
                        <Tag color="green">Đạt {habitSummary.month.done}</Tag>
                        <Tag color="red">Không đạt {habitSummary.month.missed}</Tag>
                        <Tag>Trống {habitSummary.month.empty}</Tag>
                        <Tag color="blue">
                          Tỷ lệ đạt{' '}
                          {habitSummary.month.done + habitSummary.month.missed > 0
                            ? Math.round(habitSummary.month.rate * 100)
                            : 0}
                          %
                        </Tag>
                        <Tag>
                          {habitSummary.active_items_count}/{habitSummary.items_count} đầu mục
                          active
                        </Tag>
                      </Space>

                      <Table
                        size="small"
                        rowKey="id"
                        pagination={false}
                        dataSource={habitSummary.items}
                        locale={{ emptyText: 'User chưa có đầu mục habit' }}
                        columns={[
                          {
                            title: '',
                            dataIndex: 'icon',
                            width: 40,
                            render: (icon: string) => icon || '•',
                          },
                          { title: 'Tên', dataIndex: 'name' },
                          {
                            title: 'Loại',
                            width: 100,
                            render: (_, row) =>
                              row.is_custom ? (
                                <Tag color="purple">Tuỳ chỉnh</Tag>
                              ) : (
                                <Tag>Từ mẫu</Tag>
                              ),
                          },
                          {
                            title: 'Trạng thái',
                            width: 90,
                            dataIndex: 'is_active',
                            render: (v: boolean) =>
                              v ? <Tag color="green">Hiện</Tag> : <Tag>Ẩn</Tag>,
                          },
                        ]}
                      />
                    </Space>
                  ) : null}
                </div>
              ),
            },
            {
              key: 'history',
              label: 'Lịch sử',
              children: (
                <Table
                  size="small"
                  rowKey="id"
                  loading={historyLoading}
                  dataSource={habitHistory?.data}
                  locale={{ emptyText: 'Chưa có lịch sử ghi nhận' }}
                  pagination={serverPagination(habitHistory?.meta, setHistoryPage)}
                  columns={[
                    {
                      title: 'Thời điểm',
                      dataIndex: 'changed_at',
                      width: 160,
                      render: (v: string | null) =>
                        v ? new Date(v).toLocaleString('vi-VN') : '—',
                    },
                    {
                      title: 'Đầu mục',
                      render: (_, row) => (
                        <span>
                          {row.habit_item?.icon || '•'} {row.habit_item?.name || '—'}
                        </span>
                      ),
                    },
                    {
                      title: 'Ngày ô',
                      dataIndex: 'entry_date',
                      width: 110,
                      render: (v: string | null) =>
                        v ? new Date(v + 'T00:00:00').toLocaleDateString('vi-VN') : '—',
                    },
                    {
                      title: 'Từ',
                      width: 110,
                      dataIndex: 'from_status',
                      render: (s: string | null) => statusTag(s),
                    },
                    {
                      title: 'Sang',
                      width: 110,
                      dataIndex: 'to_status',
                      render: (s: string | null) => statusTag(s),
                    },
                    {
                      title: 'Nguồn',
                      dataIndex: 'source',
                      width: 70,
                    },
                  ]}
                />
              ),
            },
          ]}
        />
      </Modal>
    </>
  );
}
