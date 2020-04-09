<?php

/* @var yii\web\View $this */
/* @var MfoSettings $settings */
/* @var integer $IdPartner */
/* @var bool $IsAdmin */

use app\models\mfo\MfoSettings;
use yii\bootstrap\Html;
use yii\web\View;

$this->title = "настройки колбэков";

$this->params['breadtitle'] = "Настройки: колбэки";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];
?>

        <div class="row">
            <div class="col-sm-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h4>Настройки: колбэки</h4>
                        <?php if ($IsAdmin) {
                            echo $this->render('_tabs');
                        } ?>
                    </div>
                    <div class="ibox-content">

                        <form method="post" class="form-horizontal" id="mfosettings">

                        <div class="form-group">
                            <div class="col-sm-12">
                                <h4>Оповещения <?=$IsAdmin ? 'ID=' . $IdPartner : ''?>:</h4>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Адрес для обратного запроса</label>
                            <div class="col-sm-10 col-md-6">
                                <input type="url" name="Settings[url]" value="<?=Html::encode($settings->url)?>" maxlength="300" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Ключ обратного запроса</label>
                            <div class="col-sm-10 col-md-6">
                                <input type="text" name="Settings[key]" value="<?=Html::encode($settings->key)?>" maxlength="20" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <h4>Адрес возврата:</h4>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Адрес возврата на страницу сайта (успех)</label>
                            <div class="col-sm-10 col-md-6">
                                <input type="url" name="Settings[UrlReturn]" value="<?=Html::encode($settings->UrlReturn)?>" maxlength="300" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Адрес возврата на страницу сайта (ошибка)</label>
                            <div class="col-sm-10 col-md-6">
                                <input type="url" name="Settings[UrlReturnFail]" value="<?=Html::encode($settings->UrlReturnFail)?>" maxlength="300" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Адрес возврата на страницу сайта (отмена)</label>
                            <div class="col-sm-10 col-md-6">
                                <input type="url" name="Settings[UrlReturnCancel]" value="<?=Html::encode($settings->UrlReturnCancel)?>" maxlength="300" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <h4>Адрес проверки возможности оплаты:</h4>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Адрес проверки возможности оплаты</label>
                            <div class="col-sm-10 col-md-6">
                                <input type="url" name="Settings[UrlCheckReq]" value="<?=Html::encode($settings->UrlCheckReq)?>" maxlength="300" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-4">
                                <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                                <input name="IdPartner" type="hidden" value="<?= $IdPartner ?>">
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


<?php $this->registerJs('lk.mfosettings()'); ?>