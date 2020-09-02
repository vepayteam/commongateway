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

                    <input type="hidden" name="OrderPay[SmsTo]">
                    <?php
                    /*=$form->field($order, 'SmsTo')->textInput([
                            'class'=>'form-control',
                            'maxlength' => 20
                     ]);*/
                     ?>

                    <?=$form->field($order, 'SumOrder')->textInput([
                        'class'=>'form-control',
                        'maxlength' => 9,
                        'value' => 0.00
                    ]);?>


                    <div class="form-group">
                        <div class="col-sm-3 btn btn-primary" id="orderpay-ordertobutton">
                            Добавить описание корзины
                        </div>
                    </div>

                    <div id="orderpay-ordertocart" hidden>

                        <div class="form-group" id=" orderpay-ordertoheader">
                            <div class="col-sm-8">
                                <label class="control-label">Наименование товара</label>
                            </div>
                            <div class="col-sm-1">
                                <label class="control-label">Кол-во</label>
                            </div>
                            <div class="col-sm-2">
                                <label class="control-label">сумма</label>
                            </div>
                            <div class="col-sm-1">
                                <!-- <label class="control-label">сумма</label> -->
                            </div>
                        </div>

                        <div id="orderpay-ordertocart-itemlist">

                        </div>

                        <div class="form-group">
                            <div class="col-sm-3 btn btn-primary" id="orderpay-ordertoaddbutton">
                                Добавить товар
                            </div>
                        </div>
                    </div>

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

                    <div hidden id="orderpay-ordertotemplate">
                        <div class="form-group field-orderpay-orderto">
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="OrderPay[OrderTo][name][]">
                            </div>
                            <div class="col-sm-1">
                                <input type="text" class="form-control field-orderpay-ordertoNum" name="OrderPay[OrderTo][qnt][]">
                            </div>
                            <div class="col-sm-2">
                                <input type="text" class="form-control field-orderpay-ordertoNum" name="OrderPay[OrderTo][sum][]">
                            </div>
                            <div class="col-sm-1 btn btn-primary orderpay-ordertodelbutton" title="Удалить">
                                <i class="fa fa-trash" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>

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

$('#orderpay-ordertocart-itemlist').on('keypress', 'input[name="OrderPay[OrderTo][sum][]"], input[name="OrderPay[OrderTo][qnt][]"]', function (event) {
    //фильтр для ввода суммы
    let val = $(this).val();
    return (event.charCode >= 48 && event.charCode <= 57) || (event.charCode === 46 && val.indexOf('.') === -1);
}).on('keyup input', function (event) {
    orderpay_sumorder_recalc();
});

function orderpay_sumorder_recalc() {
    orderlist = $('#orderpay-ordertocart-itemlist .form-group input[name="OrderPay[OrderTo][sum][]"]');

    summ = 0;
    orderlist.each(function(idx, elem) {
        summ = summ + +$(elem).val();
        });
    $('input[name="OrderPay[SumOrder]"]').val(summ);
}

tpl = $('#orderpay-ordertotemplate');
$('#orderpay-ordertobutton').click(function() {
    $('#orderpay-ordertocart').removeAttr('hidden');
    $(this).css('display', 'none');
    $('#orderpay-ordertocart-itemlist').append(tpl.html());
});
$('#orderpay-ordertoaddbutton').click(function() {
    $('#orderpay-ordertocart-itemlist').append(tpl.html());
});
$('#orderpay-ordertocart-itemlist').on('click', '.orderpay-ordertodelbutton', function(e) {
    $(this).parent().detach();
    if($('#orderpay-ordertocart-itemlist .form-group').length < 1) {
        $('#orderpay-ordertobutton').css('display', 'inline-block');
        $('#orderpay-ordertocart').attr('hidden', true);
    }
    orderpay_sumorder_recalc();
});
JS
, \yii\web\View::POS_READY);
$this->registerJs('lk.orderadd();', \yii\web\View::POS_READY);

