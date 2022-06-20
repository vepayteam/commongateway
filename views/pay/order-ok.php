<?php
/**
 * @var \app\services\payment\models\PaySchet $paySchet
 * @var string $appLang
 */

use yii\helpers\Html;
use yii\helpers\Json;

?>
<div class="r-body r-main-grid">
    <div class="r-body-main-success"><img src="/aassets/images/order-ok/success.svg" alt="Success">
        <h1><?= Yii::t('app.payment-success', 'Ваш платеж успешно исполнен') ?></h1>
    </div>
    <div class="r-body-main-notify">
        <h2><?= Yii::t('app.payment-success', 'ВНИМАНИЕ!') ?></h2>
        <p>
            <?= Yii::t('app.payment-success', 'По всем вопросам, связанным с выполнением оплаченного вами заказа, пожалуйста, обращайтесь к') ?> <?= Html::encode($paySchet->partner->Name) ?>
            <?= !empty($paySchet->partner->Phone) ? 'по телефону ' . Html::encode($paySchet->partner->Phone) : '' ?>
        </p>
    </div>
    <div class="r-body-main-action">
        <?php if (!empty($paySchet->SuccessUrl)): ?>
            <a href="<?= Html::encode($paySchet->SuccessUrl) ?>" class="r-body-main-action-return">
                <?= Yii::t('app.payment-success', 'Вернуться на сайт') ?>
            </a>
        <?php endif; ?>
    </div>
    <div class="r-body-info">
        <div class="r-body-info-row">
            <span><?= Yii::t('app.payment-success', 'Компания:') ?></span>
            <b><?= Html::encode($paySchet->partner->Name) ?></b>
        </div>
        <?php if (!empty($paySchet->partner->Phone)): ?>
            <div class="r-body-info-row">
                <span><?= Yii::t('app.payment-success', 'Телефон:') ?></span>
                <b><?= Html::encode($paySchet->partner->Phone) ?></b>
            </div>
        <?php endif; ?>
        <div class="r-body-info-row">
            <span><?= Yii::t('app.payment-success', 'Номер заказа:') ?></span>
            <b><?= Html::encode($paySchet->ID) ?></b>
        </div>
        <div class="r-body-info-row">
            <span><?= Yii::t('app.payment-success', 'Комиссия') ?></span>
            <b><?= round($paySchet->ComissSumm / 100, 2) ?></b>
        </div>
        <?php if (!empty($paySchet->Dogovor) && $appLang !== \app\services\LanguageService::APP_LANG_ENG): ?>
            <div class="r-body-info-row">
                <span><?= Yii::t('app.payment-success', 'Номер договора:') ?></span>
                <b><?= Html::encode($paySchet->Dogovor) ?></b>
            </div>
        <?php endif; ?>
        <?php if (!empty($paySchet->FIO)): ?>
            <div class="r-body-info-row">
                <span><?= Yii::t('app.payment-success', 'ФИО') ?></span>
                <b><?= Html::encode($paySchet->FIO) ?></b>
            </div>
        <?php endif; ?>

        <div class="r-body-info-split"></div>
        <div class="r-body-info-sum">
            <span><?= Yii::t('app.payment-success', 'Сумма') ?></span>
            <b><?= round($paySchet->getSummFull() / 100, 2) ?> <?= $paySchet->currency->getSymbol() ?></b>
        </div>
    </div>
</div>

<?php if (!empty($paySchet->SuccessUrl)): ?>
    <script>
        setTimeout(function () {
            window.location = <?= Json::encode($paySchet->SuccessUrl) ?>;
        }, 3000);
    </script>
<?php endif; ?>
