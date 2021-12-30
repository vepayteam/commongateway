<?php
use yii\helpers\Html;

/* @var yii\web\View $this */
/* @var array $uslugilist */

$this->title = "продажи";
$this->params['breadtitle'] = "Продажи";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];
?>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Продажи</h5>
            </div>
            <div class="ibox-content">
                <form class="form-horizontal" id="saleform">
                    <div class="form-group"><label class="col-sm-2 control-label">Дата</label>
                        <div class="btn-group" data-toggle="buttons">
                            <label class="btn btn-white active">
                                <input type="radio" name="datetype" id="datetype0" value="0" autocomplete="off" checked> Дни
                            </label>
                            <label class="btn btn-white">
                                <input type="radio" name="datetype" id="datetype1" value="1" autocomplete="off"> Месяцы
                            </label>
                        </div>
                        <div class="col-sm-10 col-md-6">
                            <div class="input-daterange input-group">
                                <input type="text" name="datefrom" value="<?=date("01.m.Y")?>" maxlength="10" class="form-control" autocomplete="off">
                                <span class="input-group-addon">по</span>
                                <input type="text" name="dateto" value="<?=date("d.m.Y")?>" maxlength="10" class="form-control" autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <div class="form-group"><label class="col-sm-2 control-label">Тип операции</label>
                        <div class="col-sm-10 col-md-6">
                            <select class="form-control multiselect-sale" multiple name="TypeUslug[]">
                                <?php foreach ($uslugilist as $usl) : ?>
		                            <option value="<?= Html::encode($usl->ID) ?>"><?= Html::encode($usl->Name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-10 col-sm-offset-2 col-md-6 col-md-offset-2">
                            <input type="submit" value="Сформировать" class="btn btn-primary">
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
                <div id="sale-graph"></div>
            </div>
        </div>
    </div>
</div>

<?php $this->registerJs('
    lk.statgraph();
    multiselect.statSale();
'); ?>
