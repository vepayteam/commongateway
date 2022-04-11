<?php

use app\services\payment\models\PaySchet;
use Carbon\Carbon;
use yii\helpers\Html;

/* @var PaySchet $paySchet */
const MAX_EXP_CARD_YEARS = 10;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= Html::csrfMetaTags() ?>
    <link href="/payasset/css/p2pform.css" rel="stylesheet">
    <link href="/payasset/css/tooltipster.main.min.css" rel="stylesheet">
    <link href="/payasset/css/tooltipster.bundle.min.css" rel="stylesheet">
    <title>Перевод с карты на карту</title>

    <script>
        var paySchetId = <?=$paySchet->ID?>;
        var pcComission = <?=$paySchet->uslugatovar->PcComission?>;
        var minsumComiss = <?=$paySchet->uslugatovar->MinsumComiss?>;
        var currMonth = <?=Carbon::now()->month?>;
        var currYear = <?=Carbon::now()->year?>;
        var minSum = <?=$paySchet->uslugatovar->MinSumm / 100?>;
        var maxSum = <?=$paySchet->uslugatovar->MaxSumm / 100?>;

    </script>

    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div class="container">
    <h2 class="center greenText">Перевод с карты на карту</h2>
    <div class="content">
        <?php if($paySchet->CancelUrl): ?>
        <button id="btnClose" data-url="<?=yii\helpers\Html::encode($paySchet->CancelUrl)?>" style="
            position: absolute;
            right: 20px;
            top: 20px;
            background: none;
            border: none;
            font-size: 1.2em;
            font-weight: bolder;
            cursor: pointer"
        >
            x
        </button>
        <?php endif; ?>
        <form name="p2pForm" action="">
            <div class="cardsBlock">
                <div class="cardWrapper">
                    <div class="card green greenCard">
                        <div class="cardBack">
                            <div class="blackStripe">
                            </div>
                            <span class="center d-block CVV-text"
                                  title="3 цифры на оборотной строне карточки">CVC2/CVV2</span>
                            <input class="CVV-input" id="cvv" placeholder="XXX" data-sequence="5" data-inputmask="'mask': '999'"
                                   type="text">
                        </div>
                        <span class="cardTopText">Номер карты отправителя:</span>
                        <div class="cardNubmerBlock">
                            <input required placeholder="XXXX" id="cardPan1" data-sequence="1" data-inputmask="'mask': '9999'" type="text">
                            <input required placeholder="XXXX" id="cardPan2" data-sequence="2" data-inputmask="'mask': '9999'" type="text">
                            <input required placeholder="XXXX" id="cardPan3" data-sequence="3" data-inputmask="'mask': '9999'" type="text">
                            <input required placeholder="XXXX" id="cardPan4" data-sequence="4" data-inputmask="'mask': '9999'" type="text">
                        </div>
                        <div class="expiryBlock">
                            <select name="expMonth" id="expMonth">
                                <?php $currMonth = Carbon::now()->month ?>
                                <?php $month = 1;
                                while ($month <= 12): ?>
                                    <option <?= $month == $currMonth ? 'selected' : '' ?>
                                            value="<?= $month ?>"
                                    >
                                        <?= sprintf('%02d', $month) ?>
                                    </option>
                                    <?php $month++ ?>
                                <?php endwhile; ?>
                            </select>
                            <select name="expYear" id="expYear">
                                <?php $currYear = $year = Carbon::now()->year;
                                while ($year < $currYear + MAX_EXP_CARD_YEARS): ?>
                                    <option value="<?= $year ?>>"><?= $year ?></option>
                                    <?php $year++ ?>
                                <?php endwhile; ?>
                            </select>
                            <div class="validThru">
                                <span>valid</span>
                                <br>
                                <span>thru</span>
                            </div>
                        </div>
                        <span class="cardHolder" style="bottom: 1px;">
                            <input placeholder="MR. CARDHOLDER" id="holder" data-inputmask-regex="[a-zA-Z ]{3,}"
                                   type="text">
                        </span>
                    </div>
                </div>
                <div class="greenArrowBlock">
                    <div class="square"></div>
                    <div class="triangle-right"></div>
                </div>
                <div class="cardWrapper">
                    <div class="card gray">
                        <span class="cardTopText">Номер карты получателя:</span>
                        <div class="cardNubmerBlock">
                            <input required placeholder="XXXX" id="outCardPan1" data-sequence="7" data-inputmask="'mask': '9999'" type="text">
                            <input required placeholder="XXXX" id="outCardPan2" data-sequence="8" data-inputmask="'mask': '9999'" type="text">
                            <input required placeholder="XXXX" id="outCardPan3" data-sequence="9" data-inputmask="'mask': '9999'" type="text">
                            <input required placeholder="XXXX" id="outCardPan4" data-sequence="10" data-inputmask="'mask': '9999'" type="text">
                        </div>
                        <div class="expiryBlock">
                            <span class="grayCardValidThru">00 / 00</span>
                            <div class="validThru">
                                <span>valid</span>
                                <br>
                                <span>thru</span>
                            </div>
                        </div>
                        <span class="cardHolder">MR. CARDHOLDER</span>
                    </div>
                </div>
            </div>
            <div class="formMainBody">
                <div class="conditionsBlock center">
                    <span>
                        * не менее <?= number_format($paySchet->uslugatovar->MinSumm / 100, 0, '', ' ') ?> руб.
                        и не более не более <?= number_format($paySchet->uslugatovar->MaxSumm / 100, 0, '', ' ') ?>
                        руб. за одну операцию
                    </span>
                    <br>
                    <span>** не более 600 000 руб. в месяц с одной карты</span>
                </div>
                <div class="inputsBlock">
                    <div class="inputsBlockItem">
                        <label for="paymentAmount">
                            Сумма перевода:
                        </label>
                        <input class="inputsBlockInput" id="paymentAmount" value="0.00" min="0" step="0.01" type="test"
                               data-inputmask-regex="^\d*(\.\d{0,2})?$"
                        >
                    </div>
                    <div class="inputsBlockItem">
                        <label class="comissionLabel" for="paymentComission">
                            Комиссия:
                        </label>
                        <input class="inputsBlockInput" id="paymentComission" disabled value="0.00" min="0" step="0.01"
                               type="number">
                    </div>
                </div>
                <span class="center d-block greenText"></span>
                <div class="emailAndButtonBlock">
                    <div class="center"></div>
                    <div class="center">
                        <div style="display: flex; margin-bottom: 10px;">
                            <input type="checkbox" id="agreeOffer" name="agreeOffer">
                            <label for="agreeOffer">
                                Я согласен(на) с правилами
                                <a target="_blank" href="/files/с_21_06_2021_Пуб_оферта_№_2_от_21_06_2021_Card2Card_2_2.pdf">оферты</a>
                            </label>
                        </div>
                        <button class="submitBtn" id="sendForm" type="submit" disabled>Отправить деньги</button>
                        <div id="formErrorOfferMessage" style="display: none; color: red">
                            Необходимо подтвердить оферту
                        </div>
                        <div id="formErrorMessage" style="display: none; color: red">
                            Подсвеченные поля обязательны для заполнения или заполнены некорректно
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
<script src="/payasset/js/jquery-1.12.4.min.js"></script>
<script src="/payasset/js/ua-parser.js"></script>
<script src="/payasset/js/jquery.inputmask.min.js"></script>
<script src="/payasset/js/tooltipster.main.min.js"></script>
<script src="/payasset/js/tooltipster.bundle.min.js"></script>
<script src="/payasset/js/p2pform.js"></script>
<?php $this->endBody() ?>
</html>
<?php $this->endPage() ?>
