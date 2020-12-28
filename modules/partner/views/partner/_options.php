<?php

use app\models\payonline\Partner;
use app\services\partners\models\PartnerOption;
use yii\bootstrap\ActiveForm;

/**
 * @var Partner $partner
 */

$form = ActiveForm::begin([
    'id' => 'partner-options__form',
    'successCssClass' => '',
]);
?>

<?php foreach (PartnerOption::LIST as $k => $optionVariable): ?>
    <div class="row form-group">

        <?php
            /** @var PartnerOption $partnerOption */
            $partnerOption = $partner->getOptions()->where(['Name' => $k])->one();
        ?>
        <input type="hidden" name="PartnerId" value="<?=(int)$partner->ID?>">
        <label class="control-label col-sm-3" for="partner-kpp"><?= $optionVariable['title'] ?></label>
        <div class="col-sm-8">
            <?php if ($optionVariable['type'] === 'textarea'): ?>
                <textarea class="form-control partner-options__input" name="<?= $k ?>" rows="5"><?= $partnerOption ? $partnerOption->Value : PartnerOption::LIST[$k]['default'] ?></textarea>
            <?php else: ?>
                <input type="<?=$optionVariable['type']?>" id="partner-kpp" class="form-control partner-options__input"
                       name="<?= $k ?>"
                       value="<?= $partnerOption ? $partnerOption->Value : PartnerOption::LIST[$k]['default'] ?>"
                       aria-invalid="false"
                >
            <?php endif; ?>


            <p class="help-block help-block-error "></p>
        </div>

    </div>
<?php endforeach; ?>
<?php
ActiveForm::end();
?>
<div class="row form-group">
    <button class="btn btn-primary" id="partner-options__submit" type="button">Сохранить</button>
</div>
