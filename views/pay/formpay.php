<?php
/* @var \yii\web\View $this */
/* @var array $params */
/* @var array $apple */
/* @var array $google */
/* @var array $samsung */
/* @var \app\models\payonline\PayForm $payform */

use app\services\partners\models\PartnerOption;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
?>
<div class="middle">
<section class="container">
    <div class="row margin-top24 rowlogo">
        <div class="col-xs-12">
            <img src="/imgs/logo_vepay.svg" alt="vepay" class="logo">
            <span class="logotext">ТЕХНОЛОГИИ В&nbsp;ДЕЙСТВИИ</span>
            <img src="/imgs/close.svg" class="closebtn" alt="close" id="closeform">
        </div>
    </div>
    <?php if ($params['IdUsluga'] == 1) : ?>
        <?php $partnerCardRegTextHeaderOption = PartnerOption::findOne(['PartnerId' => $params['IdOrg'], 'Name' => PartnerOption::CARD_REG_TEXT_HEADER_NAME]) ?>
        <?php if($partnerCardRegTextHeaderOption): ?>
            <div class="infotop">
                <?= $partnerCardRegTextHeaderOption['Value'] ?>
            </div>
        <?php else: ?>
            <div class="infotop">
                Cписанная cумма, списанная с карты, при успешном списании, вернется обратно на вашу банковскую карту.
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="row margin-top24">
            <div class="col-xs-12">
                <div class="info">Оплата в<span class="pull-right blacksumm"><?=$params['NamePartner']?></span></div>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($params['IdUsluga'] != 1) : ?>
        <div class="row nopadding">
            <div class="col-xs-12">
                <div class="info"><span>Сумма</span> <span class="pull-right blacksumm"><?=number_format($params['SummPay']/100.0, 2, ',', '')?> ₽</span></div>
                <div class="info"><span>Комиссия</span> <span class="pull-right blacksumm"><?=number_format($params['ComissSumm']/100.0, 2, ',', '')?> ₽</span></div>
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
                    'autocomplete' => 'off',
                ]); ?>
            </div>
            <div class="cvcblock">
                <img src="/imgs/info.svg" alt="info" class="infocvc" data-toggle="tooltip" data-placement="top" title="Последние 3 цифры на белой полосе карты с обратной стороны">
                <?= $form->field($payform, 'CardCVC')->passwordInput([
                    'data-inputmask-placeholder' => '_',
                    'data-inputmask-jitMasking' => 'true',
                    'data-inputmask-mask' => '9{3}',
                    'data-inputmask-regex' => '\d{3}',
                    'class' => 'form-control',
                    'value' => '',
                    'placeholder' => '',
                    'autocomplete' => 'new-password',
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
    <?php if ($params['IsUseKKmPrint']) : ?>
        <div class="row nopadding">
            <div class="col-sm-12 col-xs-12">
                <?= $form->field($payform, 'Email')->textInput([
                    'type' => 'email',
                    'class' => 'form-control notrequired',
                    'value' => '',
                    'placeholder' => 'info@vepay.online'
                ]); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="row nopadding margin-top24">
        <div class="col-xs-12">
            <input type="hidden" class="idPay" name="PayForm[IdPay]" value="<?=$params['ID']?>">
            <input type="hidden" class="user_hash" name="user_hash" value="">
            <?= Html::submitButton(
                ($params['IdUsluga'] == 1 ? 'ОТПРАВИТЬ' :
                    'ОПЛАТИТЬ ' . number_format($params['SummFull']/100.0, 2, ',', '').' ₽'
                ), [
                'class' => 'btn btn-success paybtn',
                'name' => 'paysubmit',
                'form' => 'payform',
                'id' => 'addtopay'
            ]) ?>
        </div>
    </div>

    <div class="row nopadding margin-top24" id="applepay" style="display: none">
        <div class="col-xs-12">
            <input type="hidden" class="idPay" name="PayForm[IdPay]" value="<?=$params['ID']?>">
            <input type="hidden" class="user_hash" name="user_hash" value="">
            <?= Html::button('<i class="fa fa-apple" aria-hidden="true"></i> PAY', [
                'class' => 'btn btn-success paybtn',
                'id' => 'applepaybtn'
            ]); ?>
        </div>
    </div>
    <div class="row nopadding margin-top24" id="googlepay" style="display: none">
        <div class="col-xs-12">
            <input type="hidden" class="idPay" name="PayForm[IdPay]" value="<?=$params['ID']?>">
            <input type="hidden" class="user_hash" name="user_hash" value="">
            <?= Html::button('<i class="fa fa-google" aria-hidden="true"></i> PAY', [
                'class' => 'btn btn-success paybtn',
                'id' => 'googlepaybtn'
            ]); ?>
        </div>
    </div>
    <div class="row nopadding margin-top24" id="samsungpay" style="display: none">
        <div class="col-xs-12">
            <input type="hidden" class="idPay" name="PayForm[IdPay]" value="<?=$params['ID']?>">
            <input type="hidden" class="user_hash" name="user_hash" value="">
            <?= Html::button('SAMSUNG PAY', [
                'class' => 'btn btn-success paybtn',
                'id' => 'samsungpaybtn'
            ]); ?>
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
            <div class="pslogosBtm">
                <img src="/imgs/pci-dss.png" class="opacity">
                <img src="/imgs/verified-by-visa.png" class="padding-left10 opacity">
                <img src="/imgs/mastercard-securecode.png" class="padding-left10 opacity">
                <img src="/imgs/mir-accept.png" class="padding-left10 opacity">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 text-center">
            <div class="footcopyr">ООО «ПРОЦЕССИНГОВАЯ КОМПАНИЯ БЫСТРЫХ ПЛАТЕЖЕЙ»</div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

    <div id="frame3ds" class="BankFrame" style="height: 600px; display: none">
        <form id="form3ds" action="" method='POST'>
            <input type="hidden" id="pareq3ds" name="PaReq" value="">
            <input type="hidden" id="md3ds" name="MD" value="">
            <input type="hidden" id="creq3ds" name="creq" value="">
            <input type="hidden" id="termurl3ds" name="TermUrl" value="">
            <input type="hidden" id="threeDSServerTransID" name="ThreeDSServerTransID" value="">
        </form>
    </div>

</section>
</div>

<div class="modal fade" id="3ds-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Подтверждение операции</h4>
            </div>
            <div class="modal-body">
                <iframe id="3ds-modal__form-iframe" src="" frameborder="0">

                </iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<noscript><div><img src="https://mc.yandex.ru/watch/66552382" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<?php
$this->registerJs('payform.init();');
if (isset($apple['IsUseApplepay']) && $apple['IsUseApplepay'] && isset($apple['Apple_MerchantID']) && !empty($apple['Apple_MerchantID'])) {
    $this->registerJs('payform.applepay("' . $apple['Apple_MerchantID'] . '", "' . ($params['SummFull'] / 100.0) . '", "' . $params['NamePartner'] . '");');
}
if (isset($google['IsUseGooglepay']) && $google['IsUseGooglepay']) {
    $this->registerJsFile('https://pay.google.com/gp/p/js/pay.js');
    $this->registerJs('payform.googlepay("' .
        $google['Google_MerchantID'] . '", "' .
        number_format($params['SummFull'] / 100.0, 2, '.', '') . '", "' .
        $params['NamePartner'] . '", "' .
        'mtsbank", "' .
        (Yii::$app->params['DEVMODE'] == 'Y' || Yii::$app->params['TESTMODE'] == 'Y' ? 1 : 0) .
        '");'
    );
}
if (isset($samsung['IsUseSamsungpay']) && $samsung['IsUseSamsungpay']) {
    $this->registerJs('payform.samsungpay("' . $samsung['Samsung_MerchantID'] . '", "' . number_format($params['SummFull'] / 100.0, 2, '.', '') . '", "' . $params['NamePartner'] . '");');
}
$this->registerJs('setTimeout(tracking.sendToServer, 500)', \yii\web\View::POS_READY);
$this->registerJsFile('/payasset/js/ym.js');
?>
