<?php
/* @var array $data */
/* @var $this \yii\web\View */
/* @var bool $IsAdmin */
/* @var array $requestToExport*/

use app\models\TU; ?>

<?php
function renderRow($row, $IsAdmin)
{
    static $i = 1;
?>
    <tr>
        <td><?= ($i++) ?></td>
        <td>- <?= $row['NameUsluga'] ?></td>
        <td class="text-right"><?= number_format($row['SummPay'] / 100.0,2,'.','&nbsp;') ?></td>
        <td class="text-right"><?= number_format($row['ComissSumm'] / 100.0,2,'.','&nbsp;') ?></td>
        <td class="text-right"><?= number_format(($row['SummPay'] + $row['ComissSumm']) / 100.0,2,'.','&nbsp;') ?></td>
        <?php if ($IsAdmin) : ?>
            <td class="text-right"><?= number_format($row['BankComis'] / 100.0,2,'.','&nbsp;') ?></td>
            <td class="text-right"><?= number_format($row['MerchVozn'] / 100.0,2,'.','&nbsp;') ?></td>
            <td class="text-right"><?= number_format($row['VoznagSumm'] / 100.0,2,'.','&nbsp;') ?></td>
        <?php else : ?>
            <td class="text-right"><?= number_format($row['MerchVozn'] / 100.0,2,'.','&nbsp;') ?></td>
        <?php endif; ?>
        <td class="text-right"><?= number_format($row['CntPays'],0,'.','&nbsp;') ?></td>
        <td></td>
    </tr>
<?php
}

function renderProv($row, $IsAdmin)
{
    static $i = 1;
?>
    <tr>
        <td></td>
        <td><?= $row['Name'] ?></td>
        <td class="text-right"></td>
        <td class="text-right"></td>
        <td class="text-right"></td>
        <?php if ($IsAdmin) : ?>
            <td class="text-right"></td>
            <td class="text-right"></td>
            <td class="text-right"></td>
        <?php else : ?>
            <td class="text-right"></td>
        <?php endif; ?>
        <td class="text-right"></td>
        <td></td>
    </tr>
<?php
}

function renderItog($itog, $IsAdmin)
{
?>
    <tr>
        <th colspan='2'>Итого:</th>
        <th class="text-right"><?= number_format(round($itog['summ'] / 100.0, 2),2,'.','&nbsp;') ?></th>
        <th class="text-right"><?= number_format(round($itog['comiss'] / 100.0, 2),2,'.','&nbsp;') ?></th>
        <th class="text-right"><?= number_format(round(($itog['summ'] + $itog['comiss']) / 100.0, 2),2,'.','&nbsp;') ?></th>
        <?php if ($IsAdmin) : ?>
            <th class="text-right"><?= number_format(round($itog['bankcomis'] / 100.0, 2),2,'.','&nbsp;') ?></th>
            <th class="text-right"><?= number_format(round($itog['merchvozn'] / 100.0, 2),2,'.','&nbsp;') ?></th>
            <th class="text-right"><?= number_format(round($itog['voznagsum'] / 100.0, 2),2,'.','&nbsp;') ?></th>
        <?php else : ?>
            <th class="text-right"><?= number_format(round($itog['merchvozn'] / 100.0, 2),2,'.','&nbsp;') ?></th>
        <?php endif; ?>
        <th class="text-right"><?= number_format($itog['cnt'],0,'.','&nbsp;') ?></th>
    </tr>
<?php
}
?>

<table class="table table-striped tabledata">
    <thead>
    <tr>
        <th>#</th>
        <th>Провайдер / Услуга</th>
        <th class="text-right">К зачислению</th>
        <th class="text-right">Комиссия с клиента</th>
        <th class="text-right">К оплате</th>
        <?php if ($IsAdmin) : ?>
            <th class="text-right">Комиссия провайдера</th>
            <th class="text-right">Комиссия с мерчанта</th>
            <th class="text-right">Возн. Vepay</th>
        <?php else : ?>
            <th class="text-right">Комиссия с мерчанта</th>
        <?php endif; ?>
        <th class="text-right">Число операций</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php

    $dataIn = $dataOut = $dataVyvod = [];
    $itog1 = $itog2 = $itog3 = ['summ' => 0, 'comiss' => 0, 'cnt' => 0, 'bankcomis' => 0, 'merchvozn' => 0, 'voznagsum' => 0];
    foreach ($data as $d) {
        if (TU::IsOutMfo($d['IsCustom'])) {
            $dataOut[] = $d;
        } elseif (TU::IsInAll($d['IsCustom'])) {
            $dataIn[] = $d;
        } else {
            $dataVyvod[] = $d;
        }
    }

    if (count($dataIn) > 0) {
        $provider = '';
        foreach ($dataIn as $row) {

            if ($provider != $row['Name']) {
                if ($provider != '') {
                    renderItog($itog1, $IsAdmin);
                }
                $itog1 = ['summ' => 0, 'comiss' => 0, 'cnt' => 0, 'bankcomis' => 0, 'merchvozn' => 0, 'voznagsum' => 0];
                renderProv($row, $IsAdmin);
            }
            $provider = $row['Name'];

            $itog1['summ'] += $row['SummPay'];
            $itog1['comiss'] += $row['ComissSumm'];
            $itog1['cnt'] += $row['CntPays'];
            $itog1['bankcomis'] += $row['BankComis'];
            $itog1['merchvozn'] += $row['MerchVozn'];
            $itog1['voznagsum'] += $row['VoznagSumm'];



            renderRow($row, $IsAdmin);
        }

    }
    if (count($dataOut) > 0) {
        foreach ($dataOut as $row) {
            $itog2['summ'] += $row['SummPay'];
            $itog2['comiss'] += $row['ComissSumm'];
            $itog2['cnt'] += $row['CntPays'];
            $itog2['bankcomis'] += $row['BankComis'];
            $itog2['merchvozn'] += $row['MerchVozn'];
            $itog2['voznagsum'] += $row['VoznagSumm'];

            renderRow($row, $IsAdmin);
        }
        renderItog($itog2, $IsAdmin);
    }
    if (count($dataVyvod) > 0) {
        foreach ($dataVyvod as $row) {
            $itog3['summ'] += $row['SummPay'];
            $itog3['comiss'] += $row['ComissSumm'];
            $itog3['cnt'] += $row['CntPays'];
            $itog3['bankcomis'] += $row['BankComis'];
            $itog3['merchvozn'] += $row['MerchVozn'];
            $itog3['voznagsum'] += $row['VoznagSumm'];

            renderRow($row, $IsAdmin);
        }
        renderItog($itog3, $IsAdmin);
    }
    $itog = [
        'summ' => $itog1['summ']+$itog2['summ']+$itog3['summ'],
        'comiss' => $itog1['comiss']+$itog2['comiss']+$itog3['comiss'],
        'cnt' => $itog1['cnt']+$itog2['cnt']+$itog3['cnt'],
        'bankcomis' => $itog1['bankcomis']+$itog2['bankcomis']+$itog3['bankcomis'],
        'merchvozn' => $itog1['merchvozn']+$itog2['merchvozn']+$itog3['merchvozn'],
        'voznagsum' => $itog1['voznagsum']+$itog2['voznagsum']+$itog3['voznagsum']
    ];
    ?>

    <?php if (!count($dataIn) && !count($dataOut) && !count($dataVyvod)) : ?>
        <tr><td colspan='9' style='text-align:center;'>Операции не найдены</td></tr>
    <?php else: ?>
        <?php renderItog($itog, $IsAdmin); ?>
        <tr>
            <th colspan='9'>
                <?php
                $exportLink = 'datefrom='. $requestToExport['datefrom'];
                $exportLink .= '&dateto=' . $requestToExport['dateto'];
                if (isset($requestToExport['TypeUslug']) && count($requestToExport['TypeUslug']) > 0) {
                    foreach ($requestToExport['TypeUslug'] as $usluga){
                        $exportLink .= '&TypeUslug[]='.$usluga;
                    }
                }
                $exportLink .= '&IdPart='.$requestToExport['IdPart'];
                ?>
                <a class="btn btn-white btn-xs pull-right" target="_blank" href="/partner/stat/export-otch?<?=$exportLink?>"><i class="fa fa-share"></i>&nbsp;Экспорт</a>
            </th>
        </tr>
    <?php endif; ?>
    </tbody>
</table>