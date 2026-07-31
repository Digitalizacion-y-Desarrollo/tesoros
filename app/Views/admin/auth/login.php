<?= $this->extend('layouts/admin_auth') ?>
<?= $this->section('content') ?>
<div class="auth-intro">
    <p class="eyebrow mb-0">Acceso institucional</p>
    <h1 class="auth-title font-display">Inicia sesión</h1>
    <p class="auth-lead">Ingresa con tu cuenta institucional autorizada por el comité organizador.</p>
</div>

<form method="post" action="<?= url_to('admin.login.submit') ?>" class="auth-form">
    <?= csrf_field() ?>

    <div class="auth-field">
        <label class="auth-label" for="admin-login-email">Correo institucional</label>
        <input class="form-control auth-input" id="admin-login-email" name="email" type="email" maxlength="254"
            placeholder="nombre@edomex.gob.mx" autocomplete="username"
            value="<?= esc((string) old('email')) ?>" required autofocus>
    </div>

    <div class="auth-field">
        <label class="auth-label" for="admin-login-password">Contraseña</label>
        <div class="auth-password" data-password-field>
            <input class="form-control auth-input" id="admin-login-password" name="password" type="password"
                placeholder="••••••••" autocomplete="current-password" required data-password-input>
            <button class="auth-toggle" type="button" data-password-toggle
                data-label-show="Mostrar" data-label-hide="Ocultar"
                aria-controls="admin-login-password" aria-pressed="false" hidden>Mostrar</button>
        </div>
    </div>

    <div class="auth-form-links">
        <a href="<?= url_to('admin.forgot') ?>">Olvidé mi contraseña</a>
    </div>

    <button class="btn btn-wine auth-submit" type="submit">Entrar al panel</button>
</form>

<div class="auth-help">
    <p>El acceso está limitado a personal autorizado. Todas las sesiones y acciones sobre expedientes quedan
        registradas en la bitácora del sistema.</p>
    <p>¿Necesitas una cuenta o perdiste tu acceso? Los canales institucionales de soporte están por confirmar.</p>
</div>
<?= $this->endSection() ?>
