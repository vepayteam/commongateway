<?php
/* @var yii\web\View $this */
/* @var \app\models\crypt\KeyUsers $user */

use yii\bootstrap\ActiveForm;

$this->title = "профиль";

$this->params['breadtitle'] = "Профиль";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];

?>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Профиль</h5>
            </div>
            <div class="ibox-content">
                <div class="sk-spinner sk-spinner-wave">
                    <div class="sk-rect1"></div>
                    <div class="sk-rect2"></div>
                    <div class="sk-rect3"></div>
                    <div class="sk-rect4"></div>
                    <div class="sk-rect5"></div>
                </div>
                <? $form = ActiveForm::begin([
                    'layout' => 'horizontal',
                    'options' => [
                        'id' => 'formProfileUser',
                        'name' => 'ProfileUser'
                    ],
                    'successCssClass' => '',
                    'fieldConfig' => [
                        'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                        'horizontalCssClasses' => [
                            'label' => 'col-sm-3',
                            'wrapper' => 'col-sm-6',
                            'error' => '',
                            'hint' => '',
                        ],
                    ],
                ]);?>
                <?=$form->field($user, 'FIO')->staticControl();?>
                <div class="form-group">
                    <label class="control-label col-sm-3">Срок действия пароля</label>
                    <div class="col-sm-6">
                        <?php if ($user->DateChange < time() - 90 * 86400) : ?>
                            <p class="form-control-static">Необходимо сменить пароль</p>
                        <?php else: ?>
                            <p class="form-control-static"><?=(int)(($user->DateChange + 90 * 86400 - time()) / 86400)?>  дней</p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
                ActiveForm::end();
                ?>
                <hr>
                <h3>Изменить пароль</h3>
                <form class="form-horizontal" id="passwform">
                    <div class="form-group">
                        <label class="col-lg-3 control-label" for="df">Текущий пароль</label>
                        <div class="col-lg-4">
                            <input type="password" class="form-control" id="df" name="oldpassw" value="" maxlength="30" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 control-label" for="np">Новый пароль</label>
                        <div class="col-lg-4">
                            <input type="password" name="passw" id="np" value="" maxlength="30" class="form-control" autocomplete="new-password" required>
                        </div>
                        <label></label>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3"></label>
                        <div class="col-sm-6">
                            <p class="form-control-static">Минимальная длина 7 знаков, должен ключать цифры и буквы.</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 control-label" for="np2">Повтор пароля</label>
                        <div class="col-lg-4">
                            <input type="password" name="passw2" id="np2" value="" maxlength="30" class="form-control" autocomplete="new-password" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-lg-offset-3 col-lg-4">
                            <input name="_csrf" type="hidden" value="<?= Yii::$app->request->csrfToken ?>">
                            <button class="btn btn-sm btn-primary" type="submit">Изменить</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let keymoduleChangePw = function() {
        $('#passwform').on('submit', function () {

            toastr.options = {
                closeButton: true,
                progressBar: true,
                showMethod: 'slideDown',
                timeOut: 2000
            };

            $.ajax({
                type: "POST",
                url: '/keymodule/default/changepw',
                data: $('#passwform').serialize(),
                beforeSend: function () {
                    $('#formProfileUser').closest('.ibox-content').toggleClass('sk-loading');
                },
                success: function (data) {
                    $('#formProfileUser').closest('.ibox-content').toggleClass('sk-loading');
                    if (data.status == 1) {
                        toastr.success("OK", "Пароль изменен");
                        $('#passwform').trigger("reset");
                    } else {
                        toastr.error("Ошибка, слишком простой пароль или введенные пароли не совпадают", "Ошибка");
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
</script>

<?php $this->registerJs('keymoduleChangePw();'); ?>