<script setup>
import AuthButton from '../components/auth/AuthButton.vue';
import AuthCard from '../components/auth/AuthCard.vue';
import AuthInput from '../components/auth/AuthInput.vue';
import { useRegister } from '../composables/auth/useRegister';

const {
  form,
  errors,
  error,
  loading,
  showPassword,
  showPasswordConfirmation,
  handlePasswordInput,
  handlePasswordBlur,
  handleRegister,
} = useRegister();
</script>

<template>
  <AuthCard>
    <p class="section-label">Reģistrācija</p>
    <h1>Izveido kontu</h1>
    <p class="auth-card__text">
      Aizpildi formu, lai izveidotu kontu
    </p>

    <form class="auth-form" @submit.prevent="handleRegister">
      <label>
        Lietotājvārds
        <AuthInput
          v-model="form.name"
          icon="bx bx-user"
          type="text"
          required
          autocomplete="name"
        />
        <span v-if="errors.name" class="auth-field-error">{{ errors.name[0] }}</span>
      </label>

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
          autocomplete="new-password"
          show-toggle
          :show-password="showPassword"
          @input="handlePasswordInput"
          @focusout="handlePasswordBlur"
          @toggle-password="showPassword = !showPassword"
        />
        <span v-if="errors.password" class="auth-field-error">{{ errors.password[0] }}</span>
      </label>

      <label>
        Atkārtot paroli
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

      <p v-if="error" class="auth-error">{{ error }}</p>

      <AuthButton type="submit" :loading="loading" loading-text="Lūdzu, uzgaidi...">
        Reģistrēties
      </AuthButton>
    </form>

    <p class="auth-card__bottom">
      Jau ir konts?
      <RouterLink to="/login">Ieiet</RouterLink>
    </p>
  </AuthCard>
</template>
