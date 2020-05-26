<?php

use app\models\partner\PartUserAccess;

$act = PartUserAccess::getSelRazdel(\Yii::$app->controller->action);
?>

<div class="tabs-container">
    <ul class="nav nav-tabs" role="tablist" id="recurrentpaytabs">
        <li class="active" role="presentation">
            <a class="nav-link active show" data-toggle="tab" href="#auto">Автоплатежи</a>
        </li>
        <li role="presentation">
            <a class="nav-link" data-toggle="tab" href="#auto0">Сумма платежей</a>
        </li>
        <li  role="presentation">
            <a class="nav-link" data-toggle="tab" href="#auto1">Средняя выручка</a>
        </li>
        <li  role="presentation">
            <a class="nav-link" data-toggle="tab" href="#auto2">Средняя сумма</a>
        </li>
        <li role="presentation">
            <a class="nav-link" data-toggle="tab" href="#auto3">Отток пользователей</a>
        </li>
    </ul>
</div>