<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
$errors = session('validation_errors') ?? [];
$oldParticipants = old('participants');
$oldParticipants = is_array($oldParticipants) ? array_values($oldParticipants) : [];
$participantCount = $slug === 'joven-talento-gastronomia' ? 2 : 1;
?>
<section class="application-shell py-5">
    <div class="container-xxl">
        <div class="row g-4">
            <aside class="col-lg-4">
                <div class="application-aside">
                    <p class="eyebrow mb-3">Crear borrador · Categoría <?= esc($category['number']) ?></p>
                    <h1 class="h2 font-display text-wine-dark"><?= esc($category['name']) ?></h1>
                    <p class="text-secondary mt-3">
                        Captura los datos mínimos para obtener tu folio. Después podrás completar y guardar el formulario.
                    </p>
                    <div class="application-step-list mt-4" aria-label="Etapas del registro">
                        <span class="active">1. Identificación</span>
                        <span>2. Formulario</span>
                        <span>3. Resumen y envío</span>
                    </div>
                </div>
            </aside>

            <div class="col-lg-8">
                <div class="application-card">
                    <?php if (isset($category['notice'])): ?>
                        <div class="alert provisional-notice" role="status"><?= esc($category['notice']) ?></div>
                    <?php endif ?>

                    <?php if (isset($errors['registration'])): ?>
                        <div class="alert alert-danger" role="alert"><?= esc($errors['registration']) ?></div>
                    <?php endif ?>

                    <?php if ($convocationClosed): ?>
                        <div class="alert alert-warning" role="alert">
                            <h2 class="h5">Convocatoria cerrada</h2>
                            <p class="mb-0">Ya no se admiten nuevos registros ni envíos de borradores.</p>
                        </div>
                    <?php else: ?>
                    <form method="post" action="<?= url_to('participant.create', $slug) ?>" novalidate>
                        <?= csrf_field() ?>

                        <fieldset>
                            <legend class="h4 font-display text-wine-dark">Correo de la solicitud</legend>
                            <p class="form-hint">El folio y las comunicaciones se asociarán a este correo. No podrá utilizarse en otra solicitud.</p>
                            <label class="form-label" for="email">Correo electrónico <span aria-hidden="true">*</span></label>
                            <input
                                class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                id="email"
                                name="email"
                                type="email"
                                maxlength="254"
                                autocomplete="email"
                                value="<?= esc((string) old('email')) ?>"
                                required
                            >
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= esc($errors['email']) ?></div>
                            <?php endif ?>
                        </fieldset>

                        <?php for ($index = 0; $index < $participantCount; $index++): ?>
                            <?php $person = $oldParticipants[$index] ?? []; ?>
                            <fieldset class="border-top pt-4 mt-4">
                                <legend class="h4 font-display text-wine-dark">
                                    <?= $index === 0 ? 'Persona responsable' : 'Integrante 2' ?>
                                </legend>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="first-name-<?= $index ?>">Nombre(s) *</label>
                                        <input class="form-control" id="first-name-<?= $index ?>" name="participants[<?= $index ?>][first_name]" maxlength="100" value="<?= esc((string) ($person['first_name'] ?? '')) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="curp-<?= $index ?>">CURP *</label>
                                        <input class="form-control text-uppercase" id="curp-<?= $index ?>" name="participants[<?= $index ?>][curp]" minlength="18" maxlength="18" pattern="[A-Za-z0-9]{18}" value="<?= esc((string) ($person['curp'] ?? '')) ?>" autocomplete="off" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="last-name-<?= $index ?>">Primer apellido *</label>
                                        <input class="form-control" id="last-name-<?= $index ?>" name="participants[<?= $index ?>][last_name]" maxlength="150" value="<?= esc((string) ($person['last_name'] ?? '')) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="second-last-name-<?= $index ?>">Segundo apellido</label>
                                        <input class="form-control" id="second-last-name-<?= $index ?>" name="participants[<?= $index ?>][second_last_name]" maxlength="150" value="<?= esc((string) ($person['second_last_name'] ?? '')) ?>">
                                    </div>
                                </div>
                            </fieldset>
                        <?php endfor ?>

                        <?php if (isset($errors['participants'])): ?>
                            <div class="alert alert-danger mt-3" role="alert"><?= esc($errors['participants']) ?></div>
                        <?php endif ?>

                        <fieldset class="border-top pt-4 mt-4">
                            <legend class="h4 font-display text-wine-dark">Privacidad y seguridad</legend>
                            <div class="alert provisional-notice small">
                                <a href="<?= url_to('legal.show', 'aviso-privacidad') ?>" target="_blank" rel="noopener">Aviso de privacidad provisional de desarrollo</a>, versión <?= esc($privacyVersion) ?>.
                                No sustituye el texto institucional definitivo y no debe publicarse en producción.
                            </div>
                            <div class="form-check mt-3">
                                <input class="form-check-input <?= isset($errors['accept_privacy']) ? 'is-invalid' : '' ?>" id="accept-privacy" name="accept_privacy" type="checkbox" value="1" <?= old('accept_privacy') === '1' ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="accept-privacy">He leído y acepto el aviso provisional vigente.</label>
                                <?php if (isset($errors['accept_privacy'])): ?>
                                    <div class="invalid-feedback"><?= esc($errors['accept_privacy']) ?></div>
                                <?php endif ?>
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
                        </fieldset>

                        <div class="d-flex flex-wrap justify-content-between gap-3 border-top pt-4 mt-4">
                            <a class="btn btn-outline-wine" href="<?= url_to('category.show', $slug) ?>">Volver a las bases</a>
                            <button class="btn btn-wine" type="submit">Crear borrador y obtener folio</button>
                        </div>
                    </form>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?php if ($recaptchaSiteKey !== ''): ?>
    <?= $this->section('scripts') ?>
    <script src="https://www.google.com/recaptcha/api.js?hl=es" async defer></script>
    <?= $this->endSection() ?>
<?php endif ?>
