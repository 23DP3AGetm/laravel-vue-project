# SportaHub

SportaHub ir tīmekļa platforma sporta sekciju meklēšanai un salīdzināšanai Latvijā. Sistēma palīdz lietotājiem atrast piemērotas sporta nodarbības, iepazīties ar sekciju informāciju, pieteikties nodarbībām un saņemt paziņojumus par aktivitātēm sistēmā.

## Tehnoloģijas

### Backend

* PHP 8.2
* Laravel 12
* Laravel Sanctum
* Laravel Breeze

### Frontend

* Vue 3
* Vue Router
* Vuetify
* Tailwind CSS 4
* Axios
* Vite

### Datu bāze

* MySQL

### Serveris

* Ubuntu Server
* Nginx
* Git
* Hetzner Cloud

## Ko projekts dara

* Lietotāju reģistrācija un autentifikācija.
* Sporta sekciju pārlūkošana.
* Sporta sekciju meklēšana un filtrēšana.
* Sporta sekciju aprakstu, adrešu un grafiku apskate.
* Pieteikšanās sporta sekcijām.
* Atsauksmju pievienošana un apskate.
* Lietotāju paziņojumu sistēma.
* Administrācijas funkcionalitāte.
* Responsīvs dizains dažādām ierīcēm.
* PWA (Progressive Web Application) atbalsts.

## Datu bāzes struktūra

Galvenās sistēmas tabulas:

* users
* sections
* section_addresses
* section_schedules
* section_reviews
* section_applications
* user_notifications
* admin_actions

Papildus tiek izmantotas Laravel sistēmas tabulas:

* migrations
* password_reset_tokens
* personal_access_tokens

## Funkcionalitāte

### Lietotāji

* Reģistrācija.
* Pieslēgšanās sistēmai.
* Personīgais profils.
* Paziņojumu saņemšana.
* Pieteikšanās sporta sekcijām.

### Sporta sekcijas

* Sporta sekciju katalogs.
* Informācija par sekciju.
* Adreses un atrašanās vieta.
* Nodarbību grafiki.
* Atsauksmes un vērtējumi.

### Administrācija

* Sporta sekciju pārvaldība.
* Lietotāju darbību uzraudzība.
* Sistēmas darbību žurnāls.
* Administratīvo darbību uzskaite.

## PWA atbalsts

Projektā ir ieviests:

* manifest.json
* Service Worker
* Instalēšana kā lietotne
* Offline kešošanas atbalsts

## Projekta adrese

https://sportahub.site

## Autors

Aleksandrs Getmaņenko

2026
