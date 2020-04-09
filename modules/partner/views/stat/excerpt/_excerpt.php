<?php
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
 */
$status = [0 => "Создан", 1 => "Оплачен", 2 => "Отмена", 3 => "Возврат"];
//здесь еще в зависимости от TU нужно будет прописать описание.
?>
<div>
    <h4>Информация о платеже</h4>
    <div class="transaction-info">
        <p class="name">Номер заказа</p>
        <p class="info"><?= !$data['ExtBillNumber'] ? "Не указано" : $data['ExtBillNumber'] ?></p>
    </div>
    <div class="transaction-info">
        <p class="name">ID заказа</p>
        <p class="info"><?= $data['ID'] ?></p>
    </div>
    <div class="transaction-info">
        <p class="name">Дата / Время</p>
        <p class="info"><?= $data['DateCreate'] ?></p>
    </div>
    <div class="transaction-info">
        <p class="name">Исполнен</p>
        <p class="info"><?= !$data['DateOplat'] ? "Не исполнен" : $data['DateOplat'] ?></p>
    </div>
    <div class="transaction-info">
        <p class="name">Сумма</p>
        <p class="info"><?= number_format((float)$data['SummPay']/100.0,2, '.', ' ') ?></p>
    </div>
    <div class="transaction-info">
        <p class="name">Статус</p>
        <p class="info"><?= $status[$data['Status']] ?></p>
    </div>
</div>
<div>
    <h4>Иноформация о клиенте</h4>
    <div class="transaction-info">
        <p class="name">Номер карты</p>
        <p class="info"><?= !$data['CardNum'] ? "Не указано" : $data['CardNum'] ?></p>
    </div>
    <div class="transaction-info">
        <p class="name">Тип карты</p>
        <p class="info"><?= !$data['CardType'] ? "Не указано" : $data['CardType'] ?></p>
    </div>
    <div class="transaction-info">
        <p class="name">Срок действия карты</p>
        <p class="info"><?= (!isset($data['CardExp']) || empty($data['CardExp'])) ? "Не указано" : substr(sprintf("%04d", $data['CardExp']), 0, 2)."/".substr(sprintf("%04d", $data['CardExp']), 2, 2) ?></p>
    </div>
    <div class="transaction-info">
        <p class="name">Держатель карты</p>
        <p class="info"><?= (!isset($data['CardHolder']) || empty($data['CardHolder'])) ? "Не указано" : $data['CardHolder'] ?></p>
    </div>
    <div class="transaction-info">
        <p class="name">RRN</p>
        <p class="info"><?= !$data['RRN'] ? "Не указано" : $data['RRN'] ?></p>
    </div>
</div>
<h4>Описание</h4>
<div class="transaction-info">
    <p class="name"><?= $data['ErrorInfo'] ?></p>
</div>
