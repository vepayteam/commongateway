<?php

/* @var $this \yii\web\View */
/* @var $IsAdmin bool */
/* @var $razdels array */

/* @var $IsMfo bool */

use app\models\partner\PartUserAccess;

$act = PartUserAccess::getSelRazdel(\Yii::$app->controller->action);
//\yii\helpers\VarDumper::dump($act);

$action = Yii::$app->controller->action->id . ':' . Yii::$app->request->get('queueName', 'queue');
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
            <li class="<?= $action == 'queue-info:queue' ? 'active' : '' ?>">
                <a href="/partner/admin/queue-info">
                    <i class="fa fa-list"></i>
                    <span class="nav-label">Queue info</span>
                </a>
            </li>
            <li class="<?= $action == 'get-queue-all-messages:queue' ? 'active' : '' ?>">
                <a href="/partner/admin/get-queue-all-messages">
                    <i class="fa fa-list"></i>
                    <span class="nav-label">All messages</span>
                </a>
            </li>
            <li class="<?= $action == 'get-queue-waiting-messages:queue' ? 'active' : '' ?>">
                <a href="/partner/admin/get-queue-waiting-messages">
                    <i class="fa fa-list"></i>
                    <span class="nav-label">Waiting messages</span>
                </a>
            </li>
            <li class="<?= $action == 'get-queue-reserved-messages:queue' ? 'active' : '' ?>">
                <a href="/partner/admin/get-queue-reserved-messages">
                    <i class="fa fa-list"></i>
                    <span class="nav-label">Reserved messages</span>
                </a>
            </li>


            <li class="<?= $action == 'queue-info:reportQueue' ? 'active' : '' ?>">
                <a href="/partner/admin/queue-info?queueName=reportQueue">
                    <i class="fa fa-list"></i>
                    <span class="nav-label">Report - Queue info</span>
                </a>
            </li>
            <li class="<?= $action == 'get-queue-all-messages:reportQueue' ? 'active' : '' ?>">
                <a href="/partner/admin/get-queue-all-messages?queueName=reportQueue">
                    <i class="fa fa-list"></i>
                    <span class="nav-label">Report - All messages</span>
                </a>
            </li>
            <li class="<?= $action == 'get-queue-waiting-messages:reportQueue' ? 'active' : '' ?>">
                <a href="/partner/admin/get-queue-waiting-messages?queueName=reportQueue">
                    <i class="fa fa-list"></i>
                    <span class="nav-label">Report - Waiting messages</span>
                </a>
            </li>
            <li class="<?= $action == 'get-queue-reserved-messages:reportQueue' ? 'active' : '' ?>">
                <a href="/partner/admin/get-queue-reserved-messages?queueName=reportQueue">
                    <i class="fa fa-list"></i>
                    <span class="nav-label">Report - Reserved messages</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
