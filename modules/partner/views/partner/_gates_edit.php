<?php

use app\models\bank\Banks;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\services\payment\models\UslugatovarType;


/**
 * @var Partner $partner
 */


$bankGates = $partner->getBankGates()->orderBy('TU ASC, Priority DESC')->all();
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
        <th>Номер счета</th>
        <th>Логин</th>
        <th></th>
    </tr>
    </thead>
    <tbody>

    <?php
    /** @var \app\services\payment\models\PartnerBankGate $bankGate */
    foreach ($bankGates as $bankGate):?>
        <tr data-gate='<?= json_encode($bankGate->getAttributes()) ?>'>
            <td><?= $bankGate->uslugatovarType->Name ?></td>
            <td><?= $bankGate->bank->Name ?></td>
            <td><?= $bankGate->Priority ?></td>
            <td><?= $bankGate->Enable ?></td>
            <td><?= $bankGate->SchetNumber ?></td>
            <td><?= $bankGate->Login ?></td>
            <td>
                <button class="btn btn-primary partner-edit__bank-gates-table__edit-button">
                    <i class="glyphicon glyphicon-edit"></i>
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
                    <input name="PartnerId" value="<?= $partner->ID ?>" type="hidden">
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
                                <option value="<?= $bank->ID ?>">
                                    <?= $bank->Name ?>
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
                                <option value="<?= $uslugatovarType->Id ?>">
                                    <?= $uslugatovarType->Name ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputFile">Номер счета</label>
                        <input name="SchetNumber" class="form-control" type="text">
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
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button id="partner-edit__bank-gates-edit-modal__save-button" type="button" class="btn btn-primary">Сохранить</button>
            </div>
        </div>
    </div>
</div>
