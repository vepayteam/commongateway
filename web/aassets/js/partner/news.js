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
                        form.closest('.ibox-content').toggleClass('sk-loading');
                    },
                    success: function (data) {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                        if (data.status == 1) {
                            window.location.reload();
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
        },

    };

    window.news = news;
}(jQuery || $));