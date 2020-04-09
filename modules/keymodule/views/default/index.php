<?php

/* @var yii\web\View $this */

use app\models\crypt\UserKeyLk;
use yii\web\View;

$this->title = "настройка ключей";

$this->params['breadtitle'] = "Настройка ключей";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];
?>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5>Настройка ключей</h5>
            </div>
            <div class="ibox-content">
                <?php if (UserKeyLk::accessKey1()): ?>
                    <div class="row m-b-xl m-t-sm">
                        <div class="col-sm-12"><a href="/keymodule/cardkey/insertkek1">Внести KEK 1 (БД)</a></div>
                    </div>
                <?php endif; ?>
                <?php if (UserKeyLk::accessKey2()): ?>
                    <div class="row m-b-xl m-t-sm">
                        <div class="col-sm-12"><a href="/keymodule/cardkey/insertkek2">Внести KEK 2 (Файл)</a></div>
                    </div>
                <?php endif; ?>
                <?php if (UserKeyLk::accessKey3()): ?>
                    <div class="row m-b-xl m-t-sm">
                        <div class="col-sm-12"><a href="/keymodule/cardkey/insertkek3">Внести KEK 3 (ОЗУ)</a></div>
                    </div>
                <?php endif; ?>
                <div class="row m-b-xl m-t-sm">
                    <div class="col-sm-12"><a href="/keymodule/cardkey/testkek">Тест ключей</a></div>
                </div>
                <?php if (UserKeyLk::accessKeyChange()): ?>
                    <div class="row m-b-xl m-t-sm">
                        <div class="col-sm-12"><a href="/keymodule/cardkey/changekeys">Замена ключей</a></div>
                    </div>
                    <div class="row m-b-xl m-t-sm">
                        <div class="col-sm-12"><a href="/keymodule/cardkey/initkeys">Инициализация ключей</a></div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>
