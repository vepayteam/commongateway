<?php

/* @var array $listorder */
/* @var $IsAdmin */
/* @var $sort */
/* @var $this \yii\web\View */
/* @var $dateFrom */
/* @var $dateTo */
/* @var $istransit */
/* @var $IdPartner */

use yii\helpers\Html;

$sumIn = $sumOut = 0;

?>

<table class="table table-striped tabledata" style="font-size: 0.9em">
    <thead>
    <tr>
        <th rowspan="2"><a id="sorttype" style="color: rgb(103, 106, 108);">Дата операции</a></th>
        <th colspan="2">Сумма</th>
        <th rowspan="2">Комментарий</th>
        <th rowspan="2">Контрагент</th>
    </tr>
    <tr>
        <th>Поступления</th>
        <th>Списания</th>
    </tr>
    </thead>
    <tbody>
    <?php if (count($listorder) > 0) :?>
        <?php foreach ($listorder as $row) : ?>
        <tr>
            <td data-datesort="<?=date('YmdHis', $row['DatePP'])?>"><?= date('d.m.Y H:i:s', $row['DatePP']) ?></td>
            <td class="text-right"><?= $row['IsCredit'] ? number_format($row['SummPP']/100.0,2,'.','&nbsp;') : ''?></td>
            <td class="text-right"><?= !$row['IsCredit'] ? number_format($row['SummPP']/100.0,2,'.','&nbsp;') : ''?></td>
            <td><?= Html::encode($row['Description']) ?></td>
            <td><?= Html::encode($row['Name']) ?></td>
        </tr>
        <?php if ($row['IsCredit']) $sumIn += $row['SummPP']; ?>
        <?php if (!$row['IsCredit']) {
            $sumOut += $row['SummPP'];
        } ?>

        <?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <th colspan='1'>Итого: </th>
        <th class="text-right"><?=number_format(round($sumIn/100.0, 2),2,'.','&nbsp;')?></th>
        <th class="text-right"><?=number_format(round($sumOut/100.0, 2),2,'.','&nbsp;')?></th>
        <th colspan='7'></th>
    </tr>
    <tr>
        <td colspan='6'>
            <a class="btn btn-white btn-xs pull-right" target="_blank"
               href="<?= Html::encode("/partner/mfo/exportvyp?istransit={$istransit}&dateFrom={$dateFrom}&dateTo={$dateTo}" . ($IsAdmin ? "&idpartner={$IdPartner}" : '')) ?>"
            ><i class="fa fa-share"></i>&nbsp;Экспорт</a></div>
        </td>
    </tr>
    </tfoot>
    <?php else : ?>
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