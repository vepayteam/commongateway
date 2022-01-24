<?php

/* @var array $data */
/* @var $this \yii\web\View */
/* @var bool $IsAdmin */

?>

<div class="row">
    <div class="col-lg-4 col-sm-6">
        <div class="ibox ">
            <div class="ibox-title">
                <h5>Количество новых привязок карт</h5>
            </div>
            <div class="ibox-content">
                <h2 class="no-margins"><?=number_format($data['cntnewcards'], 0, '.', ' ')?></h2>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-sm-6">
        <div class="ibox ">
            <div class="ibox-title">
                <h5>Число активных карт</h5>
            </div>
            <div class="ibox-content">
                <h2 class="no-margins"><?=number_format($data['activecards'], 0, '.', ' ')?></h2>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-sm-6">
        <div class="ibox ">
            <div class="ibox-title">
                <h5>Количество запросов на одну карту</h5>
            </div>
            <div class="ibox-content">
                <h2 class="no-margins"><?=number_format($data['reqonecard'], 0, '.', ' ')?></h2>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-sm-6">
        <div class="ibox ">
            <div class="ibox-title">
                <h5>Количество успешных платежей</h5>
            </div>
            <div class="ibox-content">
                <h2 class="no-margins"><?=number_format($data['payscards'], 0, '.', ' ')?></h2>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-sm-6">
        <div class="ibox ">
            <div class="ibox-title">
                <h5>Выручка по успешным платежам</h5>
            </div>
            <div class="ibox-content">
                <h2 class="no-margins"><?=number_format($data['sumpayscards'] / 100.0, 2, '.', ' ')?></h2>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-sm-6">
        <div class="ibox ">
            <div class="ibox-title">
                <h5>Количество запросов на списание по картам</h5>
            </div>
            <div class="ibox-content">
                <h2 class="no-margins"><?=number_format($data['reqcards'], 0, '.', ' ')?></h2>
            </div>
        </div>
    </div>

</div>
