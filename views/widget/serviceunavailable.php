<?php

use app\models\payonline\Partner;
use yii\web\View;

/* @var Partner $partner */
/* @var View $this */
?>

<section class="container">

    <div class="row">
        <div class="col-xs-12 text-center"><img src="/imgs/logo_vepay.svg" alt="vepay" class="logoend"></div>
    </div>

    <div class="row">
        <div class="col-xs-12 text-center"><h2 class="zagend">Сервис недоступен</h2></div>
    </div>

    <div class="row margin-top16">
        <div class="col-xs-12 text-center">
            <p class="infoend">Сервис оплаты по платежной ссылке для <b><?=$partner->Name?></b> недоступен.</p>
            <p class="infoend">Обратитесь к <b><?=$partner->Name?></b> для оплаты заказа.</p>
        </div>
    </div>

</section>
