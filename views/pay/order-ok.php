<?php
/**
 * @var array $params
 */
?>
<div class="r-body r-main-grid">
    <div class="r-body-main-success"><img src="/aassets/images/order-ok/success.svg" alt="Success">
        <h1>Ваш платеж успешно исполнен</h1></div>
    <div class="r-body-main-notify"><h2>ВНИМАНИЕ!</h2>
        <p>
            По всем вопросам, связанным с выполнением оплаченного вами заказа, пожалуйста, обращайтесь к <?= $params['PartnerName']?>
            <?= !empty($params['PartnerPhone']) ? 'по телефону '.$params['PartnerPhone'] : '' ?>
        </p></div>
    <div class="r-body-main-action">
        <?php if(!empty($params['SuccessUrl'])): ?>
            <a href="<?= $params['SuccessUrl'] ?>" class="r-body-main-action-return">Вернуться на сайт</a>
        <?php endif; ?>
        <div class="r-body-main-action-list">
            <a class="r-body-main-action-list-print" href="/pay/order-print/<?= $params['ID'] ?>" target="_blank">
                Печать квитанции
            </a>
            <a class="r-body-main-action-list-download" href="/pay/order-invoice/<?= $params['ID'] ?>" target="_blank">
                Скачать квитанцию
            </a>
        </div>
    </div>
    <div class="r-body-info">
        <div class="r-body-info-row"><span>Компания:</span> <b><?= $params['PartnerName'] ?></b></div>
        <?php if(!empty($params['PartnerPhone'])): ?>
            <div class="r-body-info-row"><span>Телефон:</span> <b><?= $params['PartnerPhone'] ?></b></div>
        <?php endif; ?>
        <div class="r-body-info-row"><span>Номер заказа:</span> <b><?= $params['ID'] ?></b></div>
        <div class="r-body-info-row"><span>Комиссия</span> <b><?= round($params['ComissSumm'] / 100, 2) ?></b></div>
        <?php if(!empty($params['Dogovor'])): ?>
            <div class="r-body-info-row"><span>Номер договора:</span> <b><?= $params['Dogovor'] ?></b></div>
        <?php endif; ?>
        <?php if(!empty($params['FIO'])): ?>
            <div class="r-body-info-row"><span>ФИО</span> <b><?= $params['FIO'] ?></b></div>
        <?php endif; ?>

        <div class="r-body-info-split"></div>
        <div class="r-body-info-sum"><span>Сумма</span> <b><?= round($params['SummFull'] / 100, 2) ?> ₽</b></div>
    </div>
</div>
