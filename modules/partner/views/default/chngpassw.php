<?php
/* @var yii\web\View $this */
/* @var \app\models\partner\PartnerUsers $user */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

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
                <?php $form = ActiveForm::begin([
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
                <?=$form->field($user, 'Email')->staticControl();?>
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
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 control-label" for="np2">Повтор пароля</label>
                        <div class="col-lg-4">
                            <input type="password" name="passw2" id="np2" value="" maxlength="30" class="form-control" autocomplete="new-password" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-lg-4 col-lg-offset-3 text-muted">Пароль должен содержать буквенные и цифровые символы и быть длиной не менее 8 символов</div>
                    </div>
                    <div class="form-group">
                        <div class="col-lg-offset-3 col-lg-4">
                            <?= Html::hiddenInput('_csrf', Yii::$app->request->csrfToken, ['id' => '_csrf']) ?>
                            <button class="btn btn-sm btn-primary" type="submit">Изменить</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $this->registerJs('loginNav.changepassw()'); ?>