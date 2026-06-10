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


## Sistēmas funkcionalitāte

SportaHub nodrošina:

* sporta sekciju meklēšanu;
* sporta sekciju filtrēšanu pēc dažādiem kritērijiem;
* lietotāju reģistrāciju un autentifikāciju;
* lietotāju profilu pārvaldību;
* paziņojumu sistēmu;
* sporta sekciju pārvaldību;
* administrēšanas funkcionalitāti;
* progresīvās tīmekļa lietotnes (PWA) atbalstu.


## Drošība

Projektā ir ieviesti šādi drošības pasākumi:

* lietotāju autentifikācija;
* paroļu šifrēšana;
* ievaddatu validācija;
* aizsardzība pret CSRF uzbrukumiem;
* Laravel drošības mehānismu izmantošana.

## PWA atbalsts

SportaHub atbalsta Progressive Web Application tehnoloģiju:

* manifest.json konfigurācija;
* Service Worker;
* instalēšana kā lietotne;
* kešošanas mehānismi.
