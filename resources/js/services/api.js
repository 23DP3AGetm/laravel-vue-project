const API_URL = 'http://127.0.0.1:8000/api';
const TOKEN_NAME = 'auth_token';

export function getToken() {
  return localStorage.getItem(TOKEN_NAME);
}

export function saveToken(token) {
  localStorage.setItem(TOKEN_NAME, token);
}

export function removeToken() {
  localStorage.removeItem(TOKEN_NAME);
}

export async function apiRequest(path, options = {}) {
  const token = getToken();
  const isFormData = options.body instanceof FormData;

  const headers = {
    Accept: 'application/json',
    ...options.headers,
  };

  if (!isFormData && !headers['Content-Type']) {
    headers['Content-Type'] = 'application/json';
  }

  if (token) {
    headers.Authorization = `Bearer ${token}`;
  }

  const response = await fetch(`${API_URL}${path}`, {
    ...options,
    headers,
  });

  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    const error = new Error(data.message || 'Request failed');
    error.status = response.status;
    error.errors = data.errors || {};
    throw error;
  }

  return data;
}
