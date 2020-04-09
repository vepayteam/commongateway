<?php ?>
<div class="confirm-modal-form modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Подтвердить платежные поручения</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body center">
                <p>Подтвердить операцию на сумму <strong class="orders-sum"> 000 </strong> руб. ? </p>
                <button class="btn btn-md btn-success confirm-send-sms">Подтвердить</button>
                <div class="col-md-6 col-md-offset-3 col-xs-12 div-form-control">
                    <input class="form-control " type="text" placeholder="Введите код из смс">
                </div>
                <button class="btn btn-md btn-primary button-send">Отправить</button>
                <p class="error-field"></p>
                <p class="success-field"></p>
            </div>
        </div>
    </div>
</div>

