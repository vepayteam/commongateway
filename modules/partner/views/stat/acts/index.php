<?php
/**
 * @var bool $IsAdmin
 * @var \yii\web\View $this
 * @var Partner[]|array|\yii\db\ActiveRecord[] $partnerlist
 */

$this->title = "Отчетные документы";
$this->params['breadtitle'] = "Отчетные документы";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];

use app\models\payonline\Partner; ?>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5><?=$this->title?></h5>
            </div>
            <div class="ibox-content">
                <form class="form-horizontal" method="POST" id="actlistform">
                    <div class="form-group"><label class="col-sm-2 control-label">Период</label>
                        <div class="col-sm-4">
                            <div class="input-daterange input-group">
                                <input type="text" name="datefrom" value="<?=date("m.Y", strtotime('-1 month'))?>" maxlength="10" class="form-control" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <?php if ($IsAdmin) : ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Мерчант</label>
                            <div class="col-sm-4">
                                <select class="form-control" name="IdPart" id="actidpatner">
                                    <option value="0"></option>
                                    <?php foreach ($partnerlist as $partner) : ?>
                                        <option value="<?=$partner->ID?>"><?=$partner->nameWithId?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-4">
                            <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                        </div>
                    </div>
                </form>

                <div id="actlistdata"></div>
            </div>
        </div>
    </div>
</div>
<?php
$this->registerJs("act.list();");
?>
