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
use app\models\TU;
use yii\helpers\Html;

$status = [0 => "Создан", 1 => "Оплачен", 2 => "Отмена", 3 => "Возврат"];
//здесь еще в зависимости от TU нужно будет прописать описание.
?>
<div>
    <h4>Информация о платеже</h4>
    <div class="transaction-info">
        <p class="name">Номер заказа</p>
        <p class="info"><?= Html::encode(!$data['ExtBillNumber'] ? "Не указано" : $data['ExtBillNumber']) ?></p>
    </div>
    <div class="transaction-info">
        <p class="name">ID заказа</p>
        <p class="info"><?= Html::encode($data['ID']) ?></p>
    </div>
    <div class="transaction-info">
        <p class="name">Дата / Время</p>
        <p class="info"><?= Html::encode($data['DateCreate']) ?></p>
    </div>
    <div class="transaction-info">
        <p class="name">Исполнен</p>
        <p class="info"><?= Html::encode(!$data['DateOplat'] ? "Не исполнен" : $data['DateOplat']) ?></p>
    </div>
    <div class="transaction-info">
        <p class="name">Сумма</p>
        <p class="info"><?= number_format((float)$data['SummPay']/100.0,2, '.', ' ') ?></p>
    </div>
    <div class="transaction-info">
        <p class="name">Статус</p>
        <p class="info"><?= Html::encode($status[$data['Status']]) ?></p>
    </div>
</div>
<div>
    <h4>Иноформация о клиенте</h4>
    <div class="transaction-info">
        <p class="name">Номер карты</p>
        <p class="info"><?= Html::encode(!$data['CardNum'] ? "Не указано" : $data['CardNum']) ?></p>
    </div>
    <div class="transaction-info">
        <p class="name">Тип карты</p>
        <p class="info"><?= Html::encode(!$data['CardType'] ? "Не указано" : $data['CardType']) ?></p>
    </div>
    <div class="transaction-info">
        <p class="name">Срок действия карты</p>
        <p class="info"><?= Html::encode((!isset($data['CardExp']) || empty($data['CardExp'])) ? "Не указано" : substr(sprintf("%04d", $data['CardExp']), 0, 2)."/".substr(sprintf("%04d", $data['CardExp']), 2, 2)) ?></p>
    </div>
    <div class="transaction-info">
        <p class="name">Держатель карты</p>
        <p class="info"><?= Html::encode((!isset($data['CardHolder']) || empty($data['CardHolder'])) ? "Не указано" : $data['CardHolder']) ?></p>
    </div>
    <div class="transaction-info">
        <p class="name">RRN</p>
        <p class="info"><?= Html::encode(!$data['RRN'] ? "Не указано" : $data['RRN']) ?></p>
    </div>
</div>
<h4>Описание</h4>
<div class="transaction-info">
    <p class="name"><?= Html::encode($data['ErrorInfo']) ?></p>
</div>
<?php if ($data['Status'] == 1 && TU::IsInAll($data['IsCustom'])): ?>
<div class="transaction-info">
    <div class="col-sm-12"><a class="btn btn-white btn-md pull-right" href="/partner/stat/draft/<?=Html::encode($data['ID'])?>" target="_blank">Чек</a></div>
</div>
<?php endif; ?>