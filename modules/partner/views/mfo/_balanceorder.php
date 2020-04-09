<?php

/* @var array $listorder */
/* @var $IsAdmin */
/* @var $sort */
/* @var $this \yii\web\View */
/* @var $dateFrom */
/* @var $dateTo */
/* @var $istransit */
/* @var $IdPartner */

$sumIn = $sumOut = $sumComis = 0;

?>

<table class="table table-striped tabledata" style="font-size: 0.9em">
    <thead>
    <tr>
        <th rowspan="2"><a id="sorttype" style="color: rgb(103, 106, 108);">Дата операции</a></th>
        <th colspan=<?=($IsAdmin ? "3" : "2")?> >Сумма</th>
        <th rowspan="2">Комментарий</th>
        <th rowspan="2">Контрагент</th>
    </tr>
    <tr>
        <th>Поступления</th>
        <th>Списания</th>
        <?php if ($IsAdmin) : ?>
            <th>Комиссия Vepay</th>
        <?php endif; ?>
    </tr>
    </thead>
    <tbody>
    <?php if (count($listorder) > 0) :?>
        <?php foreach ($listorder as $row) : ?>
        <tr>
            <td data-datesort="<?=date('YmdHis', $row['DatePP'])?>"><?= date('d.m.Y H:i:s', $row['DatePP']) ?></td>
            <td class="text-right"><?= $row['IsCredit'] ? number_format($row['SummPP']/100.0,2,'.','&nbsp;') : ''?></td>
            <?php if ($IsAdmin) : ?>
                <td class="text-right"><?= !$row['IsCredit'] ? number_format($row['SummPP']/100.0,2,'.','&nbsp;') : ''?></td>
                <td class="text-right"><?= !$row['IsCredit'] ? number_format($row['SummComis']/100.0,2,'.','&nbsp;') : ''?></td>
            <?php else: ?>
                <td class="text-right"><?= !$row['IsCredit'] ? number_format(($row['SummPP'] + $row['SummComis'])/100.0,2,'.','&nbsp;') : ''?></td>
            <?php endif; ?>
            <td><?= $row['Description'] ?></td>
            <td><?= $row['Name'] ?></td>
        </tr>
        <?php if ($row['IsCredit']) $sumIn += $row['SummPP']; ?>
        <?php if (!$row['IsCredit']) {
            $sumOut += $row['SummPP'];
            $sumComis += $row['SummComis'];
        } ?>

        <?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <th colspan='1'>Итого: </th>
        <th class="text-right"><?=number_format(round($sumIn/100.0, 2),2,'.','&nbsp;')?></th>
        <?php if ($IsAdmin) : ?>
            <th class="text-right"><?=number_format(round($sumOut/100.0, 2),2,'.','&nbsp;')?></th>
            <th class="text-right"><?=number_format(round($sumComis/100.0, 2),2,'.','&nbsp;')?></th>
        <?php else : ?>
            <th class="text-right"><?=number_format(round(($sumOut + $sumComis)/100.0, 2),2,'.','&nbsp;')?></th>
        <?php endif; ?>
        <th colspan='7'></th>
    </tr>
    <tr>
        <td colspan='6'>
            <a class="btn btn-white btn-xs pull-right" target="_blank"
               href="/partner/mfo/exportvyp?istransit=<?=$istransit?>&dateFrom=<?=$dateFrom?>&dateTo=<?=$dateTo?><?=($IsAdmin?'&idpartner='.$IdPartner:'')?>"
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