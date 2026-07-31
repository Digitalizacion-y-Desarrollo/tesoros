<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="home-hero">
    <div class="ornament-pattern" aria-hidden="true"></div>
    <div class="container-fluid p-0 position-relative">
        <div class="row g-0 align-items-stretch">
            <div class="col-lg-6 hero-content page-gutter">
                <span class="status-pill">
                    <span class="status-dot" aria-hidden="true"></span>
                    Convocatoria abierta 2026
                </span>
                <h1>Tesoros Gastronómicos del Estado de México</h1>
                <p class="hero-kicker">Convocatorias rumbo a París 2026</p>
                <p class="hero-description">Un programa estatal que selecciona a cocineras y cocineros tradicionales, restaurantes, jóvenes talentos y productores de bebidas ancestrales que representarán la riqueza mexiquense en la temporada cultural México–Francia.</p>
                <a class="btn btn-gold btn-lg" href="#convocatorias">Conoce las convocatorias</a>
                <dl class="hero-stats">
                    <div><dt>4</dt><dd>Categorías</dd></div>
                    <div><dt>125</dt><dd>Municipios</dd></div>
                    <div><dt>4 sep</dt><dd>Cierre de registro</dd></div>
                </dl>
            </div>
            <div class="col-lg-6 home-hero-image">
                <img src="<?= base_url('assets/images/hero-portada.png') ?>" alt="Cocinera tradicional mexiquense con la Torre Eiffel en doble exposición">
                <div class="hero-image-overlay" aria-hidden="true"></div>
                <div class="anniversary-seal" aria-label="México–Francia, 200 años de historia y amistad">
                    <span>México–Francia</span>
                    <i aria-hidden="true"></i>
                    <strong>200</strong>
                    <small>años de historia<br>y amistad</small>
                </div>
            </div>
        </div>
    </div>
</section>
<div class="color-ribbon" aria-hidden="true"></div>

<section id="convocatorias" class="calls-section">
    <div class="container-fluid page-gutter">
        <div class="row align-items-end g-4 mb-5">
            <div class="col-lg-7">
                <p class="eyebrow mb-3">Las cuatro convocatorias</p>
                <h2 class="section-title mb-0">Cuatro caminos para llevar la cocina mexiquense a París</h2>
            </div>
            <div class="col-lg-5 col-xl-4 ms-xl-auto">
                <p class="section-intro mb-0">Cada categoría tiene requisitos, etapas de evaluación y calendario propios. Revisa las bases antes de iniciar tu registro.</p>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach ($categories as $slug => $category): ?>
                <div class="col-md-6 col-xl-3">
                    <article class="call-card call-card-<?= esc($category['accent']) ?> h-100">
                        <div class="call-card-bar"></div>
                        <div class="call-card-image">
                            <img src="<?= base_url('assets/images/' . $category['cardImage']) ?>" alt="">
                            <span>Abierta</span>
                        </div>
                        <div class="call-card-body">
                            <p class="call-number">Categoría <?= esc($category['number']) ?></p>
                            <h3><?= esc($category['name']) ?></h3>
                            <p><?= esc($category['description']) ?></p>
                            <div class="call-card-footer">
                                <span>Cierra 4 sep</span>
                                <a href="<?= url_to('category.show', $slug) ?>" aria-label="Ver bases de <?= esc($category['name']) ?>">Ver bases →</a>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</section>

<?= $this->include('partials/partners') ?>
<?= $this->endSection() ?>
