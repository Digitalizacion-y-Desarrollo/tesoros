<?php
/**
 * Aviso con filete vino a la izquierda.
 *
 * @var string $notice
 */
?>
<?php if (trim((string) $notice) !== ''): ?>
<tr>
  <td class="om-pad" style="padding:26px 40px 34px 40px;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; background-color:#F7F1E7; border-left:4px solid #75001C;">
    <tr>
      <td style="padding:18px 22px; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:22px; mso-line-height-rule:exactly; color:#4A0012;"><?= esc($notice) ?></td>
    </tr>
    </table>
  </td>
</tr>
<?php endif ?>
