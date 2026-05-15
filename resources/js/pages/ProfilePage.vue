<script setup>
import AuthCard from '../components/auth/AuthCard.vue';
import AuthInput from '../components/auth/AuthInput.vue';
import UserAvatar from '../components/ui/UserAvatar.vue';
import { useProfile } from '../composables/auth/useProfile';

const {
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
  triggerFileInput,
  handleAvatarFile,
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
} = useProfile();
</script>

<template>
  <AuthCard class="profile-shell">
    <div class="profile-container">
      <div class="profile-card">
        <p class="section-label">Profils</p>
        <h1>Mans profils</h1>

        <p v-if="loading" class="auth-card__text">
          Ielādē profilu...
        </p>

        <p v-if="error" class="auth-error">{{ error }}</p>

        <div v-if="user" class="profile-details profile-list">
          <div class="profile-details__item profile-avatar-block avatar-row">
            <div class="left">
              <span class="profile-label">Avatars</span>
            </div>

            <div class="center">
              <div class="avatar-wrapper">
                <UserAvatar
                  class="profile-avatar"
                  :src="preview || (user.avatar ? `/storage/${user.avatar}` : '')"
                  :name="user.name"
                  :size="60"
                />
              </div>

              <input
                ref="fileInput"
                type="file"
                accept="image/*"
                hidden
                @change="handleAvatarFile"
              >
            </div>

            <div class="right avatar-actions">
              <div class="avatar-upload">
                <button
                  type="button"
                  class="profile-btn"
                  @click="triggerFileInput"
                >
                  Izveleties
                </button>

                <button
                  v-if="selectedFile"
                  type="button"
                  class="profile-btn"
                  :disabled="avatarLoading"
                  @click="submitAvatar"
                >
                  {{ avatarLoading ? 'Saglabat...' : 'Saglabat' }}
                </button>
              </div>

              <p v-if="avatarError" class="auth-error">{{ avatarError }}</p>
              <p v-if="avatarMessage" class="avatar-success">{{ avatarMessage }}</p>
            </div>
          </div>

          <div class="profile-details__item password-block">
            <div class="profile-details__row">
              <span class="profile-label">Lietotājvārds</span>

              <div class="profile-password-summary">
                <strong class="profile-value">{{ user.name }}</strong>

                <button
                  type="button"
                  class="profile-btn profile-edit-btn"
                  @click.stop="toggleNameEdit"
                >
                  Rediģēt
                </button>
              </div>
            </div>

            <transition name="expand">
              <div v-if="showNameEdit" class="inner profile-expand">
                <div class="profile-password-form">
                  <div class="input-wrapper">
                    <span class="icon-left" aria-hidden="true">
                      <i class="bx bx-user auth-input__icon"></i>
                    </span>
                    <input
                      v-model="nameForm.name"
                      type="text"
                      class="profile-inline-input"
                      placeholder="Jauns lietotājvārds"
                      autocomplete="name"
                    >
                  </div>

                  <span v-if="nameErrors.name" class="auth-field-error">{{ nameErrors.name[0] }}</span>
                  <p v-if="nameError" class="auth-error">{{ nameError }}</p>
                  <p v-if="nameMessage" class="auth-success">{{ nameMessage }}</p>

                  <div class="actions profile-actions">
                    <button
                      type="button"
                      class="cancel profile-cancel"
                      :disabled="nameLoading"
                      @click.stop="closeNameEdit"
                    >
                      Atcelt
                    </button>

                    <button
                      type="button"
                      class="profile-btn profile-password-btn"
                      :disabled="nameLoading"
                      @click.stop="submitName"
                    >
                      {{ nameLoading ? 'Saglabā...' : 'Atjaunināt vārdu' }}
                    </button>
                  </div>
                </div>
              </div>
            </transition>
          </div>

          <div class="profile-details__item">
            <span class="profile-label">E-pasts</span>
            <strong class="profile-value">{{ user.email }}</strong>
          </div>

          <div class="profile-details__item password-block">
            <div class="profile-details__row">
              <span class="profile-label">Parole</span>

              <div class="profile-password-summary">
                <strong class="profile-value profile-password-dots">••••••••</strong>

                <button
                  type="button"
                  class="profile-btn profile-edit-btn"
                  @click.stop="togglePasswordForm"
                >
                  Rediģēt
                </button>
              </div>
            </div>

            <transition name="expand">
              <div v-if="showPasswordForm" class="inner profile-password-form">
                <AuthInput
                  v-model="passwordForm.current_password"
                  icon="bx bx-lock-alt"
                  :type="showPassword.current ? 'text' : 'password'"
                  placeholder="Pašreizējā parole"
                  autocomplete="current-password"
                  show-toggle
                  :show-password="showPassword.current"
                  @toggle-password="togglePasswordVisibility('current')"
                />
                <span v-if="passwordErrors.current_password" class="auth-field-error">{{ passwordErrors.current_password[0] }}</span>

                <AuthInput
                  v-model="passwordForm.password"
                  icon="bx bx-lock-alt"
                  :type="showPassword.new ? 'text' : 'password'"
                  placeholder="Jaunā parole"
                  autocomplete="new-password"
                  show-toggle
                  :show-password="showPassword.new"
                  @toggle-password="togglePasswordVisibility('new')"
                />
                <span v-if="passwordErrors.password" class="auth-field-error">{{ passwordErrors.password[0] }}</span>

                <AuthInput
                  v-model="passwordForm.password_confirmation"
                  icon="bx bx-lock-alt"
                  :type="showPassword.confirm ? 'text' : 'password'"
                  placeholder="Apstiprini jauno paroli"
                  autocomplete="new-password"
                  show-toggle
                  :show-password="showPassword.confirm"
                  @toggle-password="togglePasswordVisibility('confirm')"
                />

                <p v-if="passwordError" class="auth-error">{{ passwordError }}</p>
                <p v-if="passwordMessage" class="auth-success">{{ passwordMessage }}</p>

                <div class="actions">
                  <button
                    type="button"
                    class="cancel"
                    :disabled="passwordLoading"
                    @click.stop="closePasswordForm"
                  >
                    Atcelt
                  </button>

                  <button
                    type="button"
                    class="profile-btn profile-password-btn"
                    :disabled="passwordLoading"
                    @click.stop="submitPassword"
                  >
                    {{ passwordLoading ? 'Saglabā...' : 'Mainīt paroli' }}
                  </button>
                </div>
              </div>
            </transition>
          </div>

          <div class="profile-details__item">
            <span class="profile-label">Reģistrācijas datums</span>
            <strong class="profile-value">{{ formatDate(user.created_at) }}</strong>
          </div>

          <div class="profile-details__item password-block">
            <div class="profile-details__row">
              <span class="profile-label">Organizatora profils</span>

              <div class="profile-password-summary">
                <strong class="profile-value profile-value--wide">
                  {{ user.role === 'organizator' ? 'Tu jau esi organizators' : 'Izveido organizatora profilu' }}
                </strong>

                <RouterLink
                  v-if="user.role === 'organizator'"
                  to="/organizator"
                  class="profile-btn profile-edit-btn"
                >
                  Atvērt paneli
                </RouterLink>

                <button
                  v-else
                  type="button"
                  class="profile-btn profile-edit-btn"
                  @click.stop="toggleBecomeOrganizator"
                >
                  Kļūt par organizatoru
                </button>
              </div>
            </div>

          

            <p v-if="organizatorMessage" class="auth-success">{{ organizatorMessage }}</p>

            <transition name="expand">
              <div v-if="showBecomeOrganizator && user.role !== 'organizator'" class="inner profile-password-form">
                <p class="profile-inline-note profile-inline-note--strong">
                  Apstiprini savu paroli, lai turpinātu
                </p>

                <AuthInput
                  v-model="organizatorForm.current_password"
                  icon="bx bx-lock-alt"
                  :type="showPassword.organizator ? 'text' : 'password'"
                  placeholder="Pašreizējā parole"
                  autocomplete="current-password"
                  show-toggle
                  :show-password="showPassword.organizator"
                  @toggle-password="togglePasswordVisibility('organizator')"
                />
                <span v-if="organizatorErrors.current_password" class="auth-field-error">{{ organizatorErrors.current_password[0] }}</span>
                <p v-if="organizatorError" class="auth-error">{{ organizatorError }}</p>

                <div class="actions">
                  <button
                    type="button"
                    class="cancel"
                    :disabled="organizatorLoading"
                    @click.stop="closeBecomeOrganizator"
                  >
                    Atcelt
                  </button>

                  <button
                    type="button"
                    class="profile-btn profile-password-btn"
                    :disabled="organizatorLoading"
                    @click.stop="submitBecomeOrganizator"
                  >
                    {{ organizatorLoading ? 'Apstiprina...' : 'Apstiprināt' }}
                  </button>
                </div>
              </div>
            </transition>
          </div>

          <div
            class="profile-details__item profile-details__item--danger profile-details__item--danger-accordion profile-delete"
            @click="toggleDelete"
          >
            <span class="profile-label">Dzēst kontu</span>
            <span class="profile-value" aria-hidden="true"></span>
            <button
              type="button"
              class="profile-btn delete-action-btn profile-delete-btn"
              @click.stop="toggleDelete"
            >
              Dzēst kontu
            </button>

            <transition name="expand">
              <div v-if="showDeleteConfirm" class="delete-inner">
                <p>
                  Neatgriezeniski dzēs kontu un visus datus.
                </p>

                <div class="actions">
                  <button
                    type="button"
                    class="cancel"
                    :disabled="deleting"
                    @click.stop="showDeleteConfirm = false"
                  >
                    Atcelt
                  </button>

                  <button
                    type="button"
                    class="btn-danger"
                    :disabled="deleting"
                    @click.stop="deleteAccount"
                  >
                    {{ deleting ? 'Dzēš...' : 'Dzēst neatgriezeniski' }}
                  </button>
                </div>
              </div>
            </transition>
          </div>
        </div>
      </div>
    </div>
  </AuthCard>
</template>
