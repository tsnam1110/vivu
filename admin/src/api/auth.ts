import { http, setToken } from './http';

export interface Admin {
  id: number;
  name: string;
  email: string;
  is_active: boolean;
  roles?: string[];
}

export async function login(email: string, password: string) {
  const { data } = await http.post<{ data: { token: string; admin: Admin } }>('/admin/login', {
    email,
    password,
  });
  setToken(data.data.token);
  return data.data;
}

export async function logout() {
  try {
    await http.post('/admin/logout');
  } finally {
    setToken(null);
  }
}

export async function me() {
  const { data } = await http.get<{ data: Admin }>('/admin/me');
  return data.data;
}
