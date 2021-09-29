<?php

/* @var null|string $message */
/* @var null|string $returl */
/* @var \yii\web\View $this */
?>
<section class="container">

    <div class="row">
        <div class="col-xs-12 text-center"><img src="/imgs/logo_vepay.svg" alt="vepay" class="logoend"></div>
    </div>

    <div class="row">
        <div class="col-xs-12 text-center"><img src="/imgs/notcomplete.svg" alt="stateerr" class="stateend"></div>
    </div>

    <div class="row">
        <div class="col-xs-12 text-center"><h2 class="zagend">Оплата не проведена</h2></div>
    </div>

    <div class="row">
        <div class="col-xs-12 text-center">
            <p class="infoend"><?=isset($message) && !empty($message) ? $message : 'Ошибка в процессе оплаты, указаны неверные данные карты.'?></p>
        </div>
    </div>

    <?php if (isset($returl)) : ?>
        <div class="row margin-top24">
            <div class="col-xs-offset-1 col-xs-10 text-center"><a class="btn btn-success btnret" href="/">Вернуться на сайт</a></div>
        </div>
    <?php endif; ?>

</section>