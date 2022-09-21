<?php

use app\components\widgets\EmbedJs;
use app\modules\partner\models\forms\BasicPartnerStatisticForm;
use app\modules\partner\models\search\IdentificationListFilter;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

/**
 * @var View $this
 * @var BasicPartnerStatisticForm $model
 * @var IdentificationListFilter|null $searchModel
 * @var ActiveDataProvider|null $dataProvider
 * @var array|null $partnerList
 */

$this->title = 'Статистика идентификации';
$this->params['breadtitle'] = $this->title;
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Выписка</h5>
            </div>
            <div class="ibox-content">

                <?php
                $form = ActiveForm::begin([
                    'id' => 'parts-form',
                    'layout' => 'horizontal',
                    'action' => Url::toRoute(''),
                    'method' => 'GET',
                ]);
                ?>

                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-6">
                        <div class="btn-group">
                            <button type="button" class="btn btn-white js-day">День</button>
                            <button type="button" class="btn btn-white js-week">Неделя</button>
                            <button type="button" class="btn btn-white js-month">Месяц</button>
                        </div>
                    </div>
                </div>

                <?= $form->field($model, 'dateFrom')->textInput(['class' => 'form-control js-date']) ?>

                <?= $form->field($model, 'dateTo')->textInput(['class' => 'form-control js-date']) ?>

                <?php if ($partnerList !== null): ?>
                    <?= $form->field($model, 'partnerId')->dropDownList($partnerList) ?>
                <?php endif; ?>

                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-6">
                        <?= Html::submitButton('Найти', ['class' => 'btn btn-primary']) ?>
                    </div>
                </div>

                <?php ActiveForm::end() ?>

                <?php if ($dataProvider !== null): ?>

                    <?php Pjax::begin(); ?>

                    <?php
                    echo $this->render('ident/grid', [
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                    ]);
                    ?>

                    <?php Pjax::end(); ?>

                    <br/>
                    <?= Html::beginForm('', 'GET', ['class' => 'form-horizontal clear']) ?>
                    <div class="form-group">
                        <div class="col-sm-6">
                            <?= Html::submitButton('Excel <i class="fa fa-download"></i>', ['class' => 'btn btn-default']) ?>
                        </div>
                    </div>
                    <?= Html::hiddenInput('excel', 1) ?>
                    <?= Html::endForm() ?>

                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php
EmbedJs::begin([
    'data' => [
        'dateFromId' => Html::getInputId($model, 'dateFrom'),
        'dateToId' => Html::getInputId($model, 'dateTo'),
    ]
])
?>
<script>
    let $dateFromInput = $('#' + data.dateFromId);
    let $dateToInput = $('#' + data.dateToId);

    $('.js-date').datetimepicker({
        format: 'DD.MM.YYYY',
        showClose: true,
        useCurrent: false
    });

    $dateFromInput.add($dateToInput)
        .each(function () {
            let $input = $(this);
            if (!$input.val()) {
                $input.data("DateTimePicker").date(moment(new Date()));
            }
        });

    $('.js-day').click(function () {
        $dateFromInput.data("DateTimePicker").date(moment(new Date()));
        $dateToInput.data("DateTimePicker").date(moment(new Date()));
    });
    $('.js-week').click(function () {
        $dateFromInput.data("DateTimePicker").date(moment(new Date()).subtract(7, 'days'));
        $dateToInput.data("DateTimePicker").date(moment(new Date()));
    });
    $('.js-month').click(function () {
        $dateFromInput.data("DateTimePicker").date(moment(new Date()).subtract(1, 'months'));
        $dateToInput.data("DateTimePicker").date(moment(new Date()));
    });
</script>
<?php EmbedJs::end() ?>
