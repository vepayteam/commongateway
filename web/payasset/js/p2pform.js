function decimalAdjust(type, value, exp) {
    // Если степень не определена, либо равна нулю...
    if (typeof exp === 'undefined' || +exp === 0) {
        return Math[type](value);
    }
    value = +value;
    exp = +exp;
    // Если значение не является числом, либо степень не является целым числом...
    if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0)) {
        return NaN;
    }
    // Сдвиг разрядов
    value = value.toString().split('e');
    value = Math[type](+(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp)));
    // Обратный сдвиг
    value = value.toString().split('e');
    return +(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp));
}

function convertToFullYear(lastPartYear) {
    var today = new Date()
    var startPartYear = Math.floor(today.getFullYear() / 100)

    return parseInt(startPartYear.toString() + lastPartYear.toString())
}

if (!Math.ceil10) {
    Math.ceil10 = function (value, exp) {
        return decimalAdjust('ceil', value, exp);
    };
}

$(document).ready(function() {

    $("input").inputmask({
        placeholder: '',
        showMaskOnHover: false,
        showMaskOnFocus: false
    });
    $("input").tooltipster({
        position: 'bottom',
        content: null
    });

    function enableSendButton () {
        $('#submit_btn').prop( "disabled", false);
    }

    function disableSendButton () {
        $('#submit_btn').prop( "disabled", true);
    }

    function checkButtonState () {
        var paymentAmount = $('#payment_amount').val()

        var isChecked = $('#agree_offer').is(":checked")
        var isValidPaymentAmount = paymentAmount && parseInt(paymentAmount) > 0

        if(isChecked && isValidPaymentAmount) {
            enableSendButton()
        } else {
            disableSendButton()
        }
    }

    $('#agree_offer').change(function() {
        checkButtonState()
    })

    $('#payment_amount').on('change keyup', function() {
        var val = $(this).val()
        if (!val) {
            val = 0
        }

        var amount = parseInt(val);
        var commission = amount * (pcComission/100);
        if(commission < minsumComiss) {
            commission = minsumComiss;
        }

        var strValue = Math.ceil10(commission, -2).toFixed(2).replace('.', ',')
        $('#payment_commission').text(strValue);
        checkButtonState()
    })

    let submitLocked = false;
    $('#submit_btn').click(function(e) {
        e.preventDefault();

        if (submitLocked) {
            return;
        }
        submitLocked = true;

        disableSendButton()

        $('#form__payment_details_error').css({'display': 'none'})

        var csrfParam = $('meta[name=csrf-param]').attr("content");
        var csrfToken = $('meta[name=csrf-token]').attr("content");

        var valid = true;
        var tooltipIsShow = false;
        $("input").each(function() {
            if(!$(this).inputmask('isComplete')) {
                valid = false;
                $(this).css({'border-color': 'red'});
                if(!tooltipIsShow) {
                    $(this).tooltipster('content', 'Поле заполнено некорректно');
                    $(this).tooltipster('show');
                    tooltipIsShow = true;
                }
            } else {
                $(this).tooltipster('content', null);
                $(this).css({'border-color': ''});
            }
        })

        if(!valid) {
            checkButtonState()
            submitLocked = false;
            return false;
        }

        var amount = parseFloat($('#payment_amount').val());
        var cvv = $('#cvv').val();
        var holder = $('#cardholder_name').val();
        var cardPan = $('#card_number').val();
        var outCardPan = $('#out_card_number').val();

        if (amount > maxSum) {
            $('#payment_amount').css({'border-color': 'red'});
            valid = false;

            $('#form__payment_details_error').html('Максимальная сумма платежа <b>' + maxSum.toFixed(2).replace('.', ',') + '</b> ₽')
            $('#form__payment_details_error').css({'display': 'block'})
        } else if (amount < minSum) {
            $('#payment_amount').css({'border-color': 'red'});
            valid = false;

            $('#form__payment_details_error').html('Минимальная сумма платежа <b>' + minSum.toFixed(2).replace('.', ',') + '</b> ₽')
            $('#form__payment_details_error').css({'display': 'block'})
        }

        var expiryDate = $('#expiry_date').val()
        var expiryDateMatch = expiryDate.match(/(\d{1,2})\/(\d{2})/)
        var expMonth = parseInt(expiryDateMatch[1])
        var expYear = parseInt(expiryDateMatch[2])
        expYear = convertToFullYear(expYear)

        if(expYear === currYear && expMonth < currMonth) {
            $('#expiry_date').css({'border-color': 'red'});
            valid = false;
        } else {
            $('#expiry_date').css({'border-color': ''});
        }

        if(!valid) {
            checkButtonState()
            submitLocked = false;
            return false;
        }

        var data = {
            amount: amount,
            cardPan: cardPan.replaceAll(' ', ''),
            cardExpMonth: expMonth,
            cardExpYear: expYear,
            cvv: cvv,
            cardHolder: holder,
            outCardPan: outCardPan.replaceAll(' ', ''),
        }
        data[csrfParam] = csrfToken;
        $.ajax({
            type: "POST",
            url: '/p2p/send/' + paySchetId,
            data: data,
            success: function(response) {
                checkButtonState()

                if(response.status == 1) {
                    window.location = response.url;
                } else {
                    $('#form__payment_details_error').text(response.message)
                    $('#form__payment_details_error').css({'display': 'block'})
                }

                submitLocked = false;
            },
            error: function(response) {
                checkButtonState()

                $('#form__payment_details_error').text('Ошибка запроса')
                $('#form__payment_details_error').css({'display': 'block'})

                submitLocked = false;
            }
        })
    })
})
