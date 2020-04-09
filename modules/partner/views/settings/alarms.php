<?php

/* @var yii\web\View $this */
/* @var AlarmsSettings[] $settings */
/* @var bool $IsAdmin */

use app\models\partner\admin\AlarmsSettings;
use yii\bootstrap\Html;
use yii\web\View;

$this->title = "Настройки";

$this->params['breadtitle'] = "Настройки";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];
?>
        <div class="row">
            <div class="col-sm-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h4>Настройки</h4>
                        <?php /*if ($IsAdmin) {
                            echo $this->render('_tabs');
                        }*/ ?>
                    </div>
                    <div class="ibox-content">
                        <form method="post" class="form-horizontal" id="alarmssettings">

                        <div class="form-group">
                            <div class="col-sm-12">
                                <h4>Оповещения:</h4>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">E-mail для оповещений</label>
                            <div class="col-sm-10 col-md-6">
                                <input type="text" name="SettingsEmail" value="<?=Html::encode($settings[0]->EmailAlarm)?>" maxlength="200" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Отсутствие изменений статуса операции и/или отклика на стороне эквайера в интервале между запросом обработки и моментом проверки в течение, минут</label>
                            <div class="col-sm-10 col-md-6">
                                <input type="text" name="Settings[0]" value="<?=Html::encode($settings[0]->TimeAlarm)?>" maxlength="8" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Отсутствие отклика со стороны SMS шлюза в течение, минут</label>
                            <div class="col-sm-10 col-md-6">
                                <input type="text" name="Settings[1]" value="<?=Html::encode($settings[1]->TimeAlarm)?>" maxlength="8" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Отсутствие изменений статуса операции в течение, минут</label>
                            <div class="col-sm-10 col-md-6">
                                <input type="text" name="Settings[2]" value="<?=Html::encode($settings[2]->TimeAlarm)?>" maxlength="8" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-4">
                                <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                                <input name="paytype" type="hidden" value="-1">
                                <input name="accountpay" type="hidden" value="">
                                <button class="btn btn-sm btn-primary" type="submit">Сохранить</button>
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


<?php $this->registerJs('lk.mfoalarms()'); ?>