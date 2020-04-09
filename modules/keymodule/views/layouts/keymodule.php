<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\models\crypt\UserKeyLk;
use yii\helpers\Html;
use app\assets\PartnerAsset; //innspinia

PartnerAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?= Html::csrfMetaTags() ?>
        <title>VEPAY: <?= !empty($this->title) ? Html::encode($this->title) : "Кабинет ключей" ?></title>
        <?php $this->head() ?>
    </head>
    <body <?= !UserKeyLk::IsAuth() ? 'class="gray-bg"' : '' ?> >
    <?php $this->beginBody() ?>

    <div id="wrapper">
        <?php if (UserKeyLk::IsAuth()): ?>
            <?= $this->render('@app/modules/keymodule/views/default/_menu', [
            ]);?>
            <div id="page-wrapper" class="gray-bg">
                <?= $this->render('@app/modules/keymodule/views/default/_top'); ?>
                <div class="wrapper wrapper-content">
                    <?= $content ?>
                </div>
                <?= $this->render('@app/modules/keymodule/views/default/_footer'); ?>
            </div>
        <?php else: ?>
            <?= $content ?>
        <?php endif; ?>
    </div>
    <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>