<?php

use yii\bootstrap\ActiveForm;

/* @var $this \yii\web\View */
/* @var $order \app\models\payonline\OrderPay */
/* @var int $IdPartner */
/* @var bool $IsAdmin */

$this->title = "Создать счет";

$this->params['breadtitle'] = "Создать счет";
$this->params['breadcrumbs'][] = ['label' => 'Счета', 'url' => ['/partner/order']];
$this->params['breadcrumbs'][] = $this->params['breadtitle'];

?>

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Создать счет</h5>
                    <div class="ibox-tools">
                        <a class="btn btn-xs btn-default" href="/partner/order">
                            <i class="fa fa-close" aria-hidden="true"></i> Назад</a>
                    </div>
                </div>
                <div class="ibox-content">
                    <?php
                    $form = ActiveForm::begin([
                        'layout' => 'horizontal',
                        'options' => [
                            'id' => 'formAddOrder',
                            'name' => 'formAddOrder'
                        ],
                        'successCssClass' => '',
                        'fieldConfig' => [
                            'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                            'horizontalCssClasses' => [
                                'label' => 'col-sm-3',
                                'wrapper' => 'col-sm-6',
                                'error' => '',
                                'hint' => '',
                            ],
                        ],
                    ]);?>
                    <?=$form->field($order, 'Comment')->textInput([
                            'class'=>'form-control',
                            'maxlength' => 250
                    ]);?>
                    <?=$form->field($order, 'EmailTo')->textInput([
                            'class'=>'form-control',
                            'maxlength' => 50
                    ]);?>
                    <?=$form->field($order, 'SmsTo')->textInput([
                            'class'=>'form-control',
                            'maxlength' => 20
                    ]);?>

                    <?=$form->field($order, 'SumOrder')->textInput([
                        'class'=>'form-control',
                        'maxlength' => 9,
                        'value' => 0.00
                    ]);?>

                    <div class="row">
                        <div class="col-sm-8 col-sm-offset-3">
                            <input type="hidden" name="ID" value="<?=$order->ID?>">
                            <input type="hidden" name="OrderPay[IdPartner]" value="<?=$order->IdPartner?>">
                            <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                            <button type="button" class="btn btn-primary" id="saveorder">Создать</button>
                        </div>
                    </div>
                    <?php
                    ActiveForm::end();
                    ?>
                </div>
            </div>
        </div>
    </div>

<?php

$this->registerJs(
<<<JS
$('input[data-inputmask-mask]', '#formAddOrder').inputmask();

$('input[name="OrderPay[SumOrder]"]', '#formAddOrder').keypress(function (event) {
    //фильтр для ввода суммы
    let val = $(this).val();
    return (event.charCode >= 48 && event.charCode <= 57) || (event.charCode === 46 && val.indexOf('.') === -1);
}).on('keyup input', function (event) {
});
JS
, \yii\web\View::POS_READY);
$this->registerJs('lk.orderadd();', \yii\web\View::POS_READY);

