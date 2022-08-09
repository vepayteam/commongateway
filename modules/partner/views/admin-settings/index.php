<?php
/**
 * @var View $this
 * @var AdminSettingsForm $settings
 * @var AdminSettingsBankForm[] $banks
 * @var array $bankList
 */

use app\modules\partner\models\forms\AdminSettingsBankForm;
use app\modules\partner\models\forms\AdminSettingsForm;
use yii\base\Model;
use yii\bootstrap\ActiveForm;
use yii\data\ArrayDataProvider;
use yii\grid\DataColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

$this->title = "Настройки";

$this->params['breadtitle'] = "Настройки";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];

/** @var Model[] $allModels */
$allModels = [];
array_push($allModels, $settings, ...$banks);
$hasError = false;
foreach ($allModels as $model) {
    if ($model->hasErrors()) {
        $hasError = true;
        break;
    }
}

?>

<div class="row">
    <div class="col-sm-12">

        <?php
        $form = ActiveForm::begin([
            'enableClientValidation' => false,
            'fieldConfig' => [
                /**
                 * Fix for limitations in "Awesome Bootstrap Checkbox" Bootstrap CSS plugin used in project.
                 * @see \yii\bootstrap\ActiveField::$checkboxTemplate
                 * @link https://github.com/flatlogic/awesome-bootstrap-checkbox#use
                 */
                'checkboxTemplate' => "<div class=\"checkbox\">\n{input}\n{beginLabel}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>",
            ],
        ]);
        ?>

        <?php if ($hasError): ?>
            <div class="ibox float-e-margins">
                <div class="ibox-content bg-danger">
                    <?php
                    $allModels = [];
                    array_push($allModels, $settings, ...$banks);
                    echo $form->errorSummary($allModels);
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Alarms -->
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h4>Оповещения</h4>
            </div>
            <div class="ibox-content">
                <?=
                /** {@see AdminSettingsForm::$alarmEmail} */
                $form->field($settings, 'alarmEmail')->textInput()
                ?>

                <?=
                /** {@see AdminSettingsForm::$alarmBankNoResponseInterval} */
                $form->field($settings, 'alarmBankNoResponseInterval')->textInput(['style' => 'width: 100px'])
                ?>

                <?=
                /** {@see AdminSettingsForm::$alarmSmsGateNoResponseInterval} */
                $form->field($settings, 'alarmSmsGateNoResponseInterval')->textInput(['style' => 'width: 100px'])
                ?>

                <?=
                /** {@see AdminSettingsForm::$alarmStatusFreezeInterval} */
                $form->field($settings, 'alarmStatusFreezeInterval')->textInput(['style' => 'width: 100px'])
                ?>
            </div>
        </div>
        <!-- /Alarms -->

        <!-- Banks -->
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h4>Банки</h4>
            </div>

            <div class="ibox-content">
                <?=
                /** {@see AdminSettingsForm::$bankForPayment} */
                $form->field($settings, 'bankForPayment')->dropDownList(['-1' => 'По умолчанию'] + $bankList)
                ?>

                <?=
                /** {@see AdminSettingsForm::$bankForTransferToCard} */
                $form->field($settings, 'bankForTransferToCard')->dropDownList(['-1' => 'По умолчанию'] + $bankList)
                ?>

                <?php
                $checkboxValue = function (AdminSettingsBankForm $bank, $key, $index, DataColumn $column) use ($form) {
                    return $form
                        ->field($bank, "[{$key}]{$column->attribute}", [
                            'options' => ['class' => ''],
                            'errorOptions' => ['style' => 'margin: 0'],
                        ])
                        ->label('')
                        ->checkbox();
                };

                /** {@see AdminSettingsBankForm} */
                echo GridView::widget([
                    'dataProvider' => new ArrayDataProvider(['allModels' => $banks]),
                    'layout' => '{items}',
                    'columns' => [
                        [
                            'attribute' => 'name',
                            'value' => function (AdminSettingsBankForm $bank) {
                                return Html::a($bank->name, ['bank', 'id' => $bank->id]);
                            },
                            'format' => 'raw',
                        ],
                        [
                            'attribute' => 'sortOrder',
                            'format' => 'raw',
                            'value' => function (AdminSettingsBankForm $bank, $key) use ($form) {
                                return $form
                                    ->field($bank, "[{$key}]sortOrder", [
                                        'options' => ['class' => ''],
                                        'errorOptions' => ['style' => 'margin: 0'],
                                    ])
                                    ->label(false)
                                    ->textInput(['style' => 'width: 50px;']);
                            },
                            'options' => ['style' => 'width: 170px;']
                        ],
                        [
                            'attribute' => 'aftMinSum',
                            'format' => 'raw',
                            'value' => function (AdminSettingsBankForm $bank, $key) use ($form) {
                                return $form
                                    ->field($bank, "[{$key}]aftMinSum", [
                                        'options' => ['class' => ''],
                                        'errorOptions' => ['style' => 'margin: 0'],
                                    ])
                                    ->label(false)
                                    ->textInput(['style' => 'width: 100%;']);
                            },
                            'options' => ['style' => 'width: 170px;']
                        ],
                        [
                            'attribute' => 'usePayIn',
                            'format' => 'raw',
                            'value' => $checkboxValue,
                        ],
                        [
                            'attribute' => 'useApplePay',
                            'format' => 'raw',
                            'value' => $checkboxValue,
                        ],
                        [
                            'attribute' => 'useGooglePay',
                            'format' => 'raw',
                            'value' => $checkboxValue,
                        ],
                        [
                            'attribute' => 'useSamsungPay',
                            'format' => 'raw',
                            'value' => $checkboxValue,
                        ],
                        [
                            'value' => function (AdminSettingsBankForm $bank) {
                                $icon = Html::tag('span', '', ['class' => "glyphicon glyphicon-pencil"]);
                                return Html::a($icon, ['bank', 'id' => $bank->id]);
                            },
                            'format' => 'raw',
                        ],
                    ],
                ])
                ?>
            </div>
        </div>
        <!-- /Banks -->

        <!-- Holidays -->
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h4>Праздничные дни</h4>
            </div>
            <div class="ibox-content">
                <?=
                /** {@see AdminSettingsForm::$holidayList} */
                $form->field($settings, 'holidayList')->textInput()
                ?>
            </div>
        </div>
        <!-- /Holidays -->

        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
        <br/>
        <br/>
        <?php ActiveForm::end(); ?>
    </div>
</div>



