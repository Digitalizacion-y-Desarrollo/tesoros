<?php
/**
 * Botón principal de llamada a la acción.
 *
 * @var string $label
 * @var string $url
 */
?>
<tr>
  <td class="om-pad" style="padding:0 40px 12px 40px;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="width:auto;">
    <tr>
      <td class="om-stack om-stack-pad" bgcolor="#75001C" style="background-color:#75001C; border-radius:2px; padding:15px 30px;">
        <a href="<?= esc($url, 'attr') ?>" style="display:block; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:20px; mso-line-height-rule:exactly; font-weight:bold; color:#FFFDF9; text-decoration:none;"><?= esc($label) ?></a>
      </td>
    </tr>
    </table>
  </td>
</tr>
