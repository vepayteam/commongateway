<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\assets\PartnerAsset;
use app\models\partner\UserLk;
use yii\bootstrap\Alert;
use yii\helpers\Html;

PartnerAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?= Html::csrfMetaTags() ?>
        <title>VEPAY: <?= !empty($this->title) ? Html::encode($this->title) : "Кабинет партнера" ?></title>
        <?php $this->head() ?>
    </head>
    <body <?= Yii::$app->user->isGuest ? 'class="gray-bg"' : '' ?> >
    <?php $this->beginBody() ?>

    <div id="wrapper">
        <?php if (!Yii::$app->user->isGuest): ?>
            <?= $this->render('@app/modules/partner/views/default/_menu', [
                'razdels' => UserLk::getRazdels(Yii::$app->user),
                'IsAdmin' => UserLk::IsAdmin(Yii::$app->user),
                'IsMfo' => UserLk::IsMfo(Yii::$app->user)
            ]);?>
            <div id="page-wrapper" class="gray-bg">
                <?= $this->render('@app/modules/partner/views/default/_top', [
                    'fio' => UserLk::getUserFIO(Yii::$app->user),
                    'IsAdmin' => UserLk::IsAdmin(Yii::$app->user)
                ]); ?>
                <?php if (isset($this->params['breadcrumbs'])) : ?>
                <?= $this->render('@app/modules/partner/views/default/_bread'); ?>
                <?php endif; ?>
                <div class="wrapper wrapper-content">

                    <?php if (Yii::$app->session->hasFlash('success')): ?>
                        <!-- Flash alert -->
                        <?php Alert::begin(['options' => ['class' => 'alert-success']]) ?>
                        <?= Yii::$app->session->getFlash('success') ?>
                        <?php Alert::end() ?>
                        <!-- /Flash alert -->
                    <?php endif; ?>

                    <?= $content ?>
                </div>
                <?= $this->render('@app/modules/partner/views/default/_footer'); ?>
            </div>
        <?php else: ?>
            <?= $content ?>
        <?php endif; ?>
    </div>
    <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>