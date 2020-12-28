<?php
/**
 * @var \app\services\payment\models\PaySchetLog[] $data
 */
$statuses = [0 => "В обработке", 1 => "Оплачен", 2 => "Отмена", 3 => "Возврат"];
?>

<table class="table">
    <?php foreach($data as $row): ?>
    <tr>
        <th><?=date('d.m.Y H:i:s', $row['DateCreate'])?></th>
        <th><?=$statuses[$row->Status]?></th>
        <th><?=$row->ErrorInfo?></th>
    </tr>
    <?php endforeach; ?>
</table>
