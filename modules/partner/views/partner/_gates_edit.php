<?php

use app\models\bank\Banks;
use app\models\payonline\Partner;
use app\services\payment\models\repositories\CurrencyRepository;
use app\services\payment\models\UslugatovarType;
use app\services\payment\types\AccountTypes;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @var Partner $partner
 */

$bankGates = $partner->getBankGates()->orderBy('TU ASC, Priority DESC')->all();
$currencyList = ArrayHelper::merge(['' => ''], ArrayHelper::map(CurrencyRepository::getAll(), 'Id', 'Code'));
?>

<div class="row">
    <div class="m-md">
        <h3>Настройки шлюзов</h3>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <button id="partner-edit__bank-gates-table__add-button" class="btn btn-primary">
            Добавить
        </button>
    </div>
</div>

<table id="partner-edit__bank-gates-table" class="table table-striped">
    <thead>
    <tr>
        <th>Тип Услуги</th>
        <th>Банк</th>
        <th>Приоритет</th>
        <th>Активно</th>
        <th>Тип счета</th>
        <th>Номер счета</th>
        <th>Валюта</th>
        <th>Логин</th>
        <th></th>
    </tr>
    </thead>
    <tbody>

    <?php
    /** @var \app\services\payment\models\PartnerBankGate $bankGate */
    foreach ($bankGates as $bankGate):?>
        <tr data-gate="<?= Html::encode(json_encode($bankGate->getAttributes())) ?>">
            <td><?= Html::encode($bankGate->uslugatovarType->Name) ?></td>
            <td><?= Html::encode($bankGate->bank->Name) ?></td>
            <td><?= Html::encode($bankGate->Priority) ?></td>
            <td><?= Html::encode($bankGate->Enable) ?></td>
            <td><?= Html::encode(AccountTypes::ALL_TYPES[$bankGate->SchetType]) ?></td>
            <td><?= Html::encode($bankGate->SchetNumber) ?></td>
            <td><?= Html::encode($bankGate->currency->Code) ?></td>
            <td><?= Html::encode($bankGate->Login) ?></td>
            <td>
                <button class="btn btn-primary partner-edit__bank-gates-table__edit-button">
                    <i class="glyphicon glyphicon-edit"></i>
                </button>
            </td>
            <td>
                <button class="btn btn-danger partner-edit__bank-gates-table__delete-button" data-id="<?=Html::encode($bankGate->Id)?>">
                    <i class="glyphicon glyphicon-remove"></i>
                </button>
            </td>

        </tr>
    <?php endforeach; ?>

    </tbody>
</table>


<div id="partner-edit__bank-gates-edit-modal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Редактирование / добавление шлюза</h4>
            </div>
            <div class="modal-body">

                <form id="partner-edit__bank-gates-edit-modal__gate-form">
                    <input name="PartnerId" value="<?= Html::encode($partner->ID) ?>" type="hidden">
                    <input name="Id" type="hidden">

                    <div class="form-group">
                        <input name="Enable" type="checkbox"> Активен
                    </div>

                    <div class="form-group">
                        <label for="exampleInputFile">Приоритет</label>
                        <input name="Priority" class="form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>Банк</label>
                        <select class="form-control" name="BankId">
                            <?php
                            /** @var Banks $bank */
                            foreach (Banks::find()->all() as $bank): ?>
                                <option value="<?= Html::encode($bank->ID) ?>">
                                    <?= Html::encode($bank->Name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Тип услуги</label>
                        <select class="form-control" name="TU">
                            <?php
                            /** @var UslugatovarType $uslugatovarType */
                            foreach (UslugatovarType::find()->all() as $uslugatovarType): ?>
                                <option value="<?= Html::encode($uslugatovarType->Id) ?>">
                                    <?= Html::encode($uslugatovarType->Name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Валюта</label>
                        <select class="form-control" name="CurrencyId">
                            <?php
                            /** @var CurrencyRepository */
                            foreach (CurrencyRepository::getAll() as $currency) : ?>
                                <option value="<?= Html::encode($currency->Id) ?>">
                                    <?= Html::encode($currency->Name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Тип счета</label>
                        <select class="form-control" name="SchetType">
                            <?php
                            /** @var AccountTypes */
                            foreach (AccountTypes::ALL_TYPES as $key => $type) : ?>
                                <option value="<?= Html::encode($key) ?>">
                                    <?= Html::encode($type) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="SchetNumberInput">Номер счета</label>
                        <input name="SchetNumber" id="SchetNumberInput" class="form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label for="exampleInputFile">Логин</label>
                        <input name="Login" class="form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label for="exampleInputFile">Токен</label>
                        <input name="Token" class="form-control" type="text" maxlength="1000">
                    </div>

                    <div class="form-group">
                        <label for="exampleInputFile">Пароль</label>
                        <input name="Password" class="form-control" type="text" maxlength="1000">
                    </div>

                    <div class="form-group">
                        <label for="exampleInputFile">Доп параметр №1</label>
                        <input name="AdvParam_1" class="form-control" type="text" maxlength="1000">
                    </div>

                    <div class="form-group">
                        <label for="exampleInputFile">Доп параметр №2</label>
                        <input name="AdvParam_2" class="form-control" type="text" maxlength="1000">
                    </div>

                    <div class="form-group">
                        <label for="exampleInputFile">Доп параметр №3</label>
                        <input name="AdvParam_3" class="form-control" type="text" maxlength="1000">
                    </div>

                    <div class="form-group">
                        <label for="exampleInputFile">Доп параметр №4</label>
                        <input name="AdvParam_4" class="form-control" type="text" maxlength="1000">
                    </div>

                    <div class="form-group">
                        <input name="UseGateCompensation" type="checkbox"/> Использовать комиссию шлюза
                    </div>

                    <!-- Валюта комиссии -->
                    <div class="form-group">
                        <label>Валюта фиксированной комиссии</label>
                        <select class="form-control" name="FeeCurrencyId">
                            <?php foreach ($currencyList as $id => $code) : ?>
                                <option value="<?= Html::encode($id) ?>"><?= Html::encode($code) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Валюта минимальной комиссии</label>
                        <select class="form-control" name="MinimalFeeCurrencyId">
                            <?php foreach ($currencyList as $id => $code) : ?>
                                <option value="<?= Html::encode($id) ?>"><?= Html::encode($code) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Комиссия от клиента -->
                    <div class="form-group">
                        <label>Процентная комиссия от клиента</label>
                        <input name="ClientCommission" class="form-control" type="number" step="0.01"/>
                    </div>
                    <div class="form-group">
                        <label>Фиксированная комиссия от клиента</label>
                        <input name="ClientFee" class="form-control" type="number" step="0.01"/>
                    </div>
                    <div class="form-group">
                        <label>Минимальная комиссия от клиента</label>
                        <input name="ClientMinimalFee" class="form-control" type="number" step="0.01"/>
                    </div>

                    <!-- Комиссия от контрагента (партнера/мерчанта) -->
                    <div class="form-group">
                        <label>Процентная комиссия от контрагента</label>
                        <input name="PartnerCommission" class="form-control" type="number" step="0.01"/>
                    </div>
                    <div class="form-group">
                        <label>Фиксированная комиссия от контрагента</label>
                        <input name="PartnerFee" class="form-control" type="number" step="0.01"/>
                    </div>
                    <div class="form-group">
                        <label>Минимальная комиссия от контрагента</label>
                        <input name="PartnerMinimalFee" class="form-control" type="number" step="0.01"/>
                    </div>

                    <!-- Комиссия банку -->
                    <div class="form-group">
                        <label>Процентная комиссия банку</label>
                        <input name="BankCommission" class="form-control" type="number" step="0.01"/>
                    </div>
                    <div class="form-group">
                        <label>Фиксированная комиссия банку</label>
                        <input name="BankFee" class="form-control" type="number" step="0.01"/>
                    </div>
                    <div class="form-group">
                        <label>Минимальная комиссия банку</label>
                        <input name="BankMinimalFee" class="form-control" type="number" step="0.01"/>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button id="partner-edit__bank-gates-edit-modal__save-button" type="button" class="btn btn-primary">
                    Сохранить
                </button>
            </div>
        </div>
    </div>
</div>
