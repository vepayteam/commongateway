(function ($) {

    "use strict";

    let linklink = 0;
    let site = {
        registerform: function () {
            let form = $('#siteregisterform');
            $('#regpartnerbtn').on('click', function (e) {
                e.preventDefault();

                if (linklink) {
                    linklink.abort();
                }
                linklink = $.ajax({
                    type: "POST",
                    url: '/site/register-add',
                    data: form.serialize(),
                    beforeSend: function () {
                        $('#regpartnerbtn').prop('disabled', true);
                    },
                    success: function (data) {
                        $('#regpartnerbtn').prop('disabled', false);
                        if (data.status == 1) {
                            toastr.success("OK", "Регистрация завершена");
                            if (data.url != '') {
                                window.location.href = data.url;
                            } else {
                                window.location.href = '/';
                            }
                        } else {
                            toastr.error(data.message, "Ошибка");
                        }
                    },
                    error: function () {
                        $('#regpartnerbtn').prop('disabled', false);
                        toastr.error("Ошибка запроса", "Ошибка");
                    }
                });

                return false;
            });
        }
    };

    window.site = site;

}(jQuery || $));
