<?php
/**
 * Encabezado del cuerpo: título y párrafos de apertura.
 *
 * @var string        $heading
 * @var array<string> $paragraphs HTML ya escapado por quien invoca.
 */
?>
<tr>
  <td class="om-pad" style="padding:38px 40px 8px 40px;">
    <h1 class="om-h1" style="margin:0 0 18px 0; font-family:Georgia, 'Times New Roman', serif; font-weight:normal; font-size:30px; line-height:38px; mso-line-height-rule:exactly; color:#4A0012;"><?= esc($heading) ?></h1>
    <?php foreach ($paragraphs as $paragraph): ?>
      <p style="margin:0 0 16px 0; font-family:Arial, Helvetica, sans-serif; font-size:16px; line-height:26px; mso-line-height-rule:exactly; color:#3A3A3A;"><?= $paragraph ?></p>
    <?php endforeach ?>
  </td>
</tr>
