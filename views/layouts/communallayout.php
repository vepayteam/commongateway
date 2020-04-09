<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use app\assets\MerchantAsset; //atropos

MerchantAsset::register($this);

/*if (isset(\Yii::$app->view->params['colors']) && !empty(\Yii::$app->view->params['colors'])) {
    $this->render('_communlacustomcolor', [
        'mainColor' => \Yii::$app->view->params['colors'][0],
        'mainBorderColor' => \Yii::$app->view->params['colors'][0],
        'activeColor' => \Yii::$app->view->params['colors'][1]
    ]);
}*/
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <?//<meta name="viewport" content="width=device-width, initial-scale=1">?>
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body <?=in_array(\Yii::$app->requestedRoute, ['merchant/ordercancel', 'merchant/orderok', 'merchant/confirm-recurrent']) ? 'style="background-color: #fff !important;"' : '' ?> >
    <?php $this->beginBody() ?>

    <div class="wrapper" style="<?=in_array(\Yii::$app->requestedRoute, ['merchant/ordercancel', 'merchant/orderok', 'merchant/confirm-recurrent']) ? 'padding: 0; background-color: #fff !important;' : 'padding: 0;' ?>" >
        <?= $content ?>
    </div>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>