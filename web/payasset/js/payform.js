(function ($) {

    "use strict";

    let linklink = null;

    let payform = {
        init: function () {

            $('[data-toggle="tooltip"]').tooltip();

            $("#client_data").val(JSON.stringify({
                'screenColorDepth': screen.colorDepth,
                'screenWidth': screen.width,
                'screenHeight': screen.height,
                'windowInnerWidth': window.innerWidth,
                'windowInnerHeight': window.innerHeight,
                'navigatorLanguage': navigator.language,
                'timezoneOffset': new Date().getTimezoneOffset(),
                'navigatorJavaEnabled': navigator.javaEnabled(),
                'navigatorUserAgent': navigator.userAgent
            }));

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
                    success: payform.createPaySuccess,
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

                if (confirm($('#payment_cancel_text').text())) {
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

        load3ds: function (url, pa, md, creq, termurl, threeDSServerTransID = '', html3dsForm = '') {
            // build html form from response
            if (html3dsForm) {
                var formParentDiv = $('.raw3DsForm');
                formParentDiv.html(html3dsForm);
                formParentDiv.find('form').trigger('submit');
                return;
            }
            $('#frame3ds').show();
            $('#form3ds').attr('action', url);
            $('#pareq3ds').val(pa);
            $('#md3ds').val(md);
            $('#creq3ds').val(creq);
            $('#termurl3ds').val(termurl);
            if (!creq) {
                $('#creq3ds').remove();
            }
            $('#threeDSServerTransID').val(threeDSServerTransID);
            $('#form3ds').trigger('submit');
        },

        load3dsMonetix: function(url, paReq, md, termUrl) {
            $('#frame3ds').show();
            $('#form3dsMonetix').attr('action', url);
            $('#form3dsMonetix__pa_req').val(paReq);
            $('#form3dsMonetix__md').val(md);
            $('#form3dsMonetix__term_url').val(termUrl);
            $('#form3dsMonetix').trigger('submit');
        },

        confirm3dsV2TKB: function(url, transId, termurl) {
            var json = "{\"threeDSServerTransID\": \"" + transId + "\", \"threeDSMethodNotificationURL\": \"" + termurl + "\"}",
                html = "<html lang='ru'><body>" +
                "<form hidden target='threDS' name =\"threeDS\" id=\"threeDSServerTransIDForm\" method=\"post\" action=\"" + url + "\">" +
                "<input hidden name=\"threeDSMethodData\" placeholder=\"threeDSMethodData\" value=\"" + btoa(json) + "\" />" +
                "</form> <script type=\"text/javascript\">document.getElementById('threeDSServerTransIDForm').submit();</script></body></html>";
            $('#confirm3dsV2TKBFrame').html(html);
        },

        applepay: function (merchantIdentifier, amount, label) {
            if (window.ApplePaySession) {
                let id = $('[name="PayForm[IdPay]"]').val();
                let promise = window.ApplePaySession.canMakePaymentsWithActiveCard(merchantIdentifier);
                promise.then(function (canMakePayments) {
                    if (canMakePayments) {
                        // Display Apple Pay button here.
                        $('#applepay').show();

                        $('#applepaybtn').off().on('click', function () {
                            const paymentRequest = {
                                total: {
                                    label: label,
                                    amount: amount
                                },
                                countryCode: 'RU',
                                currencyCode: 'RUB',
                                merchantCapabilities: ['supports3DS'],
                                supportedNetworks: ['masterCard', 'visa']
                            };

                            const applePaySession = new window.ApplePaySession(1, paymentRequest);
                            applePaySession.onvalidatemerchant = (event) => {
                                // отправляем запрос на валидацию сессии
                                $.ajax({
                                    type: 'POST',
                                    url: "/pay/applepayvalidate",
                                    data: {'validationURL': event.validationURL, 'IdPay': id},
                                    success: function (data, textStatus, jqXHR) {
                                        // завершаем валидацию платежной сессии
                                        applePaySession.completeMerchantValidation(data.merchantSession);
                                    },
                                    error: function (jqXHR, textStatus, errorThrown) {
                                        applePaySession.abort();
                                    }
                                });
                            };

                            applePaySession.onpaymentauthorized = (event) => {
                                //приходит после подтверждения польцем - завершить оплату
                                //передать токен в банк
                                $.ajax({
                                    type: 'POST',
                                    url: "/pay/applepaycreate",
                                    data: {'paymentToken': event.token, 'IdPay': id},
                                    success: function (data, textStatus, jqXHR) {
                                        //$("#loader").hide();
                                        if (data.status == 1) {
                                            applePaySession.completePayment(applePaySession.STATUS_SUCCESS);
                                            window.location.href = '/pay/orderok/'+id;
                                        } else {
                                            $('#error_message').html(data.message);
                                        }
                                    },
                                    error: function (jqXHR, textStatus, errorThrown) {
                                        //$("#loader").hide();
                                        $('#error_message').html("Ошибка запроса");
                                    }
                                });
                            }

                            applePaySession.begin();
                        });

                    }
                });
            }
        },

        googlepay: function (merchantIdentifier, amount, label, gate, istest) {
            if (google) {
                let paymentsClient = new google.payments.api.PaymentsClient({environment: istest ? 'TEST' : 'PROD'});

                let getGooglePaymentDataConfiguration = {
                    merchantId: merchantIdentifier,
                    environment: istest ? 'TEST' : 'PROD',
                    transactionInfo: {
                        totalPriceStatus: 'FINAL',
                        totalPrice: amount,
                        currencyCode: 'RUB' //ISO 4217
                    },
                    paymentMethodTokenizationParameters: {
                        tokenizationType: 'PAYMENT_GATEWAY',
                        parameters: {
                            gateway: gate, //
                            gatewayMerchantId: merchantIdentifier //
                        }
                    },
                    allowedPaymentMethods: ['CARD', 'TOKENIZED_CARD'],
                    cardRequirements: {
                        allowedCardNetworks: ['MASTERCARD', 'VISA']
                    },
                    phoneNumberRequired: false,
                    emailRequired: false
                }

                paymentsClient.isReadyToPay(({allowedPaymentMethods: ['CARD', 'TOKENIZED_CARD']}))
                    .then(function (response) {
                        if (response.result) {
                            //кнопка
                            $('#googlepay').show();
                        }
                    })
                    .catch(function (err) {
                        // show error in developer console for debugging
                        console.error(err);
                    });

                $('#googlepay').off().on('click', function () {
                    paymentsClient.loadPaymentData(getGooglePaymentDataConfiguration)
                        .then(function (paymentData) {
                            $.ajax({
                                type: 'POST',
                                url: "/pay/googlepaycreate",
                                data: {'paymentToken': paymentData, 'IdPay': id},
                                success: function (data, textStatus, jqXHR) {
                                    //$("#loader").hide();
                                    if (data.status == 1) {
                                        if (data.acsUrl == undefined) {
                                            window.location.href = '/pay/orderok/' + id;
                                        } else {
                                            payform.load3ds(data.acsUrl, data.paReq, data.md, data.creq, data.termUrl);
                                        }
                                    } else {
                                        $('#error_message').html(data.message);
                                    }
                                },
                                error: function (jqXHR, textStatus, errorThrown) {
                                    //$("#loader").hide();
                                    $('#error_message').html("Ошибка запроса");
                                }
                            });

                        }).catch(function (err) {
                        // show error in developer console for debugging
                        console.error(err);
                    });
                });
            }
        },

        samsungpay: function (merchantIdentifier, amount, label) {

        },

        pingMonetixCallback: function(interval) {
            let me = this;
            let id = $('[name="PayForm[IdPay]"]').val();
            $.ajax({
                type: 'POST',
                url: "/callback/monetix-ping",
                data: {
                    'paySchetId': id,
                    '_csrf': $('input[name=_csrf]').val(),
                },
                success: function (response, textStatus, jqXHR) {
                    if(!response || typeof response['status'] == 'undefined') {
                        return;
                    }
                    if(response['status'] && response['status'] == 'awaiting 3ds result') {
                        var url = response['data']['acs']['acs_url'];
                        var md = response['data']['acs']['md'];
                        var paReq = response['data']['acs']['pa_req'];
                        var termUrl = response['data']['acs']['term_url'];
                        payform.load3dsMonetix(url, paReq, md, termUrl);
                        clearInterval(interval);
                    } else if(response['status'] && response['status'] == 'awaiting redirect result') {
                        let method = response['data']['redirect_data']['method'];
                        let url = response['data']['redirect_data']['url'];
                        let fields = response['data']['redirect_data']['body'];
                        if (method === 'GET') {
                            window.location = url;
                        } else {
                            me.submitForm(method, url, fields);
                        }
                        clearInterval(interval);
                    } else if(response['status'] && response['status'] == 'decline') {
                        window.location = response.termurl;
                        clearInterval(interval);
                    } else if(response['status'] && response['status'] == 'success') {
                        window.location = '/pay/orderok?id=' + id;
                        clearInterval(interval);
                    } else {
                        window.location = response.termurl;
                        clearInterval(interval);
                    }
                }
            });
        },

        createPaySuccess: function (data, textStatus, jqXHR) {
            if (data.status == 1 && data.isNeedPingMonetix) {
                var interval = setInterval(function() {
                    payform.pingMonetixCallback(interval);
                }, 2000);
            } else if (data.status == 1 && !data.isNeed3DSRedirect) {
                if (data.isNeed3DSVerif == 1) {
                    //ок - переход по url банка
                    payform.load3ds(data.url, data.pa, data.md, data.creq, data.termurl, data.threeDSServerTransID, data.html3dsForm);
                } else {
                    // Walletto при создании платежа тут переход на orderdone при статусе charged
                    // если 3DS v2 и не требуется авторизация, переходим на orderdone
                    window.location = data.termurl;
                }
            } else if (data.status == 0 && data.threeDSMethodURL && data.isNeedSendTransIdTKB) {
                payform.confirm3dsV2TKB(data.threeDSMethodURL, data.threeDSServerTransID, data.termurl);
            } else if (data.status == 1 && data.url && data.isNeed3DSRedirect) {
                window.location = data.url;
            } else if (data.status == 2 && data.url) {
                window.location = data.url;
            } else {
                $('#addtopay').prop('disabled', false);
                $('#payform').show();
                $('input[data-inputmask-mask]', '#payform').inputmask();
                $('#error_message').html(data.message);
                $('#error_message_xs').html(data.message);
                $('#payform .errmessage').show();
            }
        },

        checkIframe: function () {
            let isFramed
            try {
                isFramed = window != window.top || document != top.document || self.location != top.location;
            } catch (e) {
                isFramed = true;
            }

            if (isFramed) {
                const middleWrapper = document.getElementById('middle-wrapper');
                if (middleWrapper) {
                    middleWrapper.classList.remove('middle-background');
                }
            }
        },
        /**
         * @param method String
         * @param url String
         * @param fields Object
         */
        submitForm: function (method, url, fields) {
            let $form = $('<form></form>')
                .hide()
                .attr('method', method)
                .attr('action', url);
            for(let key in fields){
                $('<input type="hidden"/>')
                    .attr('name', key)
                    .attr('value', fields[key])
                    .appendTo($form);
            }
            $form
                .appendTo($('html'))
                .submit();
        },

        /**
         * Показывает ошибку.
         * @param message
         */
        error: function (message) {
            $("#loader").hide();
            $('#error-message')
                .show()
                .find('.js-message-container')
                .show()
                .text(message);
        }
    };

    window.payform = payform;

}(jQuery || $));