import { reactive } from 'vue';
import { getToken, removeToken, saveToken } from './services/api';
import { getUser } from './services/authService';

export const auth = reactive({
  token: getToken(),
  user: null,
});

export function setAuth(token, user) {
  auth.token = token;
  auth.user = user;
  saveToken(token);
  window.dispatchEvent(new Event('auth-changed'));
}

export function updateAuthUser(user) {
  auth.user = user;
  window.dispatchEvent(new Event('auth-changed'));
}

export function clearAuth() {
  auth.token = null;
  auth.user = null;
  removeToken();
  window.dispatchEvent(new Event('auth-changed'));
}

export async function loadUser() {
  if (!auth.token) {
    return null;
  }

  try {
    auth.user = await getUser();
    return auth.user;
  } catch (e) {
    clearAuth();
    return null;
  }
}
