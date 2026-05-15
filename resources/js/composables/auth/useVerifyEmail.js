import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { getUser, resendVerificationEmail } from '../../services/authService';

export function useVerifyEmail() {
  const user = ref(null);
  const message = ref('');
  const error = ref('');
  const loading = ref(false);
  const countdown = ref(0);
  const isResendDisabled = ref(false);
  let countdownTimer = null;

  const resendButtonText = computed(() => {
    if (countdown.value > 0) {
      return `Nosūtīt vēlreiz pēc ${countdown.value}s`;
    }

    return 'Nosūtīt vēlreiz';
  });

  function stopCountdown() {
    if (countdownTimer) {
      clearInterval(countdownTimer);
      countdownTimer = null;
    }
  }

  function startCountdown(seconds = 60) {
    stopCountdown();
    countdown.value = seconds;
    isResendDisabled.value = true;

    countdownTimer = setInterval(() => {
      countdown.value -= 1;

      if (countdown.value <= 0) {
        countdown.value = 0;
        isResendDisabled.value = false;
        stopCountdown();
      }
    }, 1000);
  }

  onMounted(async () => {
    try {
      user.value = await getUser();
    } catch (e) {
      error.value = 'Neizdevās ielādēt lietotāja datus.';
    }
  });

  onBeforeUnmount(() => {
    stopCountdown();
  });

  async function resendEmail() {
    if (isResendDisabled.value || loading.value) {
      return;
    }

    message.value = '';
    error.value = '';
    loading.value = true;

    try {
      await resendVerificationEmail();
      message.value = 'Apstiprinājuma vēstule nosūtīta.';
      startCountdown();
    } catch (e) {
      error.value = e.message || 'Neizdevās nosūtīt vēstuli.';
    } finally {
      loading.value = false;
    }
  }

  return {
    user,
    message,
    error,
    loading,
    countdown,
    isResendDisabled,
    resendButtonText,
    resendEmail,
  };
}
