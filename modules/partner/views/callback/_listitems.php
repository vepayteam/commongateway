<?php

/* @var yii\web\View $this */
/* @var array $data */
/* @var $IsAdmin bool */

?>

<table class="table table-striped tabledata" style="font-size: 0.9em">
    <thead>
    <tr>
        <th>Операция</th>
        <th>Дата создания</th>
        <th>Адрес запроса</th>
        <th>Дата выполнения</th>
        <th>Результат</th>
        <th>Действия</th>
    </tr>
    </thead>
<?php if (count($data) > 0) : ?>
    <tbody>
        <?php foreach ($data as $row) : ?>
            <tr>
                <td><?=$row['IdPay']?></td>
                <td><?=date("d.m.Y H:i:s", $row['DateCreate'])?></td>
                <td><?=!empty($row['FullReq']) ? $row['FullReq'] : $row['Email']?></td>
                <td><?=$row['DateSend'] > 1 ? date("d.m.Y H:i:s", $row['DateSend']) : 'в очереди'?></td>
                <td>
                    <div>HTTP code: <?=$row['HttpCode']?></div>
                    <div><code><?=$row['HttpAns']?></code></div>
                </td>
                <td><input class='btn btn-white btn-xs' data-action="repeatnotif" data-id='<?= $row['ID'] ?>' type='button' value='Повторить запрос'></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
<tfoot>
<?php if ($payLoad['totalCount'] > $payLoad['pageLimit']) : ?>
    <?php $maxPage = ceil($payLoad['totalCount'] / $payLoad['pageLimit']); ?>
    <tr>
        <td colspan="15" class="footable-visible">
            <ul class="pagination">
                <li class="footable-page-arrow <?= 1 == $payLoad['page'] ? 'disabled' : '' ?>">
                    <a data-page="first" <?= $payLoad['page'] > 1 ? 'onclick="lk.notiflist(0);"' : '' ?>>«</a>
                </li>
                <li class="footable-page-arrow <?= 1 == $payLoad['page'] ? 'disabled' : '' ?>">
                    <a data-page="prev" <?= $payLoad['page'] > 1 ? 'onclick="lk.notiflist(' . ($payLoad['page'] - 1 > 0 ? $payLoad['page'] - 1 : 0) . ');"' : '' ?>>‹</a>
                </li>
                <?php for ($i = 1; $i < $maxPage; $i++) : ?>
                    <li class="footable-page <?= $i == $payLoad['page'] ? 'active' : '' ?>">
                        <a data-page="<?= $i ?>" <?= $payLoad['page'] != $i ? 'onclick="lk.notiflist(' . $i . ');"' : '' ?>><?= ($i ) ?></a>
                    </li>
                <?php endfor; ?>
                <li class="footable-page-arrow <?= $maxPage - 1 == $payLoad['page'] ? 'disabled' : '' ?>">
                    <a data-page="next" <?= $maxPage - 1 != $payLoad['page'] ? 'onclick="lk.notiflist(' . ($payLoad['page'] ) . ');"' : '' ?>>›</a>
                </li>
                <li class="footable-page-arrow <?= $maxPage - 1 == $payLoad['page'] ? 'disabled' : '' ?>">
                    <a data-page="last" <?= $maxPage - 1 != $payLoad['page'] ? 'onclick="lk.notiflist(' . ($maxPage - 1) . ');"' : '' ?>>»</a>
                </li>
            </ul>
        </td>
    </tr>
<?php endif; ?>
</tfoot>
<?php else : ?>
    <tr><td colspan='12' style='text-align:center;'>Операции не найдены</td></tr>
<?php endif; ?>
</table>