(function ($) {
    'use strict';

    window.antifraud = {
        start_script: function () {

            this.activate_date_fields();
            $(document).on('submit', '#statlistform', function (e) {
                e.preventDefault();
                antifraud.click_search();
            });
            $(document).on('click', '.send-pjax', function () {
                antifraud.modal_open(this);
            });
            $(document).on('pjax:error', function (event, obj, textError, error) {
                event.preventDefault();
            });
        },

        activate_date_fields: function () {
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

        click_search: function () {
            //ajax
            let data = $('#statlistform').serialize();
            $.pjax.reload({
                type: 'GET',
                url: '/partner/antifraud/ajax-index-list',
                container: '#some_pjax_id',
                data: data,
                push: false,
                replace: false,
                timeout: 10000,
                cache: false,
                "scrollTo": false
            });

        },

        modal_open: function (button) {
            let user_hash = $(button).attr('data-user-hash');
            let trans_id = $(button).attr('data-transaction-id');
            let data = 'user_hash=' + user_hash + '&transaction_id=' + trans_id;
            $.pjax.reload({
                type: 'GET',
                url: '/partner/antifraud/ajax-modal-info',
                container: '#pjax_modal',
                data: data,
                push: false,
                replace: false,
                timeout: 10000,
                cache: false,
                "scrollTo": false
            });
            $('#transaction_info').modal('show')
        }
    };
    window.antifraud_setting = {
        start_script: function(){
            this.send_refund();
        },

        send_refund: function(){
            $(document).on('submit','#settings-antifraud-refund', function(e){
                e.preventDefault();
                let data = $(this).serialize();
                $.ajax({
                    method: 'post',
                    url: '/partner/antifraud/settings',
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
                                text: answer.data.first_error[0],
                                type: "error"
                            }, function () {
                                $('#statlistform').trigger('submit');
                                // for(var i=0; i< answer.error; i++){
                                //     let errorInput = $('#settings-antifraud-refund input[name= ""]');
                                //     errorInput.parent().addClass('has-error');
                                // }
                                $.each(answer.data.errors, function (index, value){
                                    let errorInput = $('#settings-antifraud-refund input[name= "'+index+'"]');
                                    errorInput.parent().addClass('has-error');
                                });
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
        }
    }

}($ || jQuery));