<?php

/* @var yii\web\View $this */
/* @var $balances */
/* @var \app\models\payonline\Partner $Partner */
/* @var $IsAdmin bool */

/* @var $partners Partner[] */
/* @var $data array */
/* @var $columns array */

/* @var $partsBalanceForm \app\services\balance\models\PartsBalanceForm */

use app\models\partner\UserLk;
use app\models\payonline\Partner;
use app\services\balance\models\PartsBalanceForm;
use yii\web\View;

$this->title = "Баланс по разбивке (Платформа)";

$this->params['breadtitle'] = "Баланс по разбивке (Платформа)";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];

?>



<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Выписка</h5>
            </div>
            <div class="ibox-content">

                <form class="form-horizontal" id="parts-balance__form" method="post">

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Дата</label>
                        <div class="col-sm-10 col-md-6">
                            <div class="float-right">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-xs btn-white active" name="calDay">День
                                    </button>
                                    <button type="button" class="btn btn-xs btn-white" name="calWeek">Неделя</button>
                                    <button type="button" class="btn btn-xs btn-white" name="calMon">Месяц</button>
                                </div>
                            </div>
                            <div class="input-daterange input-group">

                                <input id="part-balance__form__datefrom" type="text" name="datefrom"
                                       value="<?=date("d.m.Y")?> 00:00" maxlength="10"
                                       class="form-control">
                                <span class="input-group-addon">по</span>
                                <input id="part-balance__form__dateto" type="text" name="dateto"
                                       value="<?=date("d.m.Y")?> 23:59"
                                       maxlength="10"
                                       class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-4">
                            <?php if (UserLk::IsAdmin(Yii::$app->user)): ?>
                                <label for="parts-balance__form__partner-select">Партнер</label>
                                <select id="part-balance__form__partnerId" class="form-control"
                                        id="parts-balance__form__partner-select">
                                    <?php foreach ($partners as $partner): ?>
                                        <option value="<?= $partner->ID ?>">
                                            <?= $partner->nameWithId ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input name="partnerId" type="hidden"
                                       value="<?= UserLk::getPartnerId(Yii::$app->user) ?>">
                            <?php endif; ?>
                            <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                            <button id="parts-balance__form__submit" type="submit" class="btn btn-sm btn-primary" style="margin-top: 20px">Найти</button>
                        </div>
                    </div>

                </form>

                <div class="sk-spinner sk-spinner-wave">
                    <div class="sk-rect1"></div>
                    <div class="sk-rect2"></div>
                    <div class="sk-rect3"></div>
                    <div class="sk-rect4"></div>
                    <div class="sk-rect5"></div>
                </div>
                <div class="table-responsive" id="parts-balance__result">
                    <div class="panel-group" id="parts-balance__accordion" role="tablist" aria-multiselectable="true">

                        <table id="example" class="table table-bordered display nowrap" style="width:100%">

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<?php $this->registerJs('lk.mfobalance()'); ?>
<script>
    var datatableColumns = <?=json_encode(PartsBalanceForm::getDatatableColumns())?>;
    var processingUri = '/partner/mfo/parts-balance-processing';
    var datatableFilters = [
        {
            column_number: 0,
            filter_type: "text",
            filter_delay: 500,
            filter_default_label: ""
        },
        {
            column_number: 1,
            filter_type: "text",
            filter_delay: 500,
            filter_default_label: ""
        },
        {
            column_number: 4,
            filter_type: "text",
            filter_delay: 500,
            filter_default_label: ""
        },
        {
            column_number: 14,
            filter_type: "text",
            filter_delay: 500,
            filter_default_label: ""
        },

    ];
</script>