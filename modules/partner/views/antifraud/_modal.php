<?php

use yii\widgets\Pjax;

?>
<div class="modal" id="transaction_info">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Информация о транзакции</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
                <?php
                    Pjax::begin([
                        'id' => 'pjax_modal',
                        'enablePushState' => false,
                        'timeout' => 1000,
                        'options'=>[
                                'class'=>'modal-body center'
                        ]
                    ]);
                    Pjax::end();
                ?>
        </div>
    </div>
</div>
