<?php
/**
 * @var \app\services\payment\models\PaySchetLog[] $data
 */

use app\services\payment\models\PaySchet;
use yii\helpers\Html;

?>

<table class="table">
    <?php foreach($data as $row): ?>
    <tr>
        <th><?=date('d.m.Y H:i:s', $row['DateCreate'])?></th>
        <th><?=Html::encode(PaySchet::STATUSES[$row->Status])?></th>
        <th><?=Html::encode($row->ErrorInfo)?></th>
    </tr>
    <?php endforeach; ?>
</table>
