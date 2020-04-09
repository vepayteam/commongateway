<?php

/* @var yii\web\View $this */
/* @var $result array */

use yii\web\View;

$this->title = "тест ключей";

$this->params['breadtitle'] = "Тест ключей";
$this->params['breadcrumbs'][] = ['label' => 'Настройка ключей', 'url' => ['/partner/cardkey']];
$this->params['breadcrumbs'][] = $this->params['breadtitle'];
?>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5>Тест ключей</h5>
            </div>
            <div class="ibox-content">
                <div class="row m-b-xl m-t-sm">
                    <div class="col-sm-12">KEK 1 (БД): <?=$result['kek1'] ? 'успешно' : 'ошибка'?></div>
                </div>
                <div class="row m-b-xl m-t-sm">
                    <div class="col-sm-12">KEK 2 (Файл): <?=$result['kek2'] ? 'успешно' : 'ошибка'?></div>
                </div>
                <div class="row m-b-xl m-t-sm">
                    <div class="col-sm-12">KEK 3 (ОЗУ): <?=$result['kek3'] ? 'успешно' : 'ошибка'?></div>
                </div>
                <div class="row m-b-xl m-t-sm">
                    <div class="col-sm-12">Целостность ключей: <?=$result['crypt'] ? 'успешно' : 'ошибка'?></div>
                </div>
                <div class="row m-b-xl m-t-sm">
                    <div class="col-sm-12">Число ключей: <?=$result['count']?></div>
                </div>
                <div class="row m-b-xl m-t-sm">
                    <div class="col-sm-12">Число доступных ключей: <?=$result['countwork']?></div>
                </div>
            </div>
        </div>
    </div>
</div>
