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
    <link href="/aassets/lk/assets/css/app.css" rel="stylesheet">
</head>
<?php $this->head() ?>
<body>
<?php $this->beginBody() ?>
<div class="main-grid" id="main-grid">
    <div class="main-sidebar" id="main-sidebar"><a class="main-sidebar-logo" href="/"></a>
        <ul class="main-sidebar-ul">
            <li class="main-sidebar-ul-li"><a class="main-sidebar-ul-li-a" href="#"><img src="/aassets/lk/assets/img/icons/sidebar/home.svg" alt="TODO"><span>Главная</span></a></li>
            <li class="main-sidebar-ul-li"><a class="main-sidebar-ul-li-a" href="#"><img src="/aassets/lk/assets/img/icons/sidebar/balance.svg" alt="TODO"><span>Баланс</span></a></li>
            <li class="main-sidebar-ul-li"><a class="main-sidebar-ul-li-a down-arrow" href="#"><img src="/aassets/lk/assets/img/icons/sidebar/money.svg" alt="TODO"><span>Платежи</span></a>
                <ul style="display: none;">
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                </ul>
            </li>
            <li class="main-sidebar-ul-li"><a class="main-sidebar-ul-li-a down-arrow" href="#"><img src="/aassets/lk/assets/img/icons/sidebar/stats.svg" alt="TODO"><span>Статистика</span></a>
                <ul style="display: none;">
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                </ul>
            </li>
            <li class="main-sidebar-ul-li"><a class="main-sidebar-ul-li-a down-arrow" href="#"><img src="/aassets/lk/assets/img/icons/sidebar/agent.svg" alt="TODO"><span>Контрагенты</span></a>
                <ul style="display: none;">
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                </ul>
            </li>
            <li class="main-sidebar-ul-li"><a class="main-sidebar-ul-li-a" href="#"><img src="/aassets/lk/assets/img/icons/sidebar/doc.svg" alt="TODO"><span>Отчетные документы</span></a></li>
            <li class="main-sidebar-ul-li"><a class="main-sidebar-ul-li-a down-arrow" href="#"><img src="/aassets/lk/assets/img/icons/sidebar/settings.svg" alt="TODO"><span>Управление</span></a>
                <ul style="display: none;">
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                    <li><a href="#">QWE</a></li>
                </ul>
            </li>
            <li class="main-sidebar-ul-li"><a class="main-sidebar-ul-li-a" href="#"><img src="/aassets/lk/assets/img/icons/sidebar/question.svg" alt="TODO"><span>Поддержка пользователей</span></a></li>
            <li class="main-sidebar-ul-li"><a class="main-sidebar-ul-li-a" href="#"><img src="/aassets/lk/assets/img/icons/sidebar/pen.svg" alt="TODO"><span>Публикации</span></a></li>
        </ul>
    </div>
    <div class="d-flex flex-column justify-between h-100">
        <div>
            <div class="content-header">
                <div class="d-flex align-center gap-4 content-header-menu-btn-parent">
                    <button class="content-header-menu-btn" id="sidebar-toggle"></button>
                    <div class="content-header-info"><span class="content-header-info-h">+7 944 954-84-95</span><span class="content-header-info-d">(часы работы: 8:00-20:00)</span></div>
                    <div class="content-header-info"><span class="content-header-info-h">info@vepay.online</span><span class="content-header-info-d">служба техподдержки</span></div>
                </div><span class="content-header-name">ООО Lorem ipsum dolor sit amet</span>
                <div class="d-flex align-center gap-4">
                    <select class="content-header-select">
                        <option value="RUS">RUS</option>
                        <option value="EN">EN</option>
                    </select>
                    <div class="content-header-icons"><span class="content-header-icons-mail"></span><span class="content-header-icons-notify"><span class="content-header-icons-badge">5</span></span></div>
                    <div class="content-header-btns">
                        <a class="btn btn-outline-secondary content-header-btns-exit" href="/lk/login/out">Выйти</a>
                        <a class="content-header-btns-change" href="/lk/login/out">сменить пользователя</a>
                    </div>
                    <div class="content-header-settings"><a href="#"><img src="/aassets/lk/assets/img/icons/settings.svg" alt="#"></a></div>
                </div>
            </div>
            <?= $content ?>
        </div>
        <div class="content-footer"><span class="content-footer-desc">ООО "ПРОЦЕССИНГОВАЯ КОМПАНИЯ БЫСТРЫХ ПЛАТЕЖЕЙ" © <?= date('Y') ?></span>
            <div class="content-footer-right">
                <button class="btn btn-outline-primary btn-white mr-4">Подключить кассу</button>
                <div class="content-footer-social">
                    <a href="#">
                        <img src="/aassets/lk/assets/img/icons/vk.svg" alt="TODO">
                    </a>
                    <a href="#">
                        <img src="/aassets/lk/assets/img/icons/instagram.svg" alt="TODO">
                    </a>
                    <a href="#">
                        <img src="/aassets/lk/assets/img/icons/facebook.svg" alt="TODO">
                    </a>
                    <a href="#">
                        <img src="/aassets/lk/assets/img/icons/telegram.svg" alt="TODO">
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="/aassets/lk/assets/js/vendors.js"></script>
<script type="text/javascript" src="/aassets/lk/assets/js/app.js"></script>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
