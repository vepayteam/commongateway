<?php

/* @var $this \yii\web\View */
/* @var $IsAdmin bool */
/* @var $razdels array */

/* @var $IsMfo bool */

use app\models\partner\PartUserAccess;

$act = PartUserAccess::getSelRazdel(\Yii::$app->controller->action);
//\yii\helpers\VarDumper::dump($act);
?>

<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            <li class="nav-header" style="background: #fff">
                <div class="profile-element">
                    <img src="/imgs/logo_vepay.svg" alt="VEPAY" width="auto" height="26" border="0">
                </div>
                <div class="logo-element">
                    <img src="/imgs/favicon.svg" alt="VEPAY" width="auto" height="26" border="0">
                </div>
            </li>

            <li class="<?= $act[-1] ?>"><a href="/partner"><i class="fa fa-home"></i> <span
                            class="nav-label">Главная</span></a></li>

            <?php if ($IsAdmin || $IsMfo) : ?>
                <li class="<?= $act[51] ?>"><a href="/partner/mfo/balance"><i class="fa fa-money"></i> <span
                                class="nav-label">Баланс</span></a></li>
            <?php endif; ?>
            <li class="<?= $act[51] ?>"><a href="/partner/mfo/parts-balance"><i class="fa fa-money"></i> <span
                            class="nav-label">Баланс по разбивке</span></a></li>
            <?php if ($IsAdmin || $IsMfo || ((count($razdels) == 0 || isset($razdels[1]) || isset($razdels[18])))) : ?>
                <li class="<?= !empty($act[1]) || !empty($act[21])? "active": ''?>">
                    <a href="" aria-expanded="<?= !empty($act[1]) || !empty($act[21]) ? 'true' : 'false' ?>">
                        <i class="fa fa-cubes"></i>
                        <span class="nav-label"> Отчеты</span>
                        <span class="fa arrow"></span>
                    </a>
                    <ul class="nav nav-second-level collapse <?= !empty($act[1]) || !empty($act[21]) ? 'in' : '' ?>"
                        aria-expanded="true" style="">
                        <li class="<?= $act[1] ?>"><a href="/partner/stat/otch">Платежи</a></li>
                        <?php if ($IsAdmin): ?>
                            <li class="<?= $act[21] ?>"><a href="/partner/stat/acts">Отчетные документы</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>
            <?php if ($IsAdmin || (count($razdels) == 0 || isset($razdels[0]))) : ?>
                <li class="<?= !empty($act[0]) || !empty($act[18]) ? 'active' : '' ?>">
                    <a href="" aria-expanded="<?= !empty($act[0]) || !empty($act[18]) ? 'true' : 'false' ?>">
                        <i class="fa fa-list"></i>
                        <span class="nav-label"> Операции</span>
                        <span class="fa arrow"></span>
                    </a>
                    <ul class="nav nav-second-level collapse <?= !empty($act[0]) || !empty($act[18]) ? 'in' : '' ?>"
                        aria-expanded="true" style="">
                        <li class="<?= $act[0] ?>"><a href="/partner/stat/list">Список операций</a></li>
                        <li class="<?= $act[18] ?>"><a href="/partner/payment-orders/list">Платежные поручения</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if ($IsAdmin || $IsMfo || ((count($razdels) == 0 || isset($razdels[10])))) : ?>
                <?php $activeSubMenu = !empty($act[10]) || !empty($act[11]) || !empty($act[12]) || !empty($act[13]) || !empty($act[14]) || !empty($act[15]) || !empty($act[16]) || !empty($act[17]) || !empty($act[23]) ?>
                <li class="<?= $activeSubMenu ? 'active' : '' ?>">
                    <a href="" aria-expanded="<?= $activeSubMenu ? 'true' : 'false' ?>">
                        <i class="fa fa-th-large"></i>
                        <span class="nav-label">Статистика</span>
                        <span class="fa arrow"></span>
                    </a>
                    <ul class="nav nav-second-level collapse <?= $activeSubMenu ? 'in' : '' ?>"
                        aria-expanded="true" style="">
                        <li class="<?= $act[10] ?>"><a href="/partner/stat/sale">Продажи</a></li>
                        <li class="<?= $act[11] ?>"><a href="/partner/stat/saledraft">Средний чек</a></li>
                        <li class="<?= $act[12] ?>"><a href="/partner/stat/salekonvers">Конверсия</a></li>
                        <?php if ($IsAdmin || $IsMfo || ((count($razdels) == 0 || isset($razdels[13])))) : ?>
                            <li class="<?= $act[13] ?>"><a href="/partner/stat/platelshik">Покупатели</a></li>
                        <?php endif; ?>
                        <li class="<?= !empty($act[14]) || !empty($act[15]) || !empty($act[16]) || !empty($act[17]) || !empty($act[23]) ? 'active' : '' ?>">
                            <a href="/partner/stat/recurrentcard">Регулярные платежи</a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if ($IsAdmin || (!$IsMfo && (count($razdels) == 0 || isset($razdels[6])))) : ?>
                <li class="<?= $act[6] ?>"><a href="/partner/order/index"><i class="fa fa-dribbble"></i> <span
                                class="nav-label">Виджет</span></a></li>
            <?php endif; ?>

            <?php if ($IsAdmin /*|| (!$IsMfo && (count($razdels) == 0 || isset($razdels[3])))*/) : ?>
                <li class="<?= $act[3] ?>"><a href="/partner/partner/index"><i class="fa fa-briefcase"></i> <span
                                class="nav-label">Контрагенты</span></a></li>
            <?php endif; ?>

            <?php if ($IsAdmin || $IsMfo || (!$IsMfo && (count($razdels) == 0 || isset($razdels[52])))) : ?>
                <li class="<?= !empty($act[52]) || !empty($act[53]) || !empty($act[54]) ? 'active' : '' ?>"><a href="/partner/settings/index"><i class="fa fa-gears"></i> <span
                                class="nav-label">Настройки</span></a></li>
            <?php endif; ?>

            <?php if ($IsAdmin || $IsMfo || (!$IsMfo && (count($razdels) == 0 || isset($razdels[7])))) : ?>
                <li class="<?= $act[7] ?>"><a href="/partner/callback/list"><i class="fa fa-bell-o"></i> <span
                                class="nav-label">Колбэки</span></a></li>
            <?php endif; ?>

            <?php if ($IsAdmin) : ?>
                <li class="<?=$act[8]?>"><a href="/partner/admin/comisotchet"><i class="fa fa-lemon-o"></i> <span class="nav-label">Вывод вознаграждения</span></a></li>
                <li class="<?= $act[19] . $act[20] . $act[22]?>"><a href="/partner/antifraud/index"><i class="fa fa-free-code-camp"></i> <span class="nav-label">Антифрод</span></a></li>
            <?php endif; ?>

        </ul>
    </div>
</nav>
