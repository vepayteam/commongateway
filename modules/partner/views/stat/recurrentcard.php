<?php
use app\models\payonline\Partner;
use yii\helpers\Html;

/* @var yii\web\View $this */
/* @var array $uslugilist */
/* @var bool $IsAdmin */
/* @var Partner[] $partnerlist */
/* @var string $name */
/* @var int $type */

$this->title = mb_strtolower($name);
$this->params['breadtitle'] = $name;
$this->params['breadcrumbs'][] = $this->params['breadtitle'];
?>
<div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Автоплатежи</h5>
                </div>
                <div class="ibox-content">
                    <form class="form-horizontal" id="recurrentcardform" method="post">
                        <div class="form-group"><label class="col-sm-2 control-label">Дата</label>
                            <div class="col-sm-7 col-md-6">
                                <div class="input-daterange input-group">
                                    <input type="text" name="datefrom" value="<?=date("1.m.Y")?>" maxlength="10" class="form-control" autocomplete="off">
                                    <span class="input-group-addon">по</span>
                                    <input type="text" name="dateto" value="<?=date("t.m.Y")?>" maxlength="10" class="form-control" autocomplete="off">
                                </div>
                            </div>
                            <div class="col-sm-3 col-md-2">
                                <div class="btn-group" data-toggle="buttons">
                                    <label class="btn btn-white active">
                                        <input type="radio" name="datetype" id="datetype0" value="0" autocomplete="off" checked="checked"> Дни
                                    </label>
                                    <label class="btn btn-white">
                                        <input type="radio" name="datetype" id="datetype1" value="1" autocomplete="off"> Месяцы
                                    </label>
                                </div>
                            </div>
                        </div>

                        <?php if ($IsAdmin) : ?>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Мерчант</label>
                                <div class="col-sm-4">
                                    <select class="form-control" name="IdPart">
                                        <option value="-1">Все</option>
                                        <?php foreach ($partnerlist as $partner) : ?>
                                            <option value="<?= Html::encode($partner->ID) ?>"><?= Html::encode($partner->nameWithId) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <div class="col-sm-10 col-sm-offset-2 col-md-6 col-md-offset-2">
                                <input type="hidden" name="graphtype" value="0">
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
                </div>
            </div>
        </div>
    </div>

    <?=$this->render('_recurent-nav');?>

    <div class="ibox-content">
        <div id="recurrentcardresult"></div>
    </div>

<?php $this->registerJs('
    lk.recurrentcard();
    multiselect.statMerchantusluga();
'); ?>
