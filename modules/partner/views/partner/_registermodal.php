<?php
/* @var yii\web\View $this */

use yii\helpers\Html;
use yii\web\View; ?>

<div id="modal-regpartner" class="modal fade" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <h4>Регистрация контрагента</h4>
                <form method="post" class="form-horizontal" id="registerpartnerform">
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <label class="control-label">Тип контрагента</label>
                            <div class="radio">
                                <input type="radio" name="PartnerAdd[TypeMerchant]" id="TypeMerchant1" value="0" checked="checked">
                                <label for="TypeMerchant1">
                                    Мерчант
                                </label>
                            </div>
                            <div class="radio">
                                <input type="radio" name="PartnerAdd[TypeMerchant]" id="TypeMerchant2" value="1">
                                <label for="TypeMerchant2">
                                    Партнер
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <label class="control-label">Юридический статус</label>
                            <div class="radio">
                                <input type="radio" name="PartnerAdd[UrState]" id="UrState1" value="0" checked="checked">
                                <label for="UrState1">
                                    Юридическое лицо
                                </label>
                            </div>
                            <div class="radio">
                                <input type="radio" name="PartnerAdd[UrState]" id="UrState2" value="1">
                                <label for="UrState2">
                                    Иидивидуальный предприниматель
                                </label>
                            </div>
                            <div class="radio">
                                <input type="radio" name="PartnerAdd[UrState]" id="UrState3" value="2">
                                <label for="UrState3">
                                    Физическое лицо
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <label for="Name" class="control-label">Наименование компании</label>
                            <input type="text" name="PartnerAdd[Name]" id="Name" class="form-control" value="" maxlength="250">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <div class="checkbox m-l-sm">
                                <input type="hidden" name="PartnerAdd[IsMfo]" value="0">
                                <input type="checkbox" id="LabelIsMfo" name="PartnerAdd[IsMfo]" value="1">
                                <label for="LabelIsMfo">Является МФО</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <?= Html::hiddenInput('_csrf', Yii::$app->request->csrfToken, ['id' => '_csrf']) ?>
                            <input type="submit" id="submitregpartner" value="Зарегистрировать" class="btn btn-primary">
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php $this->registerJs('lk.registerpartner();'); ?>