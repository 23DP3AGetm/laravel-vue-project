import { createRouter, createWebHistory } from 'vue-router';
import AboutPage from './pages/AboutPage.vue';
import SectionsPage from './pages/SectionsPage.vue';
import ContactsPage from './pages/ContactsPage.vue';
import RegisterPage from './pages/RegisterPage.vue';
import LoginPage from './pages/LoginPage.vue';
import ForgotPasswordPage from './pages/ForgotPasswordPage.vue';
import ResetPasswordPage from './pages/ResetPasswordPage.vue';
import VerifyEmailConfirmPage from './pages/VerifyEmailConfirmPage.vue';
import EmailVerifiedPage from './pages/EmailVerifiedPage.vue';
import ProfilePage from './pages/ProfilePage.vue';
import AdminPage from './pages/AdminPage.vue';
import OrganizatorPage from './pages/OrganizatorPage.vue';
import SectionDetailPage from './pages/SectionDetailPage.vue';
import { removeToken } from './services/api';
import { getUser } from './services/authService';
import { auth } from './auth';

const routes = [
  {
    path: '/',
    name: 'home',
    component: { render: () => null },
  },
  {
    path: '/par-mums',
    name: 'about',
    component: AboutPage,
  },
  {
    path: '/sekcijas',
    name: 'sections',
    component: SectionsPage,
  },
  {
    path: '/sekcija/:slug',
    name: 'section-detail',
    component: SectionDetailPage,
  },
  {
    path: '/kontakti',
    name: 'contacts',
    component: ContactsPage,
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterPage,
  },
  {
    path: '/login',
    name: 'login',
    component: LoginPage,
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: ForgotPasswordPage,
  },
  {
    path: '/reset-password',
    name: 'reset-password',
    component: ResetPasswordPage,
  },
  {
    path: '/verify-email',
    name: 'verify-email',
    component: VerifyEmailConfirmPage,
  },
  {
    path: '/email-verified',
    name: 'email-verified',
    component: EmailVerifiedPage,
  },
  {
    path: '/profile',
    name: 'profile',
    component: ProfilePage,
  },
  {
    path: '/admin',
    name: 'admin',
    component: AdminPage,
  },
  {
    path: '/organizator',
    name: 'organizator',
    component: OrganizatorPage,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
});

router.beforeEach(async (to, from, next) => {
  const token = localStorage.getItem('auth_token');

  if (to.path === '/verify-email' && to.query.token) {
    return next();
  }

  if (!token) {
    auth.user = null;

    if (['/profile', '/admin', '/organizator'].includes(to.path)) {
      return next('/login');
    }

    return next();
  }

  try {
    const user = await getUser();
    auth.user = user;

    if (user.email_verified_at === null && to.path !== '/verify-email') {
      return next('/verify-email');
    }

    if (user.email_verified_at !== null && to.path === '/verify-email') {
      return next('/');
    }

    if (to.path === '/organizator' && user.role !== 'organizator') {
      return next('/');
    }

    return next();
  } catch (e) {
    auth.user = null;
    removeToken();
    return next();
  }
});

export default router;
