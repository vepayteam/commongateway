<?php

use app\models\crypt\KeyUsers;
use yii\web\View;

/* @var yii\web\View $this */
/* @var array $data */
/* @var $IsAdmin bool */

?>

<table class="table table-striped tabledata" style="font-size: 0.9em">
    <thead>
    <tr>
        <th>Дата</th>
        <th>Пользователь</th>
        <th>Вид</th>
        <th>IP</th>
        <th>Инфо</th>
    </tr>
    </thead>
    <?php if (count($data) > 0) : ?>
        <tbody>
        <?php foreach ($data as $row) : ?>
            <tr>
                <td><?=date('d.m.Y H:i:s', $row['Date'])?></td>
                <td><?=$row['Login'].' ('.$row['IdUser'].')'?></td>
                <td><?=(isset(KeyUsers::$logType[$row['Type']]) ? KeyUsers::$logType[$row['Type']] : '') . ' (' . $row['Type'] . ')'?></td>
                <td><?=$row['IPLogin']?></td>
                <td><?=$row['DopInfo']?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    <?php else : ?>
        <tr><td colspan='12' style='text-align:center;'>Операции не найдены</td></tr>
    <?php endif; ?>
</table>
