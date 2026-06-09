<template>
  <div class="about-page">
    <section class="section page-section about-hero">
      <div class="container about-hero__grid">
        <div class="about-hero__content">
          <p class="section-label">Par mums</p>
          <h1>SportaHub apvieno sporta iespējas vienā saprotamā vietā</h1>
          <p class="about-hero__text">
            Mēs palīdzam bērniem, vecākiem un pieaugušajiem ātri atrast piemērotas sporta
            nodarbības visā Latvijā, salīdzināt variantus un pieteikties bez liekas
            meklēšanas dažādās vietnēs un sociālajos tīklos.
          </p>

          <div class="about-hero__actions">
            <RouterLink class="btn" to="/sekcijas">Apskatīt sekcijas</RouterLink>
            <RouterLink class="btn btn--outline" to="/kontakti">Sazināties ar mums</RouterLink>
          </div>
        </div>

        <aside class="about-hero__panel">
          <div class="about-highlight">
            <span class="about-highlight__eyebrow">Mūsu mērķis</span>
            <p>
              Padarīt sporta izvēli skaidru, uzticamu un pieejamu ikvienam, kurš vēlas sākt
              trenēties vai atrast sev labāko vidi izaugsmei.
            </p>
          </div>

          <div class="about-stats">
            <article class="about-stat">
              <strong>Ērtāk</strong>
              <span>Visa svarīgākā informācija vienuviet</span>
            </article>
            <article class="about-stat">
              <strong>Ātrāk</strong>
              <span>Mazāk laika meklēšanai, vairāk sportam</span>
            </article>
            <article class="about-stat">
              <strong>Drošāk</strong>
              <span>Pārskatāma izvēle ģimenēm un sportot gribētājiem</span>
            </article>
          </div>
        </aside>
      </div>
    </section>

    <section class="section about-story">
      <div class="container about-story__grid">
        <div>
          <p class="section-label">Ko mēs darām</p>
          <h2>Ne tikai katalogs, bet skaidrs starts sportam</h2>
        </div>

        <div class="about-story__text">
          <p>
            SportaHub ir veidots, lai cilvēkiem būtu vieglāk atrast sev piemērotu sporta
            veidu, saprast nodarbību piedāvājumu un ātri sazināties ar organizatoriem.
          </p>
          <p>
            Mēs savienojam sporta sekcijas un cilvēkus vienā digitālā vidē, kur viss ir
            saprotams: no sporta veida un atrašanās vietas līdz pieteikšanās iespējām.
          </p>
        </div>
      </div>
    </section>

    <section class="section about-values">
      <div class="container">
        <div class="section-heading">
          <p class="section-label">Kāpēc SportaHub</p>
          <h2>Izveidots tā, lai izvēle būtu vienkārša</h2>
        </div>

        <div class="about-cards">
          <article class="about-card">
            <span class="about-card__icon">01</span>
            <h3>Pārskatāma izvēle</h3>
            <p>
              Atrodi sekcijas pēc sporta veida, pilsētas un sev svarīgākajiem kritērijiem
              bez haotiskas informācijas meklēšanas.
            </p>
          </article>

          <article class="about-card">
            <span class="about-card__icon">02</span>
            <h3>Ģimenēm un pieaugušajiem</h3>
            <p>
              Platforma palīdz gan vecākiem atrast nodarbības bērniem, gan pieaugušajiem
              izvēlēties sev atbilstošu sportisko ikdienu.
            </p>
          </article>

          <article class="about-card">
            <span class="about-card__icon">03</span>
            <h3>Ātra saziņa</h3>
            <p>
              Mazāk lieku soļu starp atrasto sekciju un reālu pieteikšanos treniņiem vai
              pirmajai konsultācijai.
            </p>
          </article>
        </div>
      </div>
    </section>

    <section class="section about-process">
      <div class="container">
        <div class="section-heading section-heading--row">
          <div>
            <p class="section-label">Kā tas strādā</p>
            <h2>No intereses līdz pirmajam treniņam</h2>
          </div>
        </div>

        <div class="about-steps">
          <article class="about-step">
            <span>1</span>
            <h3>Izpēti iespējas</h3>
            <p>Atver sekciju sarakstu un atrodi sev vai bērnam piemērotāko sporta virzienu.</p>
          </article>

          <article class="about-step">
            <span>2</span>
            <h3>Salīdzini un izvēlies</h3>
            <p>Skaties aprakstus, atrašanās vietu un sev būtisko informāciju vienā vietā.</p>
          </article>

          <article class="about-step">
            <span>3</span>
            <h3>Piesakies un sāc</h3>
            <p>Ātri sazinies ar organizatoru un sper nākamo soli ceļā uz regulāru sportošanu.</p>
          </article>
        </div>
      </div>
    </section>

    <section class="section about-audience">
      <div class="container about-audience__grid">
        <article class="about-audience__card">
          <p class="section-label">Vecākiem</p>
          <h3>Vienkāršāks ceļš līdz bērna pirmajai sekcijai</h3>
          <p>
            Mēs palīdzam ātri atrast piemērotas nodarbības pēc atrašanās vietas un sporta
            veida, lai izvēle būtu mierīga un pārdomāta.
          </p>
        </article>

        <article class="about-audience__card">
          <p class="section-label">Pieaugušajiem</p>
          <h3>Vieta, kur atsākt vai sākt savu sporta ritmu</h3>
          <p>
            Neatkarīgi no tā, vai meklē fitnesu, peldēšanu vai komandu sportu, SportaHub
            ļauj ātrāk nonākt līdz reālai izvēlei.
          </p>
        </article>
      </div>
    </section>
  </div>
</template>

<style scoped>
.about-page {
  background:
    radial-gradient(circle at top right, rgba(65, 90, 119, 0.12), transparent 26rem),
    linear-gradient(180deg, #f8fbff 0%, #ffffff 18%, #f7f9fc 100%);
}

.about-hero {
  padding-top: 4rem;
}

.about-hero__grid,
.about-story__grid,
.about-audience__grid {
  display: grid;
  gap: 2rem;
}

.about-hero__grid {
  grid-template-columns: minmax(0, 1.5fr) minmax(320px, 0.9fr);
  align-items: start;
}

.about-hero__content h1,
.about-story h2,
.about-values h2,
.about-process h2 {
  margin: 0;
  color: #0f172a;
  line-height: 1.02;
}

.about-hero__content h1 {
  max-width: 12ch;
  font-size: clamp(2.8rem, 5vw, 5rem);
  letter-spacing: -0.05em;
}

.about-hero__text,
.about-story__text p,
.about-card p,
.about-step p,
.about-audience__card p,
.about-highlight p,
.about-stat span {
  color: #475569;
  font-size: 1.05rem;
  line-height: 1.8;
}

.about-hero__text {
  max-width: 44rem;
  margin: 1.5rem 0 0;
}

.about-hero__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin-top: 2rem;
}

.about-hero__panel {
  display: grid;
  gap: 1rem;
}

.about-highlight,
.about-stat,
.about-card,
.about-step,
.about-audience__card {
  border: 1px solid rgba(148, 163, 184, 0.22);
  border-radius: 24px;
  background: rgba(255, 255, 255, 0.92);
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
}

.about-highlight {
  padding: 1.5rem;
  background: linear-gradient(135deg, #23344d 0%, #314661 100%);
}

.about-highlight__eyebrow {
  display: inline-block;
  margin-bottom: 0.85rem;
  color: rgba(255, 255, 255, 0.72);
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.14em;
  text-transform: uppercase;
}

.about-highlight p {
  margin: 0;
  color: #f8fafc;
}

.about-stats {
  display: grid;
  gap: 1rem;
}

.about-stat {
  padding: 1.25rem 1.5rem;
}

.about-stat strong {
  display: block;
  margin-bottom: 0.45rem;
  color: #0f172a;
  font-size: 1.05rem;
}

.about-story__grid {
  grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr);
  align-items: start;
}

.about-story__text {
  display: grid;
  gap: 1rem;
}

.about-story__text p,
.about-card p,
.about-step p,
.about-audience__card p {
  margin: 0;
}

.about-cards,
.about-steps {
  display: grid;
  gap: 1.5rem;
}

.about-cards {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.about-card,
.about-step,
.about-audience__card {
  padding: 1.6rem;
}

.about-card__icon,
.about-step span {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 3rem;
  height: 3rem;
  border-radius: 999px;
  background: #e8eef7;
  color: #23344d;
  font-size: 0.95rem;
  font-weight: 800;
}

.about-card h3,
.about-step h3,
.about-audience__card h3 {
  margin: 1rem 0 0.85rem;
  color: #0f172a;
  font-size: 1.35rem;
  line-height: 1.2;
}

.about-steps {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.about-step {
  position: relative;
  overflow: hidden;
}

.about-step::after {
  content: '';
  position: absolute;
  inset: 0 auto auto 0;
  width: 100%;
  height: 4px;
  background: linear-gradient(90deg, #23344d 0%, #86a4c3 100%);
}

.about-audience__grid {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.about-audience__card {
  min-height: 100%;
}

@media (max-width: 1080px) {
  .about-hero__grid,
  .about-story__grid,
  .about-cards,
  .about-steps,
  .about-audience__grid {
    grid-template-columns: 1fr;
  }

  .about-hero__content h1 {
    max-width: 14ch;
  }
}

@media (max-width: 640px) {
  .about-hero {
    padding-top: 2rem;
  }

  .about-hero__content h1 {
    max-width: none;
    font-size: clamp(2.35rem, 12vw, 3.4rem);
  }

  .about-hero__text,
  .about-story__text p,
  .about-card p,
  .about-step p,
  .about-audience__card p,
  .about-highlight p,
  .about-stat span {
    font-size: 1rem;
    line-height: 1.7;
  }

  .about-card,
  .about-step,
  .about-audience__card,
  .about-highlight,
  .about-stat {
    border-radius: 20px;
    padding: 1.25rem;
  }
}
</style>
