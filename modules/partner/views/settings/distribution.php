<?php

/* @var Partner[] $partners */
/* @var bool $IsAdmin */

use app\models\payonline\Partner;

$this->title = "настройка рассылок";

$this->params['breadtitle'] = "Настройка рассылок";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];

?>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-title">
                <?php if ($IsAdmin) {
                    echo $this->render('_tabs');
                } ?>
            </div>
            <div class="ibox-content">

                <form method="post" class="form-horizontal" id="partner-distribution">

                    <div class="form-group">
                        <div class="col-sm-12">
                            <h4>Настройка рассылок:</h4>
                        </div>
                    </div>

                    <?php foreach ($partners as $partner) : ?>
                        <div class="form-group">
                            <div class="col-sm-4">
                                <p><?= $partner['Name'] ?></p>
                            </div>
                            <div class="col-md-4">
                                <input class="form-control" type="email" placeholder='example@online.ru'
                                       name="email[<?= $partner['ID'] ?>]" value="<?= isset($partner->distribution->email) ?$partner->distribution->email : "" ?>">
                            </div>
                            <?php
                            $payment = isset($partner->distribution->payment)? $partner->distribution->payment : false;
                            $repayment = isset($partner->distribution->repayment)? $partner->distribution->repayment : false;
                            ?>
                            <div class="col-md-2">
                                <label>
                                    Выдача
                                    <input type="checkbox" <?= $payment ? "checked" : '' ?> value="1"
                                           name="payment[<?= $partner['ID'] ?>]">
                                </label>
                            </div>
                            <div class="col-md-2">
                                <label>
                                    Погашение
                                    <input type="checkbox" <?= $repayment ? "checked" : '' ?>
                                           value="1"
                                           name="repayment[<?= $partner['ID'] ?>]">
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="form-group">
                        <div class="col-md-6">
                            <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                            <input type="submit" class="btn btn-primary" value="Сохранить данные">
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

<?php $this->registerJs('lk.mfodistribution()'); ?>
