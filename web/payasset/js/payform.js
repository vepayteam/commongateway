(function ($) {

    "use strict";

    let linklink = null;

    let payform = {
        init: function () {

            Inputmask.extendDefinitions({
                'C': {
                    validator: "[A-Za-z ]",
                    casing: "upper" //auto uppercasing
                }
            });

            $('input[data-inputmask-mask]', '#payform').inputmask();

            $('#payform-phone').on('paste', function (event) {
                let pasteData = event.originalEvent.clipboardData.getData('text');
                if (pasteData.substr(0, 1) === "8" || pasteData.substr(0, 1) === "7") {
                    pasteData = pasteData.substr(1, pasteData.length);
                } else if (pasteData.substr(0, 3) === "+ 7") {
                    pasteData = pasteData.substr(3, pasteData.length);
                } else if (pasteData.substr(0, 2) === "+7") {
                    pasteData = pasteData.substr(2, pasteData.length);
                }
                $('#payform-phone').inputmask("setvalue", pasteData);
                return false;
            });

            $('#payform-cardnumber').on('keyup', function (event) {
                let card = $('#payform-cardnumber').inputmask('unmaskedvalue');
                if (card.length > 0) {
                    if (card.substr(0, 1) === "4" && $(".pslogo").attr("src") !== "/imgs/visa.svg") {
                        $(".pslogo").attr("src", "/imgs/visa.svg");
                    } else if (card.substr(0, 1) === "2" && $(".pslogo").attr("src") !== "/imgs/mir.svg") {
                        $(".pslogo").attr("src", "/imgs/mir.svg");
                    } else if ((card.substr(0, 1) === "5") && $(".pslogo").attr("src") !== "/imgs/mastrer.svg") {
                        $(".pslogo").attr("src", "/imgs/mastrer.svg");
                    } else if ((card.substr(0, 1) === "6") && $(".pslogo").attr("src") !== "/imgs/maestro.svg") {
                        $(".pslogo").attr("src", "/imgs/maestro.svg");
                    }
                }
                return true;
            });

            $('.delcard').on('click', function () {
                $('#payform-cardnumber').inputmask("setvalue", '');
            });

            $('#payform').on('submit', function (e) {
                e.preventDefault();

                if (!payform.validateFields()) return false;

                $('input[data-inputmask-mask]', '#payform').each(function () {
                    //unmask
                    let val = $(this).inputmask('unmaskedvalue');
                    if (val) {
                        $(this).inputmask('remove');
                        $(this).val(val);
                    }
                });
                let form = $('#payform').serialize();
                if (linklink) {
                    linklink.abort();
                }
                linklink = $.ajax({
                    type: 'POST',
                    url: "/pay/createpay",
                    data: form,
                    beforeSend: function () {
                        $('.errmessage').hide();
                        $('#addtopay').prop('disabled', true); //блок кнопки
                        $('#payform').hide();
                        $("#loader").show();
                    },
                    success: function (data, textStatus, jqXHR) {
                        $("#loader").hide();

                        if (data.status == 1) {
                            //ок - переход по url банка
                            payform.load3ds(data.url, data.pa, data.md, data.termurl);
                        } else {
                            $('#addtopay').prop('disabled', false);
                            $('#payform').show();
                            $('input[data-inputmask-mask]', '#payform').inputmask();
                            $('#error_message').html(data.message);
                            $('#error_message_xs').html(data.message);
                            $('.errmessage').show();
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        if (jqXHR.status != 302) {
                            //console.log(jqXHR.status);
                            $("#loader").hide();
                            $('#error_message').html("Ошибка запроса");
                            $('#error_message_xs').html("Ошибка запроса");
                            $('.errmessage').show();
                            $('#addtopay').prop('disabled', false);
                            $('#payform').show();
                            $('input[data-inputmask-mask]', '#payform').inputmask();
                        }
                    }
                });
                return false;
            });

            $('#closeform').on('click', function () {
                let id = $('[name="PayForm[IdPay]"]').val();

                if (confirm('Отменить оплату?')) {
                    if (linklink) {
                        linklink.abort();
                    }
                    linklink = $.ajax({
                        type: 'POST',
                        url: "/pay/declinepay",
                        data: {'ID': id},
                        beforeSend: function () {
                            $('#addtopay').prop('disabled', true); //блок кнопки
                            $("#loader").show();
                        },
                        success: function (data, textStatus, jqXHR) {
                            $("#loader").hide();
                            if (data.status == 1) {
                                window.location.href = '/pay/orderok/' + id;
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            $("#loader").hide();
                            window.location.href = '/pay/orderok/'+id;
                        }
                    });
                }
                return false;
            });
        },

        validateFields: function () {
            let err = false;

            $.each($('input[data-inputmask-mask], input.nomask', '#payform'), function () {
                //console.log($(this));
                let val = $(this).inputmask('unmaskedvalue');
                let notrequired = $(this).hasClass('notrequired');
                //var valid = Inputmask.isValid($(this).val(), $(this).attr('data-inputmask-mask'));
                let re1 = new RegExp($(this).attr('data-inputmask-regex'));
                let valid = re1.test(val);
                if (!valid && !notrequired) {
                    //console.log(re1);
                    CustomValid.showErrorValid($(this), err);
                    err = err | !valid;
                }
            });

            //err = err | CustomValid.checkReuired($('input[name="PayForm[]"]', '#payform'), err);

            //err = err | CustomValid.checkEmail($('input[name="UserInfo[email]"]', '#payform'), err);

            return !err;
        },

        load3ds: function (url, pa, md, termurl) {
            $('#frame3ds').show();
            $('#form3ds').attr('action', url);
            $('#pareq3ds').val(pa);
            $('#md3ds').val(md);
            $('#termurl3ds').val(termurl);
            $('#form3ds').trigger('submit');
        }

    };

    window.payform = payform;

}(jQuery || $));