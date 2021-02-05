<?php
/**
 * @var \app\services\payment\models\PaySchetLog[] $data
 */

use app\services\payment\models\PaySchet;

?>

<table class="table">
    <?php foreach($data as $row): ?>
    <tr>
        <th><?=date('d.m.Y H:i:s', $row['DateCreate'])?></th>
        <th><?=PaySchet::STATUSES[$row->Status]?></th>
        <th><?=$row->ErrorInfo?></th>
    </tr>
    <?php endforeach; ?>
</table>
