<?php if (session()->has('message')): ?>
    <div class="container-xxl mt-3">
        <div role="status" class="alert alert-light border-gold mb-0">
            <?= esc((string) session('message')) ?>
        </div>
    </div>
<?php endif ?>

<?php if (session()->has('error')): ?>
    <div class="container-xxl mt-3">
        <div role="alert" class="alert alert-danger mb-0">
            <?= esc((string) session('error')) ?>
        </div>
    </div>
<?php endif ?>
