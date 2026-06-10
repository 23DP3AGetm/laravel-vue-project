# SportaHub

SportaHub ir tīmekļa platforma sporta sekciju meklēšanai un salīdzināšanai Latvijā.

**Projekta adrese:** https://sportahub.site

**Autors:** Aleksandrs Getmanenko

**Projekta mērķis:** palīdzēt lietotājiem atrast piemērotas sporta nodarbības pēc sporta veida, atrašanās vietas un citiem kritērijiem, kā arī nodrošināt sporta organizācijām iespēju publicēt informāciju par savām sekcijām.

## Lietotāju lomas

### Viesis

* Pārlūko sporta sekcijas
* Veic meklēšanu un filtrēšanu

### Reģistrēts lietotājs

* Pārvalda savu profilu
* Saņem paziņojumus
* Pievienojas sporta sekcijām

### Administrators

* Pārvalda sporta sekcijas
* Pārvalda lietotājus
* Rediģē un dzēš sistēmas datus


## Galvenās funkcijas

* Sporta sekciju katalogs
* Sporta sekciju meklēšana
* Datu filtrēšana
* Lietotāju reģistrācija un autentifikācija
* Lietotāju profili
* Paziņojumu sistēma
* Administrācijas funkcionalitāte
* Responsīvs dizains
* PWA (Progressive Web Application) atbalsts

## Izmantotās tehnoloģijas

### Backend

* PHP 8+
* Laravel 12
* MySQL

### Frontend

* Vue 3
* Vue Router
* Vite
* JavaScript
* HTML5
* CSS3

### Serveris

* Ubuntu Server
* Nginx
* Git
* Hetzner Cloud

## Sistēmas prasības

* PHP 8.2+
* Composer
* Node.js 18+
* npm
* MySQL 8+

## Projekta uzstādīšana

### 1. Klonēt repozitoriju

```bash
git clone https://github.com/23DP3AGetm/laravel-vue-project.git
cd laravel-vue-project
```

### 2. Instalēt PHP atkarības

```bash
composer install
```

### 3. Instalēt JavaScript atkarības

```bash
npm install
```

### 4. Izveidot .env failu

```bash
cp .env.example .env
```

### 5. Ģenerēt lietotnes atslēgu

```bash
php artisan key:generate
```

### 6. Konfigurēt datubāzi

Norādīt datubāzes parametrus `.env` failā:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sportahub
DB_USERNAME=root
DB_PASSWORD=
```

### 7. Palaist migrācijas

```bash
php artisan migrate
```

### 8. Palaist izstrādes serveri

Laravel:

```bash
php artisan serve
```

Frontend:

```bash
npm run dev
```

## Projekta struktūra

```text
app/
bootstrap/
config/
database/
public/
resources/
routes/
storage/
```

## PWA atbalsts

Projektā ir ieviests:

* manifest.json
* Service Worker
* Instalēšana kā lietotne
* Offline kešošanas atbalsts

## Autors

Aleksandrs Getmaņenko

2026
