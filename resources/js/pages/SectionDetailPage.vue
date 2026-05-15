<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import { useRoute, RouterLink } from 'vue-router';
import { auth, loadUser } from '../auth';
import {
  createSectionReview,
  createSectionApplication,
  getPublicSection,
} from '../services/authService';

const route = useRoute();
const loading = ref(true);
const submitting = ref(false);
const reviewSubmitting = ref(false);
const error = ref('');
const success = ref('');
const reviewSuccess = ref('');
const section = ref(null);
const applicationMeta = ref({ enabled: false, requires_auth: true, already_applied: false, application_status: null });
const reviewMeta = ref({ enabled: false, requires_auth: true, already_reviewed: false });
const reviewErrors = ref({});
const applicationErrors = ref({});
const showApplicationModal = ref(false);

const applicationForm = reactive({
  name: '',
  message: '',
});

const reviewForm = reactive({
  rating: 5,
  comment: '',
});

const daysOfWeek = {
  monday: 'Pirmdiena',
  tuesday: 'Otrdiena',
  wednesday: 'Trešdiena',
  thursday: 'Ceturtdiena',
  friday: 'Piektdiena',
  saturday: 'Sestdiena',
  sunday: 'Svētdiena',
};

function formatPrice(price) {
  if (price === null || price === undefined || price === '') {
    return 'Pēc vienošanās';
  }

  const numericPrice = Number(price);
  return Number.isNaN(numericPrice)
    ? `${price} EUR`
    : `${numericPrice.toFixed(2)} EUR`;
}

function hasMonthlyPrice(price) {
  return price !== null && price !== undefined && price !== '';
}

function formatDay(day) {
  return daysOfWeek[day] || day;
}

function formatSchedule(schedule) {
  if (schedule.is_day_off) {
    return 'Brīvdiena';
  }

  return `${schedule.start_time} - ${schedule.end_time}`;
}

function formatDate(value) {
  if (!value) {
    return '';
  }

  const reviewDate = new Date(value);
  const today = new Date();
  const reviewDay = new Date(reviewDate.getFullYear(), reviewDate.getMonth(), reviewDate.getDate());
  const todayDay = new Date(today.getFullYear(), today.getMonth(), today.getDate());
  const diffDays = Math.round((todayDay - reviewDay) / 86400000);

  if (diffDays === 0) {
    return 'Šodien';
  }

  if (diffDays === 1) {
    return 'Vakar';
  }

  return reviewDate.toLocaleDateString('lv-LV');
}

function getStars(rating) {
  return Array.from({ length: 5 }, (_, index) => index < Math.round(Number(rating) || 0));
}

function setReviewRating(rating) {
  reviewForm.rating = rating;
}

function isReviewStarFilled(star) {
  return star <= reviewForm.rating;
}

function getInitials(name) {
  return String(name || 'Lietotājs')
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part.charAt(0).toUpperCase())
    .join('');
}

function getAvatarUrl(user) {
  return user?.avatar ? `/storage/${user.avatar}` : '';
}

const loginRedirectTarget = computed(() => ({
  path: '/login',
  query: { redirect: route.fullPath },
}));

const registerRedirectTarget = computed(() => ({
  path: '/register',
  query: { redirect: route.fullPath },
}));

const isAuthenticated = computed(() => !!auth.user);
const canSubmitApplication = computed(() => isAuthenticated.value && !applicationMeta.value.already_applied);
const shouldPromptLogin = computed(() => !isAuthenticated.value && applicationMeta.value.requires_auth);
const applicationStatusLabel = computed(() => {
  if (applicationMeta.value.application_status === 'approved') {
    return 'Apstiprināts';
  }

  if (applicationMeta.value.application_status === 'rejected') {
    return 'Noraidīts';
  }

  return 'Gaida apstiprinājumu';
});

async function syncAuthAndSection() {
  if (auth.token && !auth.user) {
    await loadUser();
  }

  await loadSection();
}

function handleAuthChanged() {
  syncAuthAndSection();
}

async function loadSection() {
  loading.value = true;
  error.value = '';

  try {
    const response = await getPublicSection(route.params.slug);
    section.value = response.section || null;
    applicationMeta.value = response.application_meta || applicationMeta.value;
    reviewMeta.value = response.review_meta || reviewMeta.value;
    applicationForm.name = auth.user?.name || '';
  } catch (e) {
    error.value = e.message || 'Neizdevās ielādēt sekciju.';
  } finally {
    loading.value = false;
  }
}

function openApplicationModal() {
  if (!isAuthenticated.value || applicationMeta.value.already_applied) {
    return;
  }

  applicationErrors.value = {};
  applicationForm.name = auth.user?.name || '';
  showApplicationModal.value = true;
}

function closeApplicationModal() {
  showApplicationModal.value = false;
  applicationErrors.value = {};
}

async function submitApplication() {
  if (!section.value?.slug || !auth.user || applicationMeta.value.already_applied) {
    return;
  }

  submitting.value = true;
  error.value = '';
  success.value = '';
  applicationErrors.value = {};

  try {
    const response = await createSectionApplication(section.value.slug, {
      name: applicationForm.name || auth.user.name,
      email: auth.user.email,
      phone: auth.user.phone || '',
      message: applicationForm.message,
    });
    success.value = response.message || 'Pieteikums nosūtīts.';
    applicationMeta.value = {
      ...applicationMeta.value,
      already_applied: true,
      application_status: response.application?.status || applicationMeta.value.application_status || 'pending',
    };
    applicationForm.message = '';
    closeApplicationModal();
    await loadSection();
  } catch (e) {
    applicationErrors.value = e.errors || {};
    error.value = e.message || 'Neizdevās nosūtīt pieteikumu.';
  } finally {
    submitting.value = false;
  }
}

async function submitReview() {
  if (!section.value?.slug) {
    return;
  }

  reviewSubmitting.value = true;
  reviewErrors.value = {};
  reviewSuccess.value = '';

  try {
    const response = await createSectionReview(section.value.slug, reviewForm);
    reviewSuccess.value = response.message || 'Atsauksme saglabāta.';
    section.value.reviews = [response.review, ...(section.value.reviews || [])];
    section.value.average_rating = response.average_rating;
    reviewMeta.value = {
      ...reviewMeta.value,
      enabled: false,
      already_reviewed: true,
    };
    reviewForm.rating = 5;
    reviewForm.comment = '';
  } catch (e) {
    reviewErrors.value = e.errors || {};
    error.value = e.message || 'Neizdevās saglabāt atsauksmi.';
  } finally {
    reviewSubmitting.value = false;
  }
}

onMounted(async () => {
  window.addEventListener('auth-changed', handleAuthChanged);
  await syncAuthAndSection();
});

onBeforeUnmount(() => {
  window.removeEventListener('auth-changed', handleAuthChanged);
});
</script>

<template>
  <section class="section page-section">
    <div class="container">
      <div v-if="loading" class="section-detail-shell">
        <p class="auth-card__text">Ielādē sekciju...</p>
      </div>

      <div v-else-if="error && !section" class="section-detail-shell">
        <p class="auth-error">{{ error }}</p>
      </div>

      <article v-else-if="section" class="section-detail-shell section-detail-card">
        <div class="section-detail-hero">
          <img
            v-if="section.image_url"
            :src="section.image_url"
            :alt="section.title"
            class="section-detail-hero__image"
          >
          <div v-else class="section-detail-hero__placeholder">
            {{ section.title.charAt(0).toUpperCase() }}
          </div>
        </div>

        <div class="section-detail-content">
          <div class="section-detail-heading">
            <div>
              <p class="section-label">Sekcija</p>
              <h1>{{ section.title }}</h1>
              <div class="organizator-section-item__meta-line">
                <span v-if="section.sport_type">{{ section.sport_type }}</span>
                <span v-if="section.age_group">{{ section.age_group }}</span>
              </div>
            </div>
            <span class="section-price-badge section-price-badge--large">
              <span>{{ formatPrice(section.price) }}</span>
              <span v-if="hasMonthlyPrice(section.price)" class="section-price-badge__period">/ mēn.</span>
            </span>
          </div>

          <div class="section-detail-meta">
            <span>
              <i class="bx bx-map organizator-inline-icon" aria-hidden="true"></i>
              <span>{{ section.address?.full_address || section.location || 'Pilna adrese nav norādīta.' }}</span>
            </span>
            <span>{{ section.organizator?.name || 'Organizators' }}</span>
            <span v-if="section.average_rating">{{ section.average_rating }}/5</span>
          </div>

          <p class="section-detail-description">
            {{ section.description || 'Apraksts nav pievienots.' }}
          </p>

          <div class="section-detail-actions">
            <button
              v-if="canSubmitApplication"
              type="button"
              class="organizator-primary-btn"
              @click="openApplicationModal"
            >
              Pieteikties uz treniņu
            </button>

            <RouterLink
              v-else-if="shouldPromptLogin"
              :to="loginRedirectTarget"
              class="organizator-primary-btn"
            >
              Ielogoties, lai pieteiktos
            </RouterLink>

            <button
              v-else-if="applicationMeta.already_applied"
              type="button"
              class="organizator-primary-btn organizator-primary-btn--disabled"
              disabled
            >
              Jūs jau esat pieteicies
            </button>
          </div>

          <p v-if="applicationMeta.already_applied" class="profile-inline-note">
            Statuss: {{ applicationStatusLabel }}
          </p>

          <p v-if="shouldPromptLogin" class="profile-inline-note">
            Vai vēl nav konta?
            <RouterLink :to="registerRedirectTarget">Reģistrēties</RouterLink>
          </p>

          <div class="section-detail-info-grid">
            <section class="section-detail-info-card section-detail-info-card--schedule">
              <h2>Grafiks</h2>
              <div v-if="section.schedules?.length" class="section-detail-schedule-list">
                <div
                  v-for="schedule in section.schedules"
                  :key="schedule.id"
                  class="section-detail-schedule-item"
                  :class="{ 'section-detail-schedule-item--day-off': schedule.is_day_off }"
                >
                  <strong>
                    <span class="section-detail-schedule-item__dot" aria-hidden="true">{{ schedule.is_day_off ? '•' : '•' }}</span>
                    <span>{{ formatDay(schedule.day_of_week) }}</span>
                  </strong>
                  <span>{{ formatSchedule(schedule) }}</span>
                </div>
              </div>
              <p v-else>Grafiks vēl nav pievienots.</p>
            </section>
          </div>

          <section class="section-detail-review-block">
            <div class="organizator-section-header">
              <div>
                <p class="organizator-kicker">Atsauksmes</p>
                <h2>Apmeklētāju viedoklis</h2>
                <p>Skatiet, ko par šo sekciju saka apmeklētāji.</p>
              </div>
            </div>

            <div class="review-summary-card">
              <span class="review-summary-card__label">Vidējais vērtējums</span>
              <div class="review-summary-card__value-row">
                <i class="bx bx-star review-summary-card__icon" aria-hidden="true"></i>
                <strong>{{ section.average_rating || 'Nav' }}</strong>
                <span v-if="section.average_rating" class="review-stars review-stars--display">
                  <span
                    v-for="(filled, index) in getStars(section.average_rating)"
                    :key="`summary-${index}`"
                    class="review-stars__star"
                    :class="{ 'review-stars__star--filled': filled }"
                  >☆</span>
                </span>
              </div>
            </div>

            <div v-if="isAuthenticated && reviewMeta.enabled" class="organizator-form section-review-form">
              <label class="organizator-form__field">
                <span>Vērtējums</span>
                <div class="review-stars review-stars--interactive" role="radiogroup" aria-label="Vērtējums">
                  <button
                    v-for="star in 5"
                    :key="star"
                    type="button"
                    class="review-stars__button"
                    :class="{ 'review-stars__button--active': isReviewStarFilled(star) }"
                    :aria-checked="star === reviewForm.rating"
                    @click="setReviewRating(star)"
                  >
                    ☆
                  </button>
                </div>
              </label>

              <label class="organizator-form__field organizator-form__field--full">
                <span>Atsauksme</span>
                <textarea v-model="reviewForm.comment" rows="4" placeholder="Dalieties ar savu pieredzi..."></textarea>
              </label>

              <div class="organizator-form__footer">
                <button
                  type="button"
                  class="organizator-primary-btn"
                  :disabled="reviewSubmitting"
                  @click="submitReview"
                >
                  {{ reviewSubmitting ? 'Saglabā...' : 'Nosūtīt' }}
                </button>
              </div>
            </div>

            <p
              v-else-if="isAuthenticated && reviewMeta.already_reviewed"
              class="profile-inline-note review-note"
            >
              <i class="bx bx-check-circle" aria-hidden="true"></i>
              <span>Jūs jau atstājāt atsauksmi.</span>
            </p>

            <RouterLink
              v-else-if="reviewMeta.requires_auth"
              to="/login"
              class="organizator-primary-btn"
            >
              Ielogoties, lai atstātu atsauksmi
            </RouterLink>

            <p v-if="reviewSuccess" class="auth-success">{{ reviewSuccess }}</p>

            <div v-if="section.reviews?.length" class="section-review-list">
              <article v-for="review in section.reviews" :key="review.id" class="section-review-item">
                <div class="section-review-item__head">
                  <div class="section-review-item__identity">
                    <div class="review-avatar" aria-hidden="true">
                      <img
                        v-if="getAvatarUrl(review.user)"
                        :src="getAvatarUrl(review.user)"
                        :alt="review.user?.name || 'Lietotājs'"
                      >
                      <span v-else>{{ getInitials(review.user?.name) }}</span>
                    </div>
                    <div class="section-review-item__identity-copy">
                      <strong>{{ review.user?.name || 'Lietotājs' }}</strong>
                      <span class="section-review-item__date">{{ formatDate(review.reviewed_at) }}</span>
                    </div>
                  </div>
                  <span class="review-stars review-stars--display review-stars--row">
                    <span
                      v-for="(filled, index) in getStars(review.rating)"
                      :key="`${review.id}-${index}`"
                      class="review-stars__star"
                      :class="{ 'review-stars__star--filled': filled }"
                    >☆</span>
                  </span>
                </div>
                <p>{{ review.comment || 'Bez komentāra.' }}</p>
              </article>
            </div>
            <p v-else class="profile-inline-note">Vēl nav nevienas atsauksmes.</p>
          </section>

          <p v-if="error" class="auth-error">{{ error }}</p>
          <p v-if="success" class="auth-success">{{ success }}</p>
        </div>
      </article>
    </div>

    <div
      v-if="showApplicationModal && section"
      class="organizator-modal"
      @click.self="closeApplicationModal"
    >
      <div class="organizator-modal__dialog">
        <div class="organizator-modal__header">
          <div>
            <h2>Pieteikties uz treniņu</h2>
            <p>{{ section.title }}</p>
          </div>

          <button
            type="button"
            class="organizator-modal__close"
            @click="closeApplicationModal"
          >
            x
          </button>
        </div>

        <div class="organizator-form">
          <label class="organizator-form__field">
            <span>Vārds</span>
            <input v-model="applicationForm.name" type="text" autocomplete="name">
            <span v-if="applicationErrors.name" class="auth-field-error">{{ applicationErrors.name[0] }}</span>
          </label>

          <label class="organizator-form__field organizator-form__field--full">
            <span>Ziņojums trenerim</span>
            <textarea v-model="applicationForm.message" rows="4" placeholder="Pastāstiet īsi par sevi vai uzdodiet jautājumu"></textarea>
            <span v-if="applicationErrors.message" class="auth-field-error">{{ applicationErrors.message[0] }}</span>
          </label>

          <div class="organizator-form__footer">
            <button
              type="button"
              class="cancel"
              :disabled="submitting"
              @click="closeApplicationModal"
            >
              Atcelt
            </button>

            <button
              type="button"
              class="organizator-primary-btn"
              :disabled="submitting"
              @click="submitApplication"
            >
              {{ submitting ? 'Nosūta...' : 'Nosūtīt pieteikumu' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.section-detail-review-block {
  display: grid;
  gap: 14px;
  margin-top: 28px;
  padding: 18px 18px 8px;
  border: 1px solid rgba(0, 0, 0, 0.05);
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.58);
}

.review-summary-card {
  display: grid;
  gap: 6px;
  padding: 0 0 4px;
}

.review-summary-card__label {
  color: #555;
  font-size: 14px;
  font-weight: 500;
}

.review-summary-card__value-row {
  display: flex;
  align-items: center;
  gap: 8px;
  min-height: 24px;
}

.review-summary-card__icon {
  color: #aaa;
  font-size: 18px;
}

.review-summary-card__value-row strong {
  color: #333;
  font-size: 18px;
  font-weight: 600;
}

.review-stars {
  display: inline-flex;
  align-items: center;
  gap: 2px;
}

.review-stars--display {
  font-size: 16px;
}

.review-stars--row {
  margin-left: auto;
}

.review-stars__star,
.review-stars__button {
  color: rgba(245, 176, 66, 0.35);
}

.review-stars__star--filled,
.review-stars__button--active {
  color: #f5b042;
}

.review-stars__button {
  border: 0;
  padding: 0;
  background: transparent;
  font-size: 24px;
  line-height: 1;
  cursor: pointer;
}

.review-note {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #777;
}

.review-note i {
  color: #22a447;
  font-size: 16px;
}

.section-review-form {
  padding: 14px;
  border-radius: 8px;
  border: 1px solid #eee;
  background: rgba(250, 250, 250, 0.9);
  box-shadow: none;
}

.section-review-form textarea,
.section-review-form select {
  background: rgba(255, 255, 255, 0.82);
}

.section-review-list {
  display: grid;
}

.section-review-item {
  padding: 14px 0;
  border-bottom: 1px solid #eee;
  background: transparent;
}

.section-review-item:last-child {
  border-bottom: 0;
  padding-bottom: 0;
}

.section-review-item__head {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  align-items: center;
  gap: 12px;
}

.section-review-item__identity {
  display: grid;
  grid-template-columns: 36px minmax(0, 1fr);
  align-items: center;
  gap: 10px;
}

.section-review-item__identity-copy {
  display: grid;
  gap: 2px;
}

.section-review-item__identity-copy strong {
  color: #333;
  font-size: 14px;
  font-weight: 500;
}

.review-avatar {
  display: grid;
  place-items: center;
  width: 36px;
  height: 36px;
  border-radius: 999px;
  overflow: hidden;
  background: #f3f4f6;
  color: #8d98a5;
  font-size: 12px;
  font-weight: 600;
}

.review-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.section-review-item__date {
  color: #aaa;
  font-size: 12px;
}

.section-review-item p {
  margin: 10px 0 0 46px;
  color: #555;
  line-height: 1.65;
}

@media (max-width: 720px) {
  .section-detail-review-block {
    padding: 16px 14px 6px;
  }

  .section-review-item__head {
    grid-template-columns: 1fr;
  }

  .review-stars--row {
    margin-left: 46px;
  }
}
</style>
