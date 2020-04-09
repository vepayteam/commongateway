<?php

use app\models\payonline\Partner;
use app\models\payonline\PartnerBankRekviz;
use app\models\payonline\QrGroup;
use app\models\payonline\Uslugatovar;
use app\models\payonline\UslugiRegions;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\web\View;

/* @var View $this */
/* @var Uslugatovar $usl */
/* @var Partner $partner */

$this->title = " редактирование услуги";

$this->params['breadtitle'] = "Редактирование услуги";
$this->params['breadcrumbs'][] = ['label' => 'Контрагенты', 'url' => ['/partner/partner/index']];
$this->params['breadcrumbs'][] = ['label' => $partner->Name, 'url' => ['/partner/partner/partner-edit/'.$usl->IDPartner]];
$this->params['breadcrumbs'][] = $this->params['breadtitle'];

$groups = QrGroup::find()->select(['ID', 'NameGroup'])->asArray()->all();
$groups = ArrayHelper::map($groups, 'ID', 'NameGroup');

$regions = UslugiRegions::find()->select(['ID', 'NameRegion'])->asArray()->all();
$regions = ['0' => 'Все'] + ArrayHelper::map($regions, 'ID', 'NameRegion');

$rekviz = PartnerBankRekviz::find()->select(['ID', 'NamePoluchat'])->where(['IdPartner' => $usl->IDPartner])->asArray()->all();
$rekviz = ArrayHelper::map($rekviz, 'ID', 'NamePoluchat');

?>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Редактирование услуги</h5>
                    <div class="ibox-tools">
                        <a class="btn btn-xs btn-default" href="/partner/partner/partner-edit/<?=$usl->IDPartner?>#tab-2"><i class="fa fa-close" aria-hidden="true"></i> Назад</a>
                    </div>
                </div>
                <div class="ibox-content">
                    <?php
                    $form = ActiveForm::begin([
                        'layout' => 'horizontal',
                        'options' => [
                            'name' => 'EditUsluga'
                        ],
                        'successCssClass' => '',
                        'fieldConfig' => [
                            'template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{error}\n{endWrapper}",
                            'horizontalCssClasses' => [
                                'label' => 'col-sm-3',
                                'wrapper' => 'col-sm-8',
                                'error' => '',
                                'hint' => '',
                            ],
                        ],
                    ]); ?>
                    <h3>Компания: <?=$usl->partner->Name?></h3>
                    <?= $form->field($usl, 'NameUsluga')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'InfoUsluga')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'LogoProv')->textInput(['class' => 'form-control']); ?>
                    <hr>
                    <h3>Реестры:</h3>
                    <?= $form->field($usl, 'ExtReestrIDUsluga')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'ProfitExportFormat')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'SchetchikFormat')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'SchetchikNames')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'PatternFind')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'QrcodeExportFormat')->textInput(['class' => 'form-control']); ?>
                    <hr>
                    <h3>Группа:</h3>
                    <?= $form->field($usl, 'IsCustom')->dropDownList(Uslugatovar::$TypeCustom_str, ['class' => 'form-control']); ?>
                    <div class="col-sm-8 col-sm-offset-3">
                        <?php
                        echo $form->field($usl, 'HideFromList')->checkbox([
                            'template' => "<div class=\"checkbox m-l-sm\">\n{input}\n{beginLabel}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
                        ]);
                        ?>
                    </div>
                    <?= $form->field($usl, 'Group')->dropDownList($groups, ['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'Region')->dropDownList($regions, ['class' => 'form-control']); ?>
                    <hr>
                    <h3>Форма оплаты:</h3>
                    <?= $form->field($usl, 'Labels')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'Example')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'Comments')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'Mask')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'Regex')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'MinSumm')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'MaxSumm')->textInput(['class' => 'form-control']); ?>
                    <hr>
                    <h3>Форма запроса из реестра:</h3>
                    <?= $form->field($usl, 'LabelsInfo')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'ExampleInfo')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'CommentsInfo')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'MaskInfo')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'RegexInfo')->textInput(['class' => 'form-control']); ?>
                    <hr>
                    <h3>Комиссия:</h3>
                    <?= $form->field($usl, 'PcComission')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'MinsumComiss')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'ProvVoznagPC')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'ProvVoznagMin')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'ProvComisPC')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'ProvComisMin')->textInput(['class' => 'form-control']); ?>
                    <hr>
                    <h3>Экспорт платежа:</h3>
                    <?= $form->field($usl, 'TypeExport')->dropDownList(Uslugatovar::$TypeExport_str, ['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'TypeReestr')->dropDownList(Uslugatovar::$TypeReestr_str, ['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'EmailReestr')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'KodPoluchat')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'ReestrNameFormat')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'ProfitIdProvider')->textInput(['class' => 'form-control']); ?>
                    <?= $form->field($usl, 'IdBankRekviz')->dropDownList($rekviz, ['class' => 'form-control']); ?>
                    <div class="col-sm-8 col-sm-offset-3">
                        <?php
                        echo $form->field($usl, 'SendToGisjkh')->checkbox([
                            'template' => "<div class=\"checkbox m-l-sm\">\n{input}\n{beginLabel}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
                        ]);
                        ?>
                    </div>

                    <div class="row">
                        <div class="col-sm-8 col-sm-offset-3">
                            <input type="hidden" name="ID" value="<?= $usl->ID ?>">
                            <input type="hidden" name="IdPartner" value="<?= $usl->IDPartner ?>">
                            <button type="button" class="btn btn-primary" id="saveUsluga">Сохранить
                            </button>
                        </div>
                    </div>
                    <?php
                    ActiveForm::end();
                    ?>
                </div>
            </div>
        </div>
    </div>

<?php $this->registerJs('lk.partneredituslug()', yii\web\View::POS_READY);
