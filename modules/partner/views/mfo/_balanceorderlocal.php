<?php

/* @var array $listorder */
/* @var $IsAdmin */
/* @var $sort */
/* @var $this \yii\web\View */

$sumIn = $sumOut = $sumComis = 0;

?>

<table class="table table-striped tabledata" style="font-size: 0.9em">
    <thead>
    <tr>
        <th rowspan="2"><a id="sorttype" style="color: rgb(103, 106, 108);">Дата операции</a></th>
        <th colspan="2"?>Сумма</th>
        <th rowspan="2" style="width: 50%">Комментарий</th>
        <th rowspan="2">Остаток</th>
    </tr>
    <tr>
        <th>Поступления</th>
        <th>Списания</th>
    </tr>
    </thead>
    <tbody>
    <?php if (count($listorder) > 0): ?>
        <?php foreach ($listorder as $row) : ?>
            <tr>
                <td data-datesort="<?=date('YmdHis', $row['DateOp'])?>"><?= date('d.m.Y H:i:s', $row['DateOp']) ?></td>
                <td class="text-right"><?= $row['Summ'] >= 0 ? number_format($row['Summ']/100.0,2,'.','&nbsp;') : ''?></td>
                <td class="text-right"><?= $row['Summ'] < 0 ? number_format($row['Summ']/100.0, 2,'.','&nbsp;') : ''?></td>
                <td><?= $row['Comment'] ?></td>
                <td class="text-right"><?= number_format($row['SummAfter']/100.0,2,'.','') ?></td>
            </tr>
            <?php
            if ($row['Summ'] >= 0) {
                $sumIn += $row['Summ'];
            } else {
                $sumOut += $row['Summ'];
            }
            ?>

        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <th colspan='1'>Итого: </th>
            <th class="text-right"><?=number_format(round($sumIn/100.0, 2),2,'.','&nbsp;')?></th>
            <th class="text-right"><?=number_format(round($sumOut/100.0, 2),2,'.','&nbsp;')?></th>
            <th colspan='6'></th>
        </tr>
        </tfoot>
    <?php else: ?>
        <tr><td colspan='6' style='text-align:center;'>Операции не найдены</td></tr></tbody>
    <?php endif; ?>
</table>

<script>
    jQuery(document).ready(function() {
        $('#sorttype').off().on('click', function () {
            $('#sortstatem').val(<?=$sort ? 0 : 1 ?>);
            $('#mfosumlistform').trigger('submit');
        });
    });
</script>