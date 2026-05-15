<script setup>
import Button from '../ui/Button.vue';

const props = defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
  loadingText: {
    type: String,
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});
</script>

<template>
  <Button :loading="props.loading" :disabled="props.disabled">
    <slot />
  </Button>
</template>
