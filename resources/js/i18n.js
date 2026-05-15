import { computed, reactive } from 'vue';

const STORAGE_KEY = 'sportahub_language';
const DEFAULT_LOCALE = 'lv';
const SUPPORTED_LOCALES = ['lv', 'en'];

const messages = {
  lv: {
    language: {
      label: 'Valoda / Language',
      change: 'Mainīt valodu',
      lv: 'Latviešu (LV)',
      en: 'English (EN)',
    },
    nav: {
      aria: 'Galvenā navigācija',
      home: 'Sākums',
      sections: 'Sekcijas',
      about: 'Par mums',
      contacts: 'Kontakti',
    },
    authButtons: {
      login: 'Ieiet',
      register: 'Reģistrēties',
      logout: 'Iziet',
      verifyEmail: 'Pārbaudi e-pastu',
    },
    notifications: {
      aria: 'Paziņojumi',
      title: 'Paziņojumi',
      empty: 'Nav jaunu paziņojumu',
    },
    menu: {
      profileAria: 'Profils',
      profile: 'Profils',
      admin: 'Admin Panel',
      organizer: 'Organizatora panelis',
      theme: 'Mainīt tēmu',
      logout: 'Iziet',
    },
    home: {
      heroKicker: 'Sports visai ģimenei',
      heroTitle: 'Atrodi savu sporta sekciju',
      heroText: 'Izvēlies, salīdzini un piesakies nodarbībām visā Latvijā',
      heroPrimary: 'Skatīt sekcijas',
      heroSecondary: 'Pievienoties',
      benefitsKicker: 'Priekšrocības',
      benefitsTitle: 'Viss nepieciešamais vienuviet',
      howItWorksKicker: 'Kā tas strādā',
      howItWorksTitle: 'Trīs vienkārši soļi',
      sectionsKicker: 'Sekcijas',
      sectionsTitle: 'Populāri sporta veidi',
      sectionsLink: 'Skatīt visas',
      ctaKicker: 'Gatavs sākt?',
      ctaTitle: 'Sāc savu sporta ceļu jau šodien',
      ctaButton: 'Reģistrēties',
      benefits: [
        {
          icon: 'bx bx-search',
          title: 'Ērta meklēšana',
          text: 'Atrodi nodarbības pēc sporta veida, pilsētas vai vecuma grupas.',
        },
        {
          icon: 'bx bx-football',
          title: 'Dažādi sporta veidi',
          text: 'Izvēlies starp komandu sportu, cīņas mākslu, peldēšanu un fitnesu.',
        },
        {
          icon: 'bx bx-calendar-check',
          title: 'Pieteikšanās tiešsaistē',
          text: 'Piesakies treniņiem bez liekiem zvaniem un garām sarakstēm.',
        },
        {
          icon: 'bx bx-group',
          title: 'Nodarbības bērniem un pieaugušajiem',
          text: 'Sporta iespējas iesācējiem, ģimenei un tiem, kuri grib progresu.',
        },
      ],
      steps: [
        {
          number: '01',
          title: 'Atrodi sekciju',
          text: 'Apskati populārākās sporta sekcijas un atrodi sev piemērotu variantu.',
        },
        {
          number: '02',
          title: 'Reģistrējies',
          text: 'Izvēlies nodarbību un atstāj pieteikumu dažās minūtēs.',
        },
        {
          number: '03',
          title: 'Sāc trenēties',
          text: 'Sazinies ar organizatoru un sāc savu regulāro sporta ceļu.',
        },
      ],
      sports: [
        {
          title: 'Futbols',
          text: 'Komandas gars, ātrums un regulāri treniņi dažādiem vecumiem.',
          image: 'https://images.unsplash.com/photo-1431324155629-1a6deb1dec8d?auto=format&fit=crop&w=700&q=80',
        },
        {
          title: 'Peldēšana',
          text: 'Veselībai, izturībai un drošai kustībai ūdenī.',
          image: 'https://images.unsplash.com/photo-1530549387789-4c1017266635?auto=format&fit=crop&w=700&q=80',
        },
        {
          title: 'Bokss',
          text: 'Disciplīna, spēks un pārliecība par savām spējām.',
          image: 'https://images.unsplash.com/photo-1549719386-74dfcbf7dbed?auto=format&fit=crop&w=700&q=80',
        },
        {
          title: 'Fitness',
          text: 'Aktīvi treniņi ikdienas enerģijai un labākai formai.',
          image: 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=700&q=80',
        },
      ],
    },
    footer: {
      brand: 'Sporta Sekcijas',
      description: 'Sporta sekciju katalogs bērniem un pieaugušajiem visā Latvijā.',
      links: 'Saites',
      contacts: 'Kontakti',
      socials: 'Seko mums',
      copyright: '© 2026 Sporta Sekcijas. Visas tiesības aizsargātas.',
    },
    pages: {
      about: {
        kicker: 'Par mums',
        title: 'Sporta iespējas vienuviet',
        text: 'SportaHub palīdz atrast piemērotas sporta nodarbības bērniem un pieaugušajiem visā Latvijā.',
      },
      contacts: {
        kicker: 'Kontakti',
        title: 'Sazinies ar mums',
        text: 'Raksti mums uz info@sportasekcijas.lv vai zvani +371 20000000.',
      },
    },
    auth: {
      email: 'E-pasts',
      password: 'Parole',
      login: {
        kicker: 'Ieiet',
        title: 'Pieslēdzies kontam',
        text: 'Ievadi savu e-pastu un paroli, lai turpinātu.',
        submit: 'Ieiet',
        loading: 'Lūdzu, uzgaidi...',
        forgot: 'Aizmirsi paroli?',
        noAccount: 'Nav konta?',
        register: 'Reģistrēties',
      },
      register: {
        kicker: 'Reģistrācija',
        title: 'Izveido kontu',
        text: 'Aizpildi formu, lai izveidotu kontu',
        name: 'Lietotājvārds',
        confirmPassword: 'Atkārtot paroli',
        submit: 'Reģistrēties',
        loading: 'Lūdzu, uzgaidi...',
        hasAccount: 'Jau ir konts?',
        login: 'Ieiet',
      },
      forgot: {
        kicker: 'Paroles atjaunošana',
        title: 'Aizmirsi paroli?',
        text: 'Ievadi savu e-pastu, un mēs nosūtīsim drošu paroles atjaunošanas saiti.',
        submit: 'Nosūtīt atjaunošanas saiti',
        loading: 'Nosūta...',
        remember: 'Atceries paroli?',
        login: 'Ieiet',
      },
      reset: {
        kicker: 'Jauna parole',
        title: 'Atjauno paroli',
        text: 'Ievadi jaunu paroli savam SportaHub kontam.',
        newPassword: 'Jauna parole',
        confirmPassword: 'Atkārto paroli',
        submit: 'Atjaunot paroli',
        loading: 'Atjauno...',
      },
      verify: {
        kicker: 'E-pasta apstiprināšana',
        title: 'Lūdzu apstiprini savu e-pastu',
        text: 'Mēs nosūtījām apstiprinājuma vēstuli. Atver e-pastu un nospied apstiprinājuma saiti, lai pabeigtu reģistrāciju.',
        resendLoading: 'Nosūta...',
      },
      verifyConfirm: {
        kicker: 'E-pasta apstiprināšana',
        title: 'Apstiprinām e-pastu',
        text: 'Pārbaudām apstiprinājuma saiti. Tas aizņems tikai mirkli.',
        retry: 'Mēģināt vēlreiz',
        retryLoading: 'Pārbauda...',
        loading: 'Apstiprināšana...',
        done: 'E-pasts apstiprināts.',
      },
      verified: {
        kicker: 'E-pasts apstiprināts',
        title: 'Pasts veiksmīgi apstiprināts',
        text: 'Tagad jūs varat turpināt lietot SportaHub bez ierobežojumiem.',
        cta: 'Pāriet uz sākumu',
      },
    },
  },
  en: {
    language: {
      label: 'Language',
      change: 'Change language',
      lv: 'Latviešu (LV)',
      en: 'English (EN)',
    },
    nav: {
      aria: 'Main navigation',
      home: 'Home',
      sections: 'Sections',
      about: 'About us',
      contacts: 'Contacts',
    },
    authButtons: {
      login: 'Log in',
      register: 'Sign up',
      logout: 'Log out',
      verifyEmail: 'Verify email',
    },
    notifications: {
      aria: 'Notifications',
      title: 'Notifications',
      empty: 'No new notifications',
    },
    menu: {
      profileAria: 'Profile',
      profile: 'Profile',
      admin: 'Admin Panel',
      organizer: 'Organizer panel',
      theme: 'Change theme',
      logout: 'Log out',
    },
    home: {
      heroKicker: 'Sports for the whole family',
      heroTitle: 'Find your sports section',
      heroText: 'Choose, compare, and apply for activities across Latvia',
      heroPrimary: 'Browse sections',
      heroSecondary: 'Join now',
      benefitsKicker: 'Benefits',
      benefitsTitle: 'Everything you need in one place',
      howItWorksKicker: 'How it works',
      howItWorksTitle: 'Three simple steps',
      sectionsKicker: 'Sections',
      sectionsTitle: 'Popular sports',
      sectionsLink: 'View all',
      ctaKicker: 'Ready to start?',
      ctaTitle: 'Start your sports journey today',
      ctaButton: 'Sign up',
      benefits: [
        {
          icon: 'bx bx-search',
          title: 'Easy search',
          text: 'Find activities by sport, city, or age group.',
        },
        {
          icon: 'bx bx-football',
          title: 'Different sports',
          text: 'Choose between team sports, martial arts, swimming, and fitness.',
        },
        {
          icon: 'bx bx-calendar-check',
          title: 'Online applications',
          text: 'Apply for training without extra calls or long waiting lists.',
        },
        {
          icon: 'bx bx-group',
          title: 'For kids and adults',
          text: 'Sports opportunities for beginners, families, and anyone who wants progress.',
        },
      ],
      steps: [
        {
          number: '01',
          title: 'Find a section',
          text: 'Browse popular sports sections and choose the best fit for you.',
        },
        {
          number: '02',
          title: 'Register',
          text: 'Choose an activity and submit your application in just a few minutes.',
        },
        {
          number: '03',
          title: 'Start training',
          text: 'Contact the organizer and begin your regular sports routine.',
        },
      ],
      sports: [
        {
          title: 'Football',
          text: 'Team spirit, speed, and regular training for different ages.',
          image: 'https://images.unsplash.com/photo-1431324155629-1a6deb1dec8d?auto=format&fit=crop&w=700&q=80',
        },
        {
          title: 'Swimming',
          text: 'For health, endurance, and confident movement in the water.',
          image: 'https://images.unsplash.com/photo-1530549387789-4c1017266635?auto=format&fit=crop&w=700&q=80',
        },
        {
          title: 'Boxing',
          text: 'Discipline, strength, and confidence in your abilities.',
          image: 'https://images.unsplash.com/photo-1549719386-74dfcbf7dbed?auto=format&fit=crop&w=700&q=80',
        },
        {
          title: 'Fitness',
          text: 'Active workouts for daily energy and better form.',
          image: 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=700&q=80',
        },
      ],
    },
    footer: {
      brand: 'Sporta Sections',
      description: 'A sports activities catalog for children and adults across Latvia.',
      links: 'Links',
      contacts: 'Contacts',
      socials: 'Follow us',
      copyright: '© 2026 Sporta Sections. All rights reserved.',
    },
    pages: {
      about: {
        kicker: 'About us',
        title: 'Sports opportunities in one place',
        text: 'SportaHub helps people find suitable sports activities for children and adults across Latvia.',
      },
      contacts: {
        kicker: 'Contacts',
        title: 'Get in touch',
        text: 'Write to us at info@sportasekcijas.lv or call +371 20000000.',
      },
    },
    auth: {
      email: 'Email',
      password: 'Password',
      login: {
        kicker: 'Log in',
        title: 'Sign in to your account',
        text: 'Enter your email and password to continue.',
        submit: 'Log in',
        loading: 'Please wait...',
        forgot: 'Forgot your password?',
        noAccount: "Don't have an account?",
        register: 'Sign up',
      },
      register: {
        kicker: 'Registration',
        title: 'Create an account',
        text: 'Fill out the form to create your account',
        name: 'Username',
        confirmPassword: 'Repeat password',
        submit: 'Sign up',
        loading: 'Please wait...',
        hasAccount: 'Already have an account?',
        login: 'Log in',
      },
      forgot: {
        kicker: 'Password recovery',
        title: 'Forgot your password?',
        text: 'Enter your email and we will send you a secure password reset link.',
        submit: 'Send reset link',
        loading: 'Sending...',
        remember: 'Remember your password?',
        login: 'Log in',
      },
      reset: {
        kicker: 'New password',
        title: 'Reset your password',
        text: 'Enter a new password for your SportaHub account.',
        newPassword: 'New password',
        confirmPassword: 'Repeat password',
        submit: 'Reset password',
        loading: 'Resetting...',
      },
      verify: {
        kicker: 'Email verification',
        title: 'Please verify your email',
        text: 'We sent a verification email. Open it and click the verification link to complete your registration.',
        resendLoading: 'Sending...',
      },
      verifyConfirm: {
        kicker: 'Email verification',
        title: 'Verifying your email',
        text: 'We are checking the verification link. This will take only a moment.',
        retry: 'Try again',
        retryLoading: 'Checking...',
        loading: 'Verifying...',
        done: 'Email verified.',
      },
      verified: {
        kicker: 'Email verified',
        title: 'Email verified successfully',
        text: 'You can now continue using SportaHub without restrictions.',
        cta: 'Go to home',
      },
    },
  },
};

function getInitialLocale() {
  if (typeof window === 'undefined') {
    return DEFAULT_LOCALE;
  }

  const storedLocale = window.localStorage.getItem(STORAGE_KEY);

  if (storedLocale && SUPPORTED_LOCALES.includes(storedLocale)) {
    return storedLocale;
  }

  return DEFAULT_LOCALE;
}

function setDocumentLanguage(locale) {
  if (typeof document !== 'undefined') {
    document.documentElement.lang = locale;
  }
}

function resolveMessage(locale, key) {
  return key.split('.').reduce((value, part) => value?.[part], messages[locale]);
}

const i18nState = reactive({
  locale: getInitialLocale(),
});

setDocumentLanguage(i18nState.locale);

export function setLanguage(locale) {
  if (!SUPPORTED_LOCALES.includes(locale) || i18nState.locale === locale) {
    return;
  }

  i18nState.locale = locale;

  if (typeof window !== 'undefined') {
    window.localStorage.setItem(STORAGE_KEY, locale);
  }

  setDocumentLanguage(locale);
}

export function useI18n() {
  const locale = computed(() => i18nState.locale);

  function t(key) {
    return resolveMessage(i18nState.locale, key) ?? resolveMessage(DEFAULT_LOCALE, key) ?? key;
  }

  function pickText(lv, en) {
    return i18nState.locale === 'en' ? en : lv;
  }

  return {
    locale,
    setLanguage,
    t,
    pickText,
    languages: [
      { code: 'lv', labelKey: 'language.lv' },
      { code: 'en', labelKey: 'language.en' },
    ],
  };
}
