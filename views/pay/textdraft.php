<?php
/* @var array $draftData */
/* @var string $qrcode */

?>

<div>api.vepay.online</div>
<div><?=$draftData['Urlico']?> ИНН: <?=$draftData['Inn']?></div>
<div>ЗН ККТ: <?=$draftData['KassaSerialNumber']?> #<?=$draftData['NumDocument']?></div>
<div>Кассовый чек / Приход <?=$draftData['DateDraft']?></div>
<div>РН ККТ: <?=$draftData['KassaRegNumber']?></div>
<div>ФН: <?=$draftData['FNSerialNumber']?></div>
<div>Сайт ФНС: www.nalog.ru</div>
<div>Смена: <?=$draftData['Smena']?> Чек: <?=$draftData['NumDraft']?></div>
<div><?=str_ireplace("\r\n", "<br>", $draftData['Tovar'])?></div>
<div>Сумма: <?=number_format($draftData['Summ']/100.0, 2, '.', '')?></div>
<div>Адрес покупателя: <?=$draftData['Email']?></div>
<div>ИТОГ ≡<?=number_format($draftData['Summ']/100.0, 2, '.', '')?></div>
<div>ЭЛЕКТРОННО ≡<?=number_format($draftData['Summ']/100.0, 2, '.', '')?></div>
<div>СНО: <?=$draftData['Sno']?></div>
<div>ФД: <?=$draftData['FDNumber']?> ФП: <?=$draftData['FPCode']?></div>
<div><img style="width: 2cm;" src="<?=$qrcode?>">
</div>