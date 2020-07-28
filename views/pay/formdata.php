<?php
/* @var \yii\web\View $this */
/* @var array $params */
/* @var array $formData */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
?>
<div class="middle">
    <section class="container">
        <div class="row margin-top24 rowlogo">
            <div class="col-xs-12">
                <img src="/imgs/logo_vepay.svg" alt="vepay" class="logo">
                <span class="logotext">ТЕХНОЛОГИИ В&nbsp;ДЕЙСТВИИ</span>
                <img src="/imgs/close.svg" class="closebtn" alt="close" id="closeform">
            </div>
        </div>
        <div class="row margin-top24">
            <div class="col-xs-12">
                <div class="info">Оплата в<span class="pull-right blacksumm"><?=$params['NamePartner']?></span></div>
            </div>
        </div>
        <?php if ($params['IdUsluga'] != 1) : ?>
            <div class="row nopadding">
                <div class="col-xs-12">
                    <div class="info"><span>Сумма</span> <span class="pull-right blacksumm"><?=number_format($params['SummPay']/100.0, 2, ',', '')?> ₽</span></div>
                    <div class="info"><span>Комиссия</span> <span class="pull-right blacksumm"><?=number_format($params['ComissSumm']/100.0, 2, ',', '')?> ₽</span></div>
                </div>
            </div>
        <?php endif; ?>

        <div id="loader" class="col-xs-12" style="display: none">
            <div class='text-center col-xs-12 loader'><i class="fa fa-spinner fa-spin fa-fw"></i></div>
        </div>

        <div class="row margin-top24">
            <form id="save-data-form" method="post" action="/pay/save-data/<?=$params['ID']?>">
                <?php foreach($formData as $item): ?>
                    <div class="form-group">
                        <label><?=$item['Title']?></label>
                        <input type="text" name="<?=$item['Name']?>" class="form-control" data-inputmask-regex="<?=$item['Regex']?>">
                    </div>
                <?php endforeach; ?>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Далее</button>
                </div>
            </form>

        </div>



        <div class="row nopadding margin-top24">
            <div class="col-xs-12">
                <div class="errmessage" style="display: none">
                    <p id="error_message_xs"></p>
                </div>
            </div>
        </div>

        <div class="row nopadding margin-top24">
            <div class="col-xs-12 text-center">
                <div class="pslogosBtm">
                    <img src="/imgs/pci-dss.png" class="opacity">
                    <img src="/imgs/verified-by-visa.png" class="padding-left10 opacity">
                    <img src="/imgs/mastercard-securecode.png" class="padding-left10 opacity">
                    <img src="/imgs/mir-accept.png" class="padding-left10 opacity">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 text-center">
                <div class="footcopyr">ООО «ПРОЦЕССИНГОВАЯ КОМПАНИЯ БЫСТРЫХ ПЛАТЕЖЕЙ»</div>
            </div>
        </div>

    </section>
</div>
<noscript><div><img src="https://mc.yandex.ru/watch/56963551" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<?php
$this->registerJs('setTimeout(tracking.sendToServer, 500)', \yii\web\View::POS_READY);
$this->registerJsFile('/payasset/js/ym.js');
$this->registerJsFile('/payasset/js/jquery.inputmask.min.js');
?>
