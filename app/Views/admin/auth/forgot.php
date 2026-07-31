<?= $this->extend('layouts/admin_auth') ?>
<?= $this->section('content') ?>
<div class="auth-intro">
    <p class="eyebrow mb-0">Proveedor institucional</p>
    <h1 class="auth-title font-display">Recuperar acceso</h1>
    <p class="auth-lead">El servicio central enviará el enlace de recuperación al correo registrado.</p>
</div>

<form method="post" action="<?= url_to('admin.forgot.submit') ?>" class="auth-form">
    <?= csrf_field() ?>

    <div class="auth-field">
        <label class="auth-label" for="recovery-email">Correo institucional</label>
        <input class="form-control auth-input" id="recovery-email" name="email" type="email" maxlength="254"
            placeholder="nombre@edomex.gob.mx" autocomplete="email"
            value="<?= esc((string) old('email')) ?>" required autofocus>
    </div>

    <div class="auth-form-links">
        <a href="<?= url_to('admin.login') ?>">Volver al acceso</a>
    </div>

    <button class="btn btn-wine auth-submit" type="submit">Solicitar recuperación</button>
</form>

<div class="auth-help">
    <p>Por seguridad, la respuesta es la misma exista o no la cuenta registrada. Revisa la bandeja del
        correo institucional autorizado por el comité organizador.</p>
</div>
<?= $this->endSection() ?>
