import { onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { clearAuth, updateAuthUser } from '../../auth';
import {
  becomeOrganizator,
  deleteUser,
  getUser,
  updateProfileName,
  updateProfilePassword,
  uploadUserAvatar,
} from '../../services/authService';

export function useProfile() {
  const maxAvatarSizeBytes = 2 * 1024 * 1024;
  const router = useRouter();
  const user = ref(null);
  const error = ref('');
  const loading = ref(true);
  const deleting = ref(false);
  const showDeleteConfirm = ref(false);
  const avatarLoading = ref(false);
  const avatarMessage = ref('');
  const avatarError = ref('');
  const selectedFile = ref(null);
  const preview = ref('');
  const fileInput = ref(null);
  const showNameEdit = ref(false);
  const nameLoading = ref(false);
  const nameMessage = ref('');
  const nameError = ref('');
  const nameErrors = ref({});
  const showPasswordForm = ref(false);
  const passwordLoading = ref(false);
  const passwordMessage = ref('');
  const passwordError = ref('');
  const passwordErrors = ref({});
  const showBecomeOrganizator = ref(false);
  const organizatorLoading = ref(false);
  const organizatorMessage = ref('');
  const organizatorError = ref('');
  const organizatorErrors = ref({});
  const showPassword = reactive({
    current: false,
    new: false,
    confirm: false,
    organizator: false,
  });
  const passwordForm = reactive({
    current_password: '',
    password: '',
    password_confirmation: '',
  });
  const nameForm = reactive({
    name: '',
  });
  const organizatorForm = reactive({
    current_password: '',
  });

  onMounted(async () => {
    await loadProfile();
  });

  onBeforeUnmount(() => {
    resetAvatarSelection();
  });

  async function loadProfile() {
    try {
      user.value = await getUser();
      updateAuthUser(user.value);
      nameForm.name = user.value?.name || '';
    } catch (e) {
      error.value = e.message || 'Neizdevās ielādēt profilu.';
    } finally {
      loading.value = false;
    }
  }

  function formatDate(date) {
    if (!date) {
      return '-';
    }

    return new Date(date).toLocaleDateString('lv-LV', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  }

  function toggleDelete() {
    showDeleteConfirm.value = !showDeleteConfirm.value;
  }

  function resetNameForm() {
    nameForm.name = user.value?.name || '';
    nameErrors.value = {};
    nameError.value = '';
  }

  function toggleNameEdit() {
    showNameEdit.value = !showNameEdit.value;

    if (!showNameEdit.value) {
      resetNameForm();
    }
  }

  function closeNameEdit() {
    showNameEdit.value = false;
    resetNameForm();
  }

  async function submitName() {
    nameLoading.value = true;
    nameMessage.value = '';
    nameError.value = '';
    nameErrors.value = {};

    try {
      const response = await updateProfileName(nameForm);
      user.value = response.user || { ...user.value, name: nameForm.name };
      updateAuthUser(user.value);
      nameMessage.value = response.message || 'Lietotājvārds veiksmīgi atjaunināts.';
      showNameEdit.value = false;
      resetNameForm();
    } catch (e) {
      nameErrors.value = e.errors || {};
      nameError.value = e.message || 'Neizdevās atjaunināt lietotājvārdu.';
    } finally {
      nameLoading.value = false;
    }
  }

  function handleAvatarFile(event) {
    const file = event.target.files?.[0] || null;

    resetAvatarSelection();
    avatarError.value = '';
    avatarMessage.value = '';

    if (file && file.size > maxAvatarSizeBytes) {
      avatarError.value = 'Attela izmērs nedrīkst pārsniegt 2 MB.';

      if (fileInput.value) {
        fileInput.value.value = '';
      }

      return;
    }

    selectedFile.value = file;

    if (file) {
      preview.value = URL.createObjectURL(file);
    }
  }

  function triggerFileInput() {
    fileInput.value?.click();
  }

  function resetAvatarSelection() {
    if (preview.value) {
      URL.revokeObjectURL(preview.value);
      preview.value = '';
    }

    selectedFile.value = null;

    if (fileInput.value) {
      fileInput.value.value = '';
    }
  }

  async function submitAvatar() {
    if (!selectedFile.value) {
      avatarError.value = 'Izvēlies attēlu.';
      return;
    }

    avatarLoading.value = true;
    avatarError.value = '';
    avatarMessage.value = '';

    const formData = new FormData();
    formData.append('avatar', selectedFile.value);

    try {
      const response = await uploadUserAvatar(formData);
      user.value = response.user || { ...user.value, avatar: response.avatar };
      updateAuthUser(user.value);
      avatarMessage.value = 'Avatars atjaunināts';
      resetAvatarSelection();
    } catch (e) {
      avatarError.value = e.message || 'Neizdevās augšupielādēt avataru.';
    } finally {
      avatarLoading.value = false;
    }
  }

  function resetPasswordForm() {
    passwordForm.current_password = '';
    passwordForm.password = '';
    passwordForm.password_confirmation = '';
    showPassword.current = false;
    showPassword.new = false;
    showPassword.confirm = false;
    passwordErrors.value = {};
    passwordError.value = '';
  }

  function resetOrganizatorForm() {
    organizatorForm.current_password = '';
    showPassword.organizator = false;
    organizatorErrors.value = {};
    organizatorError.value = '';
  }

  function togglePasswordForm() {
    showPasswordForm.value = !showPasswordForm.value;

    if (!showPasswordForm.value) {
      resetPasswordForm();
    }
  }

  function closePasswordForm() {
    showPasswordForm.value = false;
    resetPasswordForm();
  }

  function toggleBecomeOrganizator() {
    showBecomeOrganizator.value = !showBecomeOrganizator.value;

    if (!showBecomeOrganizator.value) {
      resetOrganizatorForm();
    }
  }

  function closeBecomeOrganizator() {
    showBecomeOrganizator.value = false;
    resetOrganizatorForm();
  }

  function togglePasswordVisibility(field) {
    showPassword[field] = !showPassword[field];
  }

  async function submitPassword() {
    passwordLoading.value = true;
    passwordMessage.value = '';
    passwordError.value = '';
    passwordErrors.value = {};

    try {
      const response = await updateProfilePassword(passwordForm);
      passwordMessage.value = response.message || 'Parole veiksmīgi nomainīta.';
      showPasswordForm.value = false;
      resetPasswordForm();
    } catch (e) {
      passwordErrors.value = e.errors || {};
      passwordError.value = e.message || 'Neizdevās nomainīt paroli.';
    } finally {
      passwordLoading.value = false;
    }
  }

  async function submitBecomeOrganizator() {
    organizatorLoading.value = true;
    organizatorMessage.value = '';
    organizatorError.value = '';
    organizatorErrors.value = {};

    try {
      const response = await becomeOrganizator(organizatorForm);
      user.value = response.user || { ...user.value, role: 'organizator' };
      updateAuthUser(user.value);
      organizatorMessage.value = response.message || 'Tu veiksmīgi kļuvi par organizatoru.';
      showBecomeOrganizator.value = false;
      resetOrganizatorForm();
    } catch (e) {
      organizatorErrors.value = e.errors || {};
      organizatorError.value = e.message || 'Neizdevās aktivizēt organizatora profilu.';
    } finally {
      organizatorLoading.value = false;
    }
  }

  async function deleteAccount() {
    deleting.value = true;
    error.value = '';

    try {
      const response = await deleteUser();
      clearAuth();
      router.push(response.redirect || '/');
    } catch (e) {
      error.value = e.message || 'Neizdevās dzēst kontu.';
    } finally {
      deleting.value = false;
    }
  }

  return {
    user,
    error,
    loading,
    deleting,
    avatarLoading,
    avatarMessage,
    avatarError,
    selectedFile,
    preview,
    fileInput,
    showDeleteConfirm,
    showNameEdit,
    nameLoading,
    nameMessage,
    nameError,
    nameErrors,
    nameForm,
    showPasswordForm,
    passwordLoading,
    passwordMessage,
    passwordError,
    passwordErrors,
    showBecomeOrganizator,
    organizatorLoading,
    organizatorMessage,
    organizatorError,
    organizatorErrors,
    showPassword,
    passwordForm,
    organizatorForm,
    formatDate,
    handleAvatarFile,
    triggerFileInput,
    toggleDelete,
    toggleNameEdit,
    closeNameEdit,
    togglePasswordForm,
    closePasswordForm,
    toggleBecomeOrganizator,
    closeBecomeOrganizator,
    submitName,
    submitAvatar,
    togglePasswordVisibility,
    submitPassword,
    submitBecomeOrganizator,
    deleteAccount,
  };
}
