<?= $this->extend('layouts/admin_auth') ?>
<?= $this->section('content') ?>
<div class="auth-intro">
    <p class="eyebrow mb-0">Integración no disponible</p>
    <h1 class="auth-title font-display">Acceso no configurado</h1>
    <p class="auth-lead">
        La autenticación permanece bloqueada porque la API institucional no está configurada o no se
        encuentra disponible.
    </p>
</div>

<div class="admin-alert admin-alert-danger mt-4">
    No es posible iniciar sesión hasta que el proveedor institucional de accesos responda correctamente.
</div>

<div class="auth-help">
    <p>Si la configuración ya fue aplicada, vuelve a intentar el acceso desde la pantalla de inicio de sesión.</p>
    <p><a href="<?= url_to('admin.login') ?>">Volver al acceso administrativo</a></p>
</div>
<?= $this->endSection() ?>
