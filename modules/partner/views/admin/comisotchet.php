<?php

/* @var yii\web\View $this */
/* @var array $uslugilist */
/* @var string $veekends */
/* @var Partner[] $partnerlist  */
/* @var Bank[] $banks  */
/* @var $IsAdmin bool */

use app\models\payonline\Partner;
use app\services\payment\models\Bank;
use yii\web\View;
use app\services\payment\forms\VoznagStatForm;
use yii\helpers\Html;

$this->title = "вывод вознаграждения";

$this->params['breadtitle'] = "Вывод вознаграждения";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];

?>

    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Вывод вознаграждения</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                    <form class="form-horizontal m-t-md" id="comisotchetform">
                        <div class="form-group"><label class="col-sm-2 control-label">Дата</label>
                            <div class="col-sm-10 col-md-6">
                                <div class="input-daterange input-group">
                                    <input type="text" name="datefrom" value="<?=date("d.m.Y")?> 00:00" maxlength="10" class="form-control">
                                    <span class="input-group-addon">по</span>
                                    <input type="text" name="dateto" value="<?=date("d.m.Y")?> 23:59" maxlength="10" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Мерчант</label>
                            <div class="col-sm-4">
                                <select class="form-control" name="IdPart">
                                    <?php foreach ($partnerlist as $partner) : ?>
                                        <option value="<?=Html::encode($partner->ID)?>"><?=Html::encode($partner->nameWithId)?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Вид</label>
                            <div class="col-sm-4">
                                <?= Html::dropDownList(
                                    'TypeOtch', '', VoznagStatForm::getDropDownTypes(), ['class' => 'form-control']
                                ); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-6">
                                <?= Html::hiddenInput('_csrf', Yii::$app->request->csrfToken, ['id' => '_csrf']) ?>
                                <button class="btn btn-sm btn-primary" type="submit">Сформировать</button>
                                <a href="#modal-perevod"
                                   data-toggle="modal"
                                   class="btn btn-default btn-sm pull-right"
                                >Перечислить</a>
                            </div>
                        </div>
                    </form>
                    </div>

                    <div class="sk-spinner sk-spinner-wave">
                        <div class="sk-rect1"></div>
                        <div class="sk-rect2"></div>
                        <div class="sk-rect3"></div>
                        <div class="sk-rect4"></div>
                        <div class="sk-rect5"></div>
                    </div>
                    <div class="table-responsive" id="comisotchetresult"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="modal-perevod" class="modal fade" aria-hidden="true" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <?=$this->render('_perevodform', ['partnerlist' => $partnerlist, 'banks' => $banks])?>
                </div>
            </div>
        </div>
    </div>

    <div id="modal-vyvyodsum" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Вывод вознаграждения</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Сумма для вывода</label>
                        <input id="modal-vyvyodsum__summ" type="number" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                    <button id="modal-vyvyodsum__submit" type="button" class="btn btn-primary">Вывести</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

<?php $this->registerJs('lk.comisotchet()'); ?>
