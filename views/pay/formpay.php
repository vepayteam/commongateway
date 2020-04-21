<?php
/* @var \yii\web\View $this */
/* @var array $params */
/* @var \app\models\payonline\PayForm $payform */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
?>

<section class="container">
    <div class="row margin-top24 rowlogo">
        <div class="col-xs-12">
            <img src="/imgs/logo_vepay.svg" alt="vepay" class="logo">
            <span class="logotext">ТЕХНОЛОГИИ В&nbsp;ДЕЙСТВИИ</span>
            <img src="/imgs/close.svg" class="closebtn" alt="close" id="closeform">
        </div>
    </div>
    <?php if ($params['IdUsluga'] == 1) : ?>
        <div class="row margin-top24">
            <div class="col-xs-12"><div class="infotop">Cписанная cумма, списанная с карты, при успешном списании, вернется обратно на вашу банковскую карту.</div></div>
        </div>
    <?php else: ?>
        <div class="row margin-top24">
            <div class="col-xs-12">
                <div class="info">Оплата в: <?=$params['NamePartner']?></div>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($params['IdUsluga'] != 1) : ?>
        <div class="row nopadding">
            <div class="col-xs-12">
                <div class="info"><span>Сумма:</span> <span class="pull-right blacksumm"><?=number_format($params['SummPay']/100.0, 2, ',', '')?> ₽</span></div>
                <div class="info"><span>Комиссия:</span> <span class="pull-right blacksumm"><?=number_format($params['ComissSumm']/100.0, 2, ',', '')?> ₽</span></div>
            </div>
        </div>
    <?php endif; ?>

    <div id="loader" class="col-xs-12" style="display: none">
        <div class='text-center col-xs-12 loader'><i class="fa fa-spinner fa-spin fa-fw"></i></div>
    </div>
    <?php
    $form = ActiveForm::begin([
        'id' => 'payform',
        'errorCssClass' => null,
        'successCssClass' => null,
        'options' => [
            'class' => 'form-horizontal'
        ],
        'fieldConfig' => [
            //'template' => "{label}<div class='col-xs-12 col-sm-8'>{input}{hint}</div>",
            'template' => '{label}{input}{hint}',
        ]
    ]);
    ?>
    <div class="row margin-top24">
        <div class="col-xs-12 nopadding">
            <div class="cardnumblock">
                <img src="/imgs/visa.svg" class="pull-right pslogo">
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
            <div class="cardexpblock">
                <?= $form->field($payform, 'CardExp')->textInput([
                    'data-inputmask-placeholder' => '_',
                    'data-inputmask-jitMasking' => 'true',
                    'data-inputmask-mask' => '99/99',
                    'data-inputmask-regex' => '[01]\d{3}',
                    'class' => 'form-control',
                    'value' => '',
                    'placeholder' => 'ММ/ГГ',
                    //'autocomplete' => 'off',
                    'style' => "width: 65px;",
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
                    'style' => "width: 54px;"
                ]); ?>
            </div>

            <div class="cardholder">
                <?= $form->field($payform, 'CardHolder')->textInput([
                    'data-inputmask-placeholder' => '_',
                    'data-inputmask-jitMasking' => 'true',
                    'data-inputmask-mask' => 'C{3,60}',
                    'data-inputmask-regex' => '[\w+\s+]',
                    'class' => 'form-control',
                    'value' => '',
                    'placeholder' => ''
                ]); ?>
            </div>
        </div>
    </div>

    <div class="row nopadding" style="display: none;">
        <div class="col-sm-12 col-xs-12">
            <?= $form->field($payform, 'Phone')->textInput([
                'data-inputmask-placeholder' => '_',
                'data-inputmask-jitMasking' => 'true',
                'data-inputmask-mask' => '+ 7 (999) 999-99-99',
                'data-inputmask-regex' => '\d{10}',
                'class' => 'form-control notrequired',
                'value' => '',
                'placeholder' => '+7',
                'disabled' => 'disabled'
            ]); ?>

            <?= $form->field($payform, 'LinkPhone')->checkbox([
                'template' => "<div class=\"checkbox\">\n{input}\n{beginLabel}\n{labelTitle}\n{endLabel}</div>",
                'disabled' => 'disabled'
            ]); ?>
        </div>
    </div>

    <div class="row nopadding margin-top24">
        <div class="col-xs-12">
            <input type="hidden" class="idPay" name="PayForm[IdPay]" value="<?=$params['ID']?>">
            <input type="hidden" class="user_hash" name="user_hash" value="">
            <?= Html::submitButton('ОПЛАТИТЬ '. number_format($params['SummFull']/100.0, 2, ',', '').' ₽', [
                'class' => 'btn btn-success paybtn',
                'name' => 'paysubmit',
                'form' => 'payform',
                'id' => 'addtopay'
            ]) ?>
        </div>
    </div>

    <div class="row nopadding margin-top24">
        <div class="col-xs-12">
            <div class="errmessage" style="display: none">
                <p id="error_message_xs"></p>
            </div>
        </div>
    </div>

    <div class="row nopadding margin-top24">
        <div class="col-xs-12 text-center">
            <img src="/imgs/ps_logos.svg" class="pslogosBtm" alt="visa,mastercard,mir">
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 text-center">
            <div class="footcopyr">Сервис предоставлен VEPAY© ООО "ПКБП"</div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

    <div id="frame3ds" class="BankFrame" style="height: 600px; display: none">
        <form id="form3ds" action="" method='POST'>
            <input type="hidden" id="pareq3ds" name="PaReq" value="">
            <input type="hidden" id="md3ds" name="MD" value="">
            <input type="hidden" id="termurl3ds" name="TermUrl" value="">
        </form>
    </div>

</section>
<noscript><div><img src="https://mc.yandex.ru/watch/56963551" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<?php
$this->registerJs('payform.init();');
$this->registerJs('setTimeout(tracking.sendToServer, 500)', \yii\web\View::POS_READY);
$this->registerJsFile('/payasset/js/ym.js');
?>