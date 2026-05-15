<script setup>
const props = defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});
</script>

<template>
  <button
    class="btn relative inline-flex items-center justify-center"
    :disabled="props.loading || props.disabled"
    :style="props.loading || props.disabled ? { cursor: 'not-allowed', opacity: '0.7' } : undefined"
  >
    <span
      class="inline-flex items-center justify-center"
      :class="props.loading ? 'opacity-0' : 'opacity-100'"
      :style="props.loading ? { opacity: 0 } : { opacity: 1 }"
    >
      <slot />
    </span>

    <span
      v-show="props.loading"
      class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center"
      aria-hidden="true"
    >
      <span
        class="h-6 w-6 animate-spin rounded-full border-white border-t-transparent opacity-90"
        style="display: inline-block; width: 24px; height: 24px; border-width: 3px; border-style: solid; border-color: rgba(255, 255, 255, 0.92); border-top-color: transparent; border-radius: 9999px;"
      ></span>
    </span>
  </button>
</template>
