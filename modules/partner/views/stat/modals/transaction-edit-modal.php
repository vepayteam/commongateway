<?php

/** @var UpdateTransactionForm $updateTransactionForm */
/** @var array $statuses */

use app\modules\partner\models\forms\UpdateTransactionForm;
use yii\bootstrap\ActiveForm;

?>

<div id="transaction-edit-modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php
            $form = ActiveForm::begin([
                'id' => 'transaction-edit-modal-form',
                'method' => 'post',
                'action' => '/partner/stat/transaction-update'
            ])
            ?>

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Редактирование операции</h4>
            </div>

            <div class="modal-body">
                <?= $form->field($updateTransactionForm, 'id')->hiddenInput()->label(false) ?>
                <?= $form->field($updateTransactionForm, 'extId') ?>
                <?= $form->field($updateTransactionForm, 'paymentAmount') ?>
                <?= $form->field($updateTransactionForm, 'merchantCommission') ?>
                <?= $form->field($updateTransactionForm, 'providerCommission') ?>
                <?= $form->field($updateTransactionForm, 'status')->dropDownList($statuses) ?>
                <?= $form->field($updateTransactionForm, 'description') ?>
                <?= $form->field($updateTransactionForm, 'providerId') ?>
                <?= $form->field($updateTransactionForm, 'contractNumber') ?>
                <?= $form->field($updateTransactionForm, 'rcCode') ?>

                <?= $form->field($updateTransactionForm,'sendCallback')->checkbox([
                    'template' => "<div class=\"checkbox\">\n{input}\n{beginLabel}\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
                ]) ?>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>

            <?php ActiveForm::end() ?>
        </div>
    </div>
</div>
