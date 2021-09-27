<?php
/* @var $partnerlist Partner[]*/
/* @var $this yii\web\View */

use app\models\payonline\Partner;

?>

<div class="form-group row">
    <div class="col-sm-12">
        <h4>Перечислить средства</h4>
    </div>
</div>

<form method="post" class="form-horizontal" id="perevodform">

    <div class="form-group row">
        <label class="control-label col-sm-3">Контрагент</label>
        <div class="col-sm-8">
            <select class="form-control" name="Perechislen[IdPartner]">
                <?php foreach ($partnerlist as $partner) : ?>
                    <option value="<?=$partner->ID?>"><?=$partner->nameWithId?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group row">
        <label class="control-label col-sm-3">Счет назначения</label>
        <div class="col-sm-8">
            <div class="radio">
                <input type="radio" name="Perechislen[TypeSchet]" id="TypeSchet1" value="0" checked="checked">
                <label for="TypeSchet1">
                    Счет выдачи <div id="TypeSchet1Info"></div>
                </label>
            </div>
            <div class="radio">
                <input type="radio" name="Perechislen[TypeSchet]" id="TypeSchet2" value="1">
                <label for="TypeSchet2">
                    Банковский счет <div id="TypeSchet2Info"></div>
                </label>
            </div>
        </div>
    </div>
    <span id="infoschet" style="display: none;"></span>

    <div class="form-group row">
        <label class="control-label col-sm-3">Баланс</label>
        <div class="col-sm-8">
            <div class="col-sm-8 m-t-xs" id=balancepartner></div>
        </div>
    </div>

    <div class="form-group row">
        <label for="Name" class="control-label col-sm-3">Сумма</label>
        <div class="col-sm-4">
            <input type="text" name="Perechislen[Summ]" id="Summ" class="form-control" value="" maxlength="20">
        </div>
    </div>

    <div class="form-group row">
        <div class="col-sm-12">
            <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
            <input type="submit" id="perevodpartner" value="Перевести" class="btn btn-primary pull-right" disabled="disabled">
        </div>
    </div>

</form>

<?=$this->registerCss('
.sweet-alert p {
    text-align: left;
})
');
?>
