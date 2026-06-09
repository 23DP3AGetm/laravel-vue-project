<template>
  <section class="section page-section about-page">
    <div class="container about-page__container">
      <p class="section-label">Par mums</p>
      <h1>SportaHub palīdz atrast piemērotas sporta nodarbības vienuviet</h1>

      <div class="about-page__content">
        <p>
          SportaHub ir platforma, kas palīdz bērniem, vecākiem un pieaugušajiem ērti atrast
          sporta sekcijas visā Latvijā. Mēs apkopojam svarīgāko informāciju vienā vietā, lai
          sporta izvēle būtu vienkāršāka, saprotamāka un ātrāka.
        </p>

        <p>
          Mūsu mērķis ir padarīt ceļu līdz piemērotai nodarbībai pēc iespējas skaidrāku:
          no pirmās intereses līdz saziņai ar organizatoru un pieteikšanās procesam.
        </p>
      </div>

      <div class="about-page__actions">
        <RouterLink class="btn" to="/sekcijas">Apskatīt sekcijas</RouterLink>
        <RouterLink class="btn btn--outline" to="/kontakti">Sazināties ar mums</RouterLink>
      </div>
    </div>
  </section>
</template>

<style scoped>
.about-page {
  background: #f8fafc;
}

.about-page__container {
  max-width: 760px;
}

.about-page h1 {
  margin: 0 0 1.5rem;
  color: #0f172a;
  font-size: clamp(2rem, 4vw, 3rem);
  line-height: 1.15;
  letter-spacing: -0.03em;
}

.about-page__content {
  display: grid;
  gap: 1rem;
}

.about-page__content p {
  margin: 0;
  color: #475569;
  font-size: 1.05rem;
  line-height: 1.8;
}

.about-page__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin-top: 2rem;
}

@media (max-width: 640px) {
  .about-page h1 {
    font-size: clamp(1.8rem, 8vw, 2.4rem);
  }

  .about-page__content p {
    font-size: 1rem;
    line-height: 1.7;
  }
}
</style>
