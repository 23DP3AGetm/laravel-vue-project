import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { setAuth } from '../../auth';
import { getUser, register } from '../../services/authService';
import { useAuthErrors } from './useAuthErrors';

export function useRegister() {
  const passwordDigitsError = 'Parole nevar sastāvēt tikai no cipariem.';
  const route = useRoute();
  const router = useRouter();
  const form = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  });
  const { errors, clearErrors } = useAuthErrors();
  const error = ref('');
  const loading = ref(false);
  const showPassword = ref(false);
  const showPasswordConfirmation = ref(false);

  function isNumericOnlyPassword(value) {
    return /^\d+$/.test(value);
  }

  function validatePassword() {
    if (isNumericOnlyPassword(form.password)) {
      errors.password = [passwordDigitsError];
      return false;
    }

    delete errors.password;
    return true;
  }

  function handlePasswordInput() {
    validatePassword();
  }

  function handlePasswordBlur() {
    validatePassword();
  }

  async function handleRegister() {
    error.value = '';
    clearErrors();

    if (!validatePassword()) {
      return;
    }

    if (form.password !== form.password_confirmation) {
      errors.password_confirmation = ['Paroles nesakrīt.'];
      return;
    }

    loading.value = true;

    try {
      const data = await register(form);
      setAuth(data.token, data.user);

      const user = await getUser();

      if (user.email_verified_at === null) {
        router.push('/verify-email');
        return;
      }

      router.push(String(route.query.redirect || '/'));
    } catch (e) {
      Object.assign(errors, e.errors || {});
      error.value = e.message || 'Reģistrācija neizdevās.';
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
    showPasswordConfirmation,
    handlePasswordInput,
    handlePasswordBlur,
    handleRegister,
  };
}
