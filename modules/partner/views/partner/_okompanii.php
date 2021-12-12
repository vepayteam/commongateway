<?php
/* @var $this \yii\web\View */
/* @var $partner \app\models\payonline\Partner */

use yii\helpers\Html;

?>

<div class="ibox-content" style="border: none;">
    <h3 class="m-b-xs"><strong><?=Html::encode($partner->UrLico)?></strong></h3>
    <div>ИНН: <?=Html::encode($partner->INN)?><?=Html::encode(!empty($partner->KPP) ? 'КПП: '.$partner->KPP : '')?></div>
    <address class="m-t-xs">
        Юр.адрес:<?=Html::encode(str_replace("|", ",", $partner->UrAdres))?><br>
        Почт.адрес:<?=Html::encode(str_replace("|", ",", $partner->PostAdres))?><br>
    </address>
</div>

