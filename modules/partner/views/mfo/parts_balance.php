<?php

/* @var yii\web\View $this */
/* @var $balances */
/* @var \app\models\payonline\Partner $Partner */
/* @var $IsAdmin bool */

/* @var $partners array */
/* @var $data array */

use app\models\partner\UserLk;
use yii\web\View;

$this->title = "баланс по разбивке";

$this->params['breadtitle'] = "Баланс по разбивке";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];

?>

<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Выписка</h5>
            </div>
            <div class="ibox-content">
                <form class="form-horizontal" id="parts-balance__form" method="post">

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Дата</label>
                        <div class="col-sm-10 col-md-6">
                            <div class="float-right">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-xs btn-white active" name="calDay">День
                                    </button>
                                    <button type="button" class="btn btn-xs btn-white" name="calWeek">Неделя</button>
                                    <button type="button" class="btn btn-xs btn-white" name="calMon">Месяц</button>
                                </div>
                            </div>
                            <div class="input-daterange input-group">
                                <input type="text" name="datefrom" value="<?= date("d.m.Y") ?> 00:00" maxlength="10"
                                       class="form-control">
                                <span class="input-group-addon">по</span>
                                <input type="text" name="dateto" value="<?= date("d.m.Y") ?> 23:59" maxlength="10"
                                       class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-4">
                            <?php if (UserLk::IsAdmin(Yii::$app->user)): ?>
                                <label for="parts-balance__form__partner-select">Партнер</label>
                                <select class="form-control" name="partnerId" id="parts-balance__form__partner-select">
                                    <?php foreach ($partners as $partner): ?>
                                        <option value="<?= $partner->ID ?>"><?= $partner->Name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input name="partnerId" type="hidden"
                                       value="<?= UserLk::getPartnerId(Yii::$app->user) ?>">
                            <?php endif; ?>
                            <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                            <input name="sort" type="hidden" id="sortstatem" value="0">
                            <input id="parts-balance__form__submit" type="submit" class="btn btn-sm btn-primary"
                                   value="Найти" style="margin-top: 20px">
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
                <div class="table-responsive" id="parts-balance__result">
                    <div class="panel-group" id="parts-balance__accordion" role="tablist" aria-multiselectable="true">
                        <?php if($data): ?>
                            <?php foreach ($data as $partnerId => $partnerBlock): ?>
                                <div class="panel panel-default">
                                    <div class="panel-heading" role="tab" id="headingOne">
                                        <h4 class="panel-title">
                                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse-<?=$partnerId?>" aria-expanded="true" aria-controls="collapseOne">
                                                <?=$partners[$partnerId]->Name?>
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="collapse-<?=$partnerId?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
                                        <div class="panel-body">
                                            <table class="table table-striped">
                                                <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Extid</th>
                                                    <th>Сумма, руб.</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach($partnerBlock as $row): ?>
                                                    <tr>
                                                        <th scope="row"><?=$row['PayschetId']?></th>
                                                        <td><?=$row['Extid']?></td>
                                                        <td><?=$row['Amount']/100?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <h3>Нет данных для отображения</h3>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php $this->registerJs('lk.mfobalance()'); ?>
