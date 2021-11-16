<?php
use app\services\payment\models\PaySchet;
use Carbon\Carbon;use yii\helpers\Html;

/* @var PaySchet $paySchet */
\app\assets\P2pAsset::register($this);

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
    <title>Перевод с карты на карту</title>

    <script>
        var paySchetId = <?=$paySchet->ID?>;
        var pcComission = <?=$paySchet->uslugatovar->PcComission?>;
        var minsumComiss = <?=$paySchet->uslugatovar->MinsumComiss?>;
        var currMonth = <?=Carbon::now()->month?>;
        var currYear = <?=Carbon::now()->year?>;


    </script>

    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div class="container">
    <h2 class="center greenText">Перевод с карты на карту</h2>
    <div class="content">
        <form name="p2pForm" action="">
            <div class="cardsBlock">
                <div class="cardWrapper">
                    <div class="card green greenCard">
                        <div class="cardBack">
                            <div class="blackStripe">
                            </div>
                            <span class="center d-block CVV-text" title="3 цифры на оборотной строне карточки">CVC2/CVV2</span>
                            <input class="CVV-input" id="cvv" placeholder="XXX" data-inputmask="'mask': '999'" type="text">
                        </div>
                        <span class="cardTopText" >Номер карты отправителя:</span>
                        <div class="cardNubmerBlock">
                            <input placeholder="XXXX" id="cardPan1" data-inputmask="'mask': '9999'" type="text">
                            <input placeholder="XXXX" id="cardPan2" data-inputmask="'mask': '9999'" type="text">
                            <input placeholder="XXXX" id="cardPan3" data-inputmask="'mask': '9999'" type="text">
                            <input placeholder="XXXX" id="cardPan4" data-inputmask="'mask': '9999'" type="text">
                        </div>
                        <div class="expiryBlock">
                            <select name="expMonth" id="expMonth">
                                <?php $currMonth = Carbon::now()->month ?>
                                <?php $month = 1; while($month < 12): ?>
                                    <option <?=$month == $currMonth ? 'selected': ''?>
                                            value="<?=$month?>"
                                    >
                                        <?=sprintf('%02d', $month)?>
                                    </option>
                                    <?php $month++ ?>
                                <?php endwhile;?>
                            </select>
                            <select name="expYear" id="expYear">
                                <?php $currYear = $year = Carbon::now()->year; while($year < $currYear + MAX_EXP_CARD_YEARS): ?>
                                    <option value="<?=$year?>>"><?=$year?></option>
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
                            <input placeholder="MR. CARDHOLDER" id="holder" data-inputmask-regex="[a-zA-Z ]{3,}" type="text">
                        </span>
                    </div>
                </div>
                <div class="greenArrowBlock">
                    <div class="square" ></div>
                    <div class="triangle-right"></div>
                </div>
                <div class="cardWrapper">
                    <div class="card gray">
                        <span class="cardTopText" >Номер карты получателя:</span>
                        <div class="cardNubmerBlock">
                            <input placeholder="XXXX" id="outCardPan1" data-inputmask="'mask': '9999'" type="text">
                            <input placeholder="XXXX" id="outCardPan2" data-inputmask="'mask': '9999'" type="text">
                            <input placeholder="XXXX" id="outCardPan3" data-inputmask="'mask': '9999'" type="text">
                            <input placeholder="XXXX" id="outCardPan4" data-inputmask="'mask': '9999'" type="text">
                        </div>
                        <div class="expiryBlock">
                            <span class="grayCardValidThru" >00 / 00</span>
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
                    <span>* не более 75 000 руб. за одну операцию</span>
                    <br>
                    <span>** не более 600 000 руб. в месяц с одной карты</span>
                </div>
                <div class="inputsBlock">
                    <div class="inputsBlockItem">
                        <label for="paymentAmount">
                            Сумма перевода:
                        </label>
                        <input class="inputsBlockInput" id="paymentAmount" value="0.00" min="0" step="0.01" type="number">
                    </div>
                    <div class="inputsBlockItem">
                        <label class="comissionLabel" for="paymentComission">
                            Комиссия:
                        </label>
                        <input class="inputsBlockInput" id="paymentComission" disabled value="0.00" min="0" step="0.01"  type="number">
                    </div>
                </div>
                <span class="center d-block greenText">Укажите свою почту, и мы отправим Вам справку о совершенной операции</span>
                <div class="emailAndButtonBlock">
                    <div class="center">
                        <input id="emailInput" placeholder="укажите email" type="text"
                               data-inputmask-regex="[a-zA-Z0-9._%-]+@[a-zA-Z0-9-]+\.[a-zA-Z]{2,4}"
                        >
                    </div>
                    <div class="center">
                        <button class="submitBtn" id="sendForm" type="submit" >Отправить деньги</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
<?php $this->endBody() ?>
</html>
<?php $this->endPage() ?>
