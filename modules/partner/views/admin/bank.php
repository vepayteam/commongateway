<?php

/* @var yii\web\View $this */
/* @var \app\models\bank\Banks $Bank */

$this->title = 'комиссии банка';

$this->params['breadtitle'] = 'Вывод вознаграждения';
$this->params['breadcrumbs'][] = ['label' => 'Вывод вознаграждения', 'url' => ['/partner/admin/comisotchet']];;

$this->params['breadtitle'] = 'Комиссии банка';
$this->params['breadcrumbs'][] = $this->params['breadtitle'];

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\web\View;

?>

    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Комиссии банка</h5>
                </div>
                <div class="ibox-content">
                    <?php
                    $form = ActiveForm::begin([
                        'id' => 'banks',
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

                    <div class="row no-margins">
                        <div class="col-xs-6 col-sm-4">
                            <?= $form->field($Bank, 'EcomComis')->textInput([
                                'class' => 'form-control'
                            ]); ?>
                        </div>
                        <div class="col-xs-6 col-sm-4 p-w-lg">
                         <?= $form->field($Bank, 'JkhComis')->textInput([
                             'class' => 'form-control'
                         ]); ?>
                        </div>
                    </div>

                    <div class="row no-margins">
                        <div class="col-xs-6 col-sm-4">
                        <?= $form->field($Bank, 'AFTComis')->textInput([
                            'class' => 'form-control'
                        ]); ?>
                        </div>
                        <div class="col-xs-6 col-sm-4 p-w-lg">
                        <?= $form->field($Bank, 'AFTComisMin')->textInput([
                            'class' => 'form-control'
                        ]); ?>
                        </div>
                    </div>

                    <div class="row no-margins">
                        <div class="col-xs-6 col-sm-4">
                        <?= $form->field($Bank, 'OCTComis')->textInput([
                            'class' => 'form-control'
                        ]); ?>
                        </div>
                        <div class="col-xs-6 col-sm-4 p-w-lg">
                        <?= $form->field($Bank, 'OCTComisMin')->textInput([
                            'class' => 'form-control'
                        ]); ?>
                        </div>
                    </div>

                    <div class="row no-margins">
                        <div class="col-xs-6 col-sm-4">
                        <?= $form->field($Bank, 'FreepayComis')->textInput([
                            'class' => 'form-control'
                        ]); ?>
                        </div>
                        <div class="col-xs-6 col-sm-4 p-w-lg">
                        <?= $form->field($Bank, 'FreepayComisMin')->textInput([
                            'class' => 'form-control'
                        ]); ?>
                        </div>
                    </div>

                    <div class="row no-margins">
                        <div class="col-xs-6 col-sm-4">
                        <?= $form->field($Bank, 'OCTVozn')->textInput([
                            'class' => 'form-control'
                        ]); ?>
                        </div>
                        <div class="col-xs-6 col-sm-4 p-w-lg">
                            <?= $form->field($Bank, 'OCTVoznMin')->textInput([
                                'class' => 'form-control'
                            ]); ?>
                        </div>
                    </div>

                    <div class="row no-margins">
                        <div class="col-xs-6 col-sm-4">
                        <?= $form->field($Bank, 'FreepayVozn')->textInput([
                            'class' => 'form-control'
                        ]); ?>
                        </div>
                        <div class="col-xs-6 col-sm-4 p-w-lg">
                            <?= $form->field($Bank, 'FreepayVoznMin')->textInput([
                            'class' => 'form-control'
                        ]); ?>
                        </div>
                    </div>

                    <div class="row no-margins">
                        <div class="col-xs-6 col-sm-4">
                            <?= $form->field($Bank, 'VyvodBankComis')->textInput([
                                'class' => 'form-control'
                            ]); ?>
                        </div>
                    </div>


                    <input type="hidden" value="<?=$Bank->ID?>" name="IdBank">

                    <?= Html::Button('Сохранить', [
                        'class' => 'btn btn-primary',
                        'id' => 'submitbanks',
                        'form' => 'banks'
                    ]) ?>

                    <?php ActiveForm::end(); ?>

                    <div class="sk-spinner sk-spinner-wave">
                        <div class="sk-rect1"></div>
                        <div class="sk-rect2"></div>
                        <div class="sk-rect3"></div>
                        <div class="sk-rect4"></div>
                        <div class="sk-rect5"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php $this->registerJs('lk.banksave()'); ?>