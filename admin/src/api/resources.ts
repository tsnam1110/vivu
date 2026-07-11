import { http } from './http';

export interface Paginated<T> {
  data: T[];
  meta: {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
  };
}

export interface Experience {
  id: number;
  title: string;
  slug: string;
  status: string;
  place_name?: string;
  rating_avg: number;
  reaction_count: number;
  published_at?: string;
  user?: { name: string; username: string };
  category?: { name: string };
}

export interface Category {
  id: number;
  name: string;
  slug: string;
  icon?: string;
  description?: string;
  sort_order: number;
  is_active: boolean;
}

export interface UserRow {
  id: number;
  name: string;
  username: string;
  email?: string;
  status?: string;
  has_active_premium?: boolean;
  premium_expires_at?: string | null;
  avatar_url?: string | null;
}

export interface AvatarFrame {
  id: number;
  slug: string;
  name: string;
  description?: string | null;
  effect_type: string;
  effect_config: {
    colors?: string[];
    thickness?: number;
    speed_ms?: number;
    intensity?: number;
  };
  css_variables?: Record<string, string>;
  effect_class?: string;
  is_premium: boolean;
  show_badge: boolean;
  sort_order: number;
  is_active: boolean;
}

export interface SampleAvatar {
  id: number;
  slug: string;
  name: string;
  path: string;
  url: string;
  sort_order: number;
  is_active: boolean;
}

export interface PremiumSubscription {
  id: number;
  user_id: number;
  user?: {
    id: number;
    name: string;
    username: string;
    email?: string;
    premium_expires_at?: string | null;
    has_active_premium?: boolean;
  };
  starts_at: string;
  ends_at: string | null;
  is_lifetime: boolean;
  status: string;
  source: string;
  notes?: string | null;
  is_currently_valid?: boolean;
  created_at?: string;
}

export interface CommentRow {
  id: number;
  body: string;
  rating?: number;
  status: string;
  user?: { name: string };
}

export interface Tag {
  id: number;
  name: string;
  slug: string;
  category_id?: number | null;
  usage_count: number;
  status?: 'pending' | 'approved';
  created_by?: number | null;
  category?: { id: number; name: string; icon?: string | null } | null;
  creator?: { id: number; name: string; username?: string } | null;
}

export interface TasteTrait {
  id: number;
  type: string;
  name: string;
  slug: string;
  is_active: boolean;
}

export const listExperiences = (params?: Record<string, unknown>) =>
  http.get<Paginated<Experience>>('/admin/experiences', { params }).then((r) => r.data);

export const updateExperienceStatus = (id: number, status: string) =>
  http.patch<{ data: Experience }>(`/admin/experiences/${id}`, { status }).then((r) => r.data);

export const listUsers = (params?: Record<string, unknown>) =>
  http.get<Paginated<UserRow>>('/admin/users', { params }).then((r) => r.data);

export const updateUserStatus = (id: number, status: string) =>
  http.patch(`/admin/users/${id}`, { status }).then((r) => r.data);

export const grantUserPremium = (
  id: number,
  payload: { days?: number; lifetime?: boolean; notes?: string },
) => http.patch(`/admin/users/${id}/premium`, payload).then((r) => r.data);

export const listAvatarFrames = () =>
  http.get<{ data: AvatarFrame[] }>('/admin/avatar-frames').then((r) => r.data.data);

export const createAvatarFrame = (payload: Partial<AvatarFrame>) =>
  http.post('/admin/avatar-frames', payload).then((r) => r.data);

export const updateAvatarFrame = (id: number, payload: Partial<AvatarFrame>) =>
  http.patch(`/admin/avatar-frames/${id}`, payload).then((r) => r.data);

export const deleteAvatarFrame = (id: number) => http.delete(`/admin/avatar-frames/${id}`);

export const listSampleAvatars = () =>
  http.get<{ data: SampleAvatar[] }>('/admin/sample-avatars').then((r) => r.data.data);

export const createSampleAvatar = (payload: Partial<SampleAvatar>) =>
  http.post('/admin/sample-avatars', payload).then((r) => r.data);

export const updateSampleAvatar = (id: number, payload: Partial<SampleAvatar>) =>
  http.patch(`/admin/sample-avatars/${id}`, payload).then((r) => r.data);

export const deleteSampleAvatar = (id: number) => http.delete(`/admin/sample-avatars/${id}`);

export const listPremiumSubscriptions = (params?: Record<string, unknown>) =>
  http.get<Paginated<PremiumSubscription>>('/admin/premium-subscriptions', { params }).then((r) => r.data);

export const createPremiumSubscription = (payload: Record<string, unknown>) =>
  http.post('/admin/premium-subscriptions', payload).then((r) => r.data);

export const updatePremiumSubscription = (
  id: number,
  payload: { action: 'cancel' | 'extend'; days?: number; lifetime?: boolean; notes?: string },
) => http.patch(`/admin/premium-subscriptions/${id}`, payload).then((r) => r.data);

export const listCategories = () =>
  http.get<{ data: Category[] }>('/admin/categories').then((r) => r.data.data);

export const createCategory = (payload: Partial<Category>) =>
  http.post('/admin/categories', payload).then((r) => r.data);

export const updateCategory = (id: number, payload: Partial<Category>) =>
  http.patch(`/admin/categories/${id}`, payload).then((r) => r.data);

export const deleteCategory = (id: number) => http.delete(`/admin/categories/${id}`);

export const listComments = (params?: Record<string, unknown>) =>
  http.get<Paginated<CommentRow>>('/admin/comments', { params }).then((r) => r.data);

export const updateCommentStatus = (id: number, status: string) =>
  http.patch(`/admin/comments/${id}`, { status }).then((r) => r.data);

export const listTags = (params?: Record<string, unknown>) =>
  http.get<Paginated<Tag>>('/admin/tags', { params }).then((r) => r.data);

export const createTag = (payload: {
  name: string;
  category_id?: number | null;
  slug?: string;
  status?: 'pending' | 'approved';
}) => http.post<{ data: Tag }>('/admin/tags', payload).then((r) => r.data);

export const updateTag = (
  id: number,
  payload: {
    name?: string;
    category_id?: number | null;
    slug?: string;
    status?: 'pending' | 'approved';
  },
) => http.put<{ data: Tag }>(`/admin/tags/${id}`, payload).then((r) => r.data);

export const updateTagStatus = (id: number, status: 'pending' | 'approved') =>
  http.patch<{ data: Tag }>(`/admin/tags/${id}/status`, { status }).then((r) => r.data);

export const deleteTag = (id: number) => http.delete(`/admin/tags/${id}`);

export const listTasteTraits = () =>
  http.get<{ data: TasteTrait[] }>('/admin/taste-traits').then((r) => r.data.data);

export const createTasteTrait = (payload: { type: string; name: string }) =>
  http.post('/admin/taste-traits', payload).then((r) => r.data);
