import {
  CommentOutlined,
  LogoutOutlined,
  TagsOutlined,
  TeamOutlined,
  AppstoreOutlined,
  CompassOutlined,
  SmileOutlined,
} from '@ant-design/icons';
import { Layout, Menu, theme, Typography, Button, Space } from 'antd';
import { Link, Outlet, useLocation, useNavigate } from 'react-router-dom';
import { logout } from '../api/auth';

const { Header, Sider, Content } = Layout;

export default function AdminLayout() {
  const location = useLocation();
  const navigate = useNavigate();
  const { token } = theme.useToken();

  const selected = '/' + (location.pathname.split('/')[1] || 'experiences');

  return (
    <Layout style={{ minHeight: '100vh' }}>
      <Sider breakpoint="lg" collapsedWidth={64}>
        <div style={{ color: '#fff', padding: 16, fontWeight: 700, fontSize: 18 }}>ViVu Admin</div>
        <Menu
          theme="dark"
          mode="inline"
          selectedKeys={[selected]}
          items={[
            { key: '/experiences', icon: <CompassOutlined />, label: <Link to="/experiences">Trải nghiệm</Link> },
            { key: '/comments', icon: <CommentOutlined />, label: <Link to="/comments">Bình luận</Link> },
            { key: '/users', icon: <TeamOutlined />, label: <Link to="/users">Người dùng</Link> },
            { key: '/categories', icon: <AppstoreOutlined />, label: <Link to="/categories">Danh mục</Link> },
            { key: '/tags', icon: <TagsOutlined />, label: <Link to="/tags">Thẻ</Link> },
            { key: '/taste-traits', icon: <SmileOutlined />, label: <Link to="/taste-traits">Nhãn gu</Link> },
          ]}
        />
      </Sider>
      <Layout>
        <Header style={{ background: token.colorBgContainer, padding: '0 24px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <Typography.Title level={4} style={{ margin: 0 }}>Quản trị ViVu</Typography.Title>
          <Space>
            <Button
              icon={<LogoutOutlined />}
              onClick={async () => {
                await logout();
                navigate('/login');
              }}
            >
              Đăng xuất
            </Button>
          </Space>
        </Header>
        <Content style={{ margin: 24 }}>
          <div style={{ background: token.colorBgContainer, padding: 24, borderRadius: 12, minHeight: 360 }}>
            <Outlet />
          </div>
        </Content>
      </Layout>
    </Layout>
  );
}
