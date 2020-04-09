<?php

use app\models\partner\PartnerUsers;
use app\models\partner\PartUserAccess;
use app\models\payonline\Partner;
use yii\bootstrap\ActiveForm;

/* @var yii\web\View $this */
/* @var PartnerUsers $user */
/* @var Partner $partner */
/* @var bool $IsAdmin */

$this->title = " редактирование пользователя";

$this->params['breadtitle'] = "Редактирование пользователя";
$this->params['breadcrumbs'][] = ['label' => 'Контрагенты', 'url' => ['/partner/partner/index']];
if ($IsAdmin) {
    $this->params['breadcrumbs'][] = ['label' => $partner->Name, 'url' => ['/partner/partner/partner-edit/'.$partner->ID]];
}
$this->params['breadcrumbs'][] = $this->params['breadtitle'];
?>

<div class="row">
<div class="col-lg-12">
    <div class="ibox">
        <div class="ibox-title">
            <h5>Редактирование пользователя</h5>
            <div class="ibox-tools">
                <a class="btn btn-xs btn-default" href="/partner/partner/partner-edit/<?=$partner->ID?>#tab-3">
                    <i class="fa fa-close" aria-hidden="true"></i> Назад</a>
            </div>
        </div>
        <div class="ibox-content">
        <?php
        $form = ActiveForm::begin( [
            'layout' => 'horizontal',
            'options' => [
                'id' => 'formEditUser',
                'name' => 'EditUser'
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
        <?=$form->field($user, 'Login')->textInput(['class'=>'form-control', 'autocomplete' => 'off']);?>
        <?=$form->field($user, 'Password')->passwordInput(['class'=>'form-control', 'autocomplete' => 'off', 'value' => '']);?>
        <?=$form->field($user, 'Password2')->passwordInput(['class'=>'form-control', 'autocomplete' => 'off', 'value' => '']);?>
        <?=$form->field($user, 'FIO')->textInput(['class'=>'form-control']);?>
        <?=$form->field($user, 'Email')->textInput(['class'=>'form-control']);?>
        <?=$form->field($user, 'Doljnost')->textInput(['class'=>'form-control']);?>
        <div class="form-group">
            <label class="control-label col-sm-3">Доступ к разделам</label>
            <div class="col-sm-8">
                <?php
                    $userRazd = $user->getPartUserAccess()->all();
                    $userRazdAll = false;
                    if (!count($userRazd)) {
                        $userRazdAll = true;
                    }
                ?>
                <select class="select2 form-control" multiple="multiple" name="razdely[]">
                    <?php foreach (PartUserAccess::$razdely as $k => $r) : ?>
                    <option value="<?=$k?>"
                            <?php
                            if ($userRazdAll) {
                                echo " selected ";
                            } else {
                                foreach ($userRazd as $item) {
                                    if ($item->IdRazdel == $k) {
                                        echo " selected ";
                                        break;
                                    }
                                }
                            }
                            ?>
                    ><?=$r?></option>
                    <?php endforeach;?>
                </select>
            </div>
        </div>
        <div class="col-sm-8 col-sm-offset-3">
            <?php
            echo $form->field($user,'IsActive')->checkbox([
                'template' => "<div class=\"checkbox m-l-sm\">\n{input}\n{beginLabel}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
            ]);
            ?>
        </div>
        <div class="col-sm-8 col-sm-offset-3">
            <?php
            echo $form->field($user,'RoleUser')->checkbox([
                'template' => "<div class=\"checkbox m-l-sm\">\n{input}\n{beginLabel}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
            ]);
            ?>
        </div>
        <div class="row">
            <div class="col-sm-8 col-sm-offset-3">
                <input type="hidden" name="ID" value="<?=$user->ID?>">
                <input type="hidden" name="IdPartner" value="<?=$user->IdPartner?>">
                <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                <button type="button" class="btn btn-primary" id="saveUser">Сохранить</button>
            </div>
        </div>
        <?php
        ActiveForm::end();
        ?>
    </div>
    </div>
</div>
</div>

<?php

$this->registerJs('lk.patnerparuserslk();', \yii\web\View::POS_READY);
