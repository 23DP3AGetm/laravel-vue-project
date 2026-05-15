<script setup>
import AuthButton from '../components/auth/AuthButton.vue';
import AuthCard from '../components/auth/AuthCard.vue';
import AuthInput from '../components/auth/AuthInput.vue';
import { useForgotPassword } from '../composables/auth/useForgotPassword';

const {
  form,
  errors,
  error,
  success,
  loading,
  sendResetLink,
} = useForgotPassword();
</script>

<template>
  <AuthCard>
    <p class="section-label">Paroles atjaunošana</p>
    <h1>Aizmirsi paroli?</h1>
    <p class="auth-card__text">
      Ievadi savu e-pastu, un mēs nosūtīsim drošu paroles atjaunošanas saiti.
    </p>

    <form class="auth-form" @submit.prevent="sendResetLink">
      <label>
        E-pasts
        <AuthInput
          v-model="form.email"
          icon="bx bx-envelope"
          type="email"
          required
          autocomplete="email"
        />
        <span v-if="errors.email" class="auth-field-error">{{ errors.email[0] }}</span>
      </label>

      <p v-if="success" class="auth-success">{{ success }}</p>
      <p v-if="error" class="auth-error">{{ error }}</p>

      <AuthButton type="submit" :loading="loading" loading-text="Nosūta...">
        Nosūtīt atjaunošanas saiti
      </AuthButton>
    </form>

    <p class="auth-card__bottom">
      Atceries paroli?
      <RouterLink to="/login">Ieiet</RouterLink>
    </p>
  </AuthCard>
</template>
