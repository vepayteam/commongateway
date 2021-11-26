<?php

/* @var yii\web\View $this */
/* @var AlarmsSettings[] $settings */
/* @var bool $IsAdmin */
/* @var string $veekends */
/* @var Banks[] $banks */

use app\models\bank\Banks;
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
                        <h4>Оповещения</h4>
                        <?php /*if ($IsAdmin) {
                            echo $this->render('_tabs');
                        }*/ ?>
                    </div>
                    <div class="ibox-content">
                        <form method="post" class="form-horizontal" id="alarmssettings">

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

    <div class="row">
        <div class="col-sm-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>Банки</h5>
                </div>
                <div class="ibox-content">
                    <form class="form-horizontal m-t-md" id="banksconf">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Банк для оплаты</label>
                            <div class="col-sm-10">
                                <select class=" form-control" name="Options[<?=Banks::BANK_BY_PAYMENT_OPTION_NAME?>]">
                                    <option value="-1">Любой</option>
                                    <? foreach(Banks::getBanksByDropdown() as $id => $name) : ?>
                                        <option value="<?=$id?>"
                                            <?=($options[Banks::BANK_BY_PAYMENT_OPTION_NAME] == $id ? 'selected' : '')?>>
                                            <?=$name?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Банк для перечислений на карту</label>
                            <div class="col-sm-10">
                                <select class=" form-control" name="Options[<?=Banks::BANK_BY_TRANSFER_IN_CARD_OPTION_NAME?>]">
                                    <option value="-1">Любой</option>
                                    <? foreach(Banks::getBanksByDropdown() as $id => $name) : ?>
                                        <option value="<?=$id?>"
                                            <?=($options[Banks::BANK_BY_TRANSFER_IN_CARD_OPTION_NAME] == $id ? 'selected' : '')?>>
                                            <?=$name?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php /** @var Banks $bank */?>
                        <?php foreach ($banks as $bank) : ?>
                            <input type="hidden" name="Bank[<?=$bank->ID?>][ID]" value="<?=$bank->ID?>">
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?=$bank->Name?> приоритет:</label>
                                <div class="col-sm-2">
                                    <input type="text" name="Bank[<?=$bank->ID?>][SortOrder]" value="<?=$bank->SortOrder?>" maxlength="2" class="form-control">
                                </div>
                                <div class="col-sm-2">
                                    <div class="checkbox m-r-xs">
                                        <input type="checkbox" id="checkboxUsePayIn<?=$bank->ID?>" name="Bank[<?=$bank->ID?>][UsePayIn]" value="1" <?=$bank->UsePayIn ? 'checked' : ''?>>
                                        <label for="checkboxUsePayIn<?=$bank->ID?>">
                                            Использовать
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="checkbox m-r-xs">
                                        <input type="checkbox" id="checkboxUseApplePay<?=$bank->ID?>" name="Bank[<?=$bank->ID?>][UseApplePay]" value="1" <?=$bank->UseApplePay ? 'checked' : ''?>>
                                        <label for="checkboxUseApplePay<?=$bank->ID?>">
                                            Apple Pay
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="checkbox m-r-xs">
                                        <input type="checkbox" id="checkboxUseGooglePay<?=$bank->ID?>" name="Bank[<?=$bank->ID?>][UseGooglePay]" value="1" <?=$bank->UseGooglePay ? 'checked' : ''?>>
                                        <label for="checkboxUseGooglePay<?=$bank->ID?>">
                                            Google Pay
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="checkbox m-r-xs">
                                        <input type="checkbox" id="checkboxUseSamsungPay<?=$bank->ID?>" name="Bank[<?=$bank->ID?>][UseSamsungPay]" value="1" <?=$bank->UseSamsungPay ? 'checked' : ''?>>
                                        <label for="checkboxUseSamsungPay<?=$bank->ID?>">
                                            Samsung Pay
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-4">
                                <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
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

<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Праздничные дни</h5>
            </div>
            <div class="ibox-content">
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
<?php $this->registerJs('lk.adminsettings()'); ?>
<?php $this->registerJs('lk.banksconf()'); ?>