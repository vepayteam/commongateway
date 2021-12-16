<?php

/* @var $this \yii\web\View */

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2><?= Html::encode($this->params['breadtitle'])?></h2>
        <?= Breadcrumbs::widget([
            'homeLink' => ['label' => 'Главная', 'url' => '/partner'],
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
    </div>
    <div class="col-lg-2">

    </div>
</div>
