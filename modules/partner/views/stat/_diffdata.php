<?php

/** @var array $badStatus */
/** @var array $notFound */

use app\services\payment\models\PaySchet;

?>

<table class="table table-striped tabledata" style="font-size: 0.9em">
    <thead>
    <tr>
        <th>ID Vepay</th>
        <th>Ext ID</th>
        <th>Номер операции</th>
        <th>Статус в Vepay</th>
        <th>Статус в банке-эквайере</th>
        <th>Дата Создания/Оплаты</th>
        <th>Услуга</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($badStatus as $row): ?>
        <tr>
            <td><?= $row['paySchet']['ID'] ?></td>
            <td><?= $row['paySchet']['Extid'] ?></td>
            <td><?= $row['paySchet']['ExtBillNumber'] ?></td>
            <td><?= PaySchet::STATUSES[$row['paySchet']['Status']] ?></td>
            <td><?= $row['record']['Status'] ?></td>
            <td>
                <?= date('d.m.Y H:i:s', $row['paySchet']['DateCreate']) ?>
                <span> /</span>
                <br>
                <?= $row['paySchet']['DateOplat'] > 0 ? date('d.m.Y H:i:s', $row['paySchet']['DateOplat']) : "нет" ?>
            </td>
            <td><?= $row['paySchet']['NameUsluga'] ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<table class="table table-striped tabledata" style="font-size: 0.9em">
    <thead>
    <tr>
        <th>Номера заявки ПЦ нет в Vepay</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($notFound as $row): ?>
        <tr>
            <td><?= $row['Select'] ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<table class="table table-striped tabledata" style="font-size: 0.9em">
    <tfoot>
    <tr>
        <th>
            <form action="/partner/stat/diffexport" method="post">
                <textarea name="badStatus" style="display: none;"><?= json_encode($badStatus) ?></textarea>
                <textarea name="notFound" style="display: none;"><?= json_encode($notFound) ?></textarea>

                <button name="format" value="csv" class="btn btn-white btn-xs">
                    <i class="fa fa-share"></i>&nbsp;Экспорт csv
                </button>
                <button name="format" value="xlsx" class="btn btn-white btn-xs">
                    <i class="fa fa-share"></i>&nbsp;Экспорт xlsx
                </button>
            </form>
        </th>
    </tr>
    </tfoot>
</table>
