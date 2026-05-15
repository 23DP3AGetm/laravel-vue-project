<script setup>
import AuthButton from '../components/auth/AuthButton.vue';
import AuthCard from '../components/auth/AuthCard.vue';
import VerifyEmailPage from './VerifyEmailPage.vue';
import { useVerifyEmailConfirm } from '../composables/auth/useVerifyEmailConfirm';

const {
  loading,
  error,
  success,
  hasVerificationQuery,
  confirmEmail,
} = useVerifyEmailConfirm();
</script>

<template>
  <VerifyEmailPage v-if="!hasVerificationQuery" />

  <AuthCard v-else>
    <p class="section-label">E-pasta apstiprināšana</p>
    <h1>Apstiprinām e-pastu</h1>
    <p class="auth-card__text">
      Pārbaudām apstiprinājuma saiti. Tas aizņems tikai mirkli.
    </p>

    <p v-if="success" class="auth-success">{{ success }}</p>
    <p v-if="error" class="auth-error">{{ error }}</p>

    <AuthButton
      v-if="error"
      type="button"
      :loading="loading"
      loading-text="Pārbauda..."
      @click="confirmEmail"
    >
      Mēģināt vēlreiz
    </AuthButton>

    <p v-else class="auth-card__text">
      {{ loading ? 'Apstiprināšana...' : 'E-pasts apstiprināts.' }}
    </p>
  </AuthCard>
</template>
