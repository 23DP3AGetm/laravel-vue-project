<script setup>
import AuthButton from '../components/auth/AuthButton.vue';
import AuthCard from '../components/auth/AuthCard.vue';
import AuthInput from '../components/auth/AuthInput.vue';
import { useLogin } from '../composables/auth/useLogin';

const {
  form,
  errors,
  error,
  loading,
  showPassword,
  login,
} = useLogin();
</script>

<template>
  <AuthCard>
    <p class="section-label">Ieiet</p>
    <h1>Pieslēdzies kontam</h1>
    <p class="auth-card__text">
      Ievadi savu e-pastu un paroli, lai turpinātu.
    </p>

    <form class="auth-form" @submit.prevent="login">
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
        Parole
        <AuthInput
          v-model="form.password"
          icon="bx bx-lock-alt"
          :type="showPassword ? 'text' : 'password'"
          required
          autocomplete="current-password"
          show-toggle
          :show-password="showPassword"
          @toggle-password="showPassword = !showPassword"
        />
        <span v-if="errors.password" class="auth-field-error">{{ errors.password[0] }}</span>
      </label>

      <p v-if="error" class="auth-error">{{ error }}</p>

      <AuthButton type="submit" :loading="loading" loading-text="Lūdzu, uzgaidi...">
        Ieiet
      </AuthButton>
    </form>

    <div class="login-links">
      <RouterLink class="login-links__forgot" to="/forgot-password">
        Aizmirsi paroli?
      </RouterLink>

      <p>
        Nav konta?
        <RouterLink class="login-links__register" to="/register">Reģistrēties</RouterLink>
      </p>
    </div>
  </AuthCard>
</template>
