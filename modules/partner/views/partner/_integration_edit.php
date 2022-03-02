<?php

/* @var $this View */
/* @var array $users */
/* @var $partner Partner */
/* @var MfoSettings $settings */

$publicKey = isset($partner->accessSms->public_key)? $partner->accessSms->public_key: "";
$secretKey = isset($partner->accessSms->secret_key)?$partner->accessSms->secret_key:"";

use app\models\bank\Banks;
use app\models\mfo\MfoSettings;
use app\models\payonline\Partner;
use app\models\payonline\PartnerBankRekviz;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\web\View;

?>

<div class="row">
    <div class="m-md">
        <h3>Настройки терминалов ТКБ</h3>
    </div>
</div>

<?php
$form = ActiveForm::begin([
    'layout' => 'horizontal',
    'id' => 'formEditPartnerTkb',
    'options' => [
        'name' => 'formEditPartnerTkb'
    ],
    'successCssClass' => '',
    'fieldConfig' => [
        'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
        'horizontalCssClasses' => [
            'label' => 'col-sm-3',
            'wrapper' => 'col-sm-8',
            'error' => '',
            'hint' => '',
        ],
    ],
]);

echo $form->field($partner, 'BankForPaymentId')->dropDownList(Banks::getBanksByDropdown(), ['class' => 'form-control']);
if ($partner->IsMfo) {
    echo $form->field($partner, 'SchetTcb')->textInput(['class' => 'form-control']);
    echo $form->field($partner, 'SchetTcbTransit')->textInput(['class' => 'form-control']);
    echo $form->field($partner, 'SchetTcbNominal')->textInput(['class' => 'form-control']);
    echo $form->field($partner, 'SchetTCBUnreserve')->textInput(['class' => 'form-control']);
    ?>
    <div class="row no-margins">
        <div class="col-sm-8 col-sm-offset-3">
            <?php
            echo $form->field($partner, 'IsUnreserveComis')->checkbox([
                'template' => "<div class=\"checkbox m-l-sm\">\n{input}\n{beginLabel}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
            ]);
            ?>
        </div>
    </div>

    <div class="row no-margins">
        <div class="col-sm-8 col-sm-offset-3">
            <?php
            echo $form->field($partner,'VoznagVyplatDirect')->checkbox([
                'template' => "<div class=\"checkbox m-l-sm\">\n{input}\n{beginLabel}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
            ]);
            ?>
        </div>
    </div>

    <div class="row no-margins">
        <div class="col-sm-8 col-sm-offset-3">
            <?php
            echo $form->field($partner,'IsCommonSchetVydacha')->checkbox([
                'template' => "<div class=\"checkbox m-l-sm\">\n{input}\n{beginLabel}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
            ]);
            ?>
        </div>
    </div>

    <?php
    echo $form->field($partner, 'LoginTkbEcom')->textInput(['class' => 'form-control']);
    echo $form->field($partner, 'KeyTkbEcom')->textInput(['class' => 'form-control']);
    echo $form->field($partner, 'LoginTkbAft')->textInput(['class' => 'form-control']);
    echo $form->field($partner, 'KeyTkbAft')->textInput(['class' => 'form-control']);
    echo $form->field($partner, 'LoginTkbOct')->textInput(['class' => 'form-control']);
    echo $form->field($partner, 'KeyTkbOct')->textInput(['class' => 'form-control']);
    echo $form->field($partner, 'LoginTkbVyvod')->textInput(['class' => 'form-control']);
    echo $form->field($partner, 'KeyTkbVyvod')->textInput(['class' => 'form-control']);
    echo $form->field($partner, 'LoginTkbPerevod')->textInput(['class' => 'form-control']);
    echo $form->field($partner, 'KeyTkbPerevod')->textInput(['class' => 'form-control']);

    echo $form->field($partner, 'LoginTkbOctVyvod')->textInput(['class' => 'form-control']);
    echo $form->field($partner, 'KeyTkbOctVyvod')->textInput(['class' => 'form-control']);
    echo $form->field($partner, 'LoginTkbOctPerevod')->textInput(['class' => 'form-control']);
    echo $form->field($partner, 'KeyTkbOctPerevod')->textInput(['class' => 'form-control']);

} else {
    echo $form->field($partner, 'SchetTcbTransit')->textInput(['class' => 'form-control']);
    echo $form->field($partner, 'LoginTkbJkh')->textInput(['class' => 'form-control']);
    echo $form->field($partner, 'KeyTkbJkh')->textInput(['class' => 'form-control']);
    echo $form->field($partner, 'LoginTkbEcom')->textInput(['class' => 'form-control']);
    echo $form->field($partner, 'KeyTkbEcom')->textInput(['class' => 'form-control']);
    echo $form->field($partner, 'LoginTkbVyvod')->textInput(['class' => 'form-control']);
    echo $form->field($partner, 'KeyTkbVyvod')->textInput(['class' => 'form-control']);
}

echo $form->field($partner, 'SchetTcbParts')->textInput(['class' => 'form-control']);

?>
<div class="row">
    <div class="col-sm-8 col-sm-offset-3">
        <?= Html::hiddenInput('Partner_ID', $partner->ID) ?>
        <button type="button" class="btn btn-primary" id="btnEditPartnerTkb">Сохранить</button>
    </div>
</div>
<?php
ActiveForm::end();
?>

<div class="row">
    <div class="m-md">
        <h3>Настройки ApplePay, GooglePay, SamsungPay</h3>
    </div>
</div>

<?php
$form = ActiveForm::begin([
    'layout' => 'horizontal',
    'id' => 'formEditPartnerApplepay',
    'options' => [
        'name' => 'formEditPartnerApplepay'
    ],
    'successCssClass' => '',
    'fieldConfig' => [
        'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
        'horizontalCssClasses' => [
            'label' => 'col-sm-3',
            'wrapper' => 'col-sm-8',
            'error' => '',
            'hint' => '',
        ],
    ],
]);

echo $form->field($partner, 'Apple_MerchantID')->textInput(['class' => 'form-control']);
echo $form->field($partner, 'Apple_displayName')->textInput(['class' => 'form-control']);
//echo $form->field($partner, 'Apple_PayProcCert')->textarea(['class' => 'form-control']);
echo $form->field($partner, 'Apple_KeyPasswd')->textInput(['class' => 'form-control']);
echo $form->field($partner, 'Apple_MerchIdentKey')->fileInput(['class' => 'form-control'])->hint($partner->Apple_MerchIdentKey);
echo $form->field($partner, 'Apple_MerchIdentCert')->fileInput(['class' => 'form-control'])->hint($partner->Apple_MerchIdentCert);
?>
<div class="row no-margins">
    <div class="col-sm-8 col-sm-offset-3">
        <?php
        echo $form->field($partner,'IsUseApplepay')->checkbox([
            'template' => "<div class=\"checkbox m-l-sm\">\n{input}\n{beginLabel}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
        ]);
        ?>
    </div>
</div>

<?= $form->field($partner, 'GoogleMerchantID')->textInput(['class' => 'form-control']);?>
<div class="row no-margins">
    <div class="col-sm-8 col-sm-offset-3">
        <?php
        echo $form->field($partner,'IsUseGooglepay')->checkbox([
            'template' => "<div class=\"checkbox m-l-sm\">\n{input}\n{beginLabel}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
        ]);
        ?>
    </div>
</div>
<?= $form->field($partner, 'SamsungMerchantID')->textInput(['class' => 'form-control']);?>
<div class="row no-margins">
    <div class="col-sm-8 col-sm-offset-3">
        <?php
        echo $form->field($partner,'IsUseSamsungpay')->checkbox([
            'template' => "<div class=\"checkbox m-l-sm\">\n{input}\n{beginLabel}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
        ]);
        ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-8 col-sm-offset-3">
        <?= Html::hiddenInput('Partner_ID', $partner->ID) ?>
        <button type="button" class="btn btn-primary" id="btnEditPartnerApplepay">Сохранить</button>
    </div>
</div>
<?php
ActiveForm::end();
?>

<div class="hr-line-dashed"></div>
<div class="row">
    <div class="m-md">
        <h3>Интеграция</h3>
    </div>
</div>

<?php
$form = ActiveForm::begin([
    'layout' => 'horizontal',
    'id' => 'formEditPartnerIntegr',
    'options' => [
        'name' => 'formEditPartnerIntegr'
    ],
    'successCssClass' => '',
    'fieldConfig' => [
        'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
        'horizontalCssClasses' => [
            'label' => 'col-sm-3',
            'wrapper' => 'col-sm-8',
            'error' => '',
            'hint' => '',
        ],
    ],
]);

echo $form->field($partner, 'ID')->staticControl()->label('ID');
echo $form->field($partner, 'PaaswordApi')->textInput(['class' => 'form-control']);
echo $form->field($partner, 'IpAccesApi')->textInput(['class' => 'form-control']);

?>

<div class="row no-margins">
    <div class="col-sm-8 col-sm-offset-3">
        <?php
        echo $form->field($partner,'IsAftOnly')->checkbox([
            'template' => "<div class=\"checkbox m-l-sm\">\n{input}\n{beginLabel}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
        ]);
        ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-8 col-sm-offset-3">
        <?= Html::hiddenInput('Partner_ID', $partner->ID) ?>
        <button type="button" class="btn btn-primary" id="btnEditPartnerIntegr">Сохранить</button>
    </div>
</div>
<?php
ActiveForm::end();
?>

<div class="hr-line-dashed"></div>
<div class="row">
    <div class="m-md">
        <h3>Реквизиты для перечислений</h3>
    </div>
</div>

<?php
$bankrecv = $partner->getPartner_bank_rekviz()->one();
if (!$bankrecv) {
    $bankrecv = new PartnerBankRekviz();
    $bankrecv->IdPartner = $partner->ID;
}
$form = ActiveForm::begin([
    'layout' => 'horizontal',
    'id' => 'formEditRekviz',
    'options' => [
        'name' => 'formEditRekviz'
    ],
    'successCssClass' => '',
    'fieldConfig' => [
        'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
        'horizontalCssClasses' => [
            'label' => 'col-sm-3',
            'wrapper' => 'col-sm-8',
            'error' => '',
            'hint' => '',
        ],
    ],
]);
echo $form->field($bankrecv,'IdPartner')->hiddenInput()->label(false);
echo $form->field($bankrecv,'NamePoluchat')->textInput(['class'=>'form-control']);
echo $form->field($bankrecv,'INNPolushat')->textInput(['class'=>'form-control']);
echo $form->field($bankrecv,'KPPPoluchat')->textInput(['class'=>'form-control']);
echo $form->field($bankrecv,'KorShetPolushat')->textInput(['class'=>'form-control']);
echo $form->field($bankrecv,'RaschShetPolushat')->textInput(['class'=>'form-control']);
echo $form->field($bankrecv,'NameBankPoluchat')->textInput(['class'=>'form-control']);
echo $form->field($bankrecv,'SityBankPoluchat')->textInput(['class'=>'form-control']);
echo $form->field($bankrecv,'BIKPoluchat')->textInput(['class'=>'form-control']);
echo $form->field($bankrecv,'PokazKBK')->textInput(['class'=>'form-control']);
echo $form->field($bankrecv,'OKATO')->textInput(['class'=>'form-control']);
echo $form->field($bankrecv,'NaznachenPlatez')->textInput(['class'=>'form-control']);
echo $form->field($bankrecv,'MinSummReestrToOplat')->textInput(['class'=>'form-control']);
echo $form->field($bankrecv,'MaxIntervalOplat')->textInput(['class'=>'form-control']);
?>
<div class="col-sm-8 col-sm-offset-3">
    <?php
    echo $form->field($bankrecv,'IsDecVoznagPerecisl')->checkbox([
        'template' => "<div class=\"checkbox m-l-sm\">\n{input}\n{beginLabel}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
    ]);
    ?>
</div>

<div class="row">
    <div class="col-sm-8 col-sm-offset-3">
        <?= Html::hiddenInput('IdRecviz', $bankrecv->ID) ?>
        <button type="button" class="btn btn-primary" id="btnEditRekviz">Сохранить</button>
    </div>
</div>
<?php
ActiveForm::end();
?>

<div class="hr-line-dashed"></div>
<div class="row">
    <div class="m-md">
        <h3>Настройка OrandeData</h3>
    </div>
</div>

<?php
$form = ActiveForm::begin([
    'layout' => 'horizontal',
    'id' => 'formEditPartnerKkm',
    'options' => [
        'name' => 'formEditPartnerKkm'
    ],
    'successCssClass' => '',
    'fieldConfig' => [
        'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
        'horizontalCssClasses' => [
            'label' => 'col-sm-3',
            'wrapper' => 'col-sm-8',
            'error' => '',
            'hint' => '',
        ],
    ],
]);

echo $form->field($partner, 'OrangeDataSingKey')->fileInput(['class' => 'form-control'])->hint($partner->OrangeDataSingKey);
echo $form->field($partner, 'OrangeDataConKey')->fileInput(['class' => 'form-control'])->hint($partner->OrangeDataConKey);
echo $form->field($partner, 'OrangeDataConCert')->fileInput(['class' => 'form-control'])->hint($partner->OrangeDataConCert);

?>

<div class="row no-margins">
    <div class="col-sm-8 col-sm-offset-3">
        <?php
        echo $form->field($partner,'IsUseKKmPrint')->checkbox([
            'template' => "<div class=\"checkbox m-l-sm\">\n{input}\n{beginLabel}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
        ]);
        ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-8 col-sm-offset-3">
        <?= Html::hiddenInput('Partner_ID', $partner->ID) ?>
        <button type="button" class="btn btn-primary" id="btnEditPartnerKkm">Сохранить</button>
    </div>
</div>
<?php
ActiveForm::end();
?>

<div class="hr-line-dashed"></div>
<div class="row">
    <div class="m-md">
        <h3>Оповещения</h3>
    </div>
</div>


<form method="post" class="form-horizontal" id="formOpoveshSettings">

    <div class="form-group">
        <label class="col-sm-3 control-label">Адрес для обратного запроса</label>
        <div class="col-sm-8 col-md-6">
            <input type="url" name="Settings[url]" value="<?=Html::encode($settings->url)?>" maxlength="300" class="form-control">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label">Ключ обратного запроса</label>
        <div class="col-sm-8 col-md-6">
            <input type="text" name="Settings[key]" value="<?=Html::encode($settings->key)?>" maxlength="20" class="form-control">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 text-right">Отправлять ExtId</label>
        <div class="col-sm-8 col-md-6">
            <input type="checkbox" name="Settings[CallbackSendExtId]" <?=$settings->CallbackSendExtId ? 'checked':''?> value="1" class="form-check-input">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 text-right">Отправлять Id</label>
        <div class="col-sm-8 col-md-6">
            <input type="checkbox" name="Settings[CallbackSendId]" <?=$settings->CallbackSendId ? 'checked':''?> value="1" class="form-check-input">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 text-right">Отправлять Sum</label>
        <div class="col-sm-8 col-md-6">
            <input type="checkbox" name="Settings[CallbackSendSum]" <?=$settings->CallbackSendSum ? 'checked':''?> value="1" class="form-check-input">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 text-right">Отправлять Status</label>
        <div class="col-sm-8 col-md-6">
            <input type="checkbox" name="Settings[CallbackSendStatus]" <?=$settings->CallbackSendStatus ? 'checked':''?> value="1" class="form-check-input">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 text-right">Отправлять Channel</label>
        <div class="col-sm-8 col-md-6">
            <input type="checkbox" name="Settings[CallbackSendChannel]" <?=$settings->CallbackSendChannel ? 'checked':''?> value="1" class="form-check-input">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 text-right">Отправлять маску карты</label>
        <div class="col-sm-8 col-md-6">
            <input type="checkbox" name="Settings[CallbackSendCardMask]" <?=$settings->CallbackSendCardMask ? 'checked':''?> value="1" class="form-check-input">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 text-right">Отправлять код ошибки</label>
        <div class="col-sm-8 col-md-6">
            <input type="checkbox" name="Settings[CallbackSendErrorCode]" <?=$settings->CallbackSendErrorCode ? 'checked':''?> value="1" class="form-check-input">
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-12">
            <h4>Адрес возврата:</h4>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label">Адрес возврата на страницу сайта (успех)</label>
        <div class="col-sm-8 col-md-6">
            <input type="url" name="Settings[UrlReturn]" value="<?=Html::encode($settings->UrlReturn)?>" maxlength="300" class="form-control">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label">Адрес возврата на страницу сайта (ошибка)</label>
        <div class="col-sm-8 col-md-6">
            <input type="url" name="Settings[UrlReturnFail]" value="<?=Html::encode($settings->UrlReturnFail)?>" maxlength="300" class="form-control">
        </div>
    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label">Адрес возврата на страницу сайта (отмена)</label>
        <div class="col-sm-8 col-md-6">
            <input type="url" name="Settings[UrlReturnCancel]" value="<?=Html::encode($settings->UrlReturnCancel)?>" maxlength="300" class="form-control">
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-12">
            <h4>Адрес проверки возможности оплаты:</h4>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label">Адрес проверки возможности оплаты</label>
        <div class="col-sm-8 col-md-6">
            <input type="url" name="Settings[UrlCheckReq]" value="<?=Html::encode($settings->UrlCheckReq)?>" maxlength="300" class="form-control">
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-4">
            <?= Html::hiddenInput('_csrf', Yii::$app->request->csrfToken, ['id' => '_csrf']) ?>
            <?= Html::hiddenInput('IdPartner', $partner->ID) ?>
            <input name="paytype" type="hidden" value="-1">
            <input name="accountpay" type="hidden" value="">
            <button class="btn btn-primary" type="button" id="btnOpoveshSettings">Сохранить</button>
        </div>
    </div>

</form>

<div class="hr-line-dashed"></div>
<div class="row">
    <div class="m-md">
        <h3>Рассылки</h3>
    </div>
</div>

<form method="post" class="form-horizontal" id="formEditDistribution">
    <div class="form-group row">
        <label class="control-label col-sm-3">Адрес почты</label>
        <div class="col-sm-8">
            <input class="form-control" type="email" placeholder='example@online.ru'
                   name="email" value="<?= Html::encode(isset($partner->distribution->email) ?$partner->distribution->email : "") ?>">
        </div>
    </div>
    <div class="form-group row">
        <div class="col-sm-8 col-sm-offset-3">
            <?php
            $isvydacha = isset($partner->distribution->payment)? $partner->distribution->payment : false;
            $ispayment = isset($partner->distribution->repayment)? $partner->distribution->repayment : false;
            ?>
            <div class="checkbox m-l-sm">
                <input type="hidden" name="payment" value="0">
                <input type="checkbox" id="reestrvydacha" name="payment" value="1" <?= $isvydacha ? "checked" : '' ?>>
                <label for="reestrvydacha">Реестр по выдаче</label>
            </div>

            <div class="checkbox m-l-sm">
                <input type="hidden" name="repayment" value="0">
                <input type="checkbox" id="reestrpogashen" name="repayment" value="1" <?= $ispayment ? "checked" : '' ?>>
                <label for="reestrpogashen">Реестр по погашению</label>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-8 col-sm-offset-3">
            <input name="partner_id" type="hidden" value="<?= isset($partner) ? Html::encode($partner->ID) : 0 ?>">
            <input name="distribution_id" type="hidden" value="<?= isset($partner->distribution->payment) ? Html::encode($partner->distribution->id) : 0 ?>">
            <?= Html::hiddenInput('_csrf', Yii::$app->request->csrfToken, ['id' => '_csrf']) ?>
            <input type="button" class="btn btn-primary" value="Сохранить" id="btnEditDistribution">
        </div>
    </div>
</form>

<div class="hr-line-dashed"></div>
<div class="row">
    <div class="m-md">
        <h3>Настройки сервиса MainSms.ru</h3>
    </div>
</div>

<form class="form-horizontal" id="formEditMainSms">
    <div class="form-group">
        <label class="col-sm-3 control-label">Имя проекта:</label>
        <div class="col-sm-6">
            <input type="text" maxlength="200" class="form-control" name="publicKey" value="<?= Html::encode($publicKey) ?>">
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-3 control-label">APIKEY (ключ проекта):</label>
        <div class="col-sm-6">
            <input type="text" maxlength="50" class="form-control" name="secretKey" value="<?= Html::encode($secretKey) ?>">
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-6">
            <?= Html::hiddenInput('_csrf', Yii::$app->request->csrfToken, ['id' => '_csrf']) ?>
            <?= Html::hiddenInput('idPartner', $partner->ID) ?>
            <button class="btn btn-primary" id="#saveMainsms" type="submit">Сохранить</button>
        </div>
    </div>
</form>
