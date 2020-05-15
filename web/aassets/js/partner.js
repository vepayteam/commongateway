(function ($) {

    "use strict";

    let linklink = 0;
    let loginNav = {
        login: function () {

            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 1000
            };

            if (linklink) {
                linklink.abort();
            }

            linklink = $.ajax({
                type: "POST",
                url: '/partner/login',
                data: $('#loginform').serialize(),
                beforeSend: function () {
                },
                success: function (data) {
                    if (data.status !== 1) {
                        toastr.error("Неверный логин / пароль", "Ошибка");
                    } else {
                        window.location.href = '/partner/index';
                    }
                },
                error: function (error) {
                    if (error.status != 302) {
                        $('#loginerror').show();
                        toastr.error("Ошибка авторизации", "Ошибка");
                        window.location.reload();
                    }
                }
            });
        },

        changepassw: function () {

            $('#passwform').on('submit', function () {

                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 1000
                };

                if (linklink) {
                    linklink.abort();
                }
                linklink = $.ajax({
                    type: "POST",
                    url: '/partner/default/changepw',
                    data: $('#passwform').serialize(),
                    beforeSend: function () {
                        $('#formProfileUser').closest('.ibox-content').toggleClass('sk-loading');
                    },
                    success: function (data) {
                        $('#formProfileUser').closest('.ibox-content').toggleClass('sk-loading');
                        if (data.status == 1) {
                            toastr.success("OK", "Пароль изменен");
                            $('#passwform').reset();
                        } else {
                            toastr.error("Ошибка, введенные пароли не совпадают", "Ошибка");
                        }
                    },
                    error: function () {
                        $('#formProfileUser').closest('.ibox-content').toggleClass('sk-loading');
                        toastr.error("Ошибка запроса", "Ошибка");
                    }
                });
                return false;
            });
        }
    };

    let lk = {

        statlistreq: function (page) {

            if (linklink) {
                linklink.abort();
            }
            linklink = $.ajax({
                type: "POST",
                url: '/partner/stat/listdata?page=' + page,
                data: $('#statlistform').serialize(),
                beforeSend: function () {
                    $('#statlistform').closest('.ibox-content').toggleClass('sk-loading');
                },
                success: function (data) {
                    $('#statlistform').closest('.ibox-content').toggleClass('sk-loading');
                    if (data.status == 1) {
                        $('#statlistresult').html(data.data);
                    } else {
                        $('#statlistresult').html("<p class='text-center'>" + data.message + "</p>");
                    }
                },
                error: function () {
                    $('#statlistform').closest('.ibox-content').toggleClass('sk-loading');
                    $('#statlistresult').html("<p class='text-center'>Ошибка</p>");
                }
            });
        },

        statlist: function () {

            this.excerpt(); //логика работы модального окна по выписке.

            $('[name="calDay"],[name="calWeek"],[name="calMon"]').on('click', function () {
                $('[name="calDay"]').removeClass('active');
                $('[name="calWeek"]').removeClass('active');
                $('[name="calMon"]').removeClass('active');
                $(this).addClass('active');

                let now, today;
                now = moment(new Date()).hour(23).minute(59).seconds(59);
                today = moment(new Date()).hour(0).minute(0).seconds(0);
                if ($(this).attr('name') == "calDay") {
                    $('[name="datefrom"]').data("DateTimePicker").date(today);
                    $('[name="dateto"]').data("DateTimePicker").date(now);
                } else if ($(this).attr('name') == "calWeek") {
                    today = today.subtract(7, 'days');
                    $('[name="datefrom"]').data("DateTimePicker").date(today);
                    $('[name="dateto"]').data("DateTimePicker").date(now);
                } else if ($(this).attr('name') == "calMon") {
                    today = today.subtract(1, 'months');
                    $('[name="datefrom"]').data("DateTimePicker").date(today);
                    $('[name="dateto"]').data("DateTimePicker").date(now);
                }
            });

            $('[name="datefrom"],[name="dateto"]').datetimepicker({
                format: 'DD.MM.YYYY HH:mm',
                showClose: true
            });

            $('#statlistform').on('submit', function () {
                lk.statlistreq(0);
                return false;
            });

            $('#statlistresult').on('click', '[data-action="cancelpay"]', function () {
                let idpay = $(this).attr('data-id');
                swal({
                    title: "Подтвердите отмену платежа",
                    text: "Послу отмены платежи средства будут возвращены клиенту!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Да, отменить!",
                    cancelButtonText: "Не отменять",
                    closeOnConfirm: false
                }, function () {

                    if (linklink) {
                        linklink.abort();
                    }
                    linklink = $.ajax({
                        type: "POST",
                        url: '/partner/stat/reversorder',
                        data: {'id': idpay},
                        beforeSend: function () {
                            $('#statlistform').closest('.ibox-content').toggleClass('sk-loading');
                        },
                        success: function (data) {
                            $('#statlistform').closest('.ibox-content').toggleClass('sk-loading');
                            if (data.status == 1) {
                                swal({
                                    title: "ОК",
                                    text: data.message,
                                    type: "success"
                                }, function () {
                                    $('#statlistform').trigger('submit');
                                });
                            } else {
                                swal({
                                    title: "Ошибка",
                                    text: data.message,
                                    type: "error"
                                }, function () {
                                    $('#statlistform').trigger('submit');
                                });
                            }
                        },
                        error: function () {
                            $('#statlistform').closest('.ibox-content').toggleClass('sk-loading');
                            swal({
                                title: "Ошибка",
                                text: "Ошибка запроса",
                                type: "error"
                            }, function () {
                                $('#statlistform').trigger('submit');
                            });
                        }
                    });
                });
                return false;
            });
        },

        excerpt: function () {
            $(document).on('click', '.excerpt', function (e) {
                e.preventDefault();
                let id = $(this).attr('data-id');
                $.ajax({
                    data: 'id=' + id,
                    method: 'post',
                    url: '/partner/stat/excerpt',
                    success: function (answer) {
                        if (answer.status === 1) {
                            $('.pdf-modal .modal-body').html(answer.data);
                            $('.pdf-modal .modal-footer input:first-child').attr('data-id', id);
                            $('.pdf-modal .modal-footer input:last-child').attr('data-id', id);
                            $('.pdf-modal .modal-header h3').html('Операция «' + answer.message + '»');
                            $('.pdf-modal').modal('show');
                        } else {
                            $('#statlistform').closest('.ibox-content').toggleClass('sk-loading');
                            swal({
                                title: "Ошибка",
                                text: answer.message,
                                type: "error"
                            }, function () {
                                $('#statlistform').closest('.ibox-content').toggleClass('sk-loading');
                            });
                        }
                    },
                    error: function (error) {
                        $('#statlistform').closest('.ibox-content').toggleClass('sk-loading');
                        swal({
                            title: "Ошибка",
                            text: "Ошибка на сервере. Пожалуйста обратитесь в тех. поддержку.",
                            type: "error"
                        }, function () {
                            $('#statlistform').closest('.ibox-content').toggleClass('sk-loading');
                        });
                    },
                });
            });

            $(document).on('click', '.pdf-modal .modal-footer input:first-child', function () {
                let id = $(this).attr('data-id');
                window.open('/partner/stat/export-excerpt/' + id);
            });

            $(document).on('click', '.pdf-modal .modal-footer input:last-child', function () {
                let id = $(this).attr('data-id');
                let email = $(this).prev().val();
                $.ajax({
                    data: 'id=' + id + '&email=' + email,
                    method: 'post',
                    url: '/partner/stat/send-excerpt',
                    success: function (answer) {
                        if (answer.status == 1) {
                            swal({
                                title: "ОК",
                                text: answer.message,
                                type: "success"
                            }, function () {
                                $('#statlistform').trigger('submit');
                                $('.pdf-modal .modal-footer input:nth-child(2)').css('color', 'rgb(103, 106, 108)');
                            });
                        } else {
                            swal({
                                title: "Ошибка",
                                text: answer.data.email,
                                type: "error"
                            }, function () {
                                $('#statlistform').trigger('submit');
                                $('.pdf-modal .modal-footer input:nth-child(2)').css('color', 'red');
                            });
                        }
                    },
                    error: function (error) {
                        $('#statlistform').closest('.ibox-content').toggleClass('sk-loading');
                        swal({
                            title: "Ошибка",
                            text: "Ошибка на сервере. Пожалуйста обратитесь в тех. поддержку.",
                            type: "error"
                        }, function () {
                            $('#statlistform').closest('.ibox-content').toggleClass('sk-loading');
                        });
                    }
                });
            });

            $(document).on('change', '.pdf-modal .modal-footer input:nth-child(2)', function () {
                $(this).css('color', 'rgb(103, 106, 108)');
            });
        },

        mfodistribution: function() {
            $(document).on('submit','#partner-distribution', function(e) {
                e.preventDefault();
                let data = $(this).serialize();
                $.ajax({
                    method: 'post',
                    url: '/partner/settings/save-distribution',
                    data: data,
                    success: function(answer){
                        if (answer.status === 1){
                            swal({
                                title: "ОК",
                                text: answer.message,
                                type: "success"
                            }, function () {
                                $('#statlistform').trigger('submit');
                            });
                        }else{
                            swal({
                                title: "Ошибка",
                                text: answer.data.email[0].error,
                                type: "error"
                            }, function () {
                                $('#statlistform').trigger('submit');
                                for(var i=0; i< answer.data.email.length; i++){
                                    let errorInput = $('#partner-distribution input[name= "email['+answer.data.email[i].id+']"]');
                                    errorInput.parent().addClass('has-error');
                                }
                            });
                        }
                    },
                    error: function (error) {
                        $('#statlistform').closest('.ibox-content').toggleClass('sk-loading');
                        swal({
                            title: "Ошибка",
                            text: "Ошибка на сервере. Пожалуйста обратитесь в тех. поддержку.",
                            type: "error"
                        }, function () {
                            $('#statlistform').closest('.ibox-content').toggleClass('sk-loading');
                        });
                    }
                });

                return false;
            });
        },

        otchlist: function () {

            $('[name="datefrom"],[name="dateto"]').datetimepicker({
                format: 'DD.MM.YYYY HH:mm',
                showClose: true
            });

            $('#otchlistform').on('submit', function () {
                if (linklink) {
                    linklink.abort();
                }
                linklink = $.ajax({
                    type: "POST",
                    url: '/partner/stat/otchdata',
                    data: $('#otchlistform').serialize(),
                    beforeSend: function () {
                        $('#otchlistform').closest('.ibox-content').toggleClass('sk-loading');
                    },
                    success: function (data) {
                        $('#otchlistform').closest('.ibox-content').toggleClass('sk-loading');
                        $('#otchlistresult').html(data);
                    },
                    error: function () {
                        $('#otchlistform').closest('.ibox-content').toggleClass('sk-loading');
                        $('#otchlistresult').html('Ошибка');
                    }
                });
                return false;
            });
        },

        statgraph: function () {

            $('[name="datefrom"],[name="dateto"]').datetimepicker({
                format: 'DD.MM.YYYY'
            });

            $('#saleform').on('submit', function () {
                lk.statgraphload();
                return false;
            });

            lk.statgraphload();
        },

        statgraphload: function () {
            if (linklink) {
                linklink.abort();
            }
            linklink = $.ajax({
                type: "POST",
                url: '/partner/stat/saledata',
                data: $('#saleform').serialize(),
                beforeSend: function () {
                    $("#sale-graph").empty();
                    $('#saleform').closest('.ibox-content').toggleClass('sk-loading');
                },
                success: function (data) {
                    $('#saleform').closest('.ibox-content').toggleClass('sk-loading');
                    if (data.status == 1) {
                        Morris.Line({
                            element: 'sale-graph',
                            data: data.data,
                            xkey: 'x',
                            ykeys: ['a'],
                            labels: ['Сумма'],
                            lineColors: ['#f46f2a'],
                            hideHover: 'auto',
                            parseTime: false,
                            resize: true
                        });
                    } else {
                        $('#sale-graph').html(data.message);
                    }
                },
                error: function () {
                    $('#saleform').closest('.ibox-content').toggleClass('sk-loading');
                    $('#sale-graph').html("Ошибка запроса");
                }
            });

        },

        statgraphdraft: function () {

            $('[name="datefrom"],[name="dateto"]').datetimepicker({
                format: 'DD.MM.YYYY'
            });

            $('#saleformdraft').on('submit', function () {
                lk.statgraphdraftload();
                return false;
            });

            lk.statgraphdraftload();
        },

        statgraphdraftload: function () {
            if (linklink) {
                linklink.abort();
            }
            linklink = $.ajax({
                type: "POST",
                url: '/partner/stat/saledraftdata',
                data: $('#saleformdraft').serialize(),
                beforeSend: function () {
                    $("#sale-graphdraft").empty();
                    $('#saleformdraft').closest('.ibox-content').toggleClass('sk-loading');
                },
                success: function (data) {
                    $('#saleformdraft').closest('.ibox-content').toggleClass('sk-loading');
                    if (data.status == 1) {
                        Morris.Line({
                            element: 'sale-graphdraft',
                            data: data.data,
                            xkey: 'x',
                            ykeys: ['a'],
                            labels: ['Средний чек'],
                            lineColors: ['#f46f2a'],
                            hideHover: 'auto',
                            parseTime: false,
                            resize: true
                        });
                    } else {
                        $('#sale-graphdraft').html(data.message);
                    }
                },
                error: function () {
                    $('#saleformdraft').closest('.ibox-content').toggleClass('sk-loading');
                    $('#sale-graphdraft').html("Ошибка запроса");
                }
            });
        },

        statgraphkonvers: function () {
            $('[name="datefrom"],[name="dateto"]').datetimepicker({
                format: 'DD.MM.YYYY'
            });

            $('#saleformkonvers').on('submit', function () {
                lk.statgraphkonversload();
                return false;
            });

            lk.statgraphkonversload();
        },

        statgraphkonversload: function () {
            if (linklink) {
                linklink.abort();
            }
            linklink = $.ajax({
                type: "POST",
                url: '/partner/stat/salekonversdata',
                data: $('#saleformkonvers').serialize(),
                beforeSend: function () {
                    $("#sale-graphkonvers").empty();
                    $('#saleformkonvers').closest('.ibox-content').toggleClass('sk-loading');
                },
                success: function (data) {
                    $('#saleformkonvers').closest('.ibox-content').toggleClass('sk-loading');
                    if (data.status == 1) {
                        Morris.Line({
                            element: 'sale-graphkonvers',
                            data: data.data,
                            xkey: 'x',
                            ykeys: ['a'],
                            labels: ['Конверсия'],
                            lineColors: ['#f46f2a'],
                            hideHover: 'auto',
                            parseTime: false,
                            resize: true
                        });
                    } else {
                        $('#sale-graphkonvers').html(data.message);
                    }
                },
                error: function () {
                    $('#saleformkonvers').closest('.ibox-content').toggleClass('sk-loading');
                    $('#sale-graphkonvers').html("Ошибка запроса");
                }
            });
        },

        statgraphplatelshik: function () {

            $('[name="datefrom"],[name="dateto"]').datetimepicker({
                format: 'DD.MM.YYYY'
            });
            $('#platelshikform').on('submit', function () {
                lk.statgraphplatelshikload();
                return false;
            });
            lk.statgraphplatelshikload();
        },

        statgraphplatelshikload: function () {
            if (linklink) {
                linklink.abort();
            }
            linklink = $.ajax({
                type: "POST",
                url: '/partner/stat/platelshikdata',
                data: $('#platelshikform').serialize(),
                beforeSend: function () {
                    $("#plat-graph-error").empty();
                    $("#plat-graph-country").empty();
                    $("#plat-graph-city").empty();
                    $("#plat-graph-bank").empty();
                    $("#plat-graph-card").empty();
                    $('#platelshikform').closest('.ibox-content').toggleClass('sk-loading');
                },
                success: function (data) {
                    $('#platelshikform').closest('.ibox-content').toggleClass('sk-loading');
                    if (data.status == 1) {
                        Morris.Donut({
                            element: 'plat-graph-country',
                            data: data.country,
                            colors: ['#f46f2a'],
                            resize: true
                        });
                        Morris.Donut({
                            element: 'plat-graph-city',
                            data: data.city,
                            colors: ['#f40f36'],
                            resize: true
                        });
                        Morris.Donut({
                            element: 'plat-graph-bank',
                            data: data.bank,
                            colors: ['#f4a84c'],
                            resize: true
                        });
                        Morris.Donut({
                            element: 'plat-graph-card',
                            data: data.card,
                            colors: ['#a500f4'],
                            resize: true
                        });
                    } else {
                        $('#plat-graph-error').html(data.message);
                    }
                },
                error: function () {
                    $('#platelshikform').closest('.ibox-content').toggleClass('sk-loading');
                    $('#plat-graph-error').html("Ошибка запроса");
                }
            });
        },

        statrekurrent: function () {
            $('[name="datefrom"],[name="dateto"]').datetimepicker({
                format: 'MM.YYYY'
            });
            $('#recurrentform').on('submit', function () {
                lk.statrekurrentload();
                return false;
            });
            lk.statrekurrentload();
        },

        statrekurrentload: function () {
            if (linklink) {
                linklink.abort();
            }
            linklink = $.ajax({
                type: "POST",
                url: '/partner/stat/recurrentpaysdata',
                data: $('#recurrentform').serialize(),
                beforeSend: function () {
                    $("#recurrent-graph").empty();
                    $('#recurrentform').closest('.ibox-content').toggleClass('sk-loading');
                },
                success: function (data) {
                    $('#recurrentform').closest('.ibox-content').toggleClass('sk-loading');
                    if (data.status == 1) {
                        Morris.Line({
                            element: 'recurrent-graph',
                            data: data.data,
                            xkey: 'x',
                            ykeys: ['a'],
                            labels: [data.label],
                            lineColors: ['#f46f2a'],
                            hideHover: 'auto',
                            parseTime: false,
                            resize: true
                        });
                    } else {
                        $('#recurrent-graph').html(data.message);
                    }
                },
                error: function () {
                    $('#recurrentform').closest('.ibox-content').toggleClass('sk-loading');
                    $('#recurrent-graph').html("Ошибка запроса");
                }
            });
        },

        recurrentcard: function () {
            $('[name="datefrom"],[name="dateto"]').datetimepicker({
                format: 'DD.MM.YYYY'
            });
            $('#recurrentcardform').on('submit', function (e) {
                e.preventDefault();
                lk.recurrentcardformload();
                return false;
            });
            lk.recurrentcardformload();
        },

        recurrentcardformload: function () {
            if (linklink) {
                linklink.abort();
            }
            linklink = $.ajax({
                type: "POST",
                url: '/partner/stat/recurrentcarddata',
                data: $('#recurrentcardform').serialize(),
                beforeSend: function () {
                    $('#recurrentcardform').closest('.ibox-content').toggleClass('sk-loading');
                },
                success: function (result) {
                    $('#recurrentcardform').closest('.ibox-content').toggleClass('sk-loading');
                    if (result.status == 1) {
                        $('#recurrentcardresult').html(result.data);
                    } else {
                        $('#recurrentcardresult').html(result.message);
                    }
                },
                error: function (data) {
                    $('#recurrentcardform').closest('.ibox-content').toggleClass('sk-loading');
                    $('#recurrentcardresult').html("Ошибка запроса");
                }
            });
        },

        mfobalance: function () {
            $('[name="calDay"],[name="calWeek"],[name="calMon"]').on('click', function () {
                $('[name="calDay"]').removeClass('active');
                $('[name="calWeek"]').removeClass('active');
                $('[name="calMon"]').removeClass('active');
                $(this).addClass('active');

                let now, today;
                now = moment(new Date()).hour(23).minute(59).seconds(59);
                today = moment(new Date()).hour(0).minute(0).seconds(0);
                if ($(this).attr('name') == "calDay") {
                    $('[name="datefrom"]').data("DateTimePicker").date(today);
                    $('[name="dateto"]').data("DateTimePicker").date(now);
                } else if ($(this).attr('name') == "calWeek") {
                    today = today.subtract(7, 'days');
                    $('[name="datefrom"]').data("DateTimePicker").date(today);
                    $('[name="dateto"]').data("DateTimePicker").date(now);
                } else if ($(this).attr('name') == "calMon") {
                    today = today.subtract(1, 'months');
                    $('[name="datefrom"]').data("DateTimePicker").date(today);
                    $('[name="dateto"]').data("DateTimePicker").date(now);
                }
            });

            $('[name="datefrom"],[name="dateto"]').datetimepicker({
                format: 'DD.MM.YYYY HH:mm',
                showClose: true
            });

            $('#mfosumlistform').on('submit', function () {
                lk.mfobalancesorderreq(0);
                return false;
            });

        },

        mfobalancesorderreq: function (page) {

            if (linklink) {
                linklink.abort();
            }
            linklink = $.ajax({
                type: "POST",
                url: '/partner/mfo/balanceorder?page=' + page,
                data: $('#mfosumlistform').serialize(),
                beforeSend: function () {
                    $('#mfosumlistform').closest('.ibox-content').toggleClass('sk-loading');
                },
                success: function (result) {
                    $('#mfosumlistform').closest('.ibox-content').toggleClass('sk-loading');
                    if (result.status == 1) {
                        $('#mfobalanceresult').html(result.data);
                        $('#vypostbeg').html(result.ostbeg);
                        $('#vypostend').html(result.ostend);
                    }
                },
                error: function (data) {
                    $('#mfosumlistform').closest('.ibox-content').toggleClass('sk-loading');
                    $('#mfobalanceresult').html("Ошибка");
                }
            });
        },

        mfosettings: function () {
            $('#mfosettings').on('submit', function (e) {
                e.preventDefault();
                let form = $('#mfosettings');
                let loader = form.closest('.ibox-content');

                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 1000
                };

                if (linklink) {
                    linklink.abort();
                }
                linklink = $.ajax({
                    type: "POST",
                    url: '/partner/settings/settingssave',
                    data: form.serialize(),
                    beforeSend: function () {
                        loader.toggleClass('sk-loading');
                    },
                    success: function (data) {
                        loader.toggleClass('sk-loading');
                        if (data.status == 1) {
                            toastr.success("OK", "Изменения сохранены");
                            $('#mfosettings').reset();
                        } else {
                            toastr.error(data.message, "Ошибка");
                        }
                    },
                    error: function (data) {
                        loader.toggleClass('sk-loading');
                        toastr.error("Ошибка запроса", "Ошибка");
                    }
                });
                return false;
            });
        },

        mfoalarms: function () {
            $('#alarmssettings').on('submit', function (e) {
                e.preventDefault();
                let form = $('#alarmssettings');
                let loader = form.closest('.ibox-content');

                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 1000
                };

                if (linklink) {
                    linklink.abort();
                }
                linklink = $.ajax({
                    type: "POST",
                    url: '/partner/settings/alarmssave',
                    data: form.serialize(),
                    beforeSend: function () {
                        loader.toggleClass('sk-loading');
                    },
                    success: function (data) {
                        loader.toggleClass('sk-loading');
                        if (data.status == 1) {
                            toastr.success("OK", "Изменения сохранены");
                            $('#mfosettings').reset();
                        } else {
                            toastr.error(data.message, "Ошибка");
                        }
                    },
                    error: function (data) {
                        loader.toggleClass('sk-loading');
                        toastr.error("Ошибка запроса", "Ошибка");
                    }
                });
                return false;
            });
        },

        orderlist: function () {

            $('[name="datefrom"],[name="dateto"]').datetimepicker({
                format: 'DD.MM.YYYY'
            });

            $('#orderform').on('submit', function () {
                if (linklink) {
                    linklink.abort();
                }
                linklink = $.ajax({
                    type: "POST",
                    url: '/partner/order/list',
                    data: $('#orderform').serialize(),
                    beforeSend: function () {
                        $('#orderform').closest('.ibox-content').toggleClass('sk-loading');
                    },
                    success: function (result) {
                        $('#orderform').closest('.ibox-content').toggleClass('sk-loading');
                        if (result.status == 1) {
                            $('#orderlistresult').html(result.data);
                        } else {
                            $('#orderlistresult').html(result.message);
                        }
                    },
                    error: function (data) {
                        $('#orderform').closest('.ibox-content').toggleClass('sk-loading');
                        $('#orderlistresult').html("Ошибка запроса");
                    }
                });
                return false;
            });
        },

        orderlistdata: function () {
            $('#orderlisttable').off().on('click', '[data-action="cancelorder"]', function () {
                let id = $(this).attr('data-id');
                if (linklink) {
                    linklink.abort();
                }
                linklink = $.ajax({
                    type: "POST",
                    url: '/partner/order/cancel',
                    data: {'id': id},
                    beforeSend: function () {
                        $('#orderform').closest('.ibox-content').toggleClass('sk-loading');
                    },
                    success: function (result) {
                        $('#orderform').closest('.ibox-content').toggleClass('sk-loading');
                        if (result.status == 1) {
                            $('#orderform').trigger('submit');
                        } else {
                            toastr.error(result.message, "Ошибка");
                        }
                    },
                    error: function (data) {
                        $('#orderform').closest('.ibox-content').toggleClass('sk-loading');
                        toastr.error("Ошибка запроса", "Ошибка");
                    }
                });
                return false;
            }).on('click', '[data-action="resendorder"]', function () {
                let id = $(this).attr('data-id');
                if (linklink) {
                    linklink.abort();
                }
                linklink = $.ajax({
                    type: "POST",
                    url: '/partner/order/resend',
                    data: {'id': id},
                    beforeSend: function () {
                        $('#orderform').closest('.ibox-content').toggleClass('sk-loading');
                    },
                    success: function (result) {
                        $('#orderform').closest('.ibox-content').toggleClass('sk-loading');
                        if (result.status == 1) {
                            toastr.success("OK", "Ссылка для оплаты повторно отправлена");
                        } else {
                            toastr.error(result.message, "Ошибка");
                        }
                    },
                    error: function (data) {
                        $('#orderform').closest('.ibox-content').toggleClass('sk-loading');
                        toastr.error("Ошибка запроса", "Ошибка");
                    }
                });

            });
        },

        orderadd: function () {
            $('#saveorder').on('click', function () {

                let form = $('#formAddOrder');
                let loader = form.closest('.ibox-content');

                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 1000
                };

                if (linklink) {
                    linklink.abort();
                }
                linklink = $.ajax({
                    type: "POST",
                    url: '/partner/order/save',
                    data: form.serialize(),
                    beforeSend: function () {
                        loader.toggleClass('sk-loading');
                    },
                    success: function (data) {
                        loader.toggleClass('sk-loading');
                        if (data.status == 1) {
                            toastr.success("OK", "Счет создан");
                            window.location.href = "/partner/order";
                        } else {
                            toastr.error(data.message, "Ошибка");
                        }
                    },
                    error: function (data) {
                        loader.toggleClass('sk-loading');
                        toastr.error("Ошибка запроса", "Ошибка");
                    }
                });
                return false;
            });
        },

        notiflist: function () {
            $('[name="datefrom"],[name="dateto"]').datetimepicker({
                format: 'DD.MM.YYYY'
            });

            $('#notiflistform').on('submit', function () {
                if (linklink) {
                    linklink.abort();
                }
                linklink = $.ajax({
                    type: "POST",
                    url: '/partner/callback/listitems',
                    data: $('#notiflistform').serialize(),
                    beforeSend: function () {
                        $('#notiflistform').closest('.ibox-content').toggleClass('sk-loading');
                    },
                    success: function (result) {
                        $('#notiflistform').closest('.ibox-content').toggleClass('sk-loading');
                        if (result.status == 1) {
                            $('#notiflistresult').html(result.data);
                        } else {
                            $('#notiflistresult').html(result.message);
                        }
                    },
                    error: function (data) {
                        $('#notiflistform').closest('.ibox-content').toggleClass('sk-loading');
                        $('#notiflistresult').html("Ошибка запроса");
                    }
                });
                return false;
            });

            $('#notiflistresult').on('click', '[data-action="repeatnotif"]', function () {
                let idnotif = $(this).attr('data-id');
                if (linklink) {
                    linklink.abort();
                }
                linklink = $.ajax({
                    type: "POST",
                    url: '/partner/callback/repeat',
                    data: {'id': idnotif},
                    beforeSend: function () {
                        $('#notiflistform').closest('.ibox-content').toggleClass('sk-loading');
                    },
                    success: function (data) {
                        $('#notiflistform').closest('.ibox-content').toggleClass('sk-loading');
                        if (data.status == 1) {
                            toastr.success("OK", data.message);
                            $('#notiflistform').trigger('submit');
                        } else {
                            toastr.error("Ошибка", data.message);
                        }
                    },
                    error: function () {
                        $('#notiflistform').closest('.ibox-content').toggleClass('sk-loading');
                        toastr.error("Ошибка запроса", "Ошибка");
                    }
                });
                return false;
            });
        },

        comisotchet: function () {

            $('[name="datefrom"],[name="dateto"]').datetimepicker({
                format: 'DD.MM.YYYY HH:mm',
                showClose: true
            });

            $('#veekenddays').on('submit', function () {
                if (linklink) {
                    linklink.abort();
                }
                linklink = $.ajax({
                    type: "POST",
                    url: '/partner/admin/saveveekenddays',
                    data: $('#veekenddays').serialize(),
                    beforeSend: function () {
                        $('#veekenddays').closest('.ibox-content').toggleClass('sk-loading');
                    },
                    success: function (data) {
                        $('#veekenddays').closest('.ibox-content').toggleClass('sk-loading');
                        if (data.status == 1) {
                            toastr.success("OK", data.message);
                        } else {
                            toastr.error("Ошибка", data.message);
                        }
                    },
                    error: function () {
                        $('#veekenddays').closest('.ibox-content').toggleClass('sk-loading');
                        toastr.error("Ошибка", "Ошибка запроса.");
                    }
                });
                return false;
            });


            $('#comisotchetform').on('submit', function () {
                if (linklink) {
                    linklink.abort();
                }
                linklink = $.ajax({
                    type: "POST",
                    url: '/partner/admin/comisotchetdata',
                    data: $('#comisotchetform').serialize(),
                    beforeSend: function () {
                        $('#comisotchetresult').closest('.ibox-content').toggleClass('sk-loading');
                    },
                    success: function (data) {
                        $('#comisotchetform').closest('.ibox-content').toggleClass('sk-loading');
                        if (data.status == 1) {
                            $('#comisotchetresult').html(data.data);
                        } else {
                            $('#comisotchetresult').html(data.message);
                        }
                    },
                    error: function () {
                        $('#comisotchetform').closest('.ibox-content').toggleClass('sk-loading');
                        $('#comisotchetresult').html('Ошибка');
                    }
                });
                return false;
            });

            $('#comisotchetresult').off().on('click', 'a[data-action="vyvyodsum"]', function () {

                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 1000
                };

                let idPartner = $(this).attr('data-id');
                let type = $(this).attr('data-type');
                let dtfrom = $('[name="datefrom"]').val();
                let dtto = $('[name="dateto"]').val();
                let summ = $(this).attr('data-summ');

                if (!confirm("Вывести вознаграждение " + (summ / 100.00).toFixed(2) + " руб.?")) {
                    return false;
                }

                //вывод
                linklink = $.ajax({
                    type: "POST",
                    url: '/partner/admin/vyvodvoznag',
                    data: {'partner': idPartner, 'type': type, 'isCron': 0, 'summ': summ, 'datefrom': dtfrom, 'dateto': dtto},
                    beforeSend: function () {
                        $('#comisotchetresult').closest('.ibox-content').toggleClass('sk-loading');
                    },
                    success: function (data) {
                        $('#comisotchetform').closest('.ibox-content').toggleClass('sk-loading');
                        if (data.status == 1) {
                            toastr.success("OK", data.message);
                            $('#comisotchetform').trigger('submit');
                        } else {
                            toastr.error("Ошибка", data.message);
                        }
                    },
                    error: function () {
                        $('#comisotchetform').closest('.ibox-content').toggleClass('sk-loading');
                        toastr.error("Ошибка", "Ошибка запроса.");
                    }
                });

                return false;
            });

            $('#modal-perevod').on('shown.bs.modal', function () {
                $('#perevodform')[0].reset();
                $('[name="Perechislen[IdPartner]"]').trigger('change');
            });

            $('[name="Perechislen[IdPartner]"]').on('change', function () {

                let idPartner = $(this).val();

                linklink = $.ajax({
                    type: "POST",
                    url: '/partner/admin/perevodaginfo',
                    data: {'partner': idPartner},
                    beforeSend: function () {
                        $('#perevodpartner').prop('disabled', true);
                        $('[name="Perechislen[IdPartner]"]').prop('disabled', true);
                    },
                    success: function (data) {
                        $('[name="Perechislen[IdPartner]"]').prop('disabled', false);
                        if (data.status == 1) {
                            $('#perevodpartner').prop('disabled', false);
                            if (data.data.schettcb.length === 0) {
                                $('#TypeSchet1')
                                    .prop('disabled', true)
                                    .prop('checked', false);
                                $('#TypeSchet1Info').html('Счет отсутствует!');
                            } else {
                                $('#TypeSchet1')
                                    .prop('disabled', false)
                                    .prop('checked', true);
                                $('#TypeSchet1Info').html(data.data.schettcb);
                            }
                            if (data.data.schetrs.length === 0) {
                                $('#TypeSchet2').prop('disabled', true);
                                $('#TypeSchet2Info').html('Счет отсутствует!');
                            } else {
                                $('#TypeSchet2').prop('disabled', false);
                                if (data.data.schettcb.length === 0) {
                                    $('#TypeSchet2').prop('checked', true);
                                }
                                $('#TypeSchet2Info').html(data.data.schetrs + " " + data.data.schetinfo + ", БИК " + data.data.schetbik);
                            }

                            $('#infoschet').html(data.data.urlico + "\r\nсо счета " + data.data.schetfrom);

                            if (data.data.schettcb.length === 0 && data.data.schetrs.length === 0) {
                                $('#perevodpartner').prop('disabled', true);
                            } else {
                                $('#perevodpartner').prop('disabled', false);
                            }

                            $('#balancepartner').html(data.data.balance.toFixed(2));

                        } else {
                            $('#perevodpartner').prop('disabled', true);
                            toastr.error("Ошибка", data.message);
                        }
                    },
                    error: function () {
                        $('[name="Perechislen[IdPartner]"]').prop('disabled', false);
                        toastr.error("Ошибка", "Ошибка запроса.");
                    }
                });
            });

            $('#perevodpartner').on('click', function () {
                let balance = parseFloat($('#balancepartner').html());
                let sum = parseFloat($('#perevodform').find('#Summ').val());
                if (isNaN(sum) || sum < 1 || sum > balance) {
                    toastr.error("Ошибка", "Неверная сумма перевода");
                    return false;
                }

                let ct = "Перевести средства контрагенту?\r\n\r\n"+$('#infoschet').html()+"\r\nна счет: ";
                if ($('#TypeSchet1').prop('checked')) {
                    ct += $('#perevodform').find('#TypeSchet1Info').html()+" (выдача)";
                } else {
                    ct += $('#perevodform').find('#TypeSchet2Info').html()+" (р/с)";
                }
                ct += "\r\nСумма: " + sum.toFixed(2) + " руб.";

                swal({
                    title: "Подтвердите перевод",
                    text: ct,
                    showCancelButton: true,
                    confirmButtonColor: "#f46f2a",
                    confirmButtonText: "Подтвердить",
                    cancelButtonText: "Отмена",
                    closeOnConfirm: true
                }, function() {
                    linklink = $.ajax({
                        type: "POST",
                        url: '/partner/admin/perevodacreate',
                        data: $('#perevodform').serialize(),
                        beforeSend: function () {
                            $('#perevodpartner').prop('disabled', true);
                        },
                        success: function (data) {
                            $('#perevodpartner').prop('disabled', false);
                            if (data.status == 1) {
                                swal({title: "OK", text: data.message, type: "success"}, function () {
                                    $('#perevodform')[0].reset();
                                    $('#modal-perevod').modal('hide');
                                    $('#comisotchetform').trigger('submit');
                                });
                            } else {
                                toastr.error("Ошибка", data.message);
                            }
                        },
                        error: function () {
                            $('#perevodpartner').prop('disabled', false);
                            toastr.error("Ошибка", "Ошибка запроса.");
                        }
                    });
                });
                return false;
            });
        },

        banksave: function () {
            $('#submitbanks').on('click', function () {
                if (linklink) {
                    linklink.abort();
                }
                linklink = $.ajax({
                    type: "POST",
                    url: '/partner/admin/banksave',
                    data: $('#banks').serialize(),
                    beforeSend: function () {
                        $('#banks').closest('.ibox-content').toggleClass('sk-loading');
                    },
                    success: function (data) {
                        $('#banks').closest('.ibox-content').toggleClass('sk-loading');
                        if (data.status == 1) {
                            toastr.success("OK", "Комиссии сохранены");
                        } else {
                            toastr.error(data.message, "Ошибка");
                        }
                    },
                    error: function () {
                        $('#banks').closest('.ibox-content').toggleClass('sk-loading');
                        toastr.error("Ошибка запроса", "Ошибка");
                    }
                });

                return false;
            });
        },

        registerpartner: function () {
            let form = $('#registerpartnerform');
            form.on('submit', function (e) {
                e.preventDefault();

                if (linklink) {
                    linklink.abort();
                }
                linklink = $.ajax({
                    type: "POST",
                    url: '/partner/partner/partner-add',
                    data: form.serialize(),
                    beforeSend: function () {
                        $('#submitregpartner').prop('disabled', true);
                    },
                    success: function (data) {
                        $('#submitregpartner').prop('disabled', false);
                        if (data.status == 1) {
                            toastr.success("OK", "Партнер добавлен");
                            $('#modal-regpartner').modal('hide').on('hidden.bs.modal', function () {
                                window.location.href = '/partner/partner/partner-edit/' + data.id;
                            });
                        } else {
                            toastr.error(data.message, "Ошибка");
                        }
                    },
                    error: function () {
                        $('#submitregpartner').prop('disabled', false);
                        toastr.error("Ошибка запроса", "Ошибка");
                    }
                });

                return false;
            });
        },

        listpartners: function () {
            $('#partnertypesel0,#partnertypesel1').on('change', function () {
                let selp = $(this).val();
                if (selp >= 0) {
                    $('tr[data-parttype = "' + selp + '"]').show();
                    $('tr[data-parttype][data-parttype != "' + selp + '"]').hide();
                } else {
                    $('tr[parttype]').show();
                }
            });
            $('#partnertypesel0').trigger('change');

            $('#listpartners').on('click', 'tr[data-click]', function () {
                let link = $(this).attr("data-click");
                window.location.href = link;
            });

        },

        editpartner: function () {

            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 1000
            };

            $('#formEditCommonCont').on('submit', function () {
                let form = $('#formEditCommonCont');
                commInfo.saveKont(form);
                return false;
            });

            $('#formEditTehCont').on('submit', function () {
                let form = $('#formEditTehCont');
                commInfo.saveKont(form);
                return false;
            });

            $('#formEditFinansCont').on('submit', function () {
                let form = $('#formEditFinansCont');
                commInfo.saveKont(form);
                return false;
            });

            let url = document.location.toString();
            if (url.match('#tab')) {
                $('.nav-tabs a[href="#' + url.split('#')[1] + '"]').tab('show');
            }

            $('#btnEditRekviz').on('click', function () {
                let form = $('form[name="formEditRekviz"]');
                $.ajax({
                    url: '/partner/partner/rekviz-save',
                    method: 'POST',
                    data: form.serialize(),
                    beforeSend: function () {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                    },
                    success: function (data) {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                        if (data.status == 0) {
                            toastr.error(data.message, "Ошибка");
                        } else {
                            toastr.success("OK", "Реквизиты сохранены");
                        }
                    },
                    error: function () {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                    }
                });
                return false;
            });

            $('#btnEditPartner').on('click',function (e) {
                e.preventDefault();
                lk.savepatnerpart($('#formEditPartner'));
                return false;
            });

            $('#btnEditPartnerStatus').on('click',function (e) {
                e.preventDefault();
                lk.savepatnerpart($('#formEditPartnerStatus'));
                return false;
            });

            $('#btnEditPartnerTkb').on('click',function (e) {
                e.preventDefault();
                lk.savepatnerpart($('#formEditPartnerTkb'));
                return false;
            });

            $('#btnEditPartnerIntegr').on('click',function (e) {
                e.preventDefault();
                lk.savepatnerpart($('#formEditPartnerIntegr'));
                return false;
            });

            $('#btnEditPartnerApplepay').on('click',function (e) {
                e.preventDefault();
                let form = new FormData($("#formEditPartnerApplepay")[0]);
                $.ajax({
                    type: "POST",
                    url: '/partner/partner/partner-applepay-save',
                    data: form,
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                    },
                    success: function (data) {
                        if (data.status == 0) {
                            toastr.error(data.message, "Ошибка");
                        } else {
                            toastr.success("OK", "Ключи Apple Pay сохранены");
                        }
                    },
                    error: function () {
                        toastr.error("Ошибка запроса", "Ошибка");
                    }
                });
                return false;
            });

            $('#btnEditPartnerKkm').on('click',function (e) {
                e.preventDefault();
                let form = new FormData($("#formEditPartnerKkm")[0]);
                $.ajax({
                    type: "POST",
                    url: '/partner/partner/partner-kkm-save',
                    data: form,
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function () {
                    },
                    success: function (data) {
                        if (data.status == 0) {
                            toastr.error(data.message, "Ошибка");
                        } else {
                            toastr.success("OK", "Ключи ККМ сохранены");
                        }
                    },
                    error: function () {
                        toastr.error("Ошибка запроса", "Ошибка");
                    }
                });
                return false;
            });

            $('#formEditMainSms').on('submit',function (e) {
                e.preventDefault();
                let form = $(this);
                $.ajax({
                    url: '/partner/partner/mainsms-save',
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
                            toastr.error(data.error, "Ошибка");
                        }
                    },
                    error: function () {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                    }
                });
                return false;
            });

            $('#btnEditDistribution').on('click', function () {
                let form = $('#formEditDistribution');
                $.ajax({
                    url: '/partner/partner/save-distribution',
                    method: 'POST',
                    data: form.serialize(),
                    beforeSend: function () {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                    },
                    success: function (data) {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                        if (data.status == 0) {
                            toastr.error(data.message, "Ошибка");
                        } else {
                            toastr.success("OK", data.message);
                        }
                    },
                    error: function () {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                    }
                });
                return false;
            });
            $('#btnOpoveshSettings').on('click',function () {
                let form = $('#formOpoveshSettings');
                linklink = $.ajax({
                    type: "POST",
                    url: '/partner/settings/settingssave',
                    data: form.serialize(),
                    beforeSend: function () {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                    },
                    success: function (data) {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                        if (data.status == 1) {
                            toastr.success("OK", "Изменения сохранены");
                        } else {
                            toastr.error(data.message, "Ошибка");
                        }
                    },
                    error: function (data) {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                        toastr.error("Ошибка запроса", "Ошибка");
                    }
                });
                return false;
            });

            $('#btnEditPartnerAdmin').on('click',function () {
                let form = $('#formEditPartnerAdmin');
                linklink = $.ajax({
                    type: "POST",
                    url: '/partner/partner/users-save',
                    data: form.serialize(),
                    beforeSend: function () {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                    },
                    success: function (data) {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                        if (data.status == 1) {
                            toastr.success("OK", "Изменения сохранены");
                        } else {
                            toastr.error(data.message, "Ошибка");
                        }
                    },
                    error: function (data) {
                        form.closest('.ibox-content').toggleClass('sk-loading');
                        toastr.error("Ошибка запроса", "Ошибка");
                    }
                });
                return false;
            });
        },

        savepatnerpart: function (form) {
            $.ajax({
                url: '/partner/partner/partner-save',
                method: 'POST',
                data: form.serialize(),
                beforeSend: function () {
                    form.closest('.ibox-content').toggleClass('sk-loading');
                },
                success: function (data) {
                    form.closest('.ibox-content').toggleClass('sk-loading');
                    if (data.status == 0) {
                        toastr.error(data.message, "Ошибка");
                    } else {
                        toastr.success("OK", "Данные контрагента сохранены");
                    }
                },
                error: function () {
                    form.closest('.ibox-content').toggleClass('sk-loading');
                }
            });
        },

        patnerparuserslk: function () {

            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 1000
            };

            $('#saveUser').on('click', function () {
                let form = $('#formEditUser').serialize();
                $.ajax({
                    url: '/partner/partner/users-save',
                    method: 'POST',
                    data: form,
                    beforeSend: function () {
                        $('#saveUser').prop('disabled', true);
                    },
                    success: function (data) {
                        $('#saveUser').prop('disabled', false);
                        if (data.status == 1) {
                            toastr.success("OK", "Пользователь сохранен");
                            window.location.href = '/partner/partner/users-edit/' + data.id;
                        } else {
                            toastr.error(data.message, "Ошибка");
                        }
                    },
                    error: function () {
                        $('#saveUser').prop('disabled', false);
                    }
                });
                return false;
            });

            $(".select2").select2({
                placeholder: "Выберите разделы",
                allowClear: true
            });
        },

        partnerlistuslugi: function () {

            $('a[data-action="delUsluga"]').on('click', function () {
                let delId = $(this).attr('data-id');
                swal({
                    title: "Подтвердите удаление услуги?",
                    text: "Послу удаление услуги её использование будет невозможно!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Да, удалить!",
                    cancelButtonText: "Отмена",
                    closeOnConfirm: false
                }, function () {
                    if (linklink) {
                        linklink.abort();
                    }

                    let csrfToken = $('meta[name="csrf-token"]').attr("content");
                    linklink = $.ajax({
                        type: "POST",
                        url: '/partner/partner/uslugi-del',
                        data: {'ID': delId, '_csrf': csrfToken},
                        beforeSend: function () {
                        },
                        success: function (data) {
                            if (data.status == 1) {
                                swal({
                                    title: "Услуга удалена!",
                                    text: "Услуга была удалена.",
                                    type: "success"
                                }, function () {
                                    window.location.href = '/partner/partner/partner-edit/' + data.id + "#tab-2";
                                });
                            } else {
                                swal({
                                    title: "Ошибка",
                                    text: data.message,
                                    type: "error"
                                });
                            }
                        },
                        error: function (data) {
                            swal({
                                title: "Ошибка",
                                text: "Ошибка запроса",
                                type: "error"
                            });
                        }
                    });
                });
            });
        },

        partneredituslug: function () {

            $('#saveUsluga').on('click', function() {

                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    showMethod: 'slideDown',
                    timeOut: 1000
                };

                let form = $('form[name="EditUsluga"]').serialize();
                $.ajax({
                    url: '/partner/partner/uslugi-save',
                    method: 'POST',
                    data: form,
                    beforeSend: function() {
                        $('#saveUsluga').prop('disabled', true);
                    },
                    success: function(data) {
                        $('#saveUsluga').prop('disabled', false);
                        if (data.status == 0) {
                            toastr.error(data.message, "Ошибка");
                        } else {
                            toastr.success("OK", "Услуга сохранена");
                            window.location.href = '/partner/partner/uslugi-edit/'+data.id;
                        }
                    },
                    error: function() {
                        $('#saveUsluga').prop('disabled', false);
                    }
                });
                return false;
            });
        }
    };

    let commInfo = {

        listinit: function () {
            $('select[name="partnersel"]').on('change', function () {
                let selp = $(this).val();
                if (selp > 0) {
                    $('tr[data-partner = "' + selp + '"]').show();
                    $('tr[data-partner][data-partner != "' + selp + '"]').hide();
                } else {
                    $('tr[data-partner]').show();
                }
            });
        },

        init: function () {

        },

        saveKont: function (form) {
            $.ajax({
                url: '/partner/partner/cont-save',
                method: 'POST',
                data: form.serialize(),
                beforeSend: function () {
                    form.closest('.ibox-content').toggleClass('sk-loading');
                },
                success: function (data) {
                    form.closest('.ibox-content').toggleClass('sk-loading');
                    if (data.status == 1) {
                        toastr.success("OK", "Данные сохранены");
                    } else {
                        toastr.error(data.error, "Ошибка");
                    }
                },
                error: function () {
                    form.closest('.ibox-content').toggleClass('sk-loading');
                    toastr.error(data.error, "Ошибка");
                }
            });
        }
    };

    window.date_range = {
        start: function () {
            $('[name="calDay"],[name="calWeek"],[name="calMon"]').on('click', function () {
                $('[name="calDay"]').removeClass('active');
                $('[name="calWeek"]').removeClass('active');
                $('[name="calMon"]').removeClass('active');
                $(this).addClass('active');

                let now, today;
                now = moment(new Date()).hour(23).minute(59).seconds(59);
                today = moment(new Date()).hour(0).minute(0).seconds(0);
                if ($(this).attr('name') == "calDay") {
                    $('[name="datefrom"]').data("DateTimePicker").date(today);
                    $('[name="dateto"]').data("DateTimePicker").date(now);
                } else if ($(this).attr('name') == "calWeek") {
                    today = today.subtract(7, 'days');
                    $('[name="datefrom"]').data("DateTimePicker").date(today);
                    $('[name="dateto"]').data("DateTimePicker").date(now);
                } else if ($(this).attr('name') == "calMon") {
                    today = today.subtract(1, 'months');
                    $('[name="datefrom"]').data("DateTimePicker").date(today);
                    $('[name="dateto"]').data("DateTimePicker").date(now);
                }
            });

            $('[name="datefrom"],[name="dateto"]').datetimepicker({
                format: 'DD.MM.YYYY HH:mm',
                showClose: true
            });
        },
    };

    window.loginNav = loginNav;
    window.lk = lk;
    window.commInfo = commInfo;


}(jQuery || $));

