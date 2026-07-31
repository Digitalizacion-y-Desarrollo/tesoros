<?= $this->extend('emails/layout') ?>

<?= $this->section('preheader') ?>Tu borrador fue creado. Conserva tu folio para continuar y consultar tu participación.<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= view('emails/partials/intro', [
    'heading'    => 'Tu registro fue exitoso',
    'paragraphs' => [
        'Creamos correctamente tu borrador en la convocatoria <strong style="color:#292929;">' . esc((string) $categoryName) . '</strong>.',
        'Conserva tu folio: lo necesitarás para retomar el formulario y para consultar el estado de tu solicitud en cualquier momento.',
    ],
]) ?>

<?= view('emails/partials/highlight', [
    'label' => 'Folio de registro',
    'value' => (string) $folio,
]) ?>

<?= view('emails/partials/rows', ['rows' => [
    'Estado de la solicitud'    => 'Borrador creado',
    'Convocatoria'              => (string) $categoryName,
    'Cierre de la convocatoria' => (string) ($closeAtLabel ?? ''),
]]) ?>

<?= view('emails/partials/button', [
    'label' => 'Consultar mi folio',
    'url'   => url_to('participant.access'),
]) ?>

<?= view('emails/partials/steps', ['steps' => [
    'Puedes continuar el formulario en la sesión actual mientras siga abierta.',
    'Si necesitas ingresar después, solicita un código temporal con tu correo y tu folio.',
    'Cuando el expediente esté completo, envía tu solicitud para que el comité organizador la revise.',
]]) ?>

<?= view('emails/partials/notice', [
    'notice' => 'Este mensaje confirma la creación del registro; no contiene un código de acceso.',
]) ?>
<?= $this->endSection() ?>
