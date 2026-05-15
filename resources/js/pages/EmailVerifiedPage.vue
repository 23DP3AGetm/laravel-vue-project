<script setup>
import AuthCard from '../components/auth/AuthCard.vue';
</script>

<template>
  <AuthCard>
    <p class="section-label">E-pasts apstiprināts</p>
    <h1>Pasts veiksmīgi apstiprināts</h1>
    <p class="auth-card__text">
      Tagad jūs varat turpināt lietot SportaHub bez ierobežojumiem.
    </p>

    <RouterLink class="btn" to="/">
      Pāriet uz sākumu
    </RouterLink>
  </AuthCard>
</template>
