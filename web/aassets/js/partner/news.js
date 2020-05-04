(function ($) {

    "use strict";

    let news = {

        init: function () {
            $('#addnewsform').on('submit', function (e) {
                e.preventDefault();

                let form = $('#addnewsform');
                $.ajax({
                    url: '/partner/default/addnews',
                    method: 'POST',
                    data: form.serialize(),
                    beforeSend: function () {
                        $('#submitnews').prop('disabled', true);
                    },
                    success: function (data) {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                        if (data.status == 1) {
                            window.location.reload();
                        } else {
                            $('#submitnews').prop('disabled', false);
                            toastr.error(data.message, "Ошибка");
                        }
                    },
                    error: function () {
                        $('#submitnews').prop('disabled', false);
                    }
                });

                return false;
            });

            $('[data-click="delnews"]').off().on('click', function () {
                let id = $(this).attr('data-id');
                if (confirm("Удалить новость?")) {
                    $.ajax({
                        url: '/partner/default/delnews',
                        method: 'POST',
                        data: {'id': id},
                        beforeSend: function () {
                        },
                        success: function (data) {
                            if (data.status == 1) {
                                window.location.reload();
                            } else {
                                toastr.error(data.message, "Ошибка");
                            }
                        },
                        error: function () {
                        }
                    });
                }
                return false;
            });

            setTimeout(function alerts() {
                $.ajax({
                    url: '/partner/default/alerts',
                    method: 'GET',
                    success: function (data) {
                        if (data.status == 1 && data.data.length > 0) {
                            data.data.forEach(function(item, i) {
                                toastr.warning(item.body, item.head, {
                                    timeOut: 30000,
                                    positionClass: "toast-top-full-width"
                                });
                            });
                        }
                    }
                });
            },500);

        },

    };

    window.news = news;
}(jQuery || $));