<?php

/* @var $this \yii\web\View */
/* @var $partner \app\models\payonline\Partner */

use yii\helpers\Html;

?>

<div class="ibox-content" style="border: none;">
    <div class="sk-spinner sk-spinner-wave">
        <div class="sk-rect1"></div>
        <div class="sk-rect2"></div>
        <div class="sk-rect3"></div>
        <div class="sk-rect4"></div>
        <div class="sk-rect5"></div>
    </div>
    <h3>Общие контакты</h3>
    <form class="form-horizontal" id="formEditCommonCont">
        <div class="form-group">
            <label class="col-sm-3 control-label">Сайт:</label>
            <div class="col-sm-6">
                <input type="text" maxlength="200" class="form-control" name="URLSite" value="<?=Html::encode($partner->URLSite)?>">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Телефон:</label>
            <div class="col-sm-6">
                <input type="text" maxlength="50" class="form-control" name="Phone" value="<?=Html::encode($partner->Phone)?>">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">E-mail:</label>
            <div class="col-sm-6">
                <input type="text" maxlength="50" class="form-control" name="Email" value="<?=Html::encode($partner->Email)?>">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-6">
                <?= Html::hiddenInput('_csrf', Yii::$app->request->csrfToken, ['id' => '_csrf']) ?>
                <?= Html::hiddenInput('IdPartner', $partner->ID) ?>
                <button class="btn btn-sm btn-primary m-t-n-xs" type="submit">Сохранить</button>
            </div>
        </div>
    </form>
    <hr>
    <h3>Контактное лицо</h3>
    <form class="form-horizontal" id="formEditTehCont">
        <div class="form-group">
            <label class="col-sm-3 control-label">ФИО менеджера:</label>
            <div class="col-sm-6">
                <input type="text" maxlength="100" class="form-control" name="KontTehFio" value="<?=Html::encode($partner->KontTehFio)?>">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Телефон:</label>
            <div class="col-sm-6">
                <input type="text" maxlength="50" class="form-control" name="KontTehPhone" value="<?=Html::encode($partner->KontTehPhone)?>">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">E-mail:</label>
            <div class="col-sm-6">
                <input type="text" maxlength="50" class="form-control" name="KontTehEmail" value="<?=Html::encode($partner->KontTehEmail)?>">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-6">
                <?= Html::hiddenInput('_csrf', Yii::$app->request->csrfToken, ['id' => '_csrf']) ?>
                <?= Html::hiddenInput('IdPartner', $partner->ID) ?>
                <button class="btn btn-sm btn-primary m-t-n-xs" type="submit">Сохранить</button>
            </div>
        </div>
    </form>
    <hr>
    <h3>Контактное лицо по финансовым вопросам</h3>
    <form class="form-horizontal" id="formEditFinansCont">
        <div class="form-group">
            <label class="col-sm-3 control-label">ФИО:</label>
            <div class="col-sm-6">
                <input type="text" maxlength="100" class="form-control" name="KontFinansFio" value="<?=Html::encode($partner->KontFinansFio)?>">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">Телефон:</label>
            <div class="col-sm-6">
                <input type="text" maxlength="50" class="form-control" name="KontFinansPhone" value="<?=Html::encode($partner->KontFinansPhone)?>">
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">E-mail:</label>
            <div class="col-sm-6">
                <input type="text" maxlength="50" class="form-control" name="KontFinansEmail" value="<?=Html::encode($partner->KontFinansEmail)?>">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-6">
                <?= Html::hiddenInput('_csrf', Yii::$app->request->csrfToken, ['id' => '_csrf']) ?>
                <?= Html::hiddenInput('IdPartner', $partner->ID) ?>
                <button class="btn btn-sm btn-primary m-t-n-xs" type="submit">Сохранить</button>
            </div>
        </div>
    </form>
</div>
