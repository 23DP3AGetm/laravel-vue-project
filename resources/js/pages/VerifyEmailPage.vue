<script setup>
import { computed } from 'vue';
import AuthButton from '../components/auth/AuthButton.vue';
import AuthCard from '../components/auth/AuthCard.vue';
import AuthInput from '../components/auth/AuthInput.vue';
import { useVerifyEmail } from '../composables/auth/useVerifyEmail';

const {
  user,
  message,
  error,
  loading,
  isResendDisabled,
  resendButtonText,
  resendEmail,
} = useVerifyEmail();

const email = computed(() => user.value?.email || '');
</script>

<template>
  <AuthCard>
    <p class="section-label">E-pasta apstiprināšana</p>
    <h1>Lūdzu apstiprini savu e-pastu</h1>

    <p class="auth-card__text">
      Mēs nosūtījām apstiprinājuma vēstuli. Atver e-pastu un nospied apstiprinājuma saiti,
      lai pabeigtu reģistrāciju.
    </p>

    <div class="auth-form">
      <label>
        E-pasts
        <AuthInput
          :model-value="email"
          icon="bx bx-envelope"
          type="email"
          disabled
          readonly
          autocomplete="email"
        />
      </label>
    </div>

    <p v-if="message" class="auth-success">{{ message }}</p>
    <p v-if="error" class="auth-error">{{ error }}</p>

    <AuthButton
      type="button"
      :loading="loading"
      :disabled="isResendDisabled"
      loading-text="Nosūta..."
      @click="resendEmail"
    >
      {{ resendButtonText }}
    </AuthButton>
  </AuthCard>
</template>
