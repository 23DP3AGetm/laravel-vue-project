import { reactive, ref } from 'vue';
import { forgotPassword } from '../../services/authService';
import { useAuthErrors } from './useAuthErrors';

export function useForgotPassword() {
  const form = reactive({
    email: '',
  });
  const { errors, clearErrors } = useAuthErrors();
  const error = ref('');
  const success = ref('');
  const loading = ref(false);

  async function sendResetLink() {
    error.value = '';
    success.value = '';
    clearErrors();
    loading.value = true;

    try {
      await forgotPassword(form);
      success.value = 'Paroles atjaunošanas saite nosūtīta uz e-pastu.';
    } catch (e) {
      Object.assign(errors, e.errors || {});
      error.value = e.message || 'Neizdevās nosūtīt paroles atjaunošanas saiti.';
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
    sendResetLink,
  };
}
