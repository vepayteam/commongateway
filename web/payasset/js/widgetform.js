(function ($) {

    "use strict";

    let linklink = null;

    let widgetform = {
        init: function () {

            Inputmask.extendDefinitions({
                'C': {
                    validator: "[A-Za-z ]",
                    casing: "upper" //auto uppercasing
                }
            });

            $('input[data-inputmask-mask]', '#widgetform').inputmask();

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

            $('#btnpaywidget').on('click',function (e) {
                e.preventDefault();

                if (!widgetform.validateFields()) return false;

                $('input[data-inputmask-mask]', '#widgetform').each(function () {
                    //unmask
                    let val = $(this).inputmask('unmaskedvalue');
                    if (val) {
                        $(this).inputmask('remove');
                        $(this).val(val);
                    }
                });
                let form = $('#widgetform').serialize();
                if (linklink) {
                    linklink.abort();
                }
                linklink = $.ajax({
                    type: 'POST',
                    url: "/widget/createpay",
                    data: form,
                    beforeSend: function () {
                        $('.errmessage').hide();
                        $('#btnpaywidget').prop('disabled', true); //блок кнопки
                        $('#widgetform').hide();
                        $("#loader").show();
                    },
                    success: function (data, textStatus, jqXHR) {
                        $("#loader").hide();

                        if (data.status == 1) {
                            //ок - переход по url банка
                            widgetform.load3ds(data.url, data.pa, data.md, data.termurl);
                        } else {
                            $('#btnpaywidget').prop('disabled', false);
                            $('#widgetform').show();
                            $('input[data-inputmask-mask]', '#widgetform').inputmask();
                            $('#error_message').html(data.message);
                            $('.errmessage').show();
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        if (jqXHR.status != 302) {
                            //console.log(jqXHR.status);
                            $("#loader").hide();
                            $('#error_message').html("Ошибка запроса");
                            $('.errmessage').show();
                            $('#btnpaywidget').prop('disabled', false);
                            $('#widgetform').show();
                            $('input[data-inputmask-mask]', '#widgetform').inputmask();
                        }
                    }
                });
                return false;
            });
        },

        validateFields: function () {
            let err = false;

            $.each($('input[data-inputmask-mask], input.nomask', '#widgetform'), function () {
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

            //err = err | CustomValid.checkReuired($('input[name="PayForm[]"]', '#widgetform'), err);

            //err = err | CustomValid.checkEmail($('input[name="UserInfo[email]"]', '#widgetform'), err);

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
    window.widgetform = widgetform;
        
}(jQuery || $));