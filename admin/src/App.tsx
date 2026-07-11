import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ConfigProvider } from 'antd';
import viVN from 'antd/locale/vi_VN';
import AdminLayout from './layouts/AdminLayout';
import LoginPage from './pages/LoginPage';
import ExperiencesPage from './pages/ExperiencesPage';
import UsersPage from './pages/UsersPage';
import CommentsPage from './pages/CommentsPage';
import CategoriesPage from './pages/CategoriesPage';
import TagsPage from './pages/TagsPage';
import TasteTraitsPage from './pages/TasteTraitsPage';
import AvatarFramesPage from './pages/AvatarFramesPage';
import PremiumSubscriptionsPage from './pages/PremiumSubscriptionsPage';
import { getToken } from './api/http';

const queryClient = new QueryClient();

function RequireAuth({ children }: { children: React.ReactNode }) {
  if (!getToken()) {
    return <Navigate to="/login" replace />;
  }
  return <>{children}</>;
}

export default function App() {
  return (
    <ConfigProvider locale={viVN}>
      <QueryClientProvider client={queryClient}>
        <BrowserRouter>
          <Routes>
            <Route path="/login" element={<LoginPage />} />
            <Route
              path="/"
              element={
                <RequireAuth>
                  <AdminLayout />
                </RequireAuth>
              }
            >
              <Route index element={<Navigate to="/experiences" replace />} />
              <Route path="experiences" element={<ExperiencesPage />} />
              <Route path="users" element={<UsersPage />} />
              <Route path="comments" element={<CommentsPage />} />
              <Route path="categories" element={<CategoriesPage />} />
              <Route path="tags" element={<TagsPage />} />
              <Route path="taste-traits" element={<TasteTraitsPage />} />
              <Route path="avatar-frames" element={<AvatarFramesPage />} />
              <Route path="premium" element={<PremiumSubscriptionsPage />} />
            </Route>
          </Routes>
        </BrowserRouter>
      </QueryClientProvider>
    </ConfigProvider>
  );
}
