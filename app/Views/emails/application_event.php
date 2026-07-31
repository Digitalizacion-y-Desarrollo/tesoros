<?= $this->extend('emails/layout') ?>

<?= $this->section('preheader') ?><?= esc((string) $message) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= view('emails/partials/intro', [
    'heading'    => (string) $title,
    'paragraphs' => [esc((string) $message)],
]) ?>

<?= view('emails/partials/notice', ['notice' => (string) $detail]) ?>

<?= view('emails/partials/highlight', [
    'label' => 'Folio de registro',
    'value' => (string) $folio,
]) ?>

<?= view('emails/partials/rows', ['rows' => [
    'Convocatoria' => (string) $category_name,
]]) ?>

<?= view('emails/partials/button', [
    'label' => 'Consultar mi solicitud',
    'url'   => url_to('participant.access'),
]) ?>

<?= view('emails/partials/notice', [
    'notice' => 'Este mensaje no contiene documentos personales adjuntos. No respondas a este correo.',
]) ?>
<?= $this->endSection() ?>
