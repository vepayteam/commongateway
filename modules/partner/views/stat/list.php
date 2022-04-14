<?php

/* @var yii\web\View $this */
/* @var array $uslugilist */
/* @var Partner[] $partnerlist  */
/* @var $IsAdmin bool */

use app\models\payonline\Partner;
use app\services\payment\models\PaySchet;
use yii\helpers\Html;

$this->title = "список операций";

$this->params['breadtitle'] = "Cписок операций";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];
?>

<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Список операций</h5>
            </div>
            <div class="ibox-content">
                <form class="form-horizontal" id="statlistform">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Дата создания операции</label>
                        <div class="col-md-4">
                            <div class="float-right">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-xs btn-white active" name="calDay">День</button>
                                    <button type="button" class="btn btn-xs btn-white" name="calWeek">Неделя</button>
                                    <button type="button" class="btn btn-xs btn-white" name="calMon">Месяц</button>
                                </div>
                            </div>
                            <div class="input-daterange input-group">
                                <input type="text" name="datefrom" value="<?=date("d.m.Y")?> 00:00" maxlength="10" class="form-control">
                                <span class="input-group-addon">по</span>
                                <input type="text" name="dateto" value="<?=date("d.m.Y")?> 23:59" maxlength="10" class="form-control">
                            </div>
                        </div>
                    </div>
                    <?php if ($IsAdmin) : ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Мерчант</label>
                            <div class="col-sm-4">
                                <select class="form-control multiselect-field" multiple name="idParts[]">
                                    <?php foreach ($partnerlist as $partn) : ?>
                                        <option value="<?=Html::encode($partn->ID)?>" data-ismfo="<?= $partn->ID == 1 ? 2 : Html::encode($partn->IsMfo)?>"><?=Html::encode($partn->ID)?> | <?=Html::encode($partn->Name)?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Тип операции</label>
                        <div class="col-sm-4">
                            <select class="form-control multiselect-field" multiple name="TypeUslug[]">
                                <?php foreach ($uslugilist as $usl) : ?>
                                    <option value="<?=Html::encode($usl->ID)?>" data-partner="<?= Html::encode($usl->IsMfo) ?>"><?=Html::encode($usl->Name)?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <label class="col-sm-2 control-label">ID Vepay</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Статус</label>
                        <div class="col-sm-4">
                            <select class="form-control multiselect-status" id="sp" multiple name="status[]">
                                <?php foreach (PaySchet::STATUSES as $index => $status): ?>
                                    <option value="<?= $index ?>"><?= $status ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <label class="col-sm-2 control-label">Сумма платежа</label>
                        <div class="col-sm-4">
                            <div class="input-daterange input-group">
                                <input type="text" name="summpayFrom" maxlength="10" class="form-control">
                                <span class="input-group-addon">по</span>
                                <input type="text" name="summpayTo" maxlength="10" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Ext ID</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="Extid">
                        </div>
                        <label class="col-sm-2 control-label">Договор</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="params[0]">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Провайдер</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="params[bankName]">
                        </div>
                        <label class="col-sm-2 control-label">Номер операции на стороне провайдера</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="params[operationNumber]">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Маска карты</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="params[cardMask]">
                        </div>

                        <label class="col-sm-2 control-label">Полная сумма к оплате</label>
                        <div class="col-sm-4">
                            <div class="input-daterange input-group">
                                <input type="text" name="params[fullSummpayFrom]" maxlength="10" class="form-control">
                                <span class="input-group-addon">по</span>
                                <input type="text" name="params[fullSummpayTo]" maxlength="10" class="form-control">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="paytype" value="-1">
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-4">
                            <?= Html::hiddenInput('_csrf', Yii::$app->request->csrfToken, ['id' => '_csrf']) ?>
                            <button class="btn btn-sm btn-primary" type="submit">Найти</button>
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
                <div class="table-responsive" id="statlistresult"></div>
            </div>
        </div>
    </div>
</div>
<?=$this->render('excerpt/_modal_pdf')?>
<?php $this->registerJs('
lk.statlist();
multiselect.statList();
'
); ?>