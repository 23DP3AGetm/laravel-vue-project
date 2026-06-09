<template>
  <section class="section page-section contacts-page">
    <div class="container">
      <div class="contacts-page__inner">
        <p class="section-label">Kontakti</p>
        <h1>Sazinies ar mums</h1>

        <div class="contacts-page__content">
          <p>
            Ja tev ir jautājumi par sporta sekcijām, sadarbību vai platformas darbību,
            raksti mums uz
            <a href="mailto:info@sportasekcijas.lv">info@sportasekcijas.lv</a>
            vai zvani pa tālruni
            <a href="tel:+37124874327">+371 24874327</a>.
          </p>

          <p>
            Mēs centīsimies atbildēt pēc iespējas ātrāk un palīdzēt atrast nepieciešamo
            informāciju.
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.contacts-page {
  background: #f8fafc;
}

.contacts-page__inner {
  max-width: 760px;
}

.contacts-page h1 {
  margin: 0 0 1.5rem;
  color: #0f172a;
  font-size: clamp(2rem, 4vw, 3rem);
  line-height: 1.15;
  letter-spacing: -0.03em;
}

.contacts-page__content {
  display: grid;
  gap: 1rem;
}

.contacts-page__content p {
  margin: 0;
  color: #475569;
  font-size: 1.05rem;
  line-height: 1.8;
}

.contacts-page__content a {
  color: #334155;
  font-weight: 600;
  text-decoration: none;
}

.contacts-page__content a:hover {
  text-decoration: underline;
}

@media (max-width: 640px) {
  .contacts-page h1 {
    font-size: clamp(1.8rem, 8vw, 2.4rem);
  }

  .contacts-page__content p {
    font-size: 1rem;
    line-height: 1.7;
  }
}
</style>
