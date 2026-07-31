<?php
/**
 * Tabla de datos del envío: etiqueta a la izquierda, valor en negritas a la derecha.
 *
 * @var array<string, string> $rows
 */
$rows = array_filter($rows, static fn ($value): bool => trim((string) $value) !== '');
?>
<?php if ($rows !== []): ?>
<tr>
  <td class="om-pad" style="padding:0 40px 26px 40px;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%; border-top:1px solid #E3D6BC;">
    <?php $first = true; ?>
    <?php foreach ($rows as $label => $value): ?>
      <?php $divider = $first ? '' : 'border-top:1px solid #EFE5D2; '; ?>
      <tr>
        <td width="46%" style="width:46%; padding:16px 12px 16px 0; <?= $divider ?>font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:22px; mso-line-height-rule:exactly; color:#8A7654;"><?= esc($label) ?></td>
        <td style="padding:16px 0; <?= $divider ?>font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:22px; mso-line-height-rule:exactly; color:#292929;"><strong><?= esc($value) ?></strong></td>
      </tr>
      <?php $first = false; ?>
    <?php endforeach ?>
    </table>
  </td>
</tr>
<?php endif ?>
