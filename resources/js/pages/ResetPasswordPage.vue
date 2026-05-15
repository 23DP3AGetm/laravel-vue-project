<script setup>
import AuthButton from '../components/auth/AuthButton.vue';
import AuthCard from '../components/auth/AuthCard.vue';
import AuthInput from '../components/auth/AuthInput.vue';
import { useResetPassword } from '../composables/auth/useResetPassword';

const {
  form,
  errors,
  error,
  success,
  loading,
  showPassword,
  showPasswordConfirmation,
  submitResetPassword,
} = useResetPassword();
</script>

<template>
  <AuthCard>
    <p class="section-label">Jauna parole</p>
    <h1>Atjauno paroli</h1>
    <p class="auth-card__text">
      Ievadi jaunu paroli savam SportaHub kontam.
    </p>

    <form class="auth-form" @submit.prevent="submitResetPassword">
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

      <label>
        Jauna parole
        <AuthInput
          v-model="form.password"
          icon="bx bx-lock-alt"
          :type="showPassword ? 'text' : 'password'"
          required
          autocomplete="new-password"
          show-toggle
          :show-password="showPassword"
          @toggle-password="showPassword = !showPassword"
        />
        <span v-if="errors.password" class="auth-field-error">{{ errors.password[0] }}</span>
      </label>

      <label>
        Atkārto paroli
        <AuthInput
          v-model="form.password_confirmation"
          icon="bx bx-lock-alt"
          :type="showPasswordConfirmation ? 'text' : 'password'"
          required
          autocomplete="new-password"
          show-toggle
          :show-password="showPasswordConfirmation"
          @toggle-password="showPasswordConfirmation = !showPasswordConfirmation"
        />
        <span v-if="errors.password_confirmation" class="auth-field-error">
          {{ errors.password_confirmation[0] }}
        </span>
      </label>

      <p v-if="success" class="auth-success">{{ success }}</p>
      <p v-if="error" class="auth-error">{{ error }}</p>

      <AuthButton
        type="submit"
        :loading="loading"
        :disabled="!form.token"
        loading-text="Atjauno..."
      >
        Atjaunot paroli
      </AuthButton>
    </form>
  </AuthCard>
</template>
