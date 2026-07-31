<?php
/**
 * Plantilla base de los correos a participantes.
 * Adaptada del mockup aprobado `Plantilla Correo Participantes.html`.
 *
 * Datos opcionales:
 *   $title    string  Título del documento.
 *   $eyebrow  string  Etiqueta de convocatoria (franja crema). Se omite si viene vacía.
 *
 * Secciones:
 *   preheader  Texto de vista previa del cliente de correo.
 *   content    Cuerpo del mensaje.
 */
$eyebrowLabel = trim((string) ($eyebrow ?? ''));
$preheader    = trim($this->renderSection('preheader'));
$privacyUrl   = url_to('legal.show', 'aviso-privacidad');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="x-apple-disable-message-reformatting">
<meta name="color-scheme" content="light dark">
<meta name="supported-color-schemes" content="light dark">
<title><?= esc($title ?? 'Tesoros Gastronómicos del Estado de México') ?></title>
<!--[if mso]>
<xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml>
<![endif]-->
<style>
  @media only screen and (max-width: 620px) {
    .om-wrap { width: 100% !important; }
    .om-pad { padding-left: 24px !important; padding-right: 24px !important; }
    .om-h1 { font-size: 26px !important; line-height: 32px !important; }
    .om-stack { display: block !important; width: 100% !important; }
    .om-stack-pad { padding: 0 0 12px 0 !important; }
  }
</style>
</head>
<body style="margin:0; padding:0; background-color:#F7F1E7;">

<span style="display:none !important; visibility:hidden; opacity:0; color:transparent; height:0; width:0; overflow:hidden; mso-hide:all; font-size:1px; line-height:1px;"><?= esc($preheader) ?></span>

<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#F7F1E7;">
<tr>
<td align="center" style="padding:24px 12px 40px 12px;">

  <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" class="om-wrap" style="width:600px; max-width:600px; background-color:#FFFDF9; border:1px solid #E3D6BC;">

    <!-- Franja institucional -->
    <tr>
      <td align="center" bgcolor="#4A0012" style="background-color:#4A0012; padding:11px 24px; font-family:Arial, Helvetica, sans-serif; font-size:11px; line-height:15px; mso-line-height-rule:exactly; letter-spacing:1px; color:#E8D9AF; text-transform:uppercase;">
        Gobierno del Estado de México&nbsp;&nbsp;&middot;&nbsp;&nbsp;Secretaría de Cultura y Turismo
      </td>
    </tr>

    <!-- Encabezado -->
    <tr>
      <td class="om-pad" style="padding:26px 40px 22px 40px; border-bottom:1px solid #E3D6BC;">
        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%;">
        <tr>
          <td width="66" valign="middle" style="width:66px; padding-right:16px;">
            <img src="<?= base_url('assets/images/brand-tesoros.png') ?>" width="66" height="47" alt="Tesoros Gastronómicos del Estado de México" style="display:block; width:66px; height:47px; border:0; outline:none; text-decoration:none;">
          </td>
          <td valign="middle" style="font-family:Georgia, 'Times New Roman', serif; font-size:19px; line-height:24px; mso-line-height-rule:exactly; color:#4A0012;">
            Tesoros Gastronómicos<br>
            <span style="font-family:Arial, Helvetica, sans-serif; font-size:10px; line-height:16px; letter-spacing:2px; color:#9A7529; text-transform:uppercase;">Estado de México &middot; París 2026</span>
          </td>
        </tr>
        </table>
      </td>
    </tr>

    <?php if ($eyebrowLabel !== ''): ?>
    <!-- Etiqueta de convocatoria -->
    <tr>
      <td class="om-pad" bgcolor="#F7F1E7" style="background-color:#F7F1E7; padding:14px 40px; border-bottom:1px solid #E3D6BC; font-family:Arial, Helvetica, sans-serif; font-size:11px; line-height:16px; mso-line-height-rule:exactly; letter-spacing:1.5px; color:#75001C; text-transform:uppercase;">
        <?= esc($eyebrowLabel) ?>
      </td>
    </tr>
    <?php endif ?>

    <?= $this->renderSection('content') ?>

    <!-- Contacto -->
    <tr>
      <td class="om-pad" bgcolor="#F7F1E7" style="background-color:#F7F1E7; padding:26px 40px; border-top:1px solid #E3D6BC; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:24px; mso-line-height-rule:exactly; color:#5A5A5A;">
        ¿Dudas sobre tu registro? Los canales institucionales de atención están por confirmar y se publicarán en el sitio de la convocatoria.
      </td>
    </tr>

    <!-- Pie -->
    <tr>
      <td class="om-pad" bgcolor="#4A0012" style="background-color:#4A0012; padding:30px 40px 34px 40px; font-family:Arial, Helvetica, sans-serif; font-size:12px; line-height:20px; mso-line-height-rule:exactly; color:#D8C6A8;">
        <p style="margin:0 0 10px 0; font-family:Georgia, 'Times New Roman', serif; font-size:15px; line-height:22px; mso-line-height-rule:exactly; color:#E8D9AF;">México–Francia &middot; 200 años de historia y amistad</p>
        <p style="margin:0 0 14px 0; color:#C9B594;">Programa de convocatorias gastronómicas del Estado de México rumbo a París 2026.</p>
        <p style="margin:0; color:#B5A184;">
          Recibes este correo porque registraste una solicitud en la plataforma de convocatorias.<br>
          <a href="<?= $privacyUrl ?>" style="color:#E8D9AF; text-decoration:underline;">Aviso de privacidad</a>
        </p>
      </td>
    </tr>

  </table>

  <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" class="om-wrap" style="width:600px; max-width:600px;">
  <tr>
    <td align="center" style="padding:18px 24px 0 24px; font-family:Arial, Helvetica, sans-serif; font-size:11px; line-height:18px; mso-line-height-rule:exactly; color:#8A7654;">
      © 2026 Gobierno del Estado de México · Todos los derechos reservados
    </td>
  </tr>
  </table>

</td>
</tr>
</table>

</body>
</html>
