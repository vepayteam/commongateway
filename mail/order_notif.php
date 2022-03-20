<?php

use app\models\payonline\OrderNotif;
use app\models\payonline\OrderPay;
use yii\helpers\Html;

/* @var OrderNotif $orderNotif */
/* @var OrderPay $orderPay */
/* @var array|null $orderTo */

?>

<div>Счет № <?= Html::encode($orderNotif->IdOrder) ?> на сумму <?= $orderPay->SumOrder / 100 ?> руб.</div>
<div><?= Html::encode($orderPay->Comment) ?></div>

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
                <td><?= Html::encode($value['name']) ?></td>
                <td><?= Html::encode($value['qnt']) ?></td>
                <td><?= Html::encode($value['sum']) ?></td>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>
<?php endif; ?>

<a href="https://api.vepay.online/widget/order/<?= Html::encode($orderNotif->IdOrder) ?>">Оплатить</a>
