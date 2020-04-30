<?php

/* @var null|string $message */
/* @var null|string $returl */
/* @var \yii\web\View $this */
?>
<section class="container">

    <div class="row">
        <div class="col-xs-12 text-center"><img src="/imgs/logo_vepay.svg" alt="vepay" class="logoend"></div>
    </div>

    <div class="row margin-top24">
        <div class="col-xs-12 text-center"><h2 class="zagend">Оплата не проведена</h2></div>
    </div>

    <div class="row margin-top16">
        <div class="col-xs-12 text-center">
            <p class="infoend">Отказ банка – эмитента карты.</p>
            <p class="infoend"><?=isset($message) && !empty($message) ? $message : 'Ошибка в процессе оплаты, указаны неверные данные карты.'?></p>
        </div>
    </div>

    <?php if (/*isset($returl)*/true) : ?>
        <div class="row margin-top24">
            <div class="col-xs-10 col-xs-offset-2"><a class="btn btn-success btnret" href="/">Вернуться в магазин</a></div>
        </div>
    <?php endif; ?>

</section>