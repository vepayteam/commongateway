$(document).ready(function () {
    selectLine();
    selectAll();
    actionAnConfirmAllButton();
    openModalAfterClick();
    showInputField();
    sendSms();
    confirmCode();
    modalHide();
    orders = []; //глобальный массив.
});

/**
 * клик по строке в таблице.
 * Действие: устанавлиает чекбокс на кликнутой строке в таблице.
 * */
function selectLine() {
    //клик по строке
    $(document).on('click', '.payment-orders-list table > tbody > tr > td:not(:has(.confirm-small))', function () {
        var checkbox = $(this).parent().find('.checkbox-list');
        if (checkbox.prop('checked') === true) {
            checkbox.prop('checked', false);
        } else {
            checkbox.prop('checked', true);
        }
        checkbox.change();
    });

    //клик по чекбоксу
    $(document).on('click', '.payment-orders-list .checkbox-list', function () {
        if ($(this).prop('checked') === true) {
            $(this).prop('checked', false);
        } else {
            $(this).prop('checked', true);
        }
    })
}

/**
 * клик по "Выделить все"
 * Действие: Устанавливает все чекбоксы в положение выделить/снять.
 * */
function selectAll() {
    $(document).on('click', '.payment-orders-list .select-all', function () {
        var checkboxes = $(".payment-orders-list table > tbody > tr .checkbox-list");
        if ($(this).hasClass('checked-all')) {
            checkboxes.prop('checked', false);
            $(this).removeClass('checked-all');
        } else {
            checkboxes.prop('checked', true);
            $(this).addClass("checked-all");
        }
        checkboxes.change();
    });
}

/**
 * Активирует / дизактивирует кнопку "Подтвердить все"
 * Реагирует на каждое выделение чекбокса.
 * */
function actionAnConfirmAllButton() {
    var boxes = $(".payment-orders-list table > tbody > tr .checkbox-list");
    boxes.change(function () {
        var checkedAll = true;
        var countChecked = 0;
        boxes.each(function () {
            if ($(this).prop('checked')) {
                // checkedAll = true;
                countChecked++;
            }
        });
        if (countChecked === 0 ){
            checkedAll = false;
        }
        var button = $(".payment-orders-list .confirm-all");
        if (checkedAll === true) {
            button.removeAttr('disabled').removeClass('disabled');
        } else {
            button.attr('disabled', 'true').addClass('disabled');
        }
    })
}

/**
 * Открывает модальное окно
 * Реагирует на каждое нажатие по кнопкам "подтвердить" и "подтвердить все"
 * */
function openModalAfterClick() {
    $(document).on('click', '.payment-orders-list .confirm-all', function () {
        hideFields();
        var boxes = $(".payment-orders-list table > tbody > tr .checkbox-list");
        var sum = 0;
        boxes.each(function () {
            if ($(this).prop('checked') === true) {
                sum += parseFloat($(this).parent().parent().find('td:nth-child(4)').html().split(' ').join(''));
            }
        });
        $('.confirm-modal-form .orders-sum').html(sum.toFixed(2));
        var buttons = $(".payment-orders-list table > tbody > tr .confirm-small");
        orders = []; //глоабльный массив.
        $(buttons).each(
            function () {
                if ($(this).parent().parent().find('input.checkbox-list').prop('checked') === true){
                    orders.push(parseInt($(this).attr('data-id')));
                }
            }
        );
        $('.confirm-modal-form').modal('show');
    });

    $(document).on('click', '.payment-orders-list .confirm-small', function () {
        hideFields();
        var sum = 0;
        sum += parseFloat($(this).parent().parent().find('td:nth-child(4)').html().split(' ').join(''));
        $('.confirm-modal-form .orders-sum').html(sum.toFixed(2));
        orders = []; //глобальный массив.
        $orderId = $(this).attr('data-id');
        orders = [parseInt($orderId)];
        $('.confirm-modal-form').modal('show');
    });
}

/**
 * Отображает (показывает) поле для ввода пароля из смс.
 * Реагирует на нажатие кнопки "Подтвердить"
 * */
function showInputField(selector) {
    $(selector).fadeOut(
        function () {
            $('.confirm-modal-form .div-form-control, .confirm-modal-form .button-send').fadeIn();
        }
    );
}

function showString(selector, message) {
    $(selector).html(message);
    $(selector).fadeIn();
}

function hideString(selector){
    $(selector).fadeOut();
}

function hideFields() {
    if ($('.confirm-modal-form  .error-field').css('display') === 'block') {
        $('.confirm-modal-form  .error-field').fadeOut();
    }

    if ($('.confirm-modal-form  .success-field').css('display') === 'block') {
        $('.confirm-modal-form  .success-field').fadeOut();
    }
}

/**
 * Отправяет команду серверу на отправку сообщения
 * Реагирует на клик по кнопке "Подтвердить"
 * */
function sendSms() {
    $(document).on('click', ".confirm-modal-form .confirm-send-sms", function () {
        hideFields();
        var form = new FormData();
        form.append('AjaxForm[orders]', JSON.stringify(orders));//orders - глобальный массив.
        $.ajax({
            type: 'POST',
            url: '/partner/payment-orders/execute-orders',
            enctype: 'multipart/form-data',
            processData: false, //Important!
            contentType: false,
            data: form,
            success: function (data) {
                showInputField('.confirm-modal-form .confirm-send-sms');
            },
            error: function (data) {
                showString('.confirm-modal-form .error-field', data.responseJSON[0]);
            }
        });
    });
}

/**
 * Отправляет код для проверки на сервер
 * Реагирует на нажатие кнопки "Отправить"
 * */
function confirmCode() {
    $(document).on('click', '.confirm-modal-form  .button-send', function () {
        var code = $('.confirm-modal-form .div-form-control input').val();
        hideString('.confirm-modal-form .error-field');
        hideString('.confirm-modal-form .success-field');
        $.ajax({
            type: 'POST',
            url: '/partner/payment-orders/confirm-code',
            data: 'code=' + code,
            success: function (data) {
                $('.confirm-modal-form .div-form-control, .confirm-modal-form .button-send').fadeOut(
                    function () {
                        showString('.confirm-modal-form .success-field', data[0]);
                        // hideLines(orders);
                        setTimeout(function(){
                            location.reload();
                        }, 2000);
                    }
                );
            },
            error: function (data) {
                showString('.confirm-modal-form .error-field', data.responseJSON[0]);
            }
        });
    });
}

/**
 * Реагирует на закрытие модального окна.
 * */
function modalHide() {
    $('.confirm-modal-form').on('hidden.bs.modal', function (e) {
        $('.confirm-modal-form .div-form-control, .confirm-modal-form .button-send').fadeOut(
            function () {
                $('.confirm-modal-form .confirm-send-sms').fadeIn();
                $('.confirm-modal-form .div-form-control .form-control ').val('');
            }
        );
    });
}

/*
/!**
 * Удаляет запись из таблицы
 * *!/
function hideLines(orders) {
    var buttons = $('.payment-orders-list table > tbody > tr > td > .confirm-small');
    for (var i = 1; i <= orders.length; i++) {
        $(buttons).each(function () {
            if ($(this).attr('data-id') == i) {
                $(this).parent().parent().fadeOut('slow',
                    function () {
                        $(this).remove()
                    }
                );
            }
        });
    }

}*/
