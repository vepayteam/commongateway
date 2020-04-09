<?php
/* @var int $IdSchet */
/* @var array $params */
/* @var int $sum */

?>

<p>Здравствуйте!</p>
<p>Оплачен заказ № <?=$IdSchet?></p>
<p>Сумма <?=sprintf("%02.2f", $sum / 100.0)?></p>
<p>Реквизиты:</p>
<ul>
<?php foreach ($params as $p) : ?>
    <li><?=$p?></li>
<?php endforeach; ?>
</ul>
