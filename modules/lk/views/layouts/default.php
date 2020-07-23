<?php
/* @var $this \yii\web\View */

use yii\helpers\Html;

/* @var $content string */

$this->beginPage()
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>VEPAY: <?= !empty($this->title) ? Html::encode($this->title) : "Кабинет партнера" ?></title>
    <link href="/lk/assets/css/app.css" rel="stylesheet">
</head>
<?php $this->head() ?>
<body>
<?php $this->beginBody() ?>
<div class="main-grid" id="main-grid">
    <div class="main-sidebar" id="main-sidebar"><a class="main-sidebar-logo" href="/"></a>
        <ul class="main-sidebar-ul">
            <li class="main-sidebar-ul-li">
                <a class="main-sidebar-ul-li-a" href="#">
                    <img rc="/lk/assets/img/icons/sidebar/home.svg" alt="TODO"><span>Главная</span>
                </a>
            </li>
            <li class="main-sidebar-ul-li">
                <a class="main-sidebar-ul-li-a" href="#">
                    <img src="/lk/assets/img/icons/sidebar/balance.svg" alt="TODO"><span>Баланс</span>
                </a>
            </li>
            <li class="main-sidebar-ul-li">
                <a class="main-sidebar-ul-li-a down-arrow" href="#">
                    <img src="/lk/assets/img/icons/sidebar/money.svg" alt="TODO"><span>Платежи</span>
                </a>
                <ul style="display: none;">
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                </ul>
            </li>
            <li class="main-sidebar-ul-li"><a class="main-sidebar-ul-li-a down-arrow" href="#"><img
                            src="/lk/assets/img/icons/sidebar/stats.svg" alt="TODO"><span>Статистика</span></a>
                <ul style="display: none;">
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                </ul>
            </li>
            <li class="main-sidebar-ul-li"><a class="main-sidebar-ul-li-a down-arrow" href="#"><img
                            src="/lk/assets/img/icons/sidebar/agent.svg" alt="TODO"><span>Контрагенты</span></a>
                <ul style="display: none;">
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                </ul>
            </li>
            <li class="main-sidebar-ul-li"><a class="main-sidebar-ul-li-a" href="#"><img
                            src="/lk/assets/img/icons/sidebar/doc.svg" alt="TODO"><span>Отчетные документы</span></a></li>
            <li class="main-sidebar-ul-li"><a class="main-sidebar-ul-li-a down-arrow" href="#"><img
                            src="/lk/assets/img/icons/sidebar/settings.svg" alt="TODO"><span>Управление</span></a>
                <ul style="display: none;">
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                </ul>
            </li>
            <li class="main-sidebar-ul-li"><a class="main-sidebar-ul-li-a" href="#"><img
                            src="/lk/assets/img/icons/sidebar/question.svg" alt="TODO"><span>Поддержка пользователей</span></a>
            </li>
            <li class="main-sidebar-ul-li"><a class="main-sidebar-ul-li-a" href="#"><img
                            src="/lk/assets/img/icons/sidebar/pen.svg" alt="TODO"><span>Публикации</span></a></li>
        </ul>
    </div>
    <div class="d-flex flex-column justify-between h-100">
        <div>
            <div class="content-header">
                <button class="content-header-menu-btn" id="sidebar-toggle"></button>
                <div class="content-header-info"><span class="content-header-info-h">+7 944 954-84-95</span><span
                            class="content-header-info-d">(часы работы: 8:00-20:00)</span></div>
                <div class="content-header-info"><span class="content-header-info-h">info@vepay.online</span><span
                            class="content-header-info-d">служба техподдержки</span></div>
                <select class="content-header-select">
                    <option value="RUS">RUS</option>
                    <option value="EN">EN</option>
                </select>
            </div>
            <div class="content py-6">
                <?= $content ?>
            </div>
        </div>
        <div class="content-footer"><span class="content-footer-desc">ООО "ПРОЦЕССИНГОВАЯ КОМПАНИЯ БЫСТРЫХ ПЛАТЕЖЕЙ" © 2020</span>
            <div class="content-footer-right">
                <button class="btn btn-outline-primary btn-white mr-4">Подключить кассу</button>
                <div class="content-footer-social">
                    <a href="#">
                        <img src="/lk/assets/img/icons/vk.svg" alt="TODO">
                    </a>
                    <a href="#">
                        <img src="/lk/assets/img/icons/instagram.svg" alt="TODO">
                    </a>
                    <a href="#">
                        <img src="/lk/assets/img/icons/facebook.svg" alt="TODO">
                    </a>
                    <a href="#">
                        <img src="/lk/assets/img/icons/telegram.svg" alt="TODO">
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="/lk/assets/js/vendors.0f424e0bacf60a4ca4d0.js"></script>
<script type="text/javascript" src="/lk/assets/js/app.eba3d7a6b48c024ca7ea.js"></script>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
