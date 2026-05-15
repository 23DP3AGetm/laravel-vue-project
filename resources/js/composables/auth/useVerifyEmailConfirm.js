import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { confirmEmailVerification } from '../../services/authService';

export function useVerifyEmailConfirm() {
  const route = useRoute();
  const router = useRouter();
  const loading = ref(false);
  const error = ref('');
  const success = ref('');

  const verificationQuery = computed(() => ({
    token: String(route.query.token || ''),
    id: String(route.query.id || ''),
    hash: String(route.query.hash || ''),
    expires: String(route.query.expires || ''),
  }));

  const hasVerificationQuery = computed(() => {
    const query = verificationQuery.value;

    return Boolean(query.token && query.id && query.hash && query.expires);
  });

  async function confirmEmail() {
    if (!hasVerificationQuery.value) {
      return;
    }

    loading.value = true;
    error.value = '';
    success.value = '';

    try {
      await confirmEmailVerification(verificationQuery.value);
      success.value = 'E-pasts ir veiksmīgi apstiprināts.';
      router.push('/email-verified');
    } catch (e) {
      error.value = e.message || 'Neizdevās apstiprināt e-pastu. Saite var būt nederīga vai beigusies.';
    } finally {
      loading.value = false;
    }
  }

  onMounted(confirmEmail);

  return {
    loading,
    error,
    success,
    hasVerificationQuery,
    confirmEmail,
  };
}
