<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<section class="application-shell py-5">
    <div class="container">
        <div class="application-card col-lg-7 mx-auto text-center">
            <p class="eyebrow text-gold mb-3">Verificación</p>
            <h1 class="font-display display-6 fw-semibold text-wine-dark">Captura el código temporal</h1>
            <p class="text-secondary mt-3">
                El código contiene seis dígitos, vence en 10 minutos y solo puede utilizarse una vez.
            </p>

            <form method="post" action="<?= url_to('participant.access.verify') ?>" class="mt-4">
                <?= csrf_field() ?>
                <label class="form-label" for="access-code">Código de acceso</label>
                <input
                    class="form-control otp-input mx-auto <?= isset($errors['code']) ? 'is-invalid' : '' ?>"
                    id="access-code"
                    name="code"
                    type="text"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    minlength="6"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    autofocus
                    required
                >
                <?php if (isset($errors['code'])): ?>
                    <div class="invalid-feedback"><?= esc($errors['code']) ?></div>
                <?php endif ?>
                <p class="form-hint mt-2">Intentos disponibles: <?= (int) $attemptsRemaining ?></p>
                <button class="btn btn-wine mt-3" type="submit">Verificar y consultar</button>
            </form>

            <div class="border-top mt-4 pt-4">
                <form method="post" action="<?= url_to('participant.access.resend') ?>">
                    <?= csrf_field() ?>
                    <button
                        class="btn btn-outline-wine"
                        id="resend-code"
                        type="submit"
                        data-retry-seconds="<?= (int) $retrySeconds ?>"
                        <?= $retrySeconds > 0 ? 'disabled' : '' ?>
                    >
                        Reenviar código
                    </button>
                </form>
                <a class="d-inline-block mt-3" href="<?= url_to('participant.access') ?>">Usar otro correo o folio</a>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/access-code.js') ?>"></script>
<?= $this->endSection() ?>
