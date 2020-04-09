<?php
/* @var $this \yii\web\View */
/* @var $PartnerReg PartnerReg */
/* @var $Partner Partner */

use app\models\payonline\Partner;
use app\models\site\PartnerReg;
use yii\bootstrap\ActiveForm;

$this->title = "Регистация в системе";

?>

<div class="row">
    <div class="col-sm-12">
        <h3 class="text-center">Регистация в системе Vepay</h3>
    </div>
</div>
<div class="row">
<?php
    $form = ActiveForm::begin([
        'layout' => 'horizontal',
        'id' => 'siteregisterform',
        'options' => [
            'name' => 'Partner'
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
        <div class="col-sm-12">
            <h4 class="text-center">Данные</h4>
        </div>
    </div>

    <div class="form-group row">
        <label class="control-label col-sm-3">Тип контрагента</label>
        <div class="col-sm-8">
            <div class="radio">
                <input type="radio" name="Partner[TypeMerchant]" id="TypeMerchant1" value="0" checked="checked">
                <label for="TypeMerchant1">
                    Мерчант
                </label>
            </div>
            <div class="radio">
                <input type="radio" name="Partner[TypeMerchant]" id="TypeMerchant2" value="1">
                <label for="TypeMerchant2">
                    Партнер
                </label>
            </div>
        </div>
    </div>
    <div class="form-group row">
        <label class="control-label col-sm-3">Юридический статус</label>
        <div class="col-sm-8">
            <div class="radio">
                <input type="radio" name="Partner[UrState]" id="UrState1" value="0" checked="checked">
                <label for="UrState1">
                    Юридическое лицо
                </label>
            </div>
            <div class="radio">
                <input type="radio" name="Partner[UrState]" id="UrState2" value="1">
                <label for="UrState2">
                    Иидивидуальный предприниматель
                </label>
            </div>
            <div class="radio">
                <input type="radio" name="Partner[UrState]" id="UrState3" value="2">
                <label for="UrState3">
                    Физическое лицо
                </label>
            </div>
        </div>
    </div>
<?php

    echo $form->field($Partner,'Name')->textInput(['class'=>'form-control', 'maxlength' => 250]);
    echo $form->field($Partner,'UrLico')->textInput(['class'=>'form-control', 'maxlength' => 250]);
    echo $form->field($Partner,'INN')->textInput(['class'=>'form-control', 'maxlength' => 13]);
    echo $form->field($Partner,'KPP')->textInput(['class'=>'form-control', 'maxlength' => 12]);
    echo $form->field($Partner,'OGRN')->textInput(['class'=>'form-control', 'maxlength' => 20]);
    echo $form->field($Partner,'UrAdres')->textInput(['class'=>'form-control', 'maxlength' => 1000]);
    echo $form->field($Partner,'PostAdres')->textInput(['class'=>'form-control', 'maxlength' => 1000]);
?>
    <div class="row">
        <div class="col-sm-12">
            <h4 class="text-center">Договор</h4>
        </div>
    </div>
<?php
    echo $form->field($Partner,'PodpisantFull')->textInput(['class'=>'form-control', 'maxlength' => 100]);
    echo $form->field($Partner,'PodpisantShort')->textInput(['class'=>'form-control', 'maxlength' => 50]);
    echo $form->field($Partner,'PodpDoljpost')->textInput(['class'=>'form-control', 'maxlength' => 100]);
    echo $form->field($Partner,'PodpDoljpostRod')->textInput(['class'=>'form-control', 'maxlength' => 100]);
    echo $form->field($Partner,'PodpOsnovan')->textInput(['class'=>'form-control', 'maxlength' => 100]);
    echo $form->field($Partner,'PodpOsnovanRod')->textInput(['class'=>'form-control', 'maxlength' => 100]);
?>
    <div class="row">
        <div class="col-sm-12">
            <h4 class="text-center">Контакты</h4>
        </div>
    </div>
<?php
    echo $form->field($Partner,'URLSite')->textInput(['class'=>'form-control', 'maxlength' => 100]);
    echo $form->field($Partner,'Phone')->textInput(['class'=>'form-control', 'maxlength' => 50]);
    echo $form->field($Partner,'KontTehPhone')->textInput(['class'=>'form-control', 'maxlength' => 50]);
    echo $form->field($Partner,'KontFinansPhone')->textInput(['class'=>'form-control', 'maxlength' => 50]);
    echo $form->field($Partner,'Email')->staticControl();
    echo $form->field($Partner,'KontTehEmail')->textInput(['class'=>'form-control', 'maxlength' => 50]);
    echo $form->field($Partner,'KontFinansEmail')->textInput(['class'=>'form-control', 'maxlength' => 50]);
?>
    <div class="row">
        <div class="col-sm-12">
            <h4 class="text-center">Банковские реквизиты</h4>
        </div>
    </div>
<?php
    echo $form->field($Partner,'BankName')->textInput(['class'=>'form-control', 'maxlength' => 200]);
    echo $form->field($Partner,'BikBank')->textInput(['class'=>'form-control', 'maxlength' => 9]);
    echo $form->field($Partner,'RSchet')->textInput(['class'=>'form-control', 'maxlength' => 20]);
    echo $form->field($Partner,'KSchet')->textInput(['class'=>'form-control', 'maxlength' => 20]);
?>
    <div class="row">
        <div class="col-sm-8 col-sm-offset-3">
            <input type="hidden" name="regid" value="<?=$PartnerReg->ID?>">
            <button type="button" class="btn btn-primary" id="regpartnerbtn">Сохранить</button>
        </div>
    </div>
<?php
    ActiveForm::end();
?>
</div>

<?php $this->registerJs('site.registerform();');