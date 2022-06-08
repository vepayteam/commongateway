<?php

/* @var $this \yii\web\View */

?>

<section class="container">

    <div class="row">
        <div class="col-xs-12 text-center"><img src="/imgs/logo_vepay.svg" alt="vepay" class="logoend"></div>
    </div>

    <div class="row">
        <div class="col-xs-12 text-center"><img src="/imgs/complete.svg" alt="stateerr" class="stateend"></div>
    </div>

    <div class="row">
        <div class="col-xs-12 text-center">
            <h2 class="zagend">
                <?= Yii::t('app.payment-wait', 'Платеж находится в обработке') ?>
            </h2>
        </div>
    </div>


</section>

<?php
$this->registerJs(
    'let timerId = setTimeout(function() {
        location.reload();
    }, 15000);'
);
?>