<?php
/**@var \app\models\antifraud\tables\AFFingerPrit $record */
?>
<div class="row-info">
    <p class="left">Хэш пользователя</p>
    <p class="right"><?= $record->user_hash ?></p>
</div>
<div class="row-info">
    <p class="left">Правила (вес)</p>
    <p class="right">Выполнено / не выполненно</p>
</div>
<?php foreach ($record->stats as $stat): ?>
    <div class="row-info ">
        <p class="left" style="width: 65%"><?= $stat->rule_info->rule_title ?> (<?= $stat->current_weight ?>)</p>
        <p class="right"><?= $stat->success ? "Выполнено" : "Не выполнено" ?></p>
    </div>
<? endforeach; ?>
<div class="row-info">
    <p class="left">Итоговый вес операции</p>
    <p class="right"><?= $record->weight ?></p>
</div>
<div class="btn-danger-AF">
    <button class="btn btn btn-md btn-danger">Заблокировать пользователя</button>
</div>