"use strict";

var isNavClick = 0;
var linklink = 0;
var navigate = {

    /**
     * Навигация через hash ссылку
     * @param link
     */
    navigateByHash: function (link) {
        navigate.changeHashLink(link);
        navigate.loadHashLink();
    },

    /**
     * Навигация hash
     */
    loadHashLink: function () {
        var link = window.location.hash.substr(1);
        if (linklink) {
            linklink.abort();
        }
        if (link.length > 1) {
            linklink = $.ajax({
                type: "POST",
                url: "/shop/" + link,
                beforeSend: function () {
                    $("#prov").html("<div class='text-center col-sm-12 margin-top20'><i class=\"fa fa-spinner fa-spin fa-fw\"></i></div>");
                }
            }).done(function (data) {
                $("#prov").html(data.data);
            });
        } else {
            //нет хэша - по url обновляем
            window.location.reload();
        }
    },

    /**
     * Изменение ссылки hash (после загрузки страницы ajax)
     * @param link
     */
    changeHashLink: function(link) {
        if (link !== window.location.hash.substr(1)) {
            isNavClick = 1;
            window.location.hash = link;
        }
    },

    /**
     * Удалить ссылку hash
     */
    clearHashLink: function() {
        navigate.changeHashLink('');
    },

    exportPay: function (idpay) {
        if (linklink) {
            linklink.abort();
        }

        linklink = $.ajax({
            type: "POST",
            url: "/shop/exportpay",
            data: {'IdPay': idpay},
            beforeSend: function () {
            }
        }).done(function (data) {
        });
    }
};

/**
 * Навигация вперед/назад по хэш ссылкам
 */
$(window).on('hashchange', function(e) {
    if (isNavClick) {
        isNavClick = 0;
    } else {
        console.log(e);
        navigate.loadHashLink();
    }
});
