<div class="institutional-bar">
    <span class="flag flag-mx" aria-hidden="true"></span>
    <span>México</span>
    <span class="institutional-divider" aria-hidden="true">·</span>
    <span>Francia</span>
    <span class="flag flag-fr" aria-hidden="true"></span>
</div>
<header class="site-header">
    <nav class="navbar navbar-expand-lg bg-paper border-bottom border-gold py-3" aria-label="Navegación principal">
        <div class="container-fluid page-gutter">
            <a class="navbar-brand brand-lockup" href="<?= url_to('home') ?>">
                <img class="brand-mark" src="<?= base_url('assets/images/brand-tesoros.png') ?>" alt="">
                <span>
                    <span class="brand-title d-block">Tesoros Gastronómicos</span>
                    <span class="brand-subtitle d-block">Estado de México · París 2026</span>
                </span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavigation" aria-controls="mainNavigation" aria-expanded="false" aria-label="Abrir navegación">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="mainNavigation">
                <div class="navbar-nav align-items-lg-center gap-lg-2 pt-3 pt-lg-0">
                    <a class="nav-link" href="<?= url_to('home') ?>#convocatorias">Convocatorias</a>
                    <a class="nav-link" href="<?= url_to('home') ?>#instituciones">Instituciones</a>
                    <a class="btn btn-outline-wine ms-lg-3" href="<?= url_to('participant.access') ?>">Consulta tu folio</a>
                    <a class="btn btn-wine" href="<?= url_to('home') ?>#convocatorias">Registro</a>
                </div>
            </div>
        </div>
    </nav>
</header>
