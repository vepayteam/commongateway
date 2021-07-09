<?php

use app\models\payonline\OrderNotif;
use app\models\payonline\OrderPay;

/* @var OrderNotif $orderNotif */
/* @var OrderPay $orderPay */
/* @var array|null $orderTo */

?>

<div>Счет № <?= $orderNotif->IdOrder ?> на сумму <?= $orderPay->SumOrder / 100 ?> руб.</div>
<div><?= $orderPay->Comment ?></div>

<br>

<?php if ($orderTo): ?>
    <table>
        <thead>
        <tr>
            <td>№</td>
            <td>Наименование товара</td>
            <td>Кол-во</td>
            <td>сумма</td>
        </tr>
        </thead>

        <tbody>

        <?php foreach ($orderTo as $key => $value): ?>
            <tr>
                <td><?= $key + 1 ?></td>
                <td><?= $value['name'] ?></td>
                <td><?= $value['qnt'] ?></td>
                <td><?= $value['sum'] ?></td>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>
<?php endif; ?>

<a href="https://api.vepay.online/widget/order/<?= $orderNotif->IdOrder ?>">Оплатить</a>
