<?php

/* @var array $reqdata */
/* @var array $data */
/* @var $this \yii\web\View */
/* @var int $cnt  */
/* @var $page array|mixed */
/* @var int $cntpage  */
/* @var int $sumcomis  */
/* @var int $sumpay  */
/* @var int $bankcomis */
/* @var int $voznagps  */
/* @var bool $IsAdmin */

use app\models\payonline\Uslugatovar;
use app\models\payonline\User;
use app\models\TU;
?>

<table class="table table-striped tabledata" style="font-size: 0.9em">
    <thead>
    <tr>
        <th>ID Vepay</th>
        <th>Ext ID</th>
        <th>Услуга</th>
        <th>Реквизиты</th>
        <th class="text-right">Сумма</th>
        <th class="text-right">Комиссия</th>
        <th class="text-right">К оплате</th>
        <?php if ($IsAdmin) : ?>
            <th class="text-right">
                Комисия банка
            </th>
            <th class="text-right">
                Возн. Vepay
            </th>
        <?php endif; ?>
        <th>Дата Создания/Оплаты</th>
        <th>Номер операции</th>
        <th>Статус</th>
        <th>Ошибка</th>
        <th>Тип карты</th>
        <th>Геолокация</th>
        <th>Действия</th>
    </tr>
    </thead>
    <tbody>
<?php
    if (count($data) > 0) :
        $stClr = [0 => "blue", 1 => "green", 2 => "red", 3 => "#FF3E00"];
        $st = [0 => "В обработке", 1 => "Оплачен", 2 => "Отмена", 3 => "Возврат"];
        foreach ($data as $row) :
?>
            <tr>
                <td><?= $row['ID'] ?></td>
                <td><?= $row['Extid'] ?></td>
                <td><?= $row['NameUsluga'] ?></td>
                <td><?= str_ireplace("\r\n", "<br>", trim(str_ireplace("|", "\r\n", $row['QrParams']))) ?></td>
                <td class="text-right"><?= number_format($row['SummPay'] / 100.0,2,'.','&nbsp;') ?></td>
                <td class="text-right"><?= number_format($row['ComissSumm'] / 100.0,2,'.','&nbsp;') ?></td>
                <td class="text-right"><?= number_format(($row['SummPay']+$row['ComissSumm']) / 100.0,2,'.','&nbsp;') ?></td>
                <?php if ($IsAdmin) : ?>
                    <td class="text-right">
                        <?= number_format($row['BankComis'] / 100.0,2,'.','&nbsp;') ?>
                    </td>
                    <td class="text-right">
                        <?= number_format($row['VoznagSumm'] / 100.0,2,'.','&nbsp;') ?>
                    </td>
                <?php endif; ?>
                <td>
                    <?= date('d.m.Y H:i:s', $row['DateCreate']) ?> /<br>
                    <?= $row['DateOplat'] > 0 ? date('d.m.Y H:i:s', $row['DateOplat']) : "нет" ?>
                </td>
                <td><?= $row['ExtBillNumber'] ?></td>
                <td><span class="label label-primary" style="background-color: <?=$stClr[$row['Status']]?>">
                        <?= (!$row['sms_accept'] && $row['Status'] == 0) ? 'Создан' : $st[$row['Status']]?></span></td>
                <td>
                    <div><?= $row['ErrorInfo'] ?></div>
                    <?php if ($row['Status'] == 2 && !empty($row['RCCode'])) : ?>
                        <div>Код: <?= $row['RCCode'] ?></div>
                    <?php endif; ?>
                </td>
                <td><?= $row['CardType'] ?></td>
                <td><?= $row['CountryUser'] . " " . $row['CityUser'] ?></td>
                <td>
                    <?php if ($row['Status'] == 1 && TU::IsInPay($row['IsCustom'])): ?>
                        <input class='btn btn-white btn-xs' data-action="cancelpay" data-id='<?= $row['ID'] ?>' type='button' value='Отменить'>
                    <?php endif; ?>
                        <input class="btn btn-white btn-xs excerpt" data-id="<?=$row['ID']?>" type="button" value="Выписка">
                </td>
            </tr>
<?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <th colspan='4'>Итого:</th>
        <th class="text-right"><?= number_format(round($sumpay / 100.0, 2),2,'.','&nbsp;')?></th>
        <th class="text-right"><?= number_format(round($sumcomis / 100.0, 2),2,'.','&nbsp;')?></th>
        <th class="text-right"><?= number_format(round(($sumpay+$sumcomis) / 100.0, 2),2,'.','&nbsp;')?></th>
        <?php if ($IsAdmin) : ?>
            <th class="text-right"><?=number_format(round($bankcomis/100.0, 2),2,'.','&nbsp;')?></th>
            <th class="text-right"><?=number_format(round($voznagps/100.0, 2),2,'.','&nbsp;')?></th>
        <?php endif; ?>
        <th colspan='6'>
            <?php
            $exportLink = 'datefrom='. $reqdata['datefrom'];
            $exportLink .= '&dateto=' . $reqdata['dateto'];
            $exportLink .= '&id=' . $reqdata['id'];
            $exportLink .= '&summpay=' . $reqdata['summpay'];
            $exportLink .= '&Extid=' . $reqdata['Extid'];
            if (isset($reqdata['IdPart'])) {
                $exportLink .= '&IdPart=' . $reqdata['IdPart'];
            }
            if (isset($reqdata['status']) && count($reqdata['status']) > 0) {
                foreach ($reqdata['status'] as $status) {
                    $exportLink .= '&status[]=' . $status;
                }
            }
            if (isset($reqdata['TypeUslug']) && count($reqdata['TypeUslug']) > 0) {
                foreach ($reqdata['TypeUslug'] as $usluga){
                    $exportLink .= '&TypeUslug[]='.$usluga;
                }
            }
            if (isset($reqdata['params']) && count($reqdata['params']) > 0) {
                foreach ($reqdata['params'] as $param){
                    $exportLink .= '&params[]='.$param;
                }
            }
            ?>
            <a class="btn btn-white btn-xs pull-right" target="_blank"
               href="/partner/stat/list-export-csv?<?=$exportLink?>">
                <i class="fa fa-share"></i>&nbsp;Экспорт csv
            </a></th>
        <th>
            <a class="btn btn-white btn-xs pull-right" target="_blank"
               href="/partner/stat/listexport?<?=$exportLink?>">
                <i class="fa fa-share"></i>&nbsp;Экспорт xlsx
            </a></th>
    </tr>
    <?php if ($cnt > $cntpage) : ?>
        <?php $maxpage = ceil($cnt / $cntpage); ?>
        <tr>
            <td colspan="15" class="footable-visible">
                <ul class="pagination pull-right">
                    <li class="footable-page-arrow <?= 0 == $page ? 'disabled' : '' ?>">
                        <a data-page="first" <?= $page > 0 ? 'onclick="lk.statlistreq(0);"' : '' ?>>«</a>
                    </li>
                    <li class="footable-page-arrow <?= 0 == $page ? 'disabled' : '' ?>">
                        <a data-page="prev" <?= $page > 0 ? 'onclick="lk.statlistreq(' . ($page - 1 > 0 ? $page - 1 : 0) . ');"' : '' ?>>‹</a>
                    </li>
                    <?php for ($i = 0; $i < $maxpage; $i++) : ?>
                        <li class="footable-page <?= $i == $page ? 'active' : '' ?>">
                            <a data-page="<?= $i ?>" <?= $page != $i ? 'onclick="lk.statlistreq(' . $i . ');"' : '' ?>><?= ($i + 1) ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="footable-page-arrow <?= $maxpage - 1 == $page ? 'disabled' : '' ?>">
                        <a data-page="next" <?= $maxpage - 1 != $page ? 'onclick="lk.statlistreq(' . ($page + 1) . ');"' : '' ?>>›</a>
                    </li>
                    <li class="footable-page-arrow <?= $maxpage - 1 == $page ? 'disabled' : '' ?>">
                        <a data-page="last" <?= $maxpage - 1 != $page ? 'onclick="lk.statlistreq(' . ($maxpage - 1) . ');"' : '' ?>>»</a>
                    </li>
                </ul>
            </td>
        </tr>
    <?php endif; ?>
    </tfoot>
    <?php else : ?>
        <tr>
            <td colspan='15' style='text-align:center;'>Операции не найдены</td>
        </tr></tbody>
    <?php endif; ?>
</table>
