<?php
/**
 * @var bool $IsAdmin
 * @var Partner[]|array|\yii\db\ActiveRecord[] $partnerlist
 * @var Uslugatovar[]|array|\yii\db\ActiveRecord[] $uslugilist
 * @var \yii\web\View $this
*/
$this->title = "Антифрод";
$this->params['breadtitle'] = "Антифрод";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];

use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
?>
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <?=$this->render('_nav');?>
                </div>
                <div class="ibox-content">
                    <form class="form-horizontal" id="statlistform">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Дата</label>
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
                                    <select class="form-control" name="IdPart">
                                        <option value="-1" data-ismfo="-1">Все</option>
                                        <?php foreach ($partnerlist as $partner) : ?>
                                            <option value="<?=Html::encode($partner->ID)?>" data-ismfo="<?=Html::encode($partner->IsMfo)?>"><?=Html::encode($partner->nameWithId)?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Тип операции</label>
                            <div class="col-sm-4">
                                <select class="form-control multiselect-field" multiple name="usluga[]">
                                    <!--                                <option value="-1">Все</option>-->
                                    <?php foreach ($uslugilist as $usl) : ?>
                                        <option value="<?=Html::encode($usl->ID)?>" data-partner="<?=Html::encode($usl->IsMfo)?>"><?=Html::encode($usl->Name)?></option>
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
                                    <!--                                <option value="-1" >Все</option>-->
                                    <option value="0">Создан</option>
                                    <option value="1">Оплачен</option>
                                    <option value="2">Отменен</option>
                                    <option value="3">Возврат</option>
                                </select>
                            </div>
                            <label class="col-sm-2 control-label">Сумма платежа</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="summpay">
                            </div>
                        </div><!--
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Ext ID</label>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" name="Extid">
                        </div>
                    </div>-->
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
<?php $this->registerJs('
antifraud.start_script();
multiselect.statList();
');
Pjax::begin([
        'id' => 'some_pjax_id',
        'enablePushState'=>false,
        'timeout' => 1000,
]);
Pjax::end();

echo $this->render('_modal');
?>
