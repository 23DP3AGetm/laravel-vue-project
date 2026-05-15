<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import AuthCard from '../components/auth/AuthCard.vue';
import { updateAuthUser } from '../auth';
import {
  createOrganizatorSchedule,
  createOrganizatorSection,
  deleteOrganizatorSchedule,
  deleteOrganizatorSection,
  getOrganizatorPanel,
  updateOrganizatorApplicationStatus,
  updateOrganizatorProfile,
  updateOrganizatorSection,
} from '../services/authService';

const loading = ref(true);
const error = ref('');
const toastMessage = ref('');
const user = ref(null);
const sections = ref([]);
const applicationSummary = ref(null);
const profileLoading = ref(false);
const sectionLoading = ref(false);
const deletingSectionId = ref(null);
const confirmDeleteSectionId = ref(null);
const showSectionModal = ref(false);
const sectionErrors = ref({});
const profileErrors = ref({});
const sectionImageFile = ref(null);
const sectionImagePreview = ref('');
const existingSectionImageUrl = ref('');
const scheduleErrors = reactive({});
const scheduleLoading = reactive({});
const applicationLoading = reactive({});
const scheduleForms = reactive({});
const activeApplicationFilter = ref('pending');

const daysOfWeek = [
  { value: 'monday', label: 'Pirmdiena' },
  { value: 'tuesday', label: 'Otrdiena' },
  { value: 'wednesday', label: 'Trešdiena' },
  { value: 'thursday', label: 'Ceturtdiena' },
  { value: 'friday', label: 'Piektdiena' },
  { value: 'saturday', label: 'Sestdiena' },
  { value: 'sunday', label: 'Svētdiena' },
];

let toastTimeoutId = null;

const profileForm = reactive({
  name: '',
  phone: '',
  email: '',
  location: '',
});

const sectionForm = reactive({
  id: null,
  title: '',
  sport_type: '',
  age_group: '',
  description: '',
  price: '',
  city: '',
  full_address: '',
  status: 'active',
});

function createEmptyScheduleForm() {
  return {
    day_of_week: 'monday',
    is_day_off: false,
    start_time: '',
    end_time: '',
  };
}

function ensureScheduleForm(sectionId) {
  if (!scheduleForms[sectionId]) {
    scheduleForms[sectionId] = createEmptyScheduleForm();
  }

  return scheduleForms[sectionId];
}

function showToast(text) {
  toastMessage.value = text;

  if (toastTimeoutId) {
    clearTimeout(toastTimeoutId);
  }

  toastTimeoutId = setTimeout(() => {
    toastMessage.value = '';
  }, 2200);
}

function applyUser(nextUser) {
  user.value = nextUser;
  profileForm.name = nextUser?.name || '';
  profileForm.phone = nextUser?.phone || '';
  profileForm.email = nextUser?.email || '';
  profileForm.location = nextUser?.location || '';

  if (nextUser) {
    updateAuthUser(nextUser);
  }
}

function formatPrice(price) {
  if (price === null || price === undefined || price === '') {
    return 'Pec vienosanas';
  }

  const numericPrice = Number(price);
  return Number.isNaN(numericPrice)
    ? `${price} EUR`
    : `${numericPrice.toFixed(2)} EUR`;
}

function hasMonthlyPrice(price) {
  return price !== null && price !== undefined && price !== '';
}

function formatStatus(status) {
  return status === 'hidden' ? 'Hidden' : 'Active';
}

function formatApplicationStatus(status) {
  if (status === 'approved') {
    return 'Apstiprināts';
  }

  if (status === 'rejected') {
    return 'Noraidīts';
  }

  return 'Gaida apstiprinājumu';
}

function formatDay(day) {
  return daysOfWeek.find((item) => item.value === day)?.label || day;
}

function formatDateTime(value) {
  if (!value) {
    return '';
  }

  return new Date(value).toLocaleString('lv-LV', {
    dateStyle: 'medium',
    timeStyle: 'short',
  });
}

function formatScheduleLabel(schedule) {
  if (schedule.is_day_off) {
    return 'Brīvdiena';
  }

  return `${schedule.start_time}-${schedule.end_time}`;
}

function hasApplications(section) {
  return Array.isArray(section.applications) && section.applications.length > 0;
}

function getApplicationFilters() {
  return [
    { value: 'all', label: 'Visi' },
    { value: 'pending', label: 'Gaida' },
    { value: 'approved', label: 'Apstiprināti' },
    { value: 'rejected', label: 'Noraidīti' },
  ];
}

function getFilteredApplications(section) {
  const applications = Array.isArray(section?.applications) ? section.applications : [];

  if (activeApplicationFilter.value === 'all') {
    return applications;
  }

  return applications.filter((application) => application.status === activeApplicationFilter.value);
}

const applicationFilters = computed(() => getApplicationFilters());

function toggleDayOff(sectionId) {
  const form = ensureScheduleForm(sectionId);

  if (form.is_day_off) {
    form.start_time = '';
    form.end_time = '';
  }
}

function normalizeTimeInput(value) {
  const digits = String(value || '').replace(/\D/g, '').slice(0, 4);

  if (digits.length <= 2) {
    return digits;
  }

  return `${digits.slice(0, 2)}:${digits.slice(2)}`;
}

function handleTimeInput(sectionId, field, event) {
  const form = ensureScheduleForm(sectionId);
  form[field] = normalizeTimeInput(event.target.value);
}

function formatTimeOnBlur(sectionId, field) {
  const form = ensureScheduleForm(sectionId);
  const value = String(form[field] || '').trim();

  if (!value) {
    form[field] = '';
    return;
  }

  const match = value.match(/^(\d{1,2})(?::?(\d{1,2}))?$/);

  if (!match) {
    return;
  }

  const hours = Number(match[1]);
  const minutes = Number(match[2] ?? 0);

  if (hours > 23 || minutes > 59) {
    return;
  }

  form[field] = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
}

async function loadDashboard({ silent = false } = {}) {
  if (!silent) {
    loading.value = true;
  }

  error.value = '';

  try {
    const response = await getOrganizatorPanel();
    applyUser(response.user || null);
    sections.value = response.sections || [];
    applicationSummary.value = response.application_summary || null;

    sections.value.forEach((section) => {
      ensureScheduleForm(section.id);
    });
  } catch (e) {
    error.value = e.message || 'Neizdevas ieladet organizatora paneli.';
  } finally {
    if (!silent) {
      loading.value = false;
    }
  }
}

function resetSectionForm() {
  sectionForm.id = null;
  sectionForm.title = '';
  sectionForm.sport_type = '';
  sectionForm.age_group = '';
  sectionForm.description = '';
  sectionForm.price = '';
  sectionForm.city = '';
  sectionForm.full_address = '';
  sectionForm.status = 'active';
  sectionErrors.value = {};
  sectionImageFile.value = null;
  existingSectionImageUrl.value = '';

  if (sectionImagePreview.value) {
    URL.revokeObjectURL(sectionImagePreview.value);
    sectionImagePreview.value = '';
  }
}

function openCreateSection() {
  resetSectionForm();
  showSectionModal.value = true;
}

function editSection(section) {
  resetSectionForm();
  sectionForm.id = section.id;
  sectionForm.title = section.title || '';
  sectionForm.sport_type = section.sport_type || '';
  sectionForm.age_group = section.age_group || '';
  sectionForm.description = section.description || '';
  sectionForm.price = section.price || '';
  sectionForm.city = section.address?.city || '';
  sectionForm.full_address = section.address?.full_address || section.location || '';
  sectionForm.status = section.status || 'active';
  existingSectionImageUrl.value = section.image_url || '';
  showSectionModal.value = true;
}

function closeSectionModal() {
  resetSectionForm();
  showSectionModal.value = false;
}

function handleSectionImage(event) {
  const file = event.target.files?.[0] || null;

  sectionImageFile.value = file;

  if (sectionImagePreview.value) {
    URL.revokeObjectURL(sectionImagePreview.value);
    sectionImagePreview.value = '';
  }

  if (file) {
    sectionImagePreview.value = URL.createObjectURL(file);
  }
}

async function submitProfile() {
  profileLoading.value = true;
  profileErrors.value = {};
  error.value = '';

  try {
    const response = await updateOrganizatorProfile({
      name: profileForm.name,
      phone: profileForm.phone,
      location: profileForm.location,
    });

    applyUser(response.user || null);
    showToast(response.message || 'Profils saglabāts.');
  } catch (e) {
    profileErrors.value = e.errors || {};
    error.value = e.message || 'Neizdevas saglabāt profilu.';
  } finally {
    profileLoading.value = false;
  }
}

async function submitSection() {
  sectionLoading.value = true;
  sectionErrors.value = {};
  error.value = '';

  const formData = new FormData();
  formData.append('title', sectionForm.title);
  formData.append('sport_type', sectionForm.sport_type);
  formData.append('age_group', sectionForm.age_group);
  formData.append('description', sectionForm.description);
  formData.append('city', sectionForm.city);
  formData.append('full_address', sectionForm.full_address);
  formData.append('location', sectionForm.full_address);
  formData.append('status', sectionForm.status);

  if (sectionForm.price !== '') {
    formData.append('price', sectionForm.price);
  }

  if (sectionImageFile.value) {
    formData.append('image', sectionImageFile.value);
  }

  try {
    const response = sectionForm.id
      ? await updateOrganizatorSection(sectionForm.id, formData)
      : await createOrganizatorSection(formData);

    await loadDashboard({ silent: true });
    closeSectionModal();
    showToast(response.message || 'Sekcija saglabāta.');
  } catch (e) {
    sectionErrors.value = e.errors || {};
    error.value = e.message || 'Neizdevas saglabāt sekciju.';
  } finally {
    sectionLoading.value = false;
  }
}

function toggleSectionDelete(sectionId) {
  confirmDeleteSectionId.value = confirmDeleteSectionId.value === sectionId ? null : sectionId;
}

function closeSectionDelete() {
  confirmDeleteSectionId.value = null;
}

async function removeSection(section) {
  deletingSectionId.value = section.id;
  error.value = '';

  try {
    const response = await deleteOrganizatorSection(section.id);

    if (sectionForm.id === section.id) {
      closeSectionModal();
    }

    sections.value = sections.value.filter((item) => item.id !== section.id);
    closeSectionDelete();
    showToast(response.message || 'Sekcija dzēsta.');
  } catch (e) {
    error.value = e.message || 'Neizdevas dzēst sekciju.';
  } finally {
    deletingSectionId.value = null;
  }
}

async function submitSchedule(sectionId) {
  scheduleLoading[sectionId] = true;
  scheduleErrors[sectionId] = {};

  try {
    const response = await createOrganizatorSchedule(sectionId, ensureScheduleForm(sectionId));
    scheduleForms[sectionId] = createEmptyScheduleForm();
    await loadDashboard({ silent: true });
    showToast(response.message || 'Grafiks pievienots.');
  } catch (e) {
    scheduleErrors[sectionId] = e.errors || {};
    error.value = e.message || 'Neizdevas pievienot grafiku.';
  } finally {
    scheduleLoading[sectionId] = false;
  }
}

async function removeSchedule(sectionId, scheduleId) {
  scheduleLoading[sectionId] = true;

  try {
    const response = await deleteOrganizatorSchedule(scheduleId);
    await loadDashboard({ silent: true });
    showToast(response.message || 'Grafika ieraksts dzēsts.');
  } catch (e) {
    error.value = e.message || 'Neizdevas dzēst grafiku.';
  } finally {
    scheduleLoading[sectionId] = false;
  }
}

async function updateApplication(sectionId, applicationId, status) {
  applicationLoading[applicationId] = true;

  try {
    const response = await updateOrganizatorApplicationStatus(applicationId, status);
    await loadDashboard({ silent: true });
    showToast(response.message || 'Pieteikums atjauninats.');
  } catch (e) {
    error.value = e.message || 'Neizdevas atjauninat pieteikumu.';
  } finally {
    applicationLoading[applicationId] = false;
  }
}

onMounted(async () => {
  await loadDashboard();
});

onBeforeUnmount(() => {
  if (toastTimeoutId) {
    clearTimeout(toastTimeoutId);
  }

  if (sectionImagePreview.value) {
    URL.revokeObjectURL(sectionImagePreview.value);
  }
});
</script>

<template>
  <AuthCard class="profile-shell admin-shell organizator-shell">
    <div class="profile-container">
      <div class="profile-card admin-card">
        <div v-if="toastMessage" class="admin-toast" role="status" aria-live="polite">
          {{ toastMessage }}
        </div>

        <div class="organizator-page-head">
          <div>
            <p class="section-label">Organizators</p>
            <h1>Organizatora panelis</h1>
            <p class="organizator-page-head__text">
              Pārvaldīt profilu, sekcijas, grafikus un lietotaju pieteikumus viena vieta.
            </p>
          </div>

          <button
            type="button"
            class="organizator-primary-btn organizator-primary-btn--large"
            @click="openCreateSection"
          >
            <span class="organizator-primary-btn__icon">+</span>
            <span>Pievienot sekciju</span>
          </button>
        </div>

        <p v-if="loading" class="auth-card__text">
          Ielade organizatora paneli...
        </p>

        <p v-if="error" class="auth-error">{{ error }}</p>

        <div v-if="!loading && user" class="admin-panel">
          <section class="organizator-panel-card">
            <div class="organizator-section-header">
              <div>
                <p class="organizator-kicker">Profils</p>
                <h2>Informacija par sevi</h2>
                <p>Atjauniniet savu kontaktinformāciju un īso aprakstu, ko redzēs potenciālie klienti</p>
              </div>

              <div class="organizator-summary-badges">
                <span class="section-price-badge section-price-badge--muted">{{ sections.length }} sekcijas</span>
                <span class="section-price-badge section-price-badge--muted">
                  {{ applicationSummary?.pending ?? 0 }} Gaida apstiprinājumu
                </span>
                <span class="section-price-badge section-price-badge--muted">
                  {{ applicationSummary?.approved ?? 0 }} Apstiprināti
                </span>
                <span class="section-price-badge section-price-badge--muted">
                  {{ applicationSummary?.rejected ?? 0 }} Noraidīti
                </span>
              </div>
            </div>

            <div class="organizator-form">
              <label class="organizator-form__field">
                <span>Lietotājvārds</span>
                <input v-model="profileForm.name" type="text" autocomplete="name">
                <span v-if="profileErrors.name" class="auth-field-error">{{ profileErrors.name[0] }}</span>
              </label>

              <label class="organizator-form__field">
                <span>Tālrunis</span>
                <input v-model="profileForm.phone" type="text" autocomplete="tel">
                <span v-if="profileErrors.phone" class="auth-field-error">{{ profileErrors.phone[0] }}</span>
              </label>

              <label class="organizator-form__field organizator-form__field--full">
                <span>E-pasts</span>
                <input v-model="profileForm.email" type="email" readonly>
              </label>

              <label class="organizator-form__field">
                <span>Pilsēta</span>
                <input v-model="profileForm.location" type="text" autocomplete="address-level2">
                <span v-if="profileErrors.location" class="auth-field-error">{{ profileErrors.location[0] }}</span>
              </label>

              <div class="organizator-form__footer">
                <button
                  type="button"
                  class="organizator-primary-btn"
                  :disabled="profileLoading"
                  @click="submitProfile"
                >
                  {{ profileLoading ? 'Saglaba...' : 'Saglabāt' }}
                </button>
              </div>
            </div>
          </section>

          <section class="admin-table-card organizator-sections-card">
            <div class="organizator-section-header organizator-section-header--with-action">
              <div>
                <p class="organizator-kicker">Sekcijas</p>
                <h2>Manas sekcijas</h2>
                <p>Pārvaldiet tikai savas sadaļas, grafikus un pieteikumus.</p>
              </div>
            </div>

            <div class="organizator-sections-body">
              <div v-if="sections.length" class="organizator-sections-list">
                <article
                  v-for="section in sections"
                  :key="section.id"
                  class="organizator-section-item organizator-section-item--expanded"
                >
                  <div class="organizator-section-item__image">
                    <img
                      v-if="section.image_url"
                      :src="section.image_url"
                      :alt="section.title"
                    >
                    <div v-else class="organizator-section-item__image-placeholder">
                      {{ section.title.charAt(0).toUpperCase() }}
                    </div>
                  </div>

                  <div class="organizator-section-item__content">
                    <div class="organizator-section-item__topline">
                      <div>
                        <h3>
                          <span class="ui-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                              <path d="M7 5.75A1.75 1.75 0 0 1 8.75 4h6.5A1.75 1.75 0 0 1 17 5.75V20l-5-3-5 3V5.75Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                            </svg>
                          </span>
                          <span>{{ section.title }}</span>
                        </h3>
                        <div class="organizator-section-item__meta-line">
                          <span v-if="section.sport_type">{{ section.sport_type }}</span>
                          <span v-if="section.age_group">{{ section.age_group }}</span>
                        </div>
                      </div>
                      <span class="section-price-badge">
                        <span>{{ formatPrice(section.price) }}</span>
                        <span v-if="hasMonthlyPrice(section.price)" class="section-price-badge__period">/ mēn.</span>
                      </span>
                    </div>

                    <div class="organizator-section-item__badges">
                      <span
                        class="organizator-status-badge"
                        :class="section.status === 'hidden' ? 'organizator-status-badge--hidden' : 'organizator-status-badge--active'"
                      >
                        {{ formatStatus(section.status) }}
                      </span>
                      <span class="section-price-badge section-price-badge--muted">
                        {{ section.applications_count || 0 }} pieteikumi
                      </span>
                      <span v-if="section.average_rating" class="section-price-badge section-price-badge--muted">
                        {{ section.average_rating }}/5
                      </span>
                    </div>

                    <p>{{ section.description || 'Apraksts nav pievienots.' }}</p>

                    <div class="organizator-section-item__details">
                      <div class="organizator-address-row">
                        <span class="ui-icon ui-icon--small organizator-address-row__icon" aria-hidden="true">
                          <svg viewBox="0 0 24 24" fill="none">
                            <path d="M12 20s6-4.35 6-10a6 6 0 1 0-12 0c0 5.65 6 10 6 10Z" stroke="currentColor" stroke-width="1.8" />
                            <circle cx="12" cy="10" r="2.25" stroke="currentColor" stroke-width="1.8" />
                          </svg>
                        </span>
                        <span class="organizator-address-row__text">
                          {{ section.address?.full_address || section.address?.city || section.location || 'Lokacija nav noradita.' }}
                        </span>
                      </div>
                    </div>

                    <div class="organizator-section-item__actions">
                      <button
                        type="button"
                        class="profile-btn"
                        @click="editSection(section)"
                      >
                        <span class="ui-icon ui-icon--small" aria-hidden="true">
                          <svg viewBox="0 0 24 24" fill="none">
                            <path d="M12 20h9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            <path d="m16.5 3.5 4 4L8 20l-4 1 1-4 11.5-13.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                          </svg>
                        </span>
                        Rediģēt
                      </button>

                      <button
                        type="button"
                        class="profile-btn delete-action-btn"
                        :disabled="deletingSectionId === section.id"
                        @click="toggleSectionDelete(section.id)"
                      >
                        <span class="ui-icon ui-icon--small" aria-hidden="true">
                          <svg viewBox="0 0 24 24" fill="none">
                            <path d="M3 6h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            <path d="M8 6V4.75A1.75 1.75 0 0 1 9.75 3h4.5A1.75 1.75 0 0 1 16 4.75V6" stroke="currentColor" stroke-width="1.8" />
                            <path d="M6.75 6 8 20a1 1 0 0 0 1 .9h6a1 1 0 0 0 1-.9L17.25 6" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                          </svg>
                        </span>
                        {{ deletingSectionId === section.id ? 'Dzes...' : 'Dzēst' }}
                      </button>
                    </div>

                    <transition name="expand">
                      <div v-if="confirmDeleteSectionId === section.id" class="delete-inner">
                        <p>
                          Vai tiešām vēlaties dzēst šo sekciju?
                        </p>

                        <div class="actions">
                          <button
                            type="button"
                            class="cancel"
                            :disabled="deletingSectionId === section.id"
                            @click="closeSectionDelete"
                          >
                            Atcelt
                          </button>

                          <button
                            type="button"
                            class="btn-danger"
                            :disabled="deletingSectionId === section.id"
                            @click="removeSection(section)"
                          >
                            {{ deletingSectionId === section.id ? 'Dzes...' : 'Dzēst neatgriezeniski' }}
                          </button>
                        </div>
                      </div>
                    </transition>

                    <div class="organizator-subpanel-grid">
                      <section class="organizator-schedule-panel">
                        <div class="organizator-subpanel__head organizator-subpanel__head--schedule">
                          <h4>
                            <span class="ui-icon ui-icon--small" aria-hidden="true">
                              <svg viewBox="0 0 24 24" fill="none">
                                <path d="M8 2v4M16 2v4M3.5 9.5h17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                <rect x="3" y="4.5" width="18" height="16.5" rx="2.5" stroke="currentColor" stroke-width="1.8" />
                              </svg>
                            </span>
                            Grafiks
                          </h4>
                          <span class="organizator-subpanel__meta">{{ section.schedules?.length || 0 }} ieraksti</span>
                        </div>

                        <div class="organizator-schedule-stack">
                          <div v-if="section.schedules?.length" class="organizator-schedule-list">
                            <article
                              v-for="schedule in section.schedules"
                              :key="schedule.id"
                              class="organizator-schedule-item"
                              :class="{ 'organizator-schedule-item--day-off': schedule.is_day_off }"
                            >
                              <div class="organizator-schedule-item__content">
                                <strong>{{ formatDay(schedule.day_of_week) }}</strong>
                                <span>{{ formatScheduleLabel(schedule) }}</span>
                              </div>

                              <button
                                type="button"
                                class="organizator-schedule-item__delete"
                                :disabled="scheduleLoading[section.id]"
                                @click="removeSchedule(section.id, schedule.id)"
                              >
                                Dzēst
                              </button>
                            </article>
                          </div>

                          <div v-else class="organizator-subpanel__empty">
                            <span class="organizator-empty-icon" aria-hidden="true">
                              <svg viewBox="0 0 24 24" fill="none">
                                <path d="M8 2v4M16 2v4M3.5 9.5h17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                <rect x="3" y="4.5" width="18" height="16.5" rx="2.5" stroke="currentColor" stroke-width="1.8" />
                              </svg>
                            </span>
                            <div>
                              <strong>Grafiks vēl nav pievienots</strong>
                              <p>Lūdzu, zemāk norādiet savas pirmās nodarbības laiku</p>
                            </div>
                          </div>

                          <div class="organizator-schedule-form-block">
                            <div class="organizator-schedule-form-block__head">
                              <h5>Pievienojiet grafiku</h5>
                            </div>

                            <div class="organizator-inline-form">
                              <div class="organizator-inline-form__card">
                                <div class="organizator-inline-form__row organizator-inline-form__row--top">
                                  <label class="organizator-inline-field organizator-inline-field--select">
                                    <span class="organizator-inline-field__label">Diena</span>
                                    <span class="organizator-select-wrap">
                                      <select v-model="ensureScheduleForm(section.id).day_of_week">
                                        <option v-for="day in daysOfWeek" :key="day.value" :value="day.value">
                                          {{ day.label }}
                                        </option>
                                      </select>
                                    </span>
                                  </label>

                                  <label
                                    class="organizator-day-off-toggle"
                                    :class="{ 'organizator-day-off-toggle--active': ensureScheduleForm(section.id).is_day_off }"
                                  >
                                    <span class="organizator-day-off-toggle__label">Brīvdiena</span>
                                    <input
                                      v-model="ensureScheduleForm(section.id).is_day_off"
                                      type="checkbox"
                                      @change="toggleDayOff(section.id)"
                                    >
                                    <span class="organizator-day-off-toggle__switch" aria-hidden="true">
                                      <span class="organizator-day-off-toggle__thumb"></span>
                                    </span>
                                  </label>
                                </div>

                                <div v-if="!ensureScheduleForm(section.id).is_day_off" class="organizator-inline-form__row organizator-inline-form__row--time">
                                  <label class="organizator-inline-field organizator-inline-field--time">
                                    <span class="organizator-inline-field__label">Sakums</span>
                                    <span class="organizator-input-wrap">
                                      <span class="organizator-input-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none">
                                          <circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.7" />
                                          <path d="M12 7.75V12l3 1.75" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                      </span>
                                      <input
                                        :value="ensureScheduleForm(section.id).start_time"
                                        type="text"
                                        inputmode="numeric"
                                        placeholder="00:00"
                                        maxlength="5"
                                        @input="handleTimeInput(section.id, 'start_time', $event)"
                                        @blur="formatTimeOnBlur(section.id, 'start_time')"
                                      >
                                    </span>
                                  </label>

                                  <label class="organizator-inline-field organizator-inline-field--time">
                                    <span class="organizator-inline-field__label">Beigas</span>
                                    <span class="organizator-input-wrap">
                                      <span class="organizator-input-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none">
                                          <circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.7" />
                                          <path d="M12 7.75V12l3 1.75" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                      </span>
                                      <input
                                        :value="ensureScheduleForm(section.id).end_time"
                                        type="text"
                                        inputmode="numeric"
                                        placeholder="00:00"
                                        maxlength="5"
                                        @input="handleTimeInput(section.id, 'end_time', $event)"
                                        @blur="formatTimeOnBlur(section.id, 'end_time')"
                                      >
                                    </span>
                                  </label>

                                  <button
                                    type="button"
                                    class="organizator-primary-btn organizator-inline-form__submit"
                                    :disabled="scheduleLoading[section.id]"
                                    @click="submitSchedule(section.id)"
                                  >
                                    <span class="ui-icon ui-icon--small" aria-hidden="true">
                                      <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                      </svg>
                                    </span>
                                    <span>{{ scheduleLoading[section.id] ? 'Pievieno...' : 'Pievienot' }}</span>
                                  </button>
                                </div>

                                

                                <div v-else class="organizator-inline-form__day-off-note">
                                  Šāja diena nodarbības nenotiek
                                </div>

                                <div v-if="ensureScheduleForm(section.id).is_day_off" class="organizator-inline-form__row organizator-inline-form__row--bottom">
                                  <button
                                    type="button"
                                    class="organizator-primary-btn organizator-inline-form__submit"
                                    :disabled="scheduleLoading[section.id]"
                                    @click="submitSchedule(section.id)"
                                  >
                                    <span class="ui-icon ui-icon--small" aria-hidden="true">
                                      <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                      </svg>
                                    </span>
                                    <span>{{ scheduleLoading[section.id] ? 'Pievieno...' : 'Pievienot' }}</span>
                                  </button>
                                </div>
                              </div>
                            </div>

                            <p v-if="scheduleErrors[section.id]?.day_of_week" class="auth-field-error">
                              {{ scheduleErrors[section.id].day_of_week[0] }}
                            </p>
                            <p v-if="scheduleErrors[section.id]?.start_time" class="auth-field-error">
                              {{ scheduleErrors[section.id].start_time[0] }}
                            </p>
                            <p v-if="scheduleErrors[section.id]?.end_time" class="auth-field-error">
                              {{ scheduleErrors[section.id].end_time[0] }}
                            </p>
                          </div>
                        </div>
                      </section>

                      <section class="organizator-subpanel">
                        <div class="organizator-subpanel__head organizator-subpanel__head--applications">
                          <h4>
                            <span class="ui-icon ui-icon--small" aria-hidden="true">
                              <svg viewBox="0 0 24 24" fill="none">
                                <path d="M16 21v-1.5A3.5 3.5 0 0 0 12.5 16H7.5A3.5 3.5 0 0 0 4 19.5V21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                <circle cx="10" cy="8" r="4" stroke="currentColor" stroke-width="1.8" />
                                <path d="M20 21v-1.5A3.5 3.5 0 0 0 17.5 16.15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                <path d="M16 4.35a4 4 0 0 1 0 7.3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                              </svg>
                            </span>
                            Pieteikusies lietotaji
                          </h4>
                          <div class="organizator-applications-toolbar">
                            <span>{{ section.applications?.length || 0 }} ieraksti</span>
                            <div class="organizator-filter-chips" role="tablist" aria-label="Pieteikumu filtrs">
                              <button
                                v-for="filter in applicationFilters"
                                :key="filter.value"
                                type="button"
                                class="organizator-filter-chip"
                                :class="{ 'organizator-filter-chip--active': activeApplicationFilter === filter.value }"
                                @click="activeApplicationFilter = filter.value"
                              >
                                {{ filter.label }}
                              </button>
                            </div>
                          </div>
                        </div>

                        <div v-if="getFilteredApplications(section).length" class="organizator-subpanel__list">
                          <div
                            v-for="application in getFilteredApplications(section)"
                            :key="application.id"
                            class="organizator-subpanel__item organizator-subpanel__item--stacked"
                          >
                            <div>
                              <strong>{{ application.name }}</strong>
                              <span>{{ application.email }}</span>
                              <span v-if="application.phone">{{ application.phone }}</span>
                              <span v-if="application.created_at">{{ formatDateTime(application.created_at) }}</span>
                              <span v-if="application.message">{{ application.message }}</span>
                            </div>

                            <div class="organizator-subpanel__item-actions">
                              <span
                                class="organizator-status-badge"
                                :class="application.status === 'approved'
                                  ? 'organizator-status-badge--active'
                                  : (application.status === 'rejected'
                                    ? 'organizator-status-badge--hidden'
                                    : 'organizator-status-badge--pending')"
                              >
                                {{ formatApplicationStatus(application.status) }}
                              </span>

                              <button
                                type="button"
                                class="profile-btn"
                                :disabled="applicationLoading[application.id] || application.status === 'approved'"
                                @click="updateApplication(section.id, application.id, 'approved')"
                              >
                                Apstiprināt
                              </button>

                              <button
                                type="button"
                                class="profile-btn admin-delete-btn"
                                :disabled="applicationLoading[application.id] || application.status === 'rejected'"
                                @click="updateApplication(section.id, application.id, 'rejected')"
                              >
                                Noraidīt
                              </button>
                            </div>
                          </div>
                        </div>

                        <div
                          v-else-if="hasApplications(section)"
                          class="organizator-subpanel__empty"
                        >
                          <div>
                            <strong>Šajā filtrā pieteikumu nav</strong>
                            <p>Izvēlieties citu statusu, lai redzētu pārējos pieteikumus.</p>
                          </div>
                        </div>

                        <div v-else class="organizator-subpanel__empty">
                          <span class="organizator-empty-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                              <path d="M16 21v-1.5A3.5 3.5 0 0 0 12.5 16H7.5A3.5 3.5 0 0 0 4 19.5V21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                              <circle cx="10" cy="8" r="4" stroke="currentColor" stroke-width="1.8" />
                              <path d="M19 8v6M16 11h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            </svg>
                          </span>
                          <div>
                            <strong>Nav pieteikumu vel</strong>
                            <p>Jauni lietotaji parādīsies seit, kad pieteiksies uz sekciju.</p>
                          </div>
                        </div>
                      </section>
                    </div>
                  </div>
                </article>
              </div>

              <div v-else class="organizator-empty-state">
                <p>Vēl nav nevienas sadaļas.</p>
              </div>
            </div>
          </section>
        </div>
      </div>
    </div>
  </AuthCard>

  <div
    v-if="showSectionModal"
    class="organizator-modal"
    @click.self="closeSectionModal"
  >
    <div class="organizator-modal__dialog">
      <div class="organizator-modal__header">
        <div>
          <h2>{{ sectionForm.id ? 'Rediget sekciju' : 'Jauna sekcija' }}</h2>
          <p>Aizpildi informaciju par sekciju un saglaba izmainas.</p>
        </div>

        <button
          type="button"
          class="organizator-modal__close"
          @click="closeSectionModal"
        >
          x
        </button>
      </div>

      <div class="organizator-form">
        <label class="organizator-form__field">
          <span>Nosaukums</span>
          <input v-model="sectionForm.title" type="text">
          <span v-if="sectionErrors.title" class="auth-field-error">{{ sectionErrors.title[0] }}</span>
        </label>

        <label class="organizator-form__field">
          <span>Sporta veids</span>
          <input v-model="sectionForm.sport_type" type="text">
          <span v-if="sectionErrors.sport_type" class="auth-field-error">{{ sectionErrors.sport_type[0] }}</span>
        </label>

        <label class="organizator-form__field">
          <span>Vecuma grupa</span>
          <input v-model="sectionForm.age_group" type="text" placeholder="7-12 gadi">
          <span v-if="sectionErrors.age_group" class="auth-field-error">{{ sectionErrors.age_group[0] }}</span>
        </label>

        <label class="organizator-form__field">
          <span>Cena</span>
          <input
            v-model="sectionForm.price"
            type="number"
            min="0"
            step="10"
          >
          <span v-if="sectionErrors.price" class="auth-field-error">{{ sectionErrors.price[0] }}</span>
        </label>

        <label class="organizator-form__field">
          <span>Statuss</span>
          <select v-model="sectionForm.status">
            <option value="active">active</option>
            <option value="hidden">hidden</option>
          </select>
          <span v-if="sectionErrors.status" class="auth-field-error">{{ sectionErrors.status[0] }}</span>
        </label>

        <label class="organizator-form__field">
          <span>Pilsēta</span>
          <input v-model="sectionForm.city" type="text">
          <span v-if="sectionErrors.city" class="auth-field-error">{{ sectionErrors.city[0] }}</span>
        </label>

        <label class="organizator-form__field organizator-form__field--full">
          <span>Pilna adrese</span>
          <input v-model="sectionForm.full_address" type="text">
          <span v-if="sectionErrors.full_address" class="auth-field-error">{{ sectionErrors.full_address[0] }}</span>
          <span v-if="sectionErrors.location" class="auth-field-error">{{ sectionErrors.location[0] }}</span>
        </label>

        <label class="organizator-form__field organizator-form__field--full">
          <span>Apraksts</span>
          <textarea v-model="sectionForm.description" rows="4"></textarea>
          <span v-if="sectionErrors.description" class="auth-field-error">{{ sectionErrors.description[0] }}</span>
        </label>

        <label class="organizator-form__field organizator-form__field--full">
          <span>Attēls</span>
          <input type="file" accept="image/*" @change="handleSectionImage">
          <span v-if="sectionErrors.image" class="auth-field-error">{{ sectionErrors.image[0] }}</span>
        </label>

        <div
          v-if="sectionImagePreview || existingSectionImageUrl"
          class="organizator-section-image-preview"
        >
          <img
            :src="sectionImagePreview || existingSectionImageUrl"
            alt="Section preview"
          >
        </div>

        <div class="organizator-form__footer">
          <button
            type="button"
            class="cancel"
            :disabled="sectionLoading"
            @click="closeSectionModal"
          >
            Atcelt
          </button>

          <button
            type="button"
            class="organizator-primary-btn"
            :disabled="sectionLoading"
            @click="submitSection"
          >
            {{ sectionLoading ? 'Saglaba...' : 'Saglabāt' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
