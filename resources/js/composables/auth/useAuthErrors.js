import { reactive } from 'vue';

export function useAuthErrors() {
  const errors = reactive({});

  function clearErrors() {
    Object.keys(errors).forEach((key) => {
      delete errors[key];
    });
  }

  return {
    errors,
    clearErrors,
  };
}
