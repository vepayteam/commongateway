"use strict";

var isNavClick = 0;
var linklink = 0;
var navigate = {
    /**
     * Список услуг
     * @param grp
     * @param region
     */
    loadProvs: function (grp, region) {
        if (linklink) {
            linklink.abort();
        }

        region = typeof region !== 'undefined' ?  region : -1;
        let url = "/communal/groupform?id=" + grp;
        if (region !== -1) {
            url = url + "&region=" + region;
        }

        linklink = $.ajax({
            type: "POST",
            url: url,
            beforeSend: function () {
                $("#prov").html("<div class=\"text-center col-xs-12 margin-top100\"><i class=\"fa fa-spinner fa-spin fa-fw\"></i></div>");
            }
        }).done(function (data) {
            $("#prov").html(data.data);
            navigate.switchSelectedGroup(grp);
            navigate.changeHashLink("groupform?id=" + grp);
        });
    },

    /**
     * Каталог
     */
    loadCatalog: function () {
        if (linklink) {
            linklink.abort();
        }
        linklink = $.ajax({
            type: "POST",
            url: "/communal/catalogform",
            beforeSend: function () {
                $("#prov").html("<div class=\"text-center col-xs-12 margin-top100\"><i class=\"fa fa-spinner fa-spin fa-fw\"></i></div>");
            }
        }).done(function (data) {
            $("#prov").html(data.data);
            navigate.changeHashLink('catalogform');
        });
    },

    /**
     * Форма ввода реквизитов провайдера
     * @param prov
     * @param paramdata
     */
    loadProvForm: function (prov, paramdata) {
        if (linklink) {
            linklink.abort();
        }
        let data = '';
        if (paramdata !== undefined) {
            data = {idpay: paramdata};
        }
        linklink = $.ajax({
            type: "POST",
            url: "/communal/provform?id=" + prov,
            data: data,
            beforeSend: function () {
                $("#prov").html("<div class='text-center col-xs-12 margin-top100'><i class=\"fa fa-spinner fa-spin fa-fw\"></i></div>");
            }
        }).done(function (data) {
            $("#prov").html(data.data);

            if (data.opltkzcnt > 0) {
                $("#opltkzcnt_"+data.grp).html(data.opltkzcnt);
                $("#opltkzcntm_"+data.grp).html(data.opltkzcnt);
            } else {
                $("#opltkzcnt_"+data.grp).hide();
                $("#opltkzcntm_"+data.grp).hide();
            }

            navigate.changeHashLink("provform?id=" + prov);
        });
    },

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
        let link = window.location.hash.substr(1);
        if (linklink) {
            linklink.abort();
        }
        if (link.length > 1) {
            linklink = $.ajax({
                type: "POST",
                url: "/merchant/" + link,
                beforeSend: function () {
                    $("#prov").html("<div class='text-center col-xs-12 margin-top100'><i class=\"fa fa-spinner fa-spin fa-fw\"></i></div>");
                }
            }).done(function (data) {
                $("#prov").html(data.data);
                let grp = 0;
                if (data.grp) {
                    grp = data.grp;
                }
                navigate.switchSelectedGroup(grp);
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

    switchSelectedGroup: function (grp) {
        $("li.grp").removeClass("active");
        if (grp > 0) {
            $("li.grp").find('a[data-id=' + grp + ']').parent().addClass("active");
        }

    },

    findProv: function (grp, fform) {
        let form = $(fform).serialize();
        if (linklink) {
            linklink.abort();
        }
        linklink = $.ajax({
            type: 'POST',
            url: "/communal/searchprovs?grp="+grp,
            data: form,
            beforeSend: function () {
                $("#searchlist").html("<div class='text-center col-xs-12 margin-top10'><i class=\"fa fa-spinner fa-spin fa-fw\"></i></div>");
            },
            success: function (data) {
                $('#searchlist').html(data.data);
            },
            error: function (jqXHR) {
                console.log(jqXHR);
            }
        });

        $("body").off("click", "a.listprov").on("click", "a.listprov", function () {
            let prov = $(this).attr("data-id");
            let grp = $(this).attr("data-grp-id");
            navigate.switchSelectedGroup(grp);
            navigate.loadProvForm(prov);
        });

    },

    exportPay: function (idpay) {
        if (linklink) {
            linklink.abort();
        }

        linklink = $.ajax({
            type: "POST",
            url: "/merchant/exportpay",
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
