$(document).ready(function() {

    $("input").inputmask();

    $('#paymentAmount').change(function() {
        var amount = parseInt($(this).val());
        var comiss = amount * (pcComission/100);
        if(comiss < minsumComiss) {
            comiss = minsumComiss;
        }
        $('#paymentComission').val(comiss)
    })

    $('#sendForm').click(function(e) {
        e.preventDefault();
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

        $("input[type=text]").each(function() {
            if(!$(this).inputmask('isComplete')) {
                valid = false;
                $(this).css({'border-color': 'red'});
            } else {
                $(this).css({'border-color': 'rgb(230, 230, 230)'});
            }
        })

        if(!valid) {
            return false;
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
