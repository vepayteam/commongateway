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
        <div class="col-xs-12 text-center"><img src="/imgs/complete.svg" alt="stateerr" class="stateend"></div>
    </div>

    <div class="row">
        <div class="col-md-offset-3 col-md-6 col-sm-offset-2 col-sm-8 col-xs-offset-1 col-xs-10 text-center"><h2 class="zagend">Платёж прошёл успешно</h2></div>
    </div>

    <div class="row margin-top16">
        <div class="col-md-offset-3 col-md-6 col-sm-offset-3 col-sm-6 col-xs-offset-1 col-xs-10 text-center">
            <p class="infoend">Средства поступили на счёт продавца, теперь вы можете вернуться в магазин</p>
            <p class="infoend"><?=$message?></p>
        </div>
    </div>

    <?php if (isset($returl)) : ?>
        <div class="row margin-top24">
            <div class="col-md-offset-4 col-md-4 col-sm-offset-3 col-sm-6 col-xs-offset-1 col-xs-10 text-center"><a class="btn btn-success btnret" href="/">Вернуться на сайт</a></div>
        </div>
    <?php endif; ?>

</section>

<section class="container">

    <p><div class="text-center" style='color: green'></div></p>

</section>