<?php

use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\models\PartnerBankGate;
use app\services\payment\models\PaySchet;
use Carbon\Carbon;

/**
 * @var PaySchet $paySchet
 * @var PartnerBankGate $gate
 */

$gate = (new BankAdapterBuilder())
    ->buildByBank($paySchet->partner, $paySchet->uslugatovar, $paySchet->bank, $paySchet->currency)
    ->getPartnerBankGate();

$minSumCommission = $gate->UseGateCompensation ? ($gate->ClientMinimalFee ?? 0) : $paySchet->uslugatovar->MinsumComiss;
?>

<form action="#" class="form">
  <h1 class="form__main_header">Перевод денег без комиссий<br>с карты любого банка</h1>

  <div class="form__sender">
    <h2 class="form__sender_header">Данные отправителя</h2>

    <div class="form__sender_card_number">
      <input required
             id="card_number"
             class="control__input"
             placeholder="Номер карты"
             autocomplete="off"
             data-inputmask-placeholder="_"
             data-inputmask-jitMasking="true"
             data-inputmask-mask="9{4} 9{4} 9{4} 9{4,6}"
             data-inputmask-regex="\d{16,18}"
             type="text">
    </div>

    <div class="form__sender_cvc_expiry">
      <input required
             id="expiry_date"
             class="control__input"
             placeholder="ММ/ГГ"
             autocomplete="off"
             data-inputmask-placeholder="_"
             data-inputmask-jitMasking="true"
             data-inputmask-mask="99/99"
             data-inputmask-regex="[01]\d{3}"
             type="text">

      <input
        required
        id="cvv"
        class="control__input"
        placeholder="CVV/CVC"
        autocomplete="off"
        data-inputmask-placeholder="_"
        data-inputmask-jitMasking="true"
        data-inputmask-mask="9{3}"
        data-inputmask-regex="\d{3}"
        type="text">
    </div>

    <div class="form__sender_card_number">
      <input required
             id="cardholder_name"
             class="control__input"
             placeholder="Имя держателя карты"
             autocomplete="off"
             data-inputmask-regex="[a-zA-Z ]{3,}"
             type="text">
    </div>
  </div>

  <div class="form__recipient">
    <h2 class="form__recipient_title">Данные получателя</h2>

    <input required
           id="out_card_number"
           class="control__input form__recipient_card_number"
           placeholder="Номер карты"
           autocomplete="off"
           data-inputmask-placeholder="_"
           data-inputmask-jitMasking="true"
           data-inputmask-mask="9{4} 9{4} 9{4} 9{4,6}"
           data-inputmask-regex="\d{16,18}"
           type="text">
  </div>

  <div class="form__payment_details">
    <div class="form__payment_details__amount">
      <label class="form__payment_details__amount__label" for="payment_amount">Сумма перевода</label>
      <input class="form__payment_details__amount__input control__input"
             id="payment_amount"
             placeholder="0.00 ₽"
             data-inputmask-placeholder="_"
             data-inputmask-jitMasking="true"
             data-inputmask-regex="^\d*(\.\d{0,2})?$"
             type="text">
      <small class="form__payment_details__amount__info">
        Максимальная сумма платежа <?= number_format($paySchet->uslugatovar->MaxSumm / 100, 0, '', ' ') ?> ₽
      </small>
    </div>

    <div id="form__payment_details_error" class="form__payment_details_error" style="display: none;"></div>

    <div class="form__payment_details__commission">
      <span>Комиссия</span>
      <span><span id="payment_commission"><?= number_format($minSumCommission, 2, ',', ' ') ?></span> ₽</span>
    </div>

    <button id="submit_btn" class="form__payment_details__send" disabled="disabled">Отправить деньги</button>
  </div>

  <div class="form__offer">
    <label class="control control-checkbox form__offer__label">
      Настоящим я подтверждаю, что ознакомлен и согласен с правилами <a target="_blank" href="/files/с_21_06_2021_Пуб_оферта_№_2_от_21_06_2021_Card2Card_2_2.pdf">оферты</a>
      <input id="agree_offer" type="checkbox"/>
      <div class="control_indicator"></div>
    </label>
  </div>
</form>

<script>
  var paySchetId = <?=$paySchet->ID?>;
  var pcComission = <?=$gate->UseGateCompensation ? ($gate->ClientCommission ?? 0) : $paySchet->uslugatovar->PcComission?>;
  var minsumComiss = <?=$minSumCommission?>;
  var currMonth = <?=Carbon::now()->month?>;
  var currYear = <?=Carbon::now()->year?>;
  var minSum = <?=$paySchet->uslugatovar->MinSumm / 100?>;
  var maxSum = <?=$paySchet->uslugatovar->MaxSumm / 100?>;
</script>
