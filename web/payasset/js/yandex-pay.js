function showLoader() {
  $('.errmessage').hide();
  $('#addtopay').prop('disabled', true); //блок кнопки
  $('#payform').hide();
  $("#loader").show();
}

function hideLoader(errorMessage) {
  $("#loader").hide();
  $('#payform').show();
  $('#addtopay').prop('disabled', false);

  if (errorMessage) {
    $('.errmessage').show();
    $('#error_message_xs').html(errorMessage);
  }
}

function getFullExpiration(data) {
  const month = data.expiration_month.toString()
  const year = data.expiration_year.toString()

  if (month.length === 1) {
    month = '0' + month
  }

  return month + '/' + year.substring(2)
}

function onYaPayLoad() {
  const YaPay = window.YaPay;

  const yandexPayMerchantId = document.getElementById('yandexPayMerchantId').value
  const paymentId = document.getElementById('paymentId').value
  const paymentAmount = document.getElementById('paymentAmount').value
  const partnerId = document.getElementById('partnerId').value
  const partnerName = document.getElementById('partnerName').value

  // Сформировать данные платежа.
  const paymentData = {
    env: YaPay.PaymentEnv.Sandbox,
    version: 2,
    countryCode: YaPay.CountryCode.Ru,
    currencyCode: YaPay.CurrencyCode.Rub,
    merchant: {
      id: yandexPayMerchantId, // Наш merchant id, менять не надо
      name: partnerName, // Название мерчанта в нашей системе
    },
    order: {
      id: paymentId, // Даннные о заказе ID PaySchet
      total: {
        amount: paymentAmount, // Сумма заказа
      }
    },
    paymentMethods: [
      {
        type: YaPay.PaymentMethodType.Card,
        gateway: 'vepay',
        gatewayMerchantId: partnerId, // ID мерчанта в нашей системе
        allowedAuthMethods: [YaPay.AllowedAuthMethod.PanOnly],
        allowedCardNetworks: [
          YaPay.AllowedCardNetwork.Visa,
          YaPay.AllowedCardNetwork.Mastercard,
          YaPay.AllowedCardNetwork.Mir,
          YaPay.AllowedCardNetwork.Maestro,
          YaPay.AllowedCardNetwork.VisaElectron
        ]
      }
    ],
  };

  // Создать платеж.
  YaPay.createPayment(paymentData)
    .then(function (payment) {
      // Создать экземпляр кнопки.
      const container = document.querySelector('#yandex-pay-btn');
      const button = payment.createButton({
        type: YaPay.ButtonType.Checkout,
        theme: YaPay.ButtonTheme.Black,
        width: YaPay.ButtonWidth.Max,
      });

      // Смонтировать кнопку в DOM.
      button.mount(container);

      // Подписаться на событие click.
      button.on(YaPay.ButtonEventType.Click, function onPaymentButtonClick() {
        // Запустить оплату после клика на кнопку.
        payment.checkout();
      });

      // Подписаться на событие process.
      payment.on(YaPay.PaymentEventType.Process, function onPaymentProcess(event) {
        // Закрыть форму Yandex.Pay.
        payment.complete(YaPay.CompleteReason.Success);

        showLoader()

        $.ajax('/pay/yandex-pay/' + paymentId, {
          data: JSON.stringify({
            paymentToken: event.token,
          }),
          dataType: 'json',
          contentType: 'application/json',
          method: 'POST',
        }).done(function (response) {
          if (response.status !== 1) {
            hideLoader(response.message)
          }
          else {
            hideLoader()

            $('#payform-cardnumber').val(response.data.pan)
            $('#payform-cardexp').val(getFullExpiration(response.data))
          }
        }).fail(function (error) {
          hideLoader('Ошибка запроса')
        })
      });
    })
    .catch(function (error) {
      hideLoader('YandexPay: Ошибка создания платежа')
    });
}