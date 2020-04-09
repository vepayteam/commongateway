<?php
/* @var yii\web\View $this */
/* @var array $uslugilist */
/* @var string $name */
/* @var int $type */

$this->title = "регулярные платежи";

$this->params['breadtitle'] = $name;
$this->params['breadcrumbs'][] = $this->params['breadtitle'];
use yii\web\View;
?>

    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
<!--                    <h5>--><?//=$name?><!--</h5>-->
                    <?=$this->render('_recurent-nav');?>
                </div>
                <div class="ibox-content">
                    <form class="form-horizontal" id="recurrentform">
                        <div class="form-group"><label class="col-sm-2 control-label">Дата</label>
                            <div class="col-sm-10 col-md-6">
                                <div class="input-daterange input-group">
                                    <input type="text" name="datefrom" value="<?=date("m.Y", mktime(0,0,0,date('n')-5, 1, date('Y')))?>" maxlength="10" class="form-control" autocomplete="off">
                                    <span class="input-group-addon">по</span>
                                    <input type="text" name="dateto" value="<?=date("m.Y")?>" maxlength="10" class="form-control" autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="form-group"><label class="col-sm-2 control-label">Тип операции</label>
                            <div class="col-sm-10 col-md-6">
                                <select class="form-control multiselect-recurrentpaysdata" multiple name="TypeUslug[]">
                                    <? foreach ($uslugilist as $usl) : ?>
                                        <option value="<?=$usl->ID?>"><?=$usl->Name?></option>
                                    <? endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-10 col-sm-offset-2 col-md-6 col-md-offset-2">

                                <input type="hidden" name="datetype" value="<?=$type?>">
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
                    <div id="recurrent-graph"></div>
                </div>
            </div>
        </div>
    </div>

<?php $this->registerJs('
    lk.statrekurrent();
    multiselect.statRecurrentpaysdata();
'); ?>