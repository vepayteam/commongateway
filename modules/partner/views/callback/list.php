<?php

/* @var yii\web\View $this */
/* @var $idpartner int */

$this->title = "колбэки";

$this->params['breadtitle'] = "Список колбэков";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];

use yii\web\View; ?>

    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Список колбэков</h5>
                </div>
                <div class="ibox-content">

                    <form class="form-horizontal" id="notiflistform">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Дата</label>
                            <div class="col-md-4">
                                <div class="input-daterange input-group">
                                    <input type="text" name="datefrom" value="<?=date("d.m.Y")?> 00:00" maxlength="10" class="form-control">
                                    <span class="input-group-addon">по</span>
                                    <input type="text" name="dateto" value="<?=date("d.m.Y")?> 23:59" maxlength="10" class="form-control">
                                </div>
                            </div>
                        </div>
                        <?php if ($IsAdmin) : ?>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">HTTP-код статуса</label>
                                <div class="col-sm-4">
                                    <select class="form-control" name="httpCode">
                                        <option value="-1">Все</option>
                                        <?php foreach ($httpCodeList as $httpCode) : ?>
                                            <option value="<?=$httpCode?>"><?=$httpCode?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Статус</label>
                            <div class="col-sm-4">
                                <select class="form-control" name="notifstate">
                                    <option value="0">Все</option>
                                    <option value="1">В очереди</option>
                                    <option value="2">Отправленные</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Ext ID</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="Extid">
                            </div>
                        </div>

                        <input type="hidden" name="callback-page" value="1">

                        <div class="form-group">
                            <label class="col-sm-2 control-label">ID Vepay</label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" name="id">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-4">
                                <input name="partner" type="hidden" value="<?= $idpartner ?>">
                                <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                                <button class="btn btn-sm btn-primary" type="submit">Сформировать</button>
                            </div>
                        </div>

                        <input type="hidden" name="callback-page" value="1">

                    </form>

                    <div class="sk-spinner sk-spinner-wave">
                        <div class="sk-rect1"></div>
                        <div class="sk-rect2"></div>
                        <div class="sk-rect3"></div>
                        <div class="sk-rect4"></div>
                        <div class="sk-rect5"></div>
                    </div>
                    <div class="table-responsive" id="notiflistresult"></div>
                </div>
            </div>
        </div>
    </div>

<?php $this->registerJs('lk.notiflist();multiselect.statList();'); ?>
