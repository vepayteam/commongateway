<?php

use app\assets\PartnerAsset;
use yii\helpers\Html;

//PartnerAsset::register($this);
$this->beginPage();
/**
 * @var \yii\web\View $this
 * @var array         $data
 * Были выбраны след. данные из бд.
 * o.ErrorInfo,
 * o.DateCreate,
 * o.DateOplat,
 * o.Status,
 * o.SummPay,
 * o.ExtBillNumber,
 * o.ID
 * o.RNN,
 * o.CartType,
 * o.CardNum
 * NameUsluga
 */
$status = [0 => "Создан", 1 => "Оплачен", 2 => "Отмена", 3 => "Возврат"];
//здесь еще в зависимости от TU нужно будет прописать описание.

?>
<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="width: 100%">
            <th style="width: 30%"></th>
            <th style="color:#042bcd; width: 50%; text-align: right; font-weight: bold;">Операция «<?=$data['NameUsluga']?>»</th>
        </tr>
    </thead>
    <tr>
        <td class="sub-header" style="border-bottom: none; color:#042bcd; padding-left: 30px; font-weight: bold;">Информация о платеже</td>
        <td style="border-bottom: none;"></td>
    </tr>
    <tr>
        <td>Номер заказа</td>
        <td ><?=!$data['ExtBillNumber'] ? "Не указано" : Html::encode($data['ExtBillNumber'])?></td>
    </tr>
    <tr>
        <td>ID заказа</td>
        <td><?= Html::encode($data['ID']) ?></td>
    </tr>
    <tr>
        <td>Дата / Время</td>
        <td><?= Html::encode($data['DateCreate']) ?></td>
    </tr>
    <tr>
        <td>Исполнен</td>
        <td><?= !$data['DateOplat'] ? "Не исполнен" : Html::encode($data['DateOplat']) ?></td>
    </tr>

    <tr>
        <td>Сумма</td>
        <td><?= number_format($data['SummPay']/100.0, 2, '.', ' ') ?> ₽</td>
    </tr>
    <tr>
        <td>Статус</td>
        <td><?= Html::encode($status[$data['Status']]) ?></td>
    </tr>
    <tr>
        <td class="sub-header" style="border-bottom: none; color:#042bcd; padding-left: 30px; font-weight: bold;">Информация о клиенте</td>
        <td style="border-bottom: none;"></td>
    </tr>
    <tr>
        <td>Фио владельца</td>
        <td><?= (!isset($data['CardHolder']) || empty($data['CardHolder'])) ? "Не указано" : Html::encode($data['CardHolder']) ?></td>
    </tr>
    <tr>
        <td>Номер карты</td>
        <td><?= !$data['CardNum'] ? "Не указано" : Html::encode($data['CardNum']) ?></td>
    </tr>
    <tr>
        <td>Тип карты</td>
        <td><?= !$data['CardType'] ? "Не указано" : Html::encode($data['CardType']) ?></td>
    </tr>
    <tr>
        <td>Срок действия</td>
        <td><?= (!isset($data['CardExp']) || empty($data['CardExp'])) ? "Не указано" : substr(sprintf("%04d", $data['CardExp']), 0, 2)."/".substr(sprintf("%04d", $data['CardExp']), 2, 2) ?></td>
    </tr>
    <tr>
        <td>RRN</td>
        <td><?= !$data['RRN'] ? "Не указано" : Html::encode($data['RRN']) ?></td>
    </tr>

    <tr>
        <td class="sub-header" style="border-bottom: none; color:#042bcd; padding-left: 30px; font-weight: bold;">Описание</td>
        <td style="border-bottom: none;"></td>
    </tr>
    <tr>
        <td><?= Html::encode($data['ErrorInfo']) ?></td>
        <td></td>
    </tr>

</table>