<?php
/**
 * Bloque destacado con filete dorado: folio de registro o código temporal.
 *
 * @var string $label
 * @var string $value
 * @var string $spacing Opcional: espaciado entre caracteres del valor.
 */
$spacing = $spacing ?? '2px';
?>
<tr>
  <td class="om-pad" style="padding:14px 40px 22px 40px;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; background-color:#F7F1E7; border:1px solid #E3D6BC; border-left:4px solid #B68A38;">
    <tr>
      <td style="padding:22px 26px; font-family:Arial, Helvetica, sans-serif;">
        <span style="display:block; font-size:11px; line-height:16px; letter-spacing:1.5px; color:#8A7654; text-transform:uppercase;"><?= esc($label) ?></span>
        <span style="display:block; padding-top:6px; font-family:'Courier New', Courier, monospace; font-size:26px; line-height:32px; mso-line-height-rule:exactly; letter-spacing:<?= esc($spacing, 'attr') ?>; color:#75001C;"><?= esc($value) ?></span>
      </td>
    </tr>
    </table>
  </td>
</tr>
