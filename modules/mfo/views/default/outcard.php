<?php

/* @var $IdPay int */
/* @var $cardNumber string|null */

use app\models\payonline\Provparams;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

$provData = new Provparams();

?>
<section class="container">
<div class="panel-group hidden-xs">
</div>
<div class="provblock">
<div class="backgrform" style="padding: 15px 0 0 0 !important;">

    <div class="row" style="padding: 0 15px !important;">
        <div class="col-sm-8 col-xs-12">
            <h3>Регистрация карты</h3>
        </div>
        <div class="col-sm-4 col-xs-12">
            <div class="hidden-xs">
            </div>
        </div>
    </div>

    <div id="form_regcard" style="padding: 0 15px !important;">

        <?php
        $form = ActiveForm::begin([
            'id' => 'regoutcard',
            'options' => [
                'class' => 'form-horizontal'
            ],
            'fieldConfig' => [
                'template' => "{label}<div class='col-xs-12 col-sm-8'>{input}{hint}</div>",
            ]
        ]); ?>
        <div class="clearfix">

        </div>

        <?=$form->field($provData, 'param[]', [
                'options' => [
                    'class' => 'form-group',
                    'style' => 'margin-bottom: 5px !important'
                ]
            ])
            ->textInput([
                'data-inputmask-placeholder' => '_',
                'data-inputmask-jitMasking' => 'true',
                'data-inputmask-mask' => '9999 9999 9999 999999',
                'data-inputmask-regex' => '\d{16,18}',
                'class' => 'input-sm form-control',
                'value' => $cardNumber ?? '',
                'placeholder' => '0000 0000 0000 0000'
            ])
            ->label('Номер карты', [
                'class' => 'col-xs-12 col-sm-4 label-sm control-label'
            ]);
        ?>

        <?= $form->field($provData, 'prov', [
            'template' => "{input}",
        ])->hiddenInput(['value' => 1])->label(''); ?>

        <?php
        echo Html::input('hidden', 'IdPay', $IdPay);
        ?>

        <div class="clearfix margin-top20 margin-bottom20">
            <div class="form-group form-inline" style="margin: 10px 0 0 0">
                <div class="pull-right">
                    <?= Html::submitButton('Привязать', ['id' => 'submitpay', 'class' => 'btn btn-default']); ?>
                </div>
            </div>
            <div class="col-xs-12" style="margin: 10px 0 0 0">
                <div id='error_message' class='error'></div>
            </div>

        </div>

        <? ActiveForm::end(); ?>

    </div>

</div>
</div>
</section>

<script>
    var outCardForm = function() {
        $('input[data-inputmask-mask]', '#regoutcard').inputmask();

        $('#regoutcard').off('submit').on('submit', function () {
            if (!validateFields()) return false;

            $('input[data-inputmask-mask]', '#regoutcard').each(function () {
                //unmask
                var val = $(this).inputmask('unmaskedvalue');
                if (val) {
                    $(this).inputmask('remove');
                    $(this).val(val);
                }
            });
            var form = $(this).serialize();
            if (linklink) {
                linklink.abort();
            }
            linklink = $.ajax({
                type: 'POST',
                url: "/mfo/default/addoutcard",
                data: form,
                beforeSend: function () {
                    $('#error_message').html('');
                    $('#submitpay').prop('disabled', true); //блок кнопки
                },
                success: function (data, textStatus, jqXHR) {
                    if (data.status == 1) {
                        $('input[data-inputmask-mask]', '#regoutcard').prop('disabled', true);
                        $('#error_message').html(data.message);
                    } else {
                        $('#error_message').html(data.message);
                        $('input[data-inputmask-mask]', '#regoutcard').inputmask();
                        $('#submitpay').prop('disabled', false);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR);
                    $('#error_message').html("Ошибка запроса");
                    $('input[data-inputmask-mask]', '#regoutcard').inputmask();
                    $('#submitpay').prop('disabled', false);
                }
            });
            return false;

        });

        function validateFields() {
            var err = false;

            $.each($('input[data-inputmask-mask], input.nomask', '#regoutcard'), function () {
                //console.log($(this));
                var val = $(this).inputmask('unmaskedvalue');
                //var valid = Inputmask.isValid($(this).val(), $(this).attr('data-inputmask-mask'));
                var re1 = new RegExp($(this).attr('data-inputmask-regex'));
                var valid = re1.test(val);
                if (!valid) {
                    CustomValid.showErrorValid($(this), err);
                    err = err | !valid;
                }
            });

            return !err;
        }
    };

    if (window.jQuery) {
        jQuery(document).ready(function () {
            outCardForm();
        });
    }
</script>

<?php
$this->registerJs('outCardForm();', yii\web\View::POS_READY);
?>