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
if (!Math.ceil10) {
    Math.ceil10 = function(value, exp) {
        return decimalAdjust('ceil', value, exp);
    };
}
$(document).ready(function() {

    $("input").inputmask({
        "placeholder": "",
        showMaskOnHover: false,
        showMaskOnFocus: false
    });

    $("#btnClose").click(function() {
        window.location = $(this).data('url');
    })

    $('#paymentAmount').on('change keyup', function() {
        var amount = parseInt($(this).val());
        var comiss = amount * (pcComission/100);
        if(comiss < minsumComiss) {
            comiss = minsumComiss;
        }
        $('#paymentComission').val(Math.ceil10(comiss, -2).toString());
    })

    $('#sendForm').click(function(e) {
        e.preventDefault();

        if(!$("#agreeOffer").prop('checked')) {
            $("#formErrorOfferMessage").show();
            return false;
        } else {
            $("#formErrorOfferMessage").hide();
        }

        var csrfParam = $('meta[name=csrf-param]').attr("content");
        var csrfToken = $('meta[name=csrf-token]').attr("content");

        var valid = true;
        var amount = parseFloat($('#paymentAmount').val());
        var expMonth = parseFloat($('#expMonth').val());
        var expYear = parseFloat($('#expYear').val());
        var cvv = $('#cvv').val();
        var holder = $('#holder').val();
        var email = $('#emailInput').val();
        var cardPan = '';
        var outCardPan = '';
        for(var i = 1; i <= 4; i++) {
            cardPan += $('#cardPan' + i.toString()).val()
            outCardPan += $('#outCardPan' + i.toString()).val()
        }

        if(expYear == currYear && expMonth < currMonth) {
            $('#expMonth').css({'border-color': 'red'});
            $('#expYear').css({'border-color': 'red'});
            valid = false;
        } else {
            $('#expMonth').css({'border-color': 'rgb(230, 230, 230)'});
            $('#expYear').css({'border-color': 'rgb(230, 230, 230)'});
        }

        if(amount < 1 || amount > 75000) {
            $('#paymentAmount').css({'border-color': 'red'});
            valid = false;
        } else {
            $('#paymentAmount').css({'border-color': 'rgb(230, 230, 230)'});
        }

        $("input").each(function() {
            if(!$(this).inputmask('isComplete')) {
                valid = false;
                $(this).css({'border-color': 'red'});
            } else {
                $(this).css({'border-color': 'rgb(230, 230, 230)'});
            }
        })

        if(!valid) {
            $("#formErrorMessage").show();
            return false;
        } else {
            $("#formErrorMessage").hide();
        }

        var data = {
            amount: amount,
            cardPan: cardPan,
            cardExpMonth: expMonth,
            cardExpYear: expYear,
            cvv: cvv,
            cardHolder: holder,
            outCardPan: outCardPan,
            email: email,
        }
        data[csrfParam] = csrfToken;
        console.log(data);
        $.ajax({
            type: "POST",
            url: '/p2p/send/' + paySchetId,
            data: data,
            success: function(response) {
                if(response.status == 1) {
                    window.location = response.url;
                } else {
                    alert(response.message);
                }
            },
            error: function(response) {
                alert('Ошибка запроса');
            }
        })

        return false;
    })
})
