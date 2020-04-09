<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use app\assets\SiteAsset;

SiteAsset::register($this);

?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title>VEPAY <?= !empty($this->title) ? ' - ' . Html::encode($this->title) : '' ?></title>
        <?php $this->head() ?>
        <link rel="shortcut icon" href="/favicon.ico">
    </head>
    <body class="gray-bg">
    <?php $this->beginBody() ?>
    <div id="wrapper">
        <div class="wrapper wrapper-content">
            <?= $content ?>
        </div>
    </div>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>