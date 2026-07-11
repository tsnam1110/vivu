import { Button, Form, InputNumber, Modal, Space, Switch, Table, Tag, message } from 'antd';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { grantUserPremium, listUsers, updateUserStatus, type UserRow } from '../api/resources';
import { useState } from 'react';

export default function UsersPage() {
  const [page, setPage] = useState(1);
  const [premiumUser, setPremiumUser] = useState<UserRow | null>(null);
  const [form] = Form.useForm();
  const qc = useQueryClient();
  const { data, isLoading } = useQuery({
    queryKey: ['admin-users', page],
    queryFn: () => listUsers({ page }),
  });

  return (
    <>
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
          { title: 'ID', dataIndex: 'id', width: 70 },
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
            render: (_, r) => (
              <Space>
                <Button size="small" onClick={() => {
                  setPremiumUser(r);
                  form.setFieldsValue({ days: 30, lifetime: false });
                }}>
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
    </>
  );
}
