<?php

use app\models\partner\PartnerUsers;
use app\models\payonline\Partner;
use yii\bootstrap\ActiveForm;

/* @var yii\web\View $this */
/* @var Partner $partner */
/* @var PartnerUsers $PartnerAdmin */


?>

<div class="ibox-content" style="border: none;">
    <div class="sk-spinner sk-spinner-wave">
        <div class="sk-rect1"></div>
        <div class="sk-rect2"></div>
        <div class="sk-rect3"></div>
        <div class="sk-rect4"></div>
        <div class="sk-rect5"></div>
    </div>

<?php
    $form = ActiveForm::begin([
        'layout' => 'horizontal',
        'id' => 'formEditPartner',
        'options' => [
            'name' => 'formEditPartner'
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
?>

<div class="row">
    <div class="m-md">
        <h3>Контрагент</h3>
    </div>
</div>

<div class="form-group row">
    <label class="control-label col-sm-3">Тип контрагента</label>
    <div class="col-sm-8">
        <div class="radio">
            <input type="radio" name="Partner[TypeMerchant]" id="TypeMerchant1" value="0" <?=$partner->TypeMerchant == 0 ? 'checked="checked"' : ''?>>
            <label for="TypeMerchant1">
                Мерчант
            </label>
        </div>
        <div class="radio">
            <input type="radio" name="Partner[TypeMerchant]" id="TypeMerchant2" value="1" <?=$partner->TypeMerchant == 1 ? 'checked="checked"' : ''?>>
            <label for="TypeMerchant2">
                Партнер
            </label>
        </div>
    </div>
</div>

<?php
echo $form->field($partner,'Name')->textInput(['class'=>'form-control']);
echo $form->field($partner,'UrLico')->textInput(['class'=>'form-control']);
echo $form->field($partner,'INN')->textInput(['class'=>'form-control']);
echo $form->field($partner,'KPP')->textInput(['class'=>'form-control']);
echo $form->field($partner,'OGRN')->textInput(['class'=>'form-control']);
echo $form->field($partner,'UrAdres')->textInput(['class'=>'form-control']);
echo $form->field($partner,'PostAdres')->textInput(['class'=>'form-control']);
echo $form->field($partner,'URLSite')->textInput(['class'=>'form-control']);
?>
<div class="row no-margins">
    <div class="col-sm-8 col-sm-offset-3">
        <?php
        echo $form->field($partner,'IsMfo')->checkbox([
            'template' => "<div class=\"checkbox m-l-sm\">\n{input}\n{beginLabel}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
        ]);
        ?>
    </div>
</div>

<div class="hr-line-dashed"></div>
<div class="row">
    <div class="m-md">
        <h3>Договор</h3>
    </div>
</div>
<?php
echo $form->field($partner,'NumDogovor')->textInput(['class'=>'form-control']);
echo $form->field($partner,'DateDogovor')->textInput(['class'=>'form-control']);
echo $form->field($partner,'PodpisantFull')->textInput(['class'=>'form-control']);
echo $form->field($partner,'PodpisantShort')->textInput(['class'=>'form-control']);
echo $form->field($partner,'PodpDoljpost')->textInput(['class'=>'form-control']);
echo $form->field($partner,'PodpDoljpostRod')->textInput(['class'=>'form-control']);
echo $form->field($partner,'PodpOsnovan')->textInput(['class'=>'form-control']);
echo $form->field($partner,'PodpOsnovanRod')->textInput(['class'=>'form-control']);
?>

<div class="hr-line-dashed"></div>
<div class="row">
    <div class="m-md">
        <h3>Контакты</h3>
    </div>
</div>
<?php
echo $form->field($partner,'Phone')->textInput(['class'=>'form-control']);
echo $form->field($partner,'Email')->textInput(['class'=>'form-control']);
echo $form->field($partner,'KontTehFio')->textInput(['class'=>'form-control']);
echo $form->field($partner,'KontTehPhone')->textInput(['class'=>'form-control']);
echo $form->field($partner,'KontTehEmail')->textInput(['class'=>'form-control']);
echo $form->field($partner,'KontFinansFio')->textInput(['class'=>'form-control']);
echo $form->field($partner,'KontFinansPhone')->textInput(['class'=>'form-control']);
echo $form->field($partner,'KontFinansEmail')->textInput(['class'=>'form-control']);
echo $form->field($partner,'EmailNotif')->textInput(['class'=>'form-control']);
?>

<div class="hr-line-dashed"></div>
<div class="row">
    <div class="m-md">
        <h3>Банковские реквизиты</h3>
    </div>
</div>

<?php
echo $form->field($partner,'BankName')->textInput(['class'=>'form-control']);
echo $form->field($partner,'BikBank')->textInput(['class'=>'form-control']);
echo $form->field($partner,'RSchet')->textInput(['class'=>'form-control']);
echo $form->field($partner,'KSchet')->textInput(['class'=>'form-control']);
?>

    <div class="row">
        <div class="col-sm-8 col-sm-offset-3">
            <input type="hidden" name="Partner_ID" value="<?=$partner->ID?>">
            <button type="button" class="btn btn-primary" id="btnEditPartner">Сохранить</button>
        </div>
    </div>
<?php
    ActiveForm::end();
?>

<?php
    $form = ActiveForm::begin([
        'layout' => 'horizontal',
        'id' => 'formEditPartnerStatus',
        'options' => [
            'name' => 'formEditPartnerStatus'
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
?>

<div class="hr-line-dashed"></div>
<div class="row">
    <div class="m-md">
        <h3>Статус</h3>
    </div>
</div>

<div class="row no-margins">
    <div class="col-sm-8 col-sm-offset-3">
        <?php
        echo $form->field($partner,'IsBlocked')->checkbox([
            'template' => "<div class=\"checkbox m-l-sm\">\n{input}\n{beginLabel}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
        ]);
        ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-8 col-sm-offset-3">
        <input type="hidden" name="Partner_ID" value="<?=$partner->ID?>">
        <button type="button" class="btn btn-primary" id="btnEditPartnerStatus">Сохранить</button>
    </div>
</div>
<?php
ActiveForm::end();
?>

<div class="hr-line-dashed"></div>
<div class="row">
    <div class="m-md">
        <h3>Доступ к кабинету</h3>
    </div>
</div>

    <?php
    $form = ActiveForm::begin([
        'layout' => 'horizontal',
        'id' => 'formEditPartnerAdmin',
        'options' => [
            'name' => 'formEditPartnerAdmin'
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

    echo $form->field($PartnerAdmin,'FIO')->textInput(['class'=>'form-control', 'autocomplete' => 'off']);
    echo $form->field($PartnerAdmin,'Login')->textInput(['class'=>'form-control', 'autocomplete' => 'off']);
    echo $form->field($PartnerAdmin,'Password')->passwordInput(['class'=>'form-control', 'autocomplete' => 'new-password', 'value' => '']);
    echo $form->field($PartnerAdmin,'Password2')->passwordInput(['class'=>'form-control', 'autocomplete' => 'new-password', 'value' => ''])->hint('Пароль должен содержать буквенные и цифровые символы и быть длиной не менее 8 символов');
    ?>
    <div class="row no-margins">
        <div class="col-sm-8 col-sm-offset-3">
            <?php
            echo $form->field($PartnerAdmin,'IsActive')->checkbox([
                'template' => "<div class=\"checkbox m-l-sm\">\n{input}\n{beginLabel}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
            ]);
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-8 col-sm-offset-3">
            <input type="hidden" name="ID" value="<?=(int)$PartnerAdmin->ID?>">
            <input type="hidden" name="razdely[]" value="">
            <input type="hidden" name="PartnerUsers[RoleUser]" value="1">
            <input type="hidden" name="PartnerUsers[IdPartner]" value="<?=$partner->ID?>">
            <button type="button" class="btn btn-primary" id="btnEditPartnerAdmin">Сохранить</button>
        </div>
    </div>

<?php
    ActiveForm::end();
?>


</div>