<script setup>
const model = defineModel({
  type: [String, Number],
  default: '',
});

defineProps({
  id: {
    type: String,
    default: '',
  },
  icon: {
    type: String,
    required: true,
  },
  type: {
    type: String,
    default: 'text',
  },
  required: {
    type: Boolean,
    default: false,
  },
  autocomplete: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: '',
  },
  showToggle: {
    type: Boolean,
    default: false,
  },
  showPassword: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  readonly: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['toggle-password']);
</script>

<template>
  <div class="auth-input">
    <span class="icon-left" aria-hidden="true">
      <i :class="icon" class="auth-input__icon"></i>
    </span>
    <input
      :id="id || undefined"
      v-model="model"
      class="auth-input__field"
      :class="showToggle ? 'pr-10' : 'pr-4'"
      :type="type"
      :required="required"
      :placeholder="placeholder || undefined"
      :autocomplete="autocomplete || undefined"
      :disabled="disabled"
      :readonly="readonly"
    >
    <button
      v-if="showToggle"
      type="button"
      class="icon-right auth-input__toggle"
      @click="emit('toggle-password')"
    >
      <i :class="showPassword ? 'bx bx-hide' : 'bx bx-show'"></i>
    </button>
  </div>
</template>
