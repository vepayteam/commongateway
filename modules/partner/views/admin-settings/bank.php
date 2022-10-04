<?php
/**
 * @var View $this
 * @var AdminSettingsBankForm $bank
 */

use app\modules\partner\models\forms\AdminSettingsBankForm;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

$this->title = "Банк {$bank->name}";

$this->params['breadtitle'] = "Банк {$bank->name}";
$this->params['breadcrumbs'][] = ['label' => 'Настройки', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->params['breadtitle'];
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

        <div class="ibox float-e-margins">
            <!--            <div class="ibox-title">-->
            <!--                <h4></h4>-->
            <!--            </div>-->
            <div class="ibox-content">
                <?=
                /** {@see AdminSettingsBankForm::$aftMinSum} */
                $form->field($bank, 'aftMinSum')->textInput()
                ?>

                <?= $form->field($bank, 'usePayIn')->checkbox() ?>
                <?= $form->field($bank, 'useApplePay')->checkbox() ?>
                <?= $form->field($bank, 'useGooglePay')->checkbox() ?>
                <?= $form->field($bank, 'useSamsungPay')->checkbox() ?>

                <?=
                /** {@see AdminSettingsBankForm::$outCardRefreshStatusDelay} */
                $form->field($bank, 'outCardRefreshStatusDelay')->textInput()
                ?>

            </div>
        </div>


        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
        <br/>
        <br/>
        <?php ActiveForm::end(); ?>
    </div>
</div>
