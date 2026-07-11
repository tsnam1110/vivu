import { EyeInvisibleOutlined, EyeOutlined } from '@ant-design/icons';
import { Alert, Button, Card, Form, Input, Typography } from 'antd';
import axios from 'axios';
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { login } from '../api/auth';

function loginErrorMessage(err: unknown): string {
  if (axios.isAxiosError(err)) {
    if (!err.response) {
      return 'Không kết nối được API. Hãy chạy `php artisan serve` (http://127.0.0.1:8000).';
    }
    if (err.response.status === 422) {
      const msg = err.response.data?.message
        ?? err.response.data?.errors?.email?.[0]
        ?? err.response.data?.errors?.password?.[0];
      if (typeof msg === 'string' && msg.length > 0) {
        return msg;
      }
      return 'Email hoặc mật khẩu không đúng.';
    }
    if (err.response.status === 419) {
      return 'CSRF/session lỗi (419). Admin dùng Bearer token — đừng đưa cổng admin vào SANCTUM_STATEFUL_DOMAINS.';
    }
    if (err.response.status === 429) {
      return 'Quá nhiều lần thử. Vui lòng thử lại sau.';
    }
    return `Đăng nhập thất bại (HTTP ${err.response.status}).`;
  }
  return 'Đăng nhập thất bại. Vui lòng thử lại.';
}

export default function LoginPage() {
  const navigate = useNavigate();
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  return (
    <div style={{ minHeight: '100vh', display: 'grid', placeItems: 'center', background: '#f5f5f5' }}>
      <Card style={{ width: 380 }}>
        <Typography.Title level={3}>ViVu Admin</Typography.Title>
        <Typography.Paragraph type="secondary">Đăng nhập quản trị</Typography.Paragraph>
        {error && <Alert type="error" message={error} style={{ marginBottom: 16 }} showIcon />}
        <Form
          layout="vertical"
          onFinish={async (values) => {
            setLoading(true);
            setError(null);
            try {
              await login(values.email, values.password);
              navigate('/experiences');
            } catch (err) {
              setError(loginErrorMessage(err));
            } finally {
              setLoading(false);
            }
          }}
        >
          <Form.Item name="email" label="Email" rules={[{ required: true, type: 'email' }]}>
            <Input placeholder="admin@vivu.test" />
          </Form.Item>
          <Form.Item name="password" label="Mật khẩu" rules={[{ required: true }]}>
            <Input.Password
              placeholder="Mật khẩu"
              visibilityToggle
              iconRender={(visible) => (
                <span
                  tabIndex={-1}
                  aria-hidden
                  onMouseDown={(e) => e.preventDefault()}
                  style={{ display: 'inline-flex', outline: 'none' }}
                >
                  {visible ? <EyeOutlined /> : <EyeInvisibleOutlined />}
                </span>
              )}
            />
          </Form.Item>
          <Button type="primary" htmlType="submit" block loading={loading}>
            Đăng nhập
          </Button>
        </Form>
      </Card>
    </div>
  );
}
