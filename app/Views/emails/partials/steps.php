<?php
/**
 * Lista numerada "Qué sigue".
 *
 * @var array<string> $steps
 * @var string        $stepsTitle Opcional.
 */
$stepsTitle = $stepsTitle ?? 'Qué sigue';
?>
<?php if ($steps !== []): ?>
<tr>
  <td class="om-pad" style="padding:30px 40px 6px 40px;">
    <p style="margin:0 0 12px 0; font-family:Arial, Helvetica, sans-serif; font-size:11px; line-height:16px; mso-line-height-rule:exactly; letter-spacing:1.5px; color:#9A7529; text-transform:uppercase;"><?= esc($stepsTitle) ?></p>
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="width:100%;">
    <?php foreach ($steps as $index => $step): ?>
      <tr>
        <td width="30" valign="top" style="width:30px; padding:6px 0; font-family:Georgia, 'Times New Roman', serif; font-size:16px; line-height:26px; mso-line-height-rule:exactly; color:#B68A38;"><?= $index + 1 ?>.</td>
        <td valign="top" style="padding:6px 0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:26px; mso-line-height-rule:exactly; color:#3A3A3A;"><?= esc($step) ?></td>
      </tr>
    <?php endforeach ?>
    </table>
  </td>
</tr>
<?php endif ?>
