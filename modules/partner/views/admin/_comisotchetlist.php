<?php
/* @var array $data */
/* @var $this \yii\web\View */
/* @var bool $IsAdmin */
?>

<table class="table table-striped tabledata">
    <thead>
    <tr>
        <th>#</th>
        <th>Дата c</th>
        <th>Дата по</th>
        <th>Мерчант</th>
        <th>Реквизиты</th>
        <th>Тип</th>
        <th>Дата<br>отправки</th>
        <th class="text-right">Сумма</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (count($data) > 0) {
    $i = 1;
    $itog = ['summ' => 0];

    $i = 1;
    foreach ($data as $row) {
        $itog['summ'] += $row['Summ'];
        ?>
        <tr>
            <td><?= ($i++) ?></td>
            <td><?= date("d.m.Y", $row['DateFrom']) ?></td>
            <td><?= date("d.m.Y", $row['DateTo']) ?></td>
            <td><?= $row['NamePartner'] ?></td>
            <td><?= str_ireplace('|', '<br>', $row['QrParams']) ?></td>
            <?php if(isset($row['TypeVyvod'])): ?>
                <td><?= $row['TypeVyvod'] == 0 ? 'погашение' : 'выдача' ?></td>
            <?php elseif(isset($row['TypePerechisl'])): ?>
                <td><?= $row['TypePerechisl'] == 0 ? 'на выплату' : 'на р/с' ?></td>
            <?php else: ?>
                <td></td>
            <?php endif; ?>
            <td><?= date("d.m.Y H:i:s", $row['DateOp']) ?></td>
            <td class="text-right"><?= number_format($row['Summ'] / 100.0, 2, '.', '&nbsp;') ?></td>
        </tr>
        <?php
    }
    ?>
    </tbody>
    <tfoot>
    <tr>
        <th colspan='7'>Итого:</th>
        <th class="text-right"><?= number_format(round($itog['summ'] / 100.0, 2), 2, '.', '&nbsp;') ?></th>
    </tr>
    </tfoot>
    <?php
    } else {
        echo "<tr><td colspan='8' style='text-align:center;'>Операции не найдены</td></tr></tbody>";
    }
    ?>
</table>
