<?php

/* @var array $data */
/* @var $this \yii\web\View */
/* @var bool $IsAdmin */

?>

<table class="table table-striped tabledata" style="font-size: 0.9em">
    <thead>
    <tr>
        <th>#</th>
        <th>Параметр</th>
        <th>Значение</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>1</td>
        <td>Общее количество зарегистрированных карт</td>
        <td><?=number_format($data['cntcards'], 0, '.', ' ')?></td>
    </tr>
    <tr>
        <td>2</td>
        <td>Число активных карт</td>
        <td><?=number_format($data['activecards'], 0, '.', ' ')?></td>
    <tr>
        <td>3</td>
        <td>Число запросов к одной карте/всего (за период)</td>
        <td><?=$data['activecards'] > 0 ? number_format(round($data['reqcards'] / $data['activecards']), 0, '.', ' ') : 0?> / <?=number_format($data['reqcards'], 0, '.', ' ')?></td>
    <tr>
        <td>4</td>
        <td>Число успешных запросов</td>
        <td><?=number_format($data['payscards'], 0, '.', ' ')?></td>
    </tr>
    </tbody>
</table>