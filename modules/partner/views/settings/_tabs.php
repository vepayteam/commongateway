<?php

use app\models\partner\PartUserAccess;

$act = PartUserAccess::getSelRazdel(\Yii::$app->controller->action);
?>

<ul class="nav nav-pills nav-fill">
    <li class="nav-item">
        <a class="nav-link <?= !empty($act[52]) ? 'active' : '' ?>" href="/partner/settings/index">Колбэки</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= !empty($act[53]) ? 'active' : '' ?>" href="/partner/settings/distribution">Рассылка реестров</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= !empty($act[54]) ? 'active' : '' ?>" href="/partner/settings/alarms">Оповещения</a>
    </li>
</ul>
