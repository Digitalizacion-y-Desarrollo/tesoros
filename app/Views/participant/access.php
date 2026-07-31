<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="application-shell py-5">
    <div class="container">
        <div class="application-card col-xl-8 mx-auto">
            <p class="eyebrow text-gold mb-3">Acceso de participantes</p>
            <h1 class="font-display display-5 fw-semibold text-wine-dark">Consulta tu participación</h1>
            <p class="fs-5 text-secondary mt-3">
                Captura el correo registrado y el folio. Por seguridad enviaremos un código temporal al correo asociado.
            </p>

            <form method="post" action="<?= url_to('participant.access.request') ?>" class="mt-4">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="access-email">Correo electrónico</label>
                        <input
                            class="form-control"
                            id="access-email"
                            name="email"
                            type="email"
                            maxlength="254"
                            autocomplete="email"
                            value="<?= esc((string) old('email')) ?>"
                            required
                        >
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="access-folio">Folio</label>
                        <input
                            class="form-control text-uppercase"
                            id="access-folio"
                            name="folio"
                            type="text"
                            maxlength="18"
                            pattern="TG-2026-(CCT|RES|JTG|BTA)-[0-9]{6}"
                            placeholder="TG-2026-CCT-000001"
                            autocomplete="off"
                            value="<?= esc((string) old('folio')) ?>"
                            required
                        >
                    </div>
                </div>

                <?php if ($recaptchaSiteKey !== ''): ?>
                    <div class="g-recaptcha mt-4" data-sitekey="<?= esc($recaptchaSiteKey) ?>"></div>
                <?php else: ?>
                    <div class="alert alert-warning mt-4 mb-0" role="status">
                        reCAPTCHA no está configurado. El bypass funciona únicamente fuera de producción.
                    </div>
                <?php endif ?>
                <?php if (isset($errors['recaptcha'])): ?>
                    <p class="text-danger mt-2 mb-0"><?= esc($errors['recaptcha']) ?></p>
                <?php endif ?>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4">
                    <p class="form-hint mb-0">La respuesta será la misma exista o no la combinación indicada.</p>
                    <button class="btn btn-wine" type="submit">Enviar código temporal</button>
                </div>
            </form>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?php if ($recaptchaSiteKey !== ''): ?>
    <?= $this->section('scripts') ?>
    <script src="https://www.google.com/recaptcha/api.js?hl=es" async defer></script>
    <?= $this->endSection() ?>
<?php endif ?>
