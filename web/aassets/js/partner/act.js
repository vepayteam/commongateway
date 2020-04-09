(function ($) {

    "use strict";

    let linklink = 0;
    let act = {
        list: function () {
            $('[name="datefrom"]').datetimepicker({
                format: 'MM.YYYY'
            }).on('dp.change', function () {
                $('#actlistform').trigger('submit');
            });

            $('#actidpatner').on('change', function () {
                $('#actlistform').trigger('submit');
            });

            $('#actlistform').on('submit', function (e) {
                e.preventDefault();

                let form = $('#actlistform');
                $.ajax({
                    url: '/partner/stat/act-list',
                    method: 'POST',
                    data: form.serialize(),
                    beforeSend: function () {
                        $('#actlistdata').html('');
                        form.closest('.ibox-content').toggleClass('sk-loading');
                    },
                    success: function (data) {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                        if (data.status == 1) {
                            $('#actlistdata').html(data.data);
                        } else {
                            toastr.error(data.message, "Ошибка");
                        }
                    },
                    error: function () {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                    }
                });

                return false;
            });

            $('#actlistdata').on('click', '#btnFormirAct', function (e) {
                //сформировать
                let form = $('#actlistform');
                $.ajax({
                    url: '/partner/stat/act-create',
                    method: 'POST',
                    data: form.serialize(),
                    beforeSend: function () {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                    },
                    success: function (data) {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                        if (data.status == 1) {
                            toastr.success("OK", "Акты сформированы");
                            $('#actlistform').trigger('submit');
                        } else {
                            toastr.error(data.message, "Ошибка");
                        }
                    },
                    error: function () {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                    }
                });
                return false;
            }).on('click', '#btnPubAct', function (e) {
                //опубликовать
                let form = $('#actlistform');
                $.ajax({
                    url: '/partner/stat/act-pub',
                    method: 'POST',
                    data: form.serialize(),
                    beforeSend: function () {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                    },
                    success: function (data) {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                        if (data.status == 1) {
                            toastr.success("OK", "Акты опубликованы");
                            $('#actlistform').trigger('submit');
                        } else {
                            toastr.error(data.message, "Ошибка");
                        }
                    },
                    error: function () {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                    }
                });
                return false;
            }).on('click', '[data-click="schetsend"]', function () {
                //выставить счет
                if (confirm('Выставить счет?')) {
                    let id = $(this).attr('data-id');
                    let form = $('#actlistform');
                    $.ajax({
                        url: '/partner/stat/act-createschet',
                        method: 'POST',
                        data: {'ActId': id},
                        beforeSend: function () {
                            form.closest('.ibox-content').toggleClass('sk-loading');
                        },
                        success: function (data) {
                            form.closest('.ibox-content').toggleClass('sk-loading');
                            if (data.status == 1) {
                                toastr.success("OK", data.message);
                                $('#actlistform').trigger('submit');
                            } else {
                                toastr.error(data.message, "Ошибка");
                            }
                        },
                        error: function () {
                            form.closest('.ibox-content').toggleClass('sk-loading');
                        }
                    });
                }
                return false;
            });

            $('#actlistform').trigger('submit');
        },

        edit: function () {
            $('#btnSaveAct').on('click', function (e) {
                let form = $('#formEditAct');
                $.ajax({
                    url: '/partner/stat/act-save',
                    method: 'POST',
                    data: form.serialize(),
                    beforeSend: function () {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                    },
                    success: function (data) {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                        if (data.status == 1) {
                            toastr.success("OK", "Данные интеграции успешно сохранены.");
                        } else {
                            toastr.error(data.message, "Ошибка");
                        }
                    },
                    error: function () {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                    }
                });
                return false;
            });
        }
    };

    window.act = act;

} (jQuery || $));

