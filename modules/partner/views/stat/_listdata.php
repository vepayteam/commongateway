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
/* @var Pagination $pagination */

use app\models\payonline\Uslugatovar;
use app\models\payonline\User;
use app\models\TU;
use app\services\payment\models\PaySchet;
use yii\data\Pagination;
use yii\widgets\LinkPager;

?>

<table class="table table-striped tabledata" style="font-size: 0.9em">
    <thead>
    <tr>
        <th>ID Vepay</th>
        <th>Ext ID</th>
        <th>Услуга</th>
        <th>Реквизиты</th>
        <th class="text-right">Сумма</th>
        <th class="text-right">Комиссия с клиента</th>
        <th class="text-right">К оплате</th>
        <th class="text-right">Валюта </th>
        <?php if ($IsAdmin) : ?>
            <th class="text-right">
                Комиссия банка
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
        <th>ID мерчанта</th>
        <th>Маска карты</th>
        <th>Держатель карты</th>
        <th>RRN</th>
        <th>Хэш от номера карты</th>
        <th>Провайдер</th>
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
                <td class="text-right"><?= $row['Currency'] ?></td>
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
                <td>

                    <span class="label label-primary" style="background-color: <?=PaySchet::STATUS_COLORS[$row['Status']]?>">
                        <?= (!$row['sms_accept'] && $row['Status'] == 0) ? 'Создан' : PaySchet::STATUSES[$row['Status']] ?>
                    </span>
                </td>
                <td>
                    <div><?= $row['ErrorInfo'] ?></div>
                    <?php if ($row['Status'] == 2 && !empty($row['RCCode'])) : ?>
                        <div>Код: <?= $row['RCCode'] ?></div>
                    <?php endif; ?>
                </td>
                <td><?= $row['CardType'] ?></td>
                <td><?= $row['CountryUser'] . " " . $row['CityUser'] ?></td>
                <td><?= $row['IdOrg'] ?></td>
                <td><?= $row['CardNum'] ?></td>
                <td><?= $row['CardHolder'] ?></td>
                <td><?= $row['RRN'] ?></td>
                <td><?= $row['IdKard'] ?></td>
                <td><?= $row['BankName'] ?></td>
                <td>
                    <input class='btn btn-white btn-xs' data-action="logpay" data-id='<?= $row['ID'] ?>' type='button' value='Лог'>
                    <?php if ($row['Status'] == 1 && (TU::IsInPay($row['IsCustom']) || TU::IsInAutoAll($row['IsCustom']))): ?>
                        <input class='btn btn-white btn-xs' data-action="cancelpay" data-id='<?= $row['ID'] ?>' type='button' value='Отменить'>
                    <?php endif; ?>
                        <input class="btn btn-white btn-xs excerpt" data-id="<?=$row['ID']?>" type="button" value="Выписка">
                    <?php if ($IsAdmin && $row['Status'] != 0) : ?>
                        <a class="btn btn-white btn-xs" data-action="update-status-pay"
                           href="#" data-id="<?=$row['ID']?>">
                            Обновить статус
                        </a>
                    <?php endif; ?>
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
            $exportLink .= '&summpayFrom=' . $reqdata['summpayFrom'];
            $exportLink .= '&summpayTo=' . $reqdata['summpayTo'];
            $exportLink .= '&Extid=' . $reqdata['Extid'];
            foreach ($reqdata['idParts'] ?? [] as $partnerId) {
                $exportLink .= '&idParts[]=' . $partnerId;
            }
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
                foreach ($reqdata['params'] as $k => $param) {
                    $exportLink .= '&params['.$k.']='.$param;
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
    </tfoot>
    <?php else : ?>
        <tr>
            <td colspan='15' style='text-align:center;'>Операции не найдены</td>
        </tr></tbody>
    <?php endif; ?>
</table>

<?php
echo LinkPager::widget([
    'pagination' => $pagination,
    'hideOnSinglePage' => true,
    'prevPageLabel' => '‹',
    'nextPageLabel' => '›',
    'firstPageLabel' => '«',
    'lastPageLabel' => '»',
]);
?>
