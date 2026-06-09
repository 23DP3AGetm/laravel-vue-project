<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { auth, clearAuth } from './auth';
import { getNotifications, logout as logoutUser, markNotificationRead } from './services/authService';
import UserAvatar from './components/ui/UserAvatar.vue';

const route = useRoute();
const router = useRouter();
const authVersion = ref(0);
const headerRoot = ref(null);
const profileMenu = ref(null);
const notificationsMenu = ref(null);
const isMobileMenuOpen = ref(false);
const isProfileMenuOpen = ref(false);
const isNotificationsOpen = ref(false);
const notifications = ref([]);
const unreadNotifications = ref(0);

const isAuth = computed(() => {
  authVersion.value;
  return !!localStorage.getItem('auth_token');
});

const currentUser = computed(() => {
  authVersion.value;
  return auth.user;
});

const isAdmin = computed(() => currentUser.value?.role === 'admin');
const canAccessOrganizator = computed(() => currentUser.value?.role === 'organizator');
const headerAvatarUrl = computed(() => currentUser.value?.avatar ? `/storage/${currentUser.value.avatar}` : '');

onMounted(() => {
  window.addEventListener('storage', updateAuthState);
  window.addEventListener('auth-changed', updateAuthState);
  window.addEventListener('resize', handleViewportResize);
  document.addEventListener('click', closeMobileMenu);
  document.addEventListener('click', closeProfileMenu);
  document.addEventListener('click', closeNotificationsMenu);
  syncNotifications();
});

onBeforeUnmount(() => {
  window.removeEventListener('storage', updateAuthState);
  window.removeEventListener('auth-changed', updateAuthState);
  window.removeEventListener('resize', handleViewportResize);
  document.removeEventListener('click', closeMobileMenu);
  document.removeEventListener('click', closeProfileMenu);
  document.removeEventListener('click', closeNotificationsMenu);
});

watch(
  () => route.fullPath,
  () => {
    closeAllMenus();
  }
);

function updateAuthState() {
  authVersion.value += 1;
  syncNotifications();
}

function closeAllMenus() {
  isMobileMenuOpen.value = false;
  isProfileMenuOpen.value = false;
  isNotificationsOpen.value = false;
}

function toggleMobileMenu() {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;

  if (!isMobileMenuOpen.value) {
    isProfileMenuOpen.value = false;
    isNotificationsOpen.value = false;
  }
}

function closeMobileMenu(event) {
  if (!isMobileMenuOpen.value) {
    return;
  }

  if (headerRoot.value && !headerRoot.value.contains(event.target)) {
    closeAllMenus();
  }
}

function handleViewportResize() {
  if (window.innerWidth > 768) {
    closeAllMenus();
  }
}

function toggleProfileMenu() {
  isProfileMenuOpen.value = !isProfileMenuOpen.value;
}

function closeProfileMenu(event) {
  if (profileMenu.value && !profileMenu.value.contains(event.target)) {
    isProfileMenuOpen.value = false;
  }
}

async function syncNotifications() {
  if (!localStorage.getItem('auth_token')) {
    notifications.value = [];
    unreadNotifications.value = 0;
    isNotificationsOpen.value = false;
    return;
  }

  try {
    const response = await getNotifications();
    notifications.value = response.notifications || [];
    unreadNotifications.value = response.unread_count || 0;
  } catch (e) {
    notifications.value = [];
    unreadNotifications.value = 0;
  }
}

function toggleNotificationsMenu() {
  isNotificationsOpen.value = !isNotificationsOpen.value;

  if (isNotificationsOpen.value) {
    syncNotifications();
  }
}

function closeNotificationsMenu(event) {
  if (notificationsMenu.value && !notificationsMenu.value.contains(event.target)) {
    isNotificationsOpen.value = false;
  }
}

async function openNotification(notification) {
  if (!notification.read_at) {
    try {
      await markNotificationRead(notification.id);
      await syncNotifications();
    } catch (e) {
      // ignore notification read errors in UI flow
    }
  }

  isNotificationsOpen.value = false;

  if (notification.link) {
    router.push(notification.link);
  }
}

async function logout() {
  if (localStorage.getItem('auth_token')) {
    await logoutUser().catch(() => {});
  }

  clearAuth();
  updateAuthState();
  closeAllMenus();
  router.push('/');
}
</script>

<template>
  <div class="page">
    <header ref="headerRoot" class="header">
      <div class="container header__inner">
        <RouterLink class="header__logo" to="/">
          <span class="header__logo-mark">SS</span>
          <span>SportaHub</span>
        </RouterLink>

        <button
          class="header__menu-toggle"
          type="button"
          :aria-expanded="isMobileMenuOpen"
          aria-label="Atvert navigaciju"
          @click.stop="toggleMobileMenu"
        >
          <span class="header__menu-toggle-line" :class="{ 'header__menu-toggle-line--top-open': isMobileMenuOpen }"></span>
          <span class="header__menu-toggle-line" :class="{ 'header__menu-toggle-line--middle-open': isMobileMenuOpen }"></span>
          <span class="header__menu-toggle-line" :class="{ 'header__menu-toggle-line--bottom-open': isMobileMenuOpen }"></span>
        </button>

        <nav
          class="header__nav"
          :class="{ 'header__nav--mobile-open': isMobileMenuOpen }"
          aria-label="Galvena navigacija"
        >
          <RouterLink to="/" active-class="active" exact-active-class="active">Sākums</RouterLink>
          <RouterLink to="/sekcijas" active-class="active">Sekcijas</RouterLink>
          <RouterLink to="/par-mums" active-class="active">Par mums</RouterLink>
          <RouterLink to="/kontakti" active-class="active">Kontakti</RouterLink>
        </nav>

        <div class="header__actions" :class="{ 'header__actions--mobile-open': isMobileMenuOpen }">
          <template v-if="!isAuth">
            <RouterLink class="btn btn--ghost btn--login" to="/login">Ieiet</RouterLink>
            <RouterLink class="btn btn--small btn--header-primary" to="/register">Reģistrēties</RouterLink>
            <RouterLink v-if="false" class="btn btn--small btn--header-primary" to="/register">Reģistrēties</RouterLink>
            <RouterLink
              v-if="false"
              class="btn btn--ghost btn--login"
              to="/verify-email"
            >
              Pārbaudi e-pastu
            </RouterLink>
            <span v-if="false" class="header__user"></span>
            <button v-if="false" class="btn btn--small btn--header-primary" type="button" @click="logout">
              Iziet
            </button>
          </template>

          <template v-else>
            <div ref="notificationsMenu" class="profile-menu">
              <button
                class="profile-menu__button profile-menu__button--notification"
                type="button"
                aria-label="Paziņojumi"
                :aria-expanded="isNotificationsOpen"
                @click="toggleNotificationsMenu"
              >
                <i class="bx bx-bell"></i>
                <span v-if="unreadNotifications" class="profile-menu__badge">{{ unreadNotifications }}</span>
              </button>

              <div v-if="isNotificationsOpen" class="profile-menu__dropdown profile-menu__dropdown--notifications">
                <div class="profile-menu__dropdown-title">Paziņojumi</div>
                <button
                  v-for="notification in notifications"
                  :key="notification.id"
                  class="profile-menu__dropdown-item profile-menu__dropdown-item--notification"
                  :class="{ 'profile-menu__dropdown-item--unread': !notification.read_at }"
                  type="button"
                  @click="openNotification(notification)"
                >
                  <span>{{ notification.title }}</span>
                  <small>{{ notification.message }}</small>
                </button>
                <div v-if="!notifications.length" class="profile-menu__dropdown-empty">
                  Nav jaunu paziņojumu
                </div>
              </div>
            </div>

            <div ref="profileMenu" class="profile-menu">
              <button
                class="profile-menu__button"
                type="button"
                aria-label="Profils"
                :aria-expanded="isProfileMenuOpen"
                @click="toggleProfileMenu"
              >
                <UserAvatar
                  class="header-avatar"
                  :src="headerAvatarUrl"
                  :name="currentUser?.name"
                  :size="40"
                />
              </button>

              <div v-if="isProfileMenuOpen" class="profile-menu__dropdown">
                <RouterLink
                  class="profile-menu__dropdown-item"
                  to="/profile"
                  @click="isProfileMenuOpen = false"
                >
                  <i class="bx bx-user"></i>
                  <span>Profils</span>
                </RouterLink>
                <RouterLink
                  v-if="isAdmin"
                  class="profile-menu__dropdown-item"
                  to="/admin"
                  @click="isProfileMenuOpen = false"
                >
                  <i class="bx bx-shield-quarter"></i>
                  <span>Admin Panel</span>
                </RouterLink>
                <RouterLink
                  v-if="canAccessOrganizator"
                  class="profile-menu__dropdown-item"
                  to="/organizator"
                  @click="isProfileMenuOpen = false"
                >
                  <i class="bx bx-run"></i>
                  <span>Organizatora panelis</span>
                </RouterLink>
                <button class="profile-menu__dropdown-item" type="button">
                  <i class="bx bx-moon"></i>
                  <span>Mainīt tēmu</span>
                </button>
                <div class="profile-menu__dropdown-divider"></div>
                <button class="profile-menu__dropdown-item profile-menu__dropdown-item--danger" type="button" @click="logout">
                  <i class="bx bx-log-out"></i>
                  <span>Iziet</span>
                </button>
              </div>
            </div>
            <RouterLink v-if="false" class="btn btn--ghost btn--login" to="/login">Ieiet</RouterLink>
            <RouterLink v-if="false" class="btn btn--small btn--header-primary" to="/register">Reģistrēties</RouterLink>
          </template>
        </div>
      </div>
    </header>

    <main>
      <template v-if="route.name === 'home'">
        <section class="hero" :style="{ backgroundImage: `url('/images/sportHero.jpg')` }">
          <div class="hero__container">
            <div class="hero__content">
              <p class="section-label">Sports visai ģimenei</p>
              <h1>Atrodi savu sporta sekciju</h1>
              <p class="hero__text">
                Izvēlies, salīdzini un piesakies nodarbībām visā Latvijā
              </p>

              <div class="hero__buttons">
                <a class="btn" href="#sections">Skatīt sekcijas</a>
                <RouterLink class="btn btn--outline" to="/register">Pievienoties</RouterLink>
              </div>
            </div>
          </div>

          <div class="hero-wave" aria-hidden="true">
            <svg viewBox="0 0 1440 150" preserveAspectRatio="none">
              <path
                d="M0,80 C360,140 1080,0 1440,80 L1440,150 L0,150 Z"
                fill="#ffffff"
              />
            </svg>
          </div>
        </section>

        <section class="section" id="about">
          <div class="container">
            <div class="section-heading">
              <p class="section-label">Priekšrocības</p>
              <h2>Viss nepieciešamais vienuviet</h2>
            </div>

            <div class="cards-grid cards-grid--four">
              <article class="info-card">
                <i class="bx bx-search"></i>
                <h3>Ērta meklēšana</h3>
                <p>Atrodi nodarbības pēc sporta veida, pilsētas vai vecuma grupas.</p>
              </article>

              <article class="info-card">
                <i class="bx bx-football"></i>
                <h3>Dažādi sporta veidi</h3>
                <p>Izvēlies starp komandu sportu, cīņas mākslu, peldēšanu un fitnesu.</p>
              </article>

              <article class="info-card">
                <i class="bx bx-calendar-check"></i>
                <h3>Pieteikšanās tiešsaistē</h3>
                <p>Piesakies treniņiem bez liekiem zvaniem un garām sarakstēm.</p>
              </article>

              <article class="info-card">
                <i class="bx bx-group"></i>
                <h3>Nodarbības bērniem un pieaugušajiem</h3>
                <p>Sporta iespējas iesācējiem, ģimenei un tiem, kuri grib progresu.</p>
              </article>
            </div>
          </div>
        </section>

        <section class="section section--dark">
          <div class="container">
            <div class="section-heading">
              <p class="section-label">Kā tas strādā</p>
              <h2>Trīs vienkārši soļi</h2>
            </div>

            <div class="steps">
              <article class="step-card">
                <span>01</span>
                <h3>Atrodi sekciju</h3>
                <p>Apskati populārākās sporta sekcijas un atrodi sev piemērotu variantu.</p>
              </article>

              <article class="step-card">
                <span>02</span>
                <h3>Reģistrējies</h3>
                <p>Izvēlies nodarbību un atstāj pieteikumu dažās minūtēs.</p>
              </article>

              <article class="step-card">
                <span>03</span>
                <h3>Sāc trenēties</h3>
                <p>Sazinies ar organizatoru un sāc savu regulāro sporta ceļu.</p>
              </article>
            </div>
          </div>
        </section>
        <section class="cta">
          <div class="container cta__inner">
            <div>
              <p class="section-label">Gatavs sākt?</p>
              <h2>Sāc savu sporta ceļu jau šodien</h2>
            </div>
            <RouterLink class="btn" to="/register">Reģistrēties</RouterLink>
          </div>
        </section>
      </template>

      <RouterView v-else />
    </main>

    <footer class="footer" id="contacts">
      <div class="container footer__inner">
        <div class="footer__brand">
          <RouterLink class="header__logo" to="/">
            <span class="header__logo-mark">SS</span>
            <span>Sporta Sekcijas</span>
          </RouterLink>
          <p>Sporta sekciju katalogs bērniem un pieaugušajiem visā Latvijā.</p>
        </div>

        <div class="footer__column">
          <h3>Saites</h3>
          <RouterLink to="/">Sākums</RouterLink>
          <RouterLink to="/sekcijas">Sekcijas</RouterLink>
          <RouterLink to="/par-mums">Par mums</RouterLink>
        </div>

        <div class="footer__column">
          <h3>Kontakti</h3>
          <a href="mailto:info@sportasekcijas.lv">info@sportasekcijas.lv</a>
          <a href="tel:+37120000000">+371 20000000</a>
        </div>

        <div class="footer__column">
          <h3>Seko mums</h3>
          <div class="footer__socials">
            <a href="#" aria-label="Instagram"><i class="bx bxl-instagram"></i></a>
            <a href="#" aria-label="Facebook"><i class="bx bxl-facebook"></i></a>
            <a href="#" aria-label="TikTok"><i class="bx bxl-tiktok"></i></a>
          </div>
        </div>
      </div>

      <div class="container footer__bottom">
        <p>© 2026 Sporta Sekcijas. Visas tiesības aizsargātas.</p>
      </div>
    </footer>
  </div>
</template>
