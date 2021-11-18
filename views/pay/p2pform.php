<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/payasset/css/p2pform.css">
    <title>VPBC-1004 Верстка страницы для p2p</title>
</head>
<body>
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
                            <input class="CVV-input" placeholder="XXX" pattern="[0-9]{3}" maxlength="3" type="text">
                        </div>
                        <span class="cardTopText" >Номер карты отправителя:</span>
                        <div class="cardNubmerBlock">
                            <input placeholder="XXXX" pattern="[0-9]{4}" maxlength="4" type="text">
                            <input placeholder="XXXX" pattern="[0-9]{4}" maxlength="4" type="text">
                            <input placeholder="XXXX" pattern="[0-9]{4}" maxlength="4" type="text">
                            <input placeholder="XXXX" pattern="[0-9]{4}" maxlength="4" type="text">
                        </div>
                        <div class="expiryBlock">
                            <select name="expMonth" id="expMonth">
                                <option  value="01">01</option>
                                <option value="02">02</option>
                                <option value="03">03</option>
                                <option value="04">04</option>
                                <option value="05">05</option>
                                <option value="06">06</option>
                                <option value="07">07</option>
                                <option value="08">08</option>
                                <option value="09">09</option>
                                <option value="10">10</option>
                                <option value="11">11</option>
                                <option selected value="12">12</option>
                            </select>
                            <select name="expYear" id="expYear">
                                <option  value="2021">2021</option>
                                <option selected value="2022">2022</option>
                                <option  value="2023">2023</option>
                                <option  value="2024">2024</option>
                                <option  value="2025">2025</option>
                                <option  value="2026">2026</option>
                            </select>
                            <div class="validThru">
                                <span>valid</span>
                                <br>
                                <span>thru</span>
                            </div>
                        </div>
                        <span class="cardHolder">
                            <input placeholder="MR. CARDHOLDER" type="text">
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
                            <input placeholder="XXXX" pattern="[0-9]{4}" maxlength="4" type="text">
                            <input placeholder="XXXX" pattern="[0-9]{4}" maxlength="4" type="text">
                            <input placeholder="XXXX" pattern="[0-9]{4}" maxlength="4" type="text">
                            <input placeholder="XXXX" pattern="[0-9]{4}" maxlength="4" type="text">
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
                        <label for="paymentAmout">
                            Сумма перевода:
                        </label>
                        <input class="inputsBlockInput" id="paymentAmout" value="0.00" min="0" step="0.01" type="number">
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
                        <input id="emailInput" placeholder="укажите email" type="email">
                    </div>
                    <div class="center">
                        <button class="submitBtn"  type="submit" >Отправить деньги</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
</html>
