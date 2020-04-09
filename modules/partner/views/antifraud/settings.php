<?php
/**
 * @var \yii\web\View $this
 * @var array $data
*/
$this->title = "Антифрод - настрйоки";
$this->params['breadtitle'] = "Антифрод - настройки";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];
$this->registerJs(
        'antifraud_setting.start_script()'
);
use yii\helpers\Html; ?>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <?=$this->render('_nav');?>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="ibox">
            <div class="ibox-title">
                <h5>Настройки антифрода выплат (на карты клиентов)</h5>
            </div>
            <div class="ibox-content">
                <form id="settings-antifraud-refund">
                    <div class="row form-group">
                        <div class="col-md-4">
                            <p>Email, для уведомлений о блокированных транзакциях</p>
                        </div>
                        <div class="col-md-8">
                            <input class="form-control" type="email" name="block_email_for_antifraud_refund" placeholder='Введите email' value= "<?=isset($data['block_email_for_antifraud_refund'])? $data['block_email_for_antifraud_refund'] : ""?>">
                        </div>
                    </div>
                    <?=Html :: hiddenInput(Yii::$app->getRequest()->csrfParam, Yii::$app->getRequest()->getCsrfToken(), []);?>
                    <input type="submit" class="btn btn-primary" value="Сохранить данные">
                </form>
            </div>
        </div>
    </div>
</div>
