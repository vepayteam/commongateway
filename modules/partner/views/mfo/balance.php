<?php

/* @var yii\web\View $this */
/* @var BalanceResponse $BalanceResponse  */
/* @var \app\models\payonline\Partner $Partner */
/* @var $IsAdmin bool */

use app\services\balance\response\BalanceResponse;
use app\services\payment\helpers\PaymentHelper;
use app\services\payment\types\AccountTypes;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
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
                                <input name="idpartner" type="hidden" value="<?= Html::encode($Partner->ID) ?>">
                                <?= Html::hiddenInput('_csrf', Yii::$app->request->csrfToken, ['id' => '_csrf']) ?>
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

        <?php if ($BalanceResponse->status === BalanceResponse::STATUS_DONE): ?>
        <div class="col-sm-4">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Баланс</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-sm-12">
                            <h5><?=Html::encode($Partner->Name)?></h5>
                        </div>
                    </div>

                    <div class="row">
                        <?php foreach (ArrayHelper::index($BalanceResponse->balance, null, 'bank_name') as $bankName => $balances): ?>
                            <div class="col-sm-12">
                                <b><?= Html::encode($bankName) ?></b>

                                <div class="balance-container">
                                    <?php foreach ($balances as $balance): ?>
                                        <div class="balance-container__item">
                                            <div>
                                                <?php if ($balance->account_type !== AccountTypes::TYPE_DEFAULT): ?>
                                                    <span><?= Html::encode(AccountTypes::ALL_TYPES[$balance->account_type]) ?>:</span>
                                                <?php endif; ?>

                                                <?php if ($balance->description !== null): ?>
                                                    <span><?= Html::encode($balance->description) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <b><?= PaymentHelper::formatSum($balance->amount) ?> <?= Html::encode($balance->currency) ?></b>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

<?php $this->registerJs('lk.mfobalance()'); ?>
