<?php
use app\models\payonline\Banks;

/* @var yii\web\View $this */
/* @var array $uslugilist */
/* @var array $magazlist */
/* @var $partnerlist */
/* @var $IsAdmin bool */
/* @var $bankList Banks[] */


$this->title = 'отчет по платежам';

$this->params['breadtitle'] = 'Отчет по платежам';
$this->params['breadcrumbs'][] = $this->params['breadtitle'];
?>

    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Отчет по платежам</h5>
                </div>
                <div class="ibox-content">
                    <form class="form-horizontal" id="otchlistform">
                        <div class="form-group"><label class="col-sm-2 control-label">Дата</label>
                            <div class="col-sm-4">
                                <div class="input-daterange input-group">
                                    <input type="text" name="datefrom" value="<?= date('d.m.Y') ?> 00:00" maxlength="10" class="form-control">
                                    <span class="input-group-addon">по</span>
                                    <input type="text" name="dateto" value="<?= date('d.m.Y') ?> 23:59" maxlength="10" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Провайдер</label>
                            <div class="col-sm-4">
                                <select class="form-control multiselect-field" multiple name="idBank[]">
                                    <?php foreach ($bankList as $bank) : ?>
                                        <option value="<?= $bank->ID ?>"><?= $bank->Name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php if ($IsAdmin) : ?>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Мерчант</label>
                                <div class="col-sm-4">
                                    <select class="form-control" name="IdPart">
                                        <option value="-1" data-ismfo="-1">Все</option>
                                        <?php foreach ($partnerlist as $partn) : ?>
                                            <option value="<?= $partn->ID ?>"
                                                data-ismfo="<?= $partn->ID == 1 ? 2 : $partn->IsMfo ?>"><?= $partn->Name ?></option>
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
                                        <option value="<?= $usl->ID ?>" data-partner="<?= $usl->IsMfo ?>"><?= $usl->Name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-4">
                                <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                                <button class="btn btn-sm btn-primary" type="submit">Сформировать</button>
                            </div>
                            <div class="col-sm-offset-2 col-sm-4">
                                <a href="/partner/stat/otchetps" target="_blank" class="btn btn-sm btn-default" id="otchetpsxls">Отчет по ПС (xls)</a>
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
                    <div class="table-responsive" id="otchlistresult"></div>
                </div>
            </div>
        </div>
    </div>
<?php
$this->registerJs('
lk.otchlist();
multiselect.otch();
') ?>