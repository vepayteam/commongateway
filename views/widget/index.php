<?php

use app\models\payonline\OrderPay;
use app\models\payonline\PayForm;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\web\View;

/* @var View $this */
/* @var OrderPay $order */
/* @var bool $isorder */
/* @var PayForm $payform */
?>

<section class="container">
    <div class="row no-row-margins">
        <div class="col-xs-12">
            <div class="row">
                <img src="/imgs/logo_vepay.svg" alt="vepay" class="logo">
            </div>
        </div>
    </div>
    <div class="row margin-top24">
        <div class="col-xs-4">
            <div class="infosumm">Сумма к оплате</div>
            <div class="summ"><?=number_format($order->SumOrder / 100.0, 2, ',', '')?> ₽</div>
        </div>
        <div class="col-xs-8">
            <div class="infomag">Адрес магазина</div>
            <div class="mag"><?=Html::encode($order->partner->Name)?></div>
        </div>
    </div>

    <div id="loader" class="col-xs-12 margin-top16" style="display: none">
        <div class='text-center col-xs-12 loader'><i class="fa fa-spinner fa-spin fa-fw"></i>
        </div>
    </div>
    <?php
    $form = ActiveForm::begin([
        'id' => 'widgetform',
        'errorCssClass' => null,
        'successCssClass' => null,
        'options' => [
            'class' => 'form-horizontal'
        ],
        'fieldConfig' => [
            'template' => '{label}{input}{hint}',
        ]
    ]);
    ?>
    <div class="row no-row-margins margin-top24">
        <div class="cardnumblock">
            <img src="/imgs/visa.svg" class="pull-right pslogo">
            <img src="/imgs/icondel.svg" alt="delcard" class="delcard">
            <?= $form->field($payform, 'CardNumber')->textInput([
                'data-inputmask-placeholder' => '_',
                'data-inputmask-jitMasking' => 'true',
                'data-inputmask-mask' => '9{4} 9{4} 9{4} 9{4,6}',
                'data-inputmask-regex' => '\d{16,18}',
                'class' => 'form-control',
                'value' => '',
                'placeholder' => '**** **** **** ****',
                'autocomplete' => 'off'
            ]); ?>
        </div>
        <div class="expblock">
            <?= $form->field($payform, 'CardExp')->textInput([
                'data-inputmask-placeholder' => '_',
                'data-inputmask-jitMasking' => 'true',
                'data-inputmask-mask' => '99/99',
                'data-inputmask-regex' => '[01]\d{3}',
                'class' => 'form-control',
                'value' => '',
                'placeholder' => 'ММ/ГГ',
                'autocomplete' => 'off',
                'style' => "width: 72px;",
            ]); ?>
        </div>
    </div>

    <div class="row no-row-margins">
        <div class="holderblock">
            <?= $form->field($payform, 'CardHolder')->textInput([
                'data-inputmask-placeholder' => '_',
                'data-inputmask-jitMasking' => 'true',
                'data-inputmask-mask' => 'C{3,60}',
                'data-inputmask-regex' => '[\w+\s+]',
                'class' => 'form-control',
                'value' => '',
                'placeholder' => 'IVANOV IVAN'
            ]); ?>
        </div>
        <div class="cvcblock">
            <img src="/imgs/info.svg" alt="info" class="infocvc" title="Код с обратной стороны карты">
            <?= $form->field($payform, 'CardCVC')->passwordInput([
                'data-inputmask-placeholder' => '_',
                'data-inputmask-jitMasking' => 'true',
                'data-inputmask-mask' => '9{3}',
                'data-inputmask-regex' => '\d{3}',
                'class' => 'form-control',
                'value' => '',
                'placeholder' => '',
                'autocomplete' => 'new-password',
                'style' => "width: 55px;"
            ]); ?>
        </div>
    </div>
    <div class="row no-row-margins">
        <div class="col-xs-12">
            <?= $form->field($payform, 'Email')->textInput([
                'type' => 'email',
                'class' => 'form-control borderemail',
                'value' => $order->EmailTo,
                'placeholder' => 'info@vepay.online'
            ]); ?>
        </div>
    </div>

    <div class="row no-row-margins">
        <div class="row nopadding margin-top20">
            <div class="col-xs-12">
                <?= Html::hiddenInput('IdOrder', $order->ID, ['class' => 'idPay']) ?>
                <?= Html::hiddenInput('isorder', $isorder) ?>

                <?php if (!$isorder) :?>
                    <?= Html::hiddenInput('Order[IdPartner]', $order->IdPartner) ?>
                    <?= Html::hiddenInput('Order[Comment]', $order->Comment) ?>
                    <?= Html::hiddenInput('Order[SumOrder]', $order->SumOrder) ?>
                <?php endif; ?>

                <?= Html::Button('Оплатить', [
                    'class' => 'btn btn-success paybtn',
                    'name' => 'paysubmit',
                    'form' => 'payform',
                    'id' => 'btnpaywidget'
                ]) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

    <div class="row margin-top16">
        <div class="col-xs-12">
            <div class="errmessage" style="display: none">
                <p id="error_message"></p>
            </div>
        </div>
    </div>

    <div id="frame3ds" class="BankFrame" style="display: none">
        <form id="form3ds" action="" method='POST'>
            <input type="hidden" id="pareq3ds" name="PaReq" value="">
            <input type="hidden" id="md3ds" name="MD" value="">
            <input type="hidden" id="termurl3ds" name="TermUrl" value="">
        </form>
    </div>

</section>

<?php
$this->registerJs('widgetform.init();', yii\web\View::POS_READY);
?>
