<?php

/* @var yii\web\View $this */
/* @var $partnerlist  */
/* @var $IsAdmin bool */

$this->title = "Виджет";

$this->params['breadtitle'] = "Виджет";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];

use yii\web\View;
?>

<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Список счетов</h5>
            </div>
            <div class="ibox-content">
                <div class="row">
                    <div class="col-sm-12">
                        <a href="/partner/order/add" class="btn btn-primary pull-right">Создать</a>
                    </div>
                </div>
                <form class="form-horizontal" id="orderform">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Дата</label>
                        <div class="col-sm-4">
                            <div class="input-daterange input-group">
                                <input type="text" name="datefrom" value="<?=date("d.m.Y")?>" maxlength="10" class="form-control">
                                <span class="input-group-addon">по</span>
                                <input type="text" name="dateto" value="<?=date("d.m.Y")?>" maxlength="10" class="form-control">
                            </div>
                        </div>
                    </div>
                    <?php if ($IsAdmin) : ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Мерчант</label>
                            <div class="col-sm-4">
                                <select class="form-control" name="partner">
                                    <option value="-1" data-ismfo="-1">Все</option>
                                    <?php foreach ($partnerlist as $partn) : ?>
                                        <option value="<?=$partn->ID?>" data-ismfo="<?= $partn->ID == 1 ? 2 : $partn->IsMfo?>"><?=$partn->Name?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">Статус</label>
                        <div class="col-sm-4">
                            <select class="form-control multiselect-order" id="sp" multiple name="status[]">
<!--                                <option value="-1">Все</option>-->
                                <option value="0">Создан</option>
                                <option value="1">Оплачен</option>
                                <option value="2">Отменен</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-4">
                            <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
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
                <div class="table-responsive" id="orderlistresult"></div>
            </div>
        </div>
    </div>
</div>

<?php $this->registerJs('
    lk.orderlist()
    multiselect.orderIndex();
'); ?>