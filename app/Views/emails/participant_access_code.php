<?= $this->extend('emails/layout') ?>

<?= $this->section('preheader') ?>Tu código temporal de acceso vence en 10 minutos y solo puede utilizarse una vez.<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= view('emails/partials/intro', [
    'heading'    => 'Código temporal de acceso',
    'paragraphs' => [
        'Usa el siguiente código para consultar la solicitud con folio <strong style="color:#292929;">' . esc((string) $folio) . '</strong>.',
    ],
]) ?>

<?= view('emails/partials/highlight', [
    'label'   => 'Código temporal',
    'value'   => (string) $code,
    'spacing' => '8px',
]) ?>

<?= view('emails/partials/rows', ['rows' => [
    'Folio de la solicitud' => (string) $folio,
    'Vigencia del código'   => 'Vence el ' . (string) $expiresAt,
]]) ?>

<?= view('emails/partials/button', [
    'label' => 'Ir a consultar mi solicitud',
    'url'   => url_to('participant.access'),
]) ?>

<?= view('emails/partials/steps', [
    'stepsTitle' => 'Cómo usarlo',
    'steps'      => [
        'Abre la página de consulta e ingresa tu correo y tu folio.',
        'Captura este código antes de que venza para abrir tu solicitud.',
        'Si el código venció, solicita uno nuevo desde la misma página.',
    ],
]) ?>

<?= view('emails/partials/notice', [
    'notice' => 'El código vence en 10 minutos y solo puede utilizarse una vez. Si no solicitaste este acceso, ignora el mensaje y no compartas el código con nadie. No respondas a este correo.',
]) ?>
<?= $this->endSection() ?>
