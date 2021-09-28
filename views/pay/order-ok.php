<?php
/**
 * @var \app\services\payment\models\PaySchet $paySchet
 */
?>
<div class="r-body r-main-grid">
    <div class="r-body-main-success"><img src="/aassets/images/order-ok/success.svg" alt="Success">
        <h1>Ваш платеж успешно исполнен</h1></div>
    <div class="r-body-main-notify"><h2>ВНИМАНИЕ!</h2>
        <p>
            По всем вопросам, связанным с выполнением оплаченного вами заказа, пожалуйста, обращайтесь к <?= $paySchet->partner->Name ?>
            <?= !empty($paySchet->partner->Phone) ? 'по телефону ' . $paySchet->partner->Phone : '' ?>
        </p></div>
    <div class="r-body-main-action">
        <?php if(!empty($paySchet->SuccessUrl)): ?>
            <a href="<?= $paySchet->SuccessUrl ?>" class="r-body-main-action-return">Вернуться на сайт</a>
        <?php endif; ?>
    </div>
    <div class="r-body-info">
        <div class="r-body-info-row"><span>Компания:</span> <b><?= $paySchet->partner->Name ?></b></div>
        <?php if(!empty($paySchet->partner->Phone)): ?>
            <div class="r-body-info-row"><span>Телефон:</span> <b><?= $paySchet->partner->Phone ?></b></div>
        <?php endif; ?>
        <div class="r-body-info-row"><span>Номер заказа:</span> <b><?= $paySchet->ID ?></b></div>
        <div class="r-body-info-row"><span>Комиссия</span> <b><?= round($paySchet->ComissSumm / 100, 2) ?></b></div>
        <?php if(!empty($paySchet->Dogovor)): ?>
            <div class="r-body-info-row"><span>Номер договора:</span> <b><?= $paySchet->Dogovor ?></b></div>
        <?php endif; ?>
        <?php if(!empty($paySchet->FIO)): ?>
            <div class="r-body-info-row"><span>ФИО</span> <b><?= $paySchet->FIO ?></b></div>
        <?php endif; ?>

        <div class="r-body-info-split"></div>
        <div class="r-body-info-sum"><span>Сумма</span> <b><?= round($paySchet->getSummFull() / 100, 2) ?> <?= $paySchet->currency->getSymbol() ?></b></div>
    </div>
</div>

<?php if(!empty($paySchet->SuccessUrl)): ?>
    <script>
        setTimeout(function() {
            window.location = "<?= $paySchet->SuccessUrl ?>";
        }, 3000);
    </script>
<?php endif; ?>
