<?php
/* @var array $data */
/* @var array $postData */
/* @var $this \yii\web\View */
/* @var bool $IsAdmin */
/* @var array $requestToExport*/

use app\models\TU;
use yii\helpers\Html;
?>
<table class="table table-hover tabledata partner-stat-otch">
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
        $providerData = [];
        foreach ($data as $d) {
            // data by provider
            if (!array_key_exists($d['bankName'], $providerData)) {
                $providerData[$d['bankName']] = [];
            }
            $providerData[$d['bankName']][] = $d;
        }
    ?>
    <?php $providerCounter = 1; ?>
    <?php
        $overallSummary = ['summ' => 0, 'comiss' => 0, 'cnt' => 0, 'bankcomis' => 0, 'merchvozn' => 0, 'voznagsum' => 0];
    ?>
    <?php if (count($providerData)) : ?>
        <?php foreach ($providerData as $provider => $data) : ?>
            <?php
            $itog = ['summ' => 0, 'comiss' => 0, 'cnt' => 0, 'bankcomis' => 0, 'merchvozn' => 0, 'voznagsum' => 0];

            ?>
            <?php //provider output ?>
            <tr class="partner-stat-otch_provider">
                <td><?=$providerCounter++?></td>
                <td><strong><?= Html::encode($provider ?? '') ?></strong></td>
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
            <?php //data output ?>
            <?php foreach ($data as $i => $row) : ?>
                <tr class="partner-stat-otch_datarow <?=($i%2)?'darkned':''?>">
                    <td></td>
                    <td>- <?= Html::encode($row['NameUsluga']) ?></td>
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
                    $itog['summ'] += $row['SummPay'];
                    $itog['comiss'] += $row['ComissSumm'];
                    $itog['cnt'] += $row['CntPays'];
                    $itog['bankcomis'] += $row['BankComis'];
                    $itog['merchvozn'] += $row['MerchVozn'];
                    $itog['voznagsum'] += $row['VoznagSumm'];
                ?>
            <?php endforeach; ?>
            <?php //summary output ?>
            <tr class="partner-stat-otch_summary">
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
                <th></th>
            </tr>
            <?php
            $overallSummary['summ'] += $itog['summ'];
            $overallSummary['comiss'] += $itog['comiss'];
            $overallSummary['cnt'] += $itog['cnt'];
            $overallSummary['bankcomis'] += $itog['bankcomis'];
            $overallSummary['merchvozn'] += $itog['merchvozn'];
            $overallSummary['voznagsum'] += $itog['voznagsum'];
            ?>
        <?php endforeach; ?>
        <tr>
            <td colspan="10"></td>
        </tr>>
        <tr class="partner-stat-otch_summary">
            <th colspan='2'>Итого:</th>
            <th class="text-right"><?= number_format(round($overallSummary['summ'] / 100.0, 2),2,'.','&nbsp;') ?></th>
            <th class="text-right"><?= number_format(round($overallSummary['comiss'] / 100.0, 2),2,'.','&nbsp;') ?></th>
            <th class="text-right"><?= number_format(round(($overallSummary['summ'] + $overallSummary['comiss']) / 100.0, 2),2,'.','&nbsp;') ?></th>
            <?php if ($IsAdmin) : ?>
                <th class="text-right"><?= number_format(round($overallSummary['bankcomis'] / 100.0, 2),2,'.','&nbsp;') ?></th>
                <th class="text-right"><?= number_format(round($overallSummary['merchvozn'] / 100.0, 2),2,'.','&nbsp;') ?></th>
                <th class="text-right"><?= number_format(round($overallSummary['voznagsum'] / 100.0, 2),2,'.','&nbsp;') ?></th>
            <?php else : ?>
                <th class="text-right"><?= number_format(round($overallSummary['merchvozn'] / 100.0, 2),2,'.','&nbsp;') ?></th>
            <?php endif; ?>
            <th class="text-right"><?= number_format($overallSummary['cnt'],0,'.','&nbsp;') ?></th>
            <th></th>
        </tr>
        <tr>
            <th colspan='10'>
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
                <a class="btn btn-white btn-xs pull-right" target="_blank" href="/partner/stat/export-otch?<?=http_build_query($postData)?>"><i class="fa fa-share"></i>&nbsp;Экспорт</a>
            </th>
        </tr>
    <?php else: ?>
        <tr>
            <td colspan='9' style='text-align:center;'>
                Операции не найдены
            </td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>