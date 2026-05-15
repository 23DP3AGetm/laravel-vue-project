import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { setAuth } from '../../auth';
import { getUser, login as loginRequest } from '../../services/authService';
import { useAuthErrors } from './useAuthErrors';

export function useLogin() {
  const route = useRoute();
  const router = useRouter();
  const form = reactive({
    email: '',
    password: '',
  });
  const { errors, clearErrors } = useAuthErrors();
  const error = ref('');
  const loading = ref(false);
  const showPassword = ref(false);

  async function login() {
    error.value = '';
    clearErrors();
    loading.value = true;

    try {
      const data = await loginRequest(form);
      setAuth(data.token, data.user);

      const user = await getUser();

      if (user.email_verified_at === null) {
        router.push('/verify-email');
        return;
      }

      router.push(String(route.query.redirect || '/'));
    } catch (e) {
      Object.assign(errors, e.errors || {});
      error.value = e.message || 'Nepareizs e-pasts vai parole.';
    } finally {
      loading.value = false;
    }
  }

  return {
    form,
    errors,
    error,
    loading,
    showPassword,
    login,
  };
}
