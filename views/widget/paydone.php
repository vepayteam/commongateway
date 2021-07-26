<?php

use app\services\partners\models\PartnerOption;
use app\services\payment\models\PaySchet;
use yii\web\View;

/* @var null|string $message */
/* @var null|string $returl */
/* @var PaySchet $paySchet */
/* @var View $this */

$paymentFormWithoutVepay = PartnerOption::findOne(['PartnerId' => $paySchet->ID, 'Name' => PartnerOption::PAYMENT_FORM_WITHOUT_VEPAY]);
?>

<section class="container">

    <?php if (!$paymentFormWithoutVepay || $paymentFormWithoutVepay->Value === 'false'): ?>
        <div class="row">
            <div class="col-xs-12 text-center"><img src="/imgs/logo_vepay.svg" alt="vepay" class="logoend"></div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-xs-12 text-center"><h2 class="zagend">Платёж прошёл успешно</h2></div>
    </div>

    <div class="row margin-top16">
        <div class="col-xs-12 text-center">
            <p class="infoend">Средства поступили на счёт продавца, теперь вы можете вернуться в магазин</p>
            <p class="infoend"><?=$message?></p>
        </div>
    </div>

    <?php if (/*isset($returl)*/true) : ?>
        <div class="row margin-top24">
            <div class="col-xs-10 col-xs-offset-2"><a class="btn btn-success btnret" href="/">Вернуться в магазин</a></div>
        </div>
    <?php endif; ?>

</section>
