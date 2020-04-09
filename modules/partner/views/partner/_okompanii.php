<?php
/* @var $this \yii\web\View */
/* @var $partner \app\models\payonline\Partner */

?>

<div class="ibox-content" style="border: none;">
    <h3 class="m-b-xs"><strong><?=$partner->UrLico?></strong></h3>
    <div>ИНН: <?=$partner->INN?><?=!empty($partner->KPP) ? 'КПП: '.$partner->KPP : ''?></div>
    <address class="m-t-xs">
        Юр.адрес:<?=str_ireplace("|", ",", $partner->UrAdres)?><br>
        Почт.адрес:<?=str_ireplace("|", ",", $partner->PostAdres)?><br>
    </address>
</div>

