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
            <li class="<?= $_SERVER['REQUEST_URI'] == '/partner/admin/queue-info' ? 'active' : '' ?>">
                <a href="/partner/admin/queue-info">
                    <i class="fa fa-list"></i>
                    <span class="nav-label">Queue info</span>
                </a>
            </li><li class="<?= $_SERVER['REQUEST_URI'] == '/partner/admin/get-queue-all-messages' ? 'active' : '' ?>">
                <a href="/partner/admin/get-queue-all-messages">
                    <i class="fa fa-list"></i>
                    <span class="nav-label">All messages</span>
                </a>
            </li>
            <li class="<?= $_SERVER['REQUEST_URI'] == '/partner/admin/get-queue-waiting-messages' ? 'active' : '' ?>">
                <a href="/partner/admin/get-queue-waiting-messages">
                    <i class="fa fa-list"></i>
                    <span class="nav-label">Waiting messages</span>
                </a>
            </li>
            <li class="<?= $_SERVER['REQUEST_URI'] == '/partner/admin/get-queue-reserved-messages' ? 'active' : '' ?>">
                <a href="/partner/admin/get-queue-reserved-messages">
                    <i class="fa fa-list"></i>
                    <span class="nav-label">Reserved messages</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
