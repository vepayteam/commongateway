<?php

/* @var yii\web\View $this */
/* @var array $uslugilist */
/* @var string $veekends */
/* @var $partnerlist  */
/* @var $IsAdmin bool */

use yii\web\View;

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
                    <form class="form-horizontal m-t-md" id="veekenddays">
                        <div class="form-group"><label class="col-sm-2 control-label">Праздничные дни</label>
                            <div class="col-sm-10 col-md-6">
                                <input type="text" name="veekenddays" value="<?=$veekends?>" maxlength="200" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-4">
                                <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                                <button class="btn btn-sm btn-primary" type="submit">Сохранить</button>
                            </div>
                        </div>
                    </form>
                    </div>
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
                                    <option value="-1">Все</option>
                                    <?php foreach ($partnerlist as $partn) : ?>
                                        <option value="<?=$partn->ID?>"><?=$partn->Name?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Вид</label>
                            <div class="col-sm-4">
                                <select class="form-control" name="TypeOtch">
                                    <option value="0">Отчет</option>
                                    <option value="1">История перечислений на р/с</option>
                                    <option value="2">История перечислений на счет выдачи</option>
                                    <option value="3">История вывода вознаграждения</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-6">
                                <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
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
                    <?=$this->render('_perevodform', ['partnerlist' => $partnerlist])?>
                </div>
            </div>
        </div>
    </div>

<?php $this->registerJs('lk.comisotchet()'); ?>