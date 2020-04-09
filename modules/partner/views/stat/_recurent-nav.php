<?php

use app\models\partner\PartUserAccess;

$act = PartUserAccess::getSelRazdel(\Yii::$app->controller->action);
?>
<ul class="nav nav-pills nav-fill">
    <li class="nav-item">
        <a class="nav-link <?= !empty($act[14]) ? 'active' : '' ?>" href="/partner/stat/recurrentpays">Сумма платежей</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= !empty($act[15]) ? 'active' : '' ?>" href="/partner/stat/recurrentcomis">Средняя
            выручка</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= !empty($act[17]) ? 'active' : '' ?>" href="/partner/stat/recurrentmiddle">Средняя сумма</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= !empty($act[16]) ? 'active' : '' ?>" href="/partner/stat/recurrentremove">Отток пользователей</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= !empty($act[23]) ? 'active' : '' ?>" href="/partner/stat/recurrentcard">Автоплатежи</a>
    </li>
</ul>