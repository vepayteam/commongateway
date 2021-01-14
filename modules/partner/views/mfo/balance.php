<?php

/* @var yii\web\View $this */
/* @var $balances  */
/* @var \app\models\payonline\Partner $Partner */
/* @var $IsAdmin bool */

use yii\web\View;

$this->title = "баланс";

$this->params['breadtitle'] = "Баланс";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];

?>

    <div class="row">
        <div class="col-sm-8">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Выписка по счёту</h5>
                </div>
                <div class="ibox-content">
                    <form class="form-horizontal" id="mfosumlistform">

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Дата</label>
                            <div class="col-sm-10 col-md-6">
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
                            <?php if (!empty($Partner->SchetTcbNominal)): ?>
                                <div class="col-sm-3 col-sm-offset-1">
                                    <label>Остаток на начало:</label>
                                    <div id="vypostbeg">-</div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Счет</label>
                            <div class="col-sm-6">
                                <select class="form-control" name="istransit">
                                    <option value="0" selected>Транзитный счет<?=(empty($Partner->SchetTcbNominal) ? ' на выдачу' : '')?></option>
                                    <?php if (!empty($Partner->SchetTcbTransit)): ?>
                                        <option value="1">Транзитный счет на погашение</option>
                                    <?php endif; ?>
                                    <?php if (!empty($Partner->SchetTcbNominal)): ?>
                                        <option value="2">Номинальный счет</option>
                                    <?php endif; ?>
                                    <?php if ($IsAdmin) : ?>
                                        <option value="10">Выписка по выдаче</option>
                                        <option value="11">Выписка по погашению</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <?php if (!empty($Partner->SchetTcbNominal)): ?>
                                <div class="col-sm-3 col-sm-offset-1">
                                    <label>Остаток на конец:</label>
                                    <div id="vypostend">-</div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-4">
                                <input name="idpartner" type="hidden" value="<?= $Partner->ID ?>">
                                <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                                <input name="sort" type="hidden" id="sortstatem" value="0">
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
                    <div class="table-responsive" id="mfobalanceresult"></div>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Баланс</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-sm-12">
                            <h5><?=$Partner->Name?></h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <?php if (!empty($Partner->SchetTcbNominal)): ?>
                                    Баланс номинального счета:
                                <?php else: ?>
                                    Баланс транзитного счета на погашение:
                                <?php endif; ?>
                            </div>
                            <div class="col-sm-12">
                                <label>
                                    <?=number_format($balances['localin'],2, '.',' ')?> руб.
                                </label>
                                <?php if ($IsAdmin) : ?>
                                    <div>&nbsp;Возн.: <label><?=number_format($balances['comisin'],2, '.',' ')?> руб.</label></div>
                                    <?php if (!empty($Partner->SchetTcbNominal)): ?>
                                        <div>&nbsp;ТКБ: <label><?=number_format($balances['tcbnomin'] ?? 0,2, '.',' ')?> руб.</label></div>
                                    <?php else: ?>
                                        <div>&nbsp;ТКБ: <label><?=number_format($balances['tcbtrans'] ?? 0,2, '.',' ')?> руб.</label></div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <div class="col-sm-12">
                                Баланс транзитного счета<?=(empty($Partner->SchetTcbNominal) ? ' на выдачу' : '')?>:
                            </div>
                            <div class="col-sm-12">
                                <label>
                                    <?=number_format($balances['localout'],2, '.',' ')?> руб.
                                </label>
                                <?php if ($IsAdmin) : ?>
                                    <div>&nbsp;Возн.: <label><?=number_format($balances['comisout'],2, '.',' ')?> руб.</label></div>
                                    <div>&nbsp;ТКБ: <label><?=number_format($balances['tcb'] ?? 0,2, '.',' ')?> руб.</label></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>

<?php $this->registerJs('lk.mfobalance()'); ?>