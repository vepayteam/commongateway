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
<?php else : ?>
    <tr><td colspan='12' style='text-align:center;'>Операции не найдены</td></tr>
<?php endif; ?>
</table>
