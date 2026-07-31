<?php $footerCategories = config('Portal')->categories; ?>
<footer class="site-footer mt-auto">
    <div class="footer-pattern" aria-hidden="true"></div>
    <div class="container-fluid page-gutter position-relative">
        <div class="row g-5">
            <div class="col-lg-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="footer-brand-mark">
                        <img src="<?= base_url('assets/images/brand-tesoros.png') ?>" alt="">
                    </span>
                    <span class="font-display fs-5 text-white">Tesoros Gastronómicos</span>
                </div>
                <p class="footer-copy mb-0">Programa de convocatorias gastronómicas del Estado de México rumbo a París 2026.</p>
            </div>
            <div class="col-6 col-lg-3">
                <p class="footer-heading">Convocatorias</p>
                <nav class="d-flex flex-column gap-2" aria-label="Convocatorias">
                    <?php foreach ($footerCategories as $slug => $category): ?>
                        <a href="<?= url_to('category.show', $slug) ?>"><?= esc($category['shortName']) ?></a>
                    <?php endforeach ?>
                </nav>
            </div>
            <div class="col-6 col-lg-2">
                <p class="footer-heading">Participantes</p>
                <nav class="d-flex flex-column gap-2" aria-label="Participantes">
                    <a href="<?= url_to('home') ?>#convocatorias">Registro</a>
                    <a href="<?= url_to('participant.access') ?>">Consulta tu folio</a>
                    <a href="<?= url_to('home') ?>#convocatorias">Requisitos</a>
                </nav>
            </div>
            <div class="col-lg-3">
                <p class="footer-heading">Información</p>
                <p class="footer-copy mb-2">Convocatoria estatal de única ocasión.</p>
                <nav class="d-flex flex-column gap-2" aria-label="Información legal">
                    <a href="<?= url_to('legal.show', 'aviso-privacidad') ?>">Aviso de privacidad</a>
                    <a href="<?= url_to('legal.show', 'terminos-condiciones') ?>">Términos y condiciones</a>
                    <a href="<?= url_to('legal.show', 'conservacion-informacion') ?>">Conservación de información</a>
                    <a href="<?= url_to('legal.show', 'consentimiento-imagen') ?>">Datos e imagen</a>
                </nav>
            </div>
        </div>
        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between gap-2">
            <span>© 2026 Gobierno del Estado de México</span>
            <span class="text-gold">México–Francia · 200 años de historia y amistad</span>
        </div>
    </div>
</footer>
