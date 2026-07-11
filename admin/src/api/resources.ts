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

export const listTags = () =>
  http.get<Paginated<Tag>>('/admin/tags').then((r) => r.data);

export const createTag = (payload: { name: string; category_id?: number | null }) =>
  http.post('/admin/tags', payload).then((r) => r.data);

export const listTasteTraits = () =>
  http.get<{ data: TasteTrait[] }>('/admin/taste-traits').then((r) => r.data.data);

export const createTasteTrait = (payload: { type: string; name: string }) =>
  http.post('/admin/taste-traits', payload).then((r) => r.data);
