<?php

use app\models\partner\stat\StatFilter;
use app\models\payonline\Partner;

/**
 * @var \yii\web\View $this
 * @var Partner[] $partners
 */

echo $this->render('@app/modules/partner/views/selectpartner', [
    'partners' => $partners,
    'all' => false
]);

?>
<!--Добавлено 30.12.19-->
<div class="row">
    <div class="col-md-6">
        <div class="ibox">
            <div class="ibox-title">
                <h5>Настройка рассылок</h5>
            </div>
            <div class="ibox-content">
                <form method="post" id="partner-distribution">
                    <? foreach ($partners as $partner) : ?>
                        <div class="row form-group" style="display: flex;">
                            <div class="col-md-2 ">
                                <p><?= $partner['Name'] ?></p>
                            </div>
                            <div class="col-md-5">
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
                            <div class="col-md-3">
                                <label>
                                    Погашение
                                    <input type="checkbox" <?= $repayment ? "checked" : '' ?>
                                           value="1"
                                           name="repayment[<?= $partner['ID'] ?>]">
                                </label>
                            </div>
                        </div>
                    <? endforeach; ?>
                    <input type="submit" class="btn btn-primary" value="Сохранить данные">
                </form>
            </div>
        </div>
    </div>
</div>