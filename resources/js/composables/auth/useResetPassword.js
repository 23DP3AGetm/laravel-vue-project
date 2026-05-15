import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { resetPassword } from '../../services/authService';
import { useAuthErrors } from './useAuthErrors';

export function useResetPassword() {
  const route = useRoute();
  const router = useRouter();
  const form = reactive({
    token: route.query.token || '',
    email: route.query.email || '',
    password: '',
    password_confirmation: '',
  });
  const { errors, clearErrors } = useAuthErrors();
  const error = ref('');
  const success = ref('');
  const loading = ref(false);
  const showPassword = ref(false);
  const showPasswordConfirmation = ref(false);

  async function submitResetPassword() {
    error.value = '';
    success.value = '';
    clearErrors();

    if (form.password !== form.password_confirmation) {
      errors.password_confirmation = ['Paroles nesakrīt.'];
      return;
    }

    loading.value = true;

    try {
      await resetPassword(form);
      success.value = 'Parole veiksmīgi atjaunota. Tagad vari pieslēgties.';

      setTimeout(() => {
        router.push('/login');
      }, 900);
    } catch (e) {
      Object.assign(errors, e.errors || {});
      error.value = e.message || 'Neizdevās atjaunot paroli.';
    } finally {
      loading.value = false;
    }
  }

  return {
    form,
    errors,
    error,
    success,
    loading,
    showPassword,
    showPasswordConfirmation,
    submitResetPassword,
  };
}
