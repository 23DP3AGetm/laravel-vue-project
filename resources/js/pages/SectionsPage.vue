<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import { getPublicSections } from '../services/authService';

const loading = ref(true);
const error = ref('');
const sections = ref([]);
const filterOptions = ref({
  sport_types: [],
  cities: [],
  age_groups: [],
});

const priceMinOptions = [5, 10, 15, 20, 30, 50];
const priceMaxOptions = [10, 20, 30, 50];

const filters = reactive({
  search: '',
  sport_type: '',
  city: '',
  age_group: '',
  price_min: '',
  price_max: '',
});

let filtersDebounceId = null;

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

function formatPriceRangeLabel() {
  if (filters.price_min && filters.price_max) {
    return `${filters.price_min}–${filters.price_max} €`;
  }

  if (filters.price_min) {
    return `No ${filters.price_min} €`;
  }

  if (filters.price_max) {
    return `Līdz ${filters.price_max} €`;
  }

  return '';
}

const activeFilters = computed(() => {
  const items = [];

  if (filters.sport_type) {
    items.push({ key: 'sport_type', label: filters.sport_type });
  }

  if (filters.city) {
    items.push({ key: 'city', label: filters.city });
  }

  if (filters.age_group) {
    items.push({ key: 'age_group', label: filters.age_group });
  }

  const priceLabel = formatPriceRangeLabel();

  if (priceLabel) {
    items.push({ key: 'price_range', label: priceLabel });
  }

  return items;
});

async function loadSections() {
  loading.value = true;
  error.value = '';

  try {
    const response = await getPublicSections(filters);
    sections.value = response.sections || [];
    filterOptions.value = response.filters || filterOptions.value;
  } catch (e) {
    error.value = e.message || 'Neizdevās ielādēt sadaļas.';
  } finally {
    loading.value = false;
  }
}

function queueFilterReload() {
  if (filtersDebounceId) {
    clearTimeout(filtersDebounceId);
  }

  filtersDebounceId = setTimeout(() => {
    loadSections();
  }, 300);
}

function clearSearch() {
  filters.search = '';
}

function clearPriceRange() {
  filters.price_min = '';
  filters.price_max = '';
}

function removeActiveFilter(key) {
  if (key === 'price_range') {
    clearPriceRange();
    return;
  }

  filters[key] = '';
}

function clearAllFilters() {
  filters.search = '';
  filters.sport_type = '';
  filters.city = '';
  filters.age_group = '';
  filters.price_min = '';
  filters.price_max = '';
}

watch(
  () => [
    filters.search,
    filters.sport_type,
    filters.city,
    filters.age_group,
    filters.price_min,
    filters.price_max,
  ],
  () => {
    queueFilterReload();
  }
);

onMounted(async () => {
  await loadSections();
});

onBeforeUnmount(() => {
  if (filtersDebounceId) {
    clearTimeout(filtersDebounceId);
  }
});
</script>

<template>
  <section class="section" id="sections">
    <div class="container">
      <div class="section-heading section-heading--row">
        <div>
          <p class="section-label">Sekcijas</p>
          <h2>Atrodi savu nākamo treniņu</h2>
        </div>
        <span class="section-link">{{ sections.length }} aktīvas sekcijas</span>
      </div>

      <div class="sections-filters sections-filters--marketplace">
        <div class="sections-filters__group sections-filters__group--search">
          <div class="sections-filters__group-head">
            <span class="sections-filters__group-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none">
                <circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8" />
                <path d="m16 16 4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
              </svg>
            </span>
            <span>Meklēt</span>
          </div>

          <label class="sections-filters__field sections-filters__field--search">
            <span class="sections-filters__label">Nosaukums vai sporta veids</span>
            <span class="sections-filters__search-wrap">
              <span class="sections-filters__search-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                  <circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8" />
                  <path d="m16 16 4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                </svg>
              </span>
              <input v-model="filters.search" type="text" placeholder="Nosaukums vai sporta veids">
              <button
                v-if="filters.search"
                type="button"
                class="sections-filters__clear-btn"
                aria-label="Notīrīt meklēšanu"
                @click="clearSearch"
              >
                <svg viewBox="0 0 24 24" fill="none">
                  <path d="M7 7 17 17M17 7 7 17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                </svg>
              </button>
            </span>
          </label>
        </div>

        <div class="sections-filters__group">
          <div class="sections-filters__group-head">
            <span class="sections-filters__group-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M4 7h16M7 12h10M10 17h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
              </svg>
            </span>
            <span>Filtri</span>
          </div>

          <div class="sections-filters__grid sections-filters__grid--marketplace">
            <label class="sections-filters__field">
              <span class="sections-filters__label">
                <span class="sections-filters__label-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="7.5" stroke="currentColor" stroke-width="1.8" />
                    <path d="M12 4.5c2.2 2.15 3.3 4.65 3.3 7.5s-1.1 5.35-3.3 7.5c-2.2-2.15-3.3-4.65-3.3-7.5s1.1-5.35 3.3-7.5Z" stroke="currentColor" stroke-width="1.8" />
                  </svg>
                </span>
                Sporta veids
              </span>
              <select v-model="filters.sport_type">
                <option value="">Visi</option>
                <option v-for="item in filterOptions.sport_types" :key="item" :value="item">{{ item }}</option>
              </select>
            </label>

            <label class="sections-filters__field">
              <span class="sections-filters__label">
                <span class="sections-filters__label-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none">
                    <path d="M12 20s6-4.35 6-10a6 6 0 1 0-12 0c0 5.65 6 10 6 10Z" stroke="currentColor" stroke-width="1.8" />
                    <circle cx="12" cy="10" r="2.25" stroke="currentColor" stroke-width="1.8" />
                  </svg>
                </span>
                Pilsēta
              </span>
              <select v-model="filters.city">
                <option value="">Visas</option>
                <option v-for="item in filterOptions.cities" :key="item" :value="item">{{ item }}</option>
              </select>
            </label>

            <label class="sections-filters__field">
              <span class="sections-filters__label">
                <span class="sections-filters__label-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.8" />
                    <path d="M5 20a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                  </svg>
                </span>
                Vecuma grupa
              </span>
              <select v-model="filters.age_group">
                <option value="">Visas</option>
                <option v-for="item in filterOptions.age_groups" :key="item" :value="item">{{ item }}</option>
              </select>
            </label>
          </div>
        </div>

        <div class="sections-filters__group">
          <div class="sections-filters__group-head">
            <span class="sections-filters__group-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none">
                <path d="M16.5 6.5a5.9 5.9 0 0 0-3.4-1c-3.2 0-5.6 2.1-6.2 5.2h8.5M6.7 13.3h7.9m-7.8 0c.7 3 3 5.2 6.1 5.2 1.3 0 2.5-.34 3.6-1" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </span>
            <span>Cena</span>
          </div>

          <div class="sections-filters__price-grid">
            <div class="sections-filters__field sections-filters__field--chips">
              <span class="sections-filters__label">Cena no</span>
              <div class="sections-filter-chips" role="group" aria-label="Cena no">
                <button
                  type="button"
                  class="sections-filter-chip"
                  :class="{ 'sections-filter-chip--active': filters.price_min === '' }"
                  @click="filters.price_min = ''"
                >
                  Jebkura
                </button>
                <button
                  v-for="price in priceMinOptions"
                  :key="`min-${price}`"
                  type="button"
                  class="sections-filter-chip"
                  :class="{ 'sections-filter-chip--active': filters.price_min === String(price) }"
                  @click="filters.price_min = String(price)"
                >
                  {{ price }}
                </button>
              </div>
            </div>

            <div class="sections-filters__field sections-filters__field--chips">
              <span class="sections-filters__label">Cena līdz</span>
              <div class="sections-filter-chips" role="group" aria-label="Cena līdz">
                <button
                  type="button"
                  class="sections-filter-chip"
                  :class="{ 'sections-filter-chip--active': filters.price_max === '' }"
                  @click="filters.price_max = ''"
                >
                  Jebkura
                </button>
                <button
                  v-for="price in priceMaxOptions"
                  :key="`max-${price}`"
                  type="button"
                  class="sections-filter-chip"
                  :class="{ 'sections-filter-chip--active': filters.price_max === String(price) }"
                  @click="filters.price_max = String(price)"
                >
                  {{ price }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-if="activeFilters.length" class="sections-filters__active">
          <span class="sections-filters__active-label">Aktīvie filtri:</span>
          <div class="sections-filters__active-list">
            <button
              v-for="item in activeFilters"
              :key="item.key"
              type="button"
              class="sections-filters__active-chip"
              @click="removeActiveFilter(item.key)"
            >
              <span>{{ item.label }}</span>
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <button type="button" class="sections-filters__clear-all" @click="clearAllFilters">
            Notīrīt visu
          </button>
        </div>
      </div>

      <p v-if="loading" class="auth-card__text">Ielādē sekcijas...</p>
      <p v-else-if="error" class="auth-error">{{ error }}</p>

      <div v-else-if="sections.length" class="organizator-sections-list sections-grid">
        <article v-for="section in sections" :key="section.id" class="organizator-section-item sections-card">
          <div class="organizator-section-item__image sections-card__image">
            <img v-if="section.image_url" :src="section.image_url" :alt="section.title">
            <div v-else class="organizator-section-item__image-placeholder">
              {{ section.title.charAt(0).toUpperCase() }}
            </div>
          </div>

          <div class="organizator-section-item__content sections-card__content">
            <div class="organizator-section-item__topline sections-card__topline">
              <div>
                <h3>
                  <i class="bx bx-bookmark organizator-inline-icon" aria-hidden="true"></i>
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

            <p class="sections-card__description">{{ section.description || 'Apraksts nav pievienots.' }}</p>

            <div class="organizator-section-item__details sections-card__details">
              <span>
                <i class="bx bx-map organizator-inline-icon" aria-hidden="true"></i>
                <span>{{ section.address?.city || section.location || 'Lokācija nav norādīta.' }}</span>
              </span>
              <span v-if="section.average_rating">{{ section.average_rating }}/5</span>
            </div>

            <div class="organizator-section-item__actions sections-card__actions">
              <RouterLink
                class="organizator-primary-btn sections-card__action-btn"
                :to="`/sekcija/${section.slug}`"
              >
                Atvērt sekciju
              </RouterLink>
            </div>
          </div>
        </article>
      </div>

      <div v-else class="organizator-empty-state">
        <p>Vēl nav pievienota neviena aktīva sadaļa</p>
      </div>
    </div>
  </section>
</template>
