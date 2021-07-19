<?php

use app\models\site\ContactForm;
use yii\web\View;

/**
 * Форма "Обратная связь" для окна
 *
 * @var $model ContactForm
 * @var $this View
 */

?>
<div class="form_wrapper" id="contactwindow">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title" id="myModalLabel">Обратная связь</h4>
    </div>
    <div class="modal-body">
        <form id="form_mes1" name="form_mes" action="#" method="post" class="send_common" enctype="multipart/form-data">
            <div class="row">
                <div class="form-group">
                    <div class="col-md-12">
                        <select name="ContactForm[subject]" id="subject" class="form-control input-lg">
                            <option value="review">Оставить отзыв</option>
                            <option value="idea">Озвучить идею</option>
                            <option value="help">Получить помощь</option>
                            <option value="cooperation">Предложить сотрудничество</option>
                            <option value="checkpay">Поиск платежа</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row" id="contactform">
                <div class="form-group">
                    <div class="col-md-6">
                        <label>Ваше имя <span class="required">*</span></label>
                        <input class="form-control" type="text" name="ContactForm[name]" title="Ваше имя" maxlength="100" value="">
                    </div>
                    <div class="col-md-6">
                        <label>E-mail <span class="required">*</span></label>
                        <input class="form-control" type="text" name="ContactForm[email]" title="Введите E-mail"
                               value="" maxlength="100">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label>Сообщение <span class="required">*</span></label>
                        <textarea class="form-control" name="ContactForm[body]" title="Сообщение"
                                  rows="5"></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <div><label>Прикрепите файл</label></div>
                        <div>
                            <input type="button" id="fileOpen" class="btn btn-default" value="Выберите файл">
                            <input type="file" name="file" id="fileUpload" value="" style="display: none;">
                            <label>
                                <span class="filename" id="selFile">Изображение объемом не более 10 Мб</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" id="searchpayform" style="display: none">
                <div class="form-group">
                    <div class="col-md-12">
                        <label>Адрес e-mail: <span class="required">*</span></label>
                        <input class="form-control" type="text" name="ContactForm[email2]" title="Введите E-mail" maxlength="100" value="">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6">
                        <label>Номер заказа <span class="required">*</span></label>
                        <input class="form-control" type="text" name="ContactForm[order]" title="Введите номер заказа"
                               value="" maxlength="100">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6">
                        <label>Дата платежа <span class="required">*</span></label>
                        <div class="input-group date datetimepicker">
                            <input class="form-control" type="text" name="ContactForm[date]" title="Дата" value="<?=date("d.m.Y")?>"
                                   maxlength="20">
                            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="clearfix">
                    <div class="col-md-12 text-right">
                        <label class="block fsize10">&nbsp;</label>
                        <input name="send" id="contact_submit_modal" type="submit" value="Отправить"
                               class="btn btn-primary btn-lg pull-right" data-loading-text="Loading...">
                        <button class="btn btn-default btn-lg pull-right" style="margin-right: 15px" data-dismiss="modal">Отмена</button>
                    </div>
                </div>
                <input type="hidden" name="ContactForm[type]" value="feedback">
                <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
            </div>
        </form>
    </div>
</div>

<script>
    $("#form_mes1").on("submit", function () {

        var type = $('#subject').find(":selected").val();
        if (type != "checkpay") {
            if (!validateFields()) return false;
        } else {
            if (!validateFieldsCheckPay()) return false;
        }

        var form = new FormData($("#form_mes1")[0]);
        $.ajax({
            type: "POST",
            url: "/site/contactsend",
            beforeSend: function () {
                $("input[name=\"ContactForm[email]\"]").tooltip('destroy');
                $("#contact_submit_modal").prop("disabled", true);
            },
            data: form,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                $("#contact_submit_modal").prop("disabled", false);
                if (data.status == 1) {
                    $('#contactwindow').html("<div style='padding: 20px'><h3>"+data.head+"</h3><p>"+data.message+"</p><div><input name='close' type='button' value='Закрыть' class='btn btn-primary btn-lg' onclick='closeModal();'></div></div>");
                    var fh = 190;
                    var wh = $(window).height();
                    if (wh > fh + 30) {
                        $('.modal').css('padding-top', (wh - fh) / 2 - 40);
                    } else {
                        $('.modal').css('padding-top', 0);
                    }
                } else {
                    CustomValid.showErrorValid($("input[name=\"ContactForm[email]\"]"));
                }
            },
            error: function () {
                $("#contact_submit_modal").prop("disabled", false);
            }
        });
        return false;
    });

    $('[name="ContactForm[date]"]').parent().datetimepicker({
        viewMode: 'days',
        format: 'DD.MM.YYYY'
    });

    $('input[name*=phone]').inputmask({mask: "+7 (999) 999-99-99"});

    $('#fileOpen').on('click', function () {
        $('#fileUpload').trigger('click');
    });

    $('#fileUpload').change(function () {
        var file = this.files[0];
        if (file !== undefined) {
            var name = file.name;
            var size = file.size;
            var type = file.type;

            $('#selFile').html(file.name);
            console.log(file.type);

            if (file.name.length < 1) {
                $('#fileUpload').val('');
                $('#selFile').html('Файл не выбран');
            } else if (file.size > 20000000) {
                alert("Файл слишком большой");
                $('#fileUpload').val('');
                $('#selFile').html('Файл не выбран');
            } else if (file.type !== 'image/png' && file.type !== 'image/jpg' && file.type !== 'image/gif' &&
                file.type !== 'image/jpeg' && file.type !== 'application/pdf') {
                alert("Файл не картинка или документ");
                $('#fileUpload').val('');
                $('#selFile').html('Файл не выбран');
            }
        } else {
            $('#fileUpload').val('');
            $('#selFile').html('Файл не выбран');
        }
    });

    function closeModal() {
        $('.modal').modal('hide');
    }

    function validateFields() {
        var err = false;
        err = err | CustomValid.checkReuired($('input[name="ContactForm[name]"]'), err);
        err = err | CustomValid.checkEmail($('input[name="ContactForm[email]"]'), err);
        err = err | CustomValid.checkReuired($('textarea[name="ContactForm[body]"]'), err);
        return !err;
    }

    function validateFieldsCheckPay() {
        var err = false;
        err = err | CustomValid.checkEmail($('input[name="ContactForm[email2]"]'), err);
        err = err | CustomValid.checkReuired($('input[name="ContactForm[order]"]'), err);
        err = err | CustomValid.checkReuired($('textarea[name="ContactForm[date]"]'), err);
        return !err;
    }

    $('#subject').on('change', function () {
        var type = $(this).find(":selected").val();
        if (type == "checkpay") {
            $('#contactform').hide();
            $('#searchpayform').show();
        } else {
            $('#contactform').show();
            $('#searchpayform').hide();
        }

    })

</script>
