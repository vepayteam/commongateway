<?php
/* @var array $dataIn */
/* @var array $dataOut */
/* @var $this \yii\web\View */
/* @var bool $IsAdmin */

use yii\helpers\Html;

?>

<?php
function renderRow(array $row, $type, &$itog)
{
    static $i = 1;
    $itog['summ'] += $row['SummPay'];
    $itog['comiss'] += $row['ComissSumm'];
    $itog['cnt'] += $row['CntPays'];
    $itog['bankvozn'] += $row['VoznagSumm'];
    $itog['summvyveden'] += $row['SummVyveden'];
    $itog['summperechislen'] += $row['SummVyveden'];
    ?>
    <tr>
        <td><?= ($i++) ?></td>
        <td><?= Html::encode($row['NamePartner']) ?></td>
        <td class="text-right"><?= number_format($row['SummPay'] / 100.0, 2, '.', '&nbsp;') ?></td>
        <td class="text-right"><?= number_format($row['ComissSumm'] / 100.0, 2, '.', '&nbsp;') ?></td>
        <td class="text-right"><?= number_format(($row['SummPay'] + $row['ComissSumm']) / 100.0, 2, '.', '&nbsp;') ?></td>
        <td class="text-right"><?= number_format($row['VoznagSumm'] / 100.0, 2, '.', '&nbsp;') ?></td>
        <td class="text-right">
            <?= number_format($row['SummVyveden'] / 100.0, 2, '.', '&nbsp;') ?>
            <?php if (isset($row['DataVyveden']) && $row['DataVyveden'] > 0) : ?>
                <div class="text-muted">по <?=date("m.Y", $row['DataVyveden'])?></div>
            <?php endif; ?>
        </td>
        <td class="text-right">
            <?= number_format($row['SummPerechisl'] / 100.0, 2, '.', '&nbsp;') ?>
            <?php if (isset($row['DataPerechisl']) && $row['DataPerechisl'] > 0) : ?>
                <div class="text-muted">по <?=date("d.m.Y", $row['DataPerechisl'])?></div>
            <?php endif; ?>
        </td>
        <td class="text-right"><?= Html::encode($row['CntPays']) ?></td>
        <td class="text-right">
            <?php if (!($type == 1 && $row['VoznagVyplatDirect'] == 0)): ?>
            <a href="#"
               class="btn btn-default btn-sm"
               data-action="vyvyodsum"
               data-type="<?=Html::encode($type)?>"
               data-id="<?=Html::encode($row['IDPartner'])?>"
                <?php if ($row['VoznagSumm'] - $row['SummVyveden'] <= 0) : ?>
                    disabled="disabled"
                <?php endif; ?>
               data-summ="<?=($row['VoznagSumm'] - $row['SummVyveden'])?>">Вывести</a>
            <?php endif; ?>
        </td>
    </tr>
<?php
}
?>

<table class="table table-striped tabledata">
    <thead>
    <tr>
        <th>#</th>
        <th>Название Точки</th>
        <th class="text-right">К зачислению</th>
        <th class="text-right">Комиссия</th>
        <th class="text-right">Оплачено</th>
        <th class="text-right">Возн.<br>Vepay</th>
        <th class="text-right">Выведено<br>вознаграждение</th>
        <th class="text-right">Перечислены<br>платежи</th>
        <th class="text-right">Число<br>операций</th>
        <th class="text-right">Вывести<br>вознаграждение</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (count($dataIn) > 0 || count($dataOut) > 0) {
    $i = 1;
    $itog = ['summ' => 0, 'comiss' => 0, 'cnt' => 0, 'bankvozn' => 0, 'summvyveden' => 0, 'summperechislen' => 0];

    echo "<tr><td colspan='14'>Погашения</td></tr></tbody>";
    foreach ($dataIn as $row) {
        renderRow($row, 0,$itog);
    }
    echo "<tr><td colspan='14'>Выдачи</td></tr></tbody>";
    foreach ($dataOut as $row) {
        renderRow($row, 1,$itog);
    }
    ?>
    </tbody>
    <tfoot>
    <tr>
        <th colspan='2'>Итого:</th>
        <th class="text-right"><?= number_format(round($itog['summ'] / 100.0, 2), 2, '.', '&nbsp;') ?></th>
        <th class="text-right"><?= number_format(round($itog['comiss'] / 100.0, 2), 2, '.', '&nbsp;') ?></th>
        <th class="text-right"><?= number_format(round(($itog['summ'] + $itog['comiss']) / 100.0, 2), 2, '.', '&nbsp;') ?></th>
        <th class="text-right"><?= number_format(round($itog['bankvozn'] / 100.0, 2), 2, '.', '&nbsp;') ?></th>
        <th class="text-right"><?= number_format(round($itog['summvyveden'] / 100.0, 2), 2, '.', '&nbsp;') ?></th>
        <th class="text-right"><?= number_format(round($itog['summperechislen'] / 100.0, 2), 2, '.', '&nbsp;') ?></th>
        <th class="text-right"><?= Html::encode($itog['cnt']) ?></th>
    </tr>
    </tfoot>
    <?php
    } else {
        echo "<tr><td colspan='14' style='text-align:center;'>Операции не найдены</td></tr></tbody>";
    }
    ?>
</table>
