<?php

/* @var array $data */
/* @var $this \yii\web\View */
/* @var bool $IsAdmin */

use yii\helpers\Html;

?>

<table class="table table-striped tabledata" id="orderlisttable" style="font-size: 0.9em">
    <thead>
    <tr>
        <th>Номер счета</th>
        <th>Статус</th>
        <th>Дата создания</th>
        <th>Дата оплаты</th>
        <th class="text-right">Сумма</th>
        <th>Адрес электронной почты</th>
        <th>Номер телефона</th>
        <th>Действие</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (count($data) > 0) :
        $stClr = [0 => "blue", 1 => "green", 2 => "red", 3 => "red"];
        $st = [0 => "Создан", 1 => "Оплачен", 2 => "Отменен"];
        $sumpay = 0;
        foreach ($data as $row) :
            $cancelBtn = "";
            $sumpay += $row['SumOrder'];
            $actionBtn = '';
            if ($row['StateOrder'] == 0) {
                $actionBtn = '
                <div class="btn-group">
                    <button data-id="' . Html::encode($row['ID']) . '" data-action="cancelorder" class="btn-white btn btn-xs">Отменить</button>
                    <button data-id="' . Html::encode($row['ID']) . '" data-action="resendorder" class="btn-white btn btn-xs">Повторно</button>
                </div>';
            }
            ?>
            <tr>
                <td><a href="/widget/order/<?= Html::encode($row['ID']) ?>" target="_blank"><?= Html::encode($row['ID']) ?></a></td>
                <td><span class="label label-primary" style="background-color: <?=$stClr[$row['StateOrder']]?>">
                            <?=$st[$row['StateOrder']]?></span></td>
                <td><?= date('d.m.Y H:i:s', $row['DateAdd']) ?></td>
                <td>
                    <?= Html::encode($row['DateOplata'] > 0 ? date('d.m.Y H:i:s', $row['DateOplata'])."<br>".$row['IdPaySchet'] : "нет") ?>
                </td>
                <td class="text-right"><?= number_format($row['SumOrder'] / 100.0,2,'.',' ') ?></td>
                <td><?= Html::encode($row['EmailTo']) ?></td>
                <td><?= Html::encode($row['SmsTo']) ?></td>
                <td><?= $actionBtn ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <th colspan='4'>Итого: </th>
            <th class="text-right"><?=number_format(round($sumpay/100.0, 2), 2, '.', ' ')?></th>
            <th colspan='3'></th>
        </tr>
        </tfoot>
    <?php else : ?>
        <tr><td colspan='12' style='text-align:center;'>Операции не найдены</td></tr></tbody>
    <?php endif; ?>
</table>

<script>
    lk.orderlistdata();
</script>