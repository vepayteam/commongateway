<?php

use app\models\partner\PartUserAccess;

$act = PartUserAccess::getSelRazdel(\Yii::$app->controller->action);
?>
<ul class="nav nav-pills nav-fill">
    <li class="nav-item">
        <a class="nav-link <?= !empty($act[19]) ? 'active' : '' ?>" href="/partner/antifraud/index">Список операций</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= !empty($act[20]) ? 'active' : '' ?>" href="/partner/antifraud/all-stat">Описание и общая статистика</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= !empty($act[22]) ? 'active' : '' ?>" href="/partner/antifraud/settings">Настройки</a>
    </li>
</ul>