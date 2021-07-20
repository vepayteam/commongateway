<?php

use app\models\site\ContactForm;
use yii\helpers\Html;
use yii\web\View;

/**
 * Форма "Напишите нам" встраиваемая
 *
 * @var ContactForm $model
 * @var View $this
 * @var string $formType
 */

?>
<form id="form_cont" name="form_cont" action="#" method="post" class="send_common">
    <div class="row">
        <div class="form-group">
            <div class="col-md-12">
                <label>Организация <span class="required">*</span></label>
                <input class="form-control" type="text" name="ContactFormInline[org]" maxlength="100" title="Ваша организация" value="">
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-4">
                <label>Ваше имя <span class="required">*</span></label>
                <input class="form-control" type="text" name="ContactFormInline[name]" maxlength="100" title="Ваше имя" value="">
            </div>
            <div class="col-md-4">
                <label>Телефон <span class="required">*</span></label>
                <input class="form-control" type="text" name="ContactFormInline[phone]" maxlength="20" title="Ваш телефон" value="">
            </div>
            <div class="col-md-4">
                <label>E-mail <span class="required">*</span></label>
                <input class="form-control" type="text" name="ContactFormInline[email]" maxlength="100" title="Введите E-mail" value="" data-toggle="tooltip" data-placement="bottom">
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-12"><label>Сообщение <span class="required">*</span></label>
                <textarea class="form-control" name="ContactFormInline[body]" title="Введите сообщение" rows="8"></textarea>
            </div>
        </div>
    </div>
    <div class="clearfix">
        <span class="pull-left">
            <label class="block fsize10">&nbsp;</label>
            <input name="send" id="contact_submit" type="submit" value="Отправить" class="btn btn-primary btn-lg" data-loading-text="Loading...">
        </span>
    </div>
    <input type="hidden" name="ContactFormInline[type]" value="<?=$formType?>">
    <?= Html::hiddenInput(Yii::$app->getRequest()->csrfParam, Yii::$app->getRequest()->getCsrfToken(), []); ?>
</form>

<?
$this->registerJs('    
    $("#form_cont").on("submit", function() {
        if (!validateFields()) return false;    
        var form = $(this).serialize();
        $.ajax({
            type: "POST",
            url: "/site/contactsend",
            beforeSend: function() {
                $("#contact_submit").prop("disabled", true);
                $("input[name=\"ContactFormInline[email]\"]").tooltip(\'destroy\');
            },
            data: form,
            success: function (data) {
                $("#contact_submit").prop("disabled", false);
                if (data.status == 1) {
                    $("#form_cont")[0].reset();
                    $(".modal-content").html(\'<div class="form_wrapper" id="contactwindow" wndh="290"><div style="padding: 20px"><h3>\'+data.head+\'</h3><p>\'+data.message+\'</p><div><input name="close" type="button" value="Закрыть" class="btn btn-primary btn-lg" id="closeModal"></div></div></div>\');
                    $(".modal").modal("show");
                } else {
                    CustomValid.showErrorValid($("input[name=\"ContactFormInline[email]\"]"));                    
                }
            },
            error: function () {
                $("#contact_submit").prop("disabled", false);
            }
        });  
        return false;
    });
    

    $(\'input[name*=phone]\').inputmask({mask: "+7 (999) 999-99-99"});
    
    $(".modal").on("click", "#closeModal", function () {
        $(".modal").modal("hide");
    });
    
    function validateFields() {
        var err = false;
        err = err | CustomValid.checkReuired($(\'input[name = "ContactFormInline[org]"]\'), err);
        err = err | CustomValid.checkReuired($(\'input[name = "ContactFormInline[name]"]\'), err);
        err = err | CustomValid.checkReuired($(\'input[name = "ContactFormInline[phone]"]\'), err);
        err = err | CustomValid.checkEmail($(\'input[name = "ContactFormInline[email]"]\'), err);
        err = err | CustomValid.checkReuired($(\'textarea[name = "ContactFormInline[body]"]\'), err);
        return !err;
    }    
', View::POS_READY);
?>
