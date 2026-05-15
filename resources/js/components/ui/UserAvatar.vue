<script setup>
import { computed } from 'vue';

const props = defineProps({
  src: {
    type: String,
    default: '',
  },
  name: {
    type: String,
    default: '',
  },
  size: {
    type: [Number, String],
    default: 40,
  },
  alt: {
    type: String,
    default: 'User avatar',
  },
});

const avatarInitial = computed(() => {
  return (props.name || '?').trim().charAt(0).toUpperCase() || '?';
});

const avatarStyle = computed(() => {
  const size = typeof props.size === 'number' ? `${props.size}px` : props.size;

  return {
    '--avatar-size': size,
  };
});
</script>

<template>
  <span
    class="user-avatar"
    :class="{ 'user-avatar--fallback': !src }"
    :style="avatarStyle"
  >
    <img
      v-if="src"
      :src="src"
      class="user-avatar__image"
      :alt="alt"
    >
    <span v-else class="user-avatar__fallback" aria-hidden="true">
      {{ avatarInitial }}
    </span>
  </span>
</template>
