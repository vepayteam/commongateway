<?php

/* @var View $this */
/* @var $uslugi Uslugatovar[] */

/* @var $partner Partner */

use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use yii\web\View;

?>

<div class="row">
    <div class="m-md">
        <a href="/partner/partner/uslugi-add/<?= $partner->ID ?>" id="UslugiAddBtn" class="btn btn-primary">Добавить</a>
    </div>
</div>

<div class="project-list">
    <table class="table table-hover">
        <thead>
        <tr>
            <th>Номер</th>
            <th>Название</th>
            <th>Сумма</th>
            <th colspan="2">Комиссия</th>
            <th class="text-right">Корзина</th>
        </tr>
        </thead>
        <tbody>
        <?php if (is_array($uslugi) && count($uslugi) > 0) : ?>
            <?php foreach ($uslugi as $usl) : ?>
                <tr data-partner="<?= $usl->IDPartner ?>">
                    <td class="project-title"><?= $usl->ID ?></td>
                    <td class="project-title"><?= $usl->NameUsluga ?></td>
                    <? /*<td class="project-title"><?=($usl->IsCustom ? 'виджет' : $usl['Labels'])?></td>
                        <td class="project-title"><?=($usl->qr_group ? $usl->qr_group->NameGroup : '')?></td>
                        <td class="project-title"><?=($usl->uslugi_regions ? $usl->uslugi_regions->NameRegion : 'Все')?></td>*/ ?>
                    <td class="project-title"><?= $usl->MinSumm / 100.0 ?><?= ($usl->MaxSumm != $usl->MinSumm ? "- " . $usl->MaxSumm / 100.0 : "") ?></td>
                    <td class="project-title"><?= $usl->PcComission ?>%</td>
                    <td class="project-title"><?= ($usl->MinsumComiss > 0 ? "не менее " . $usl->MinsumComiss . " руб" : "") ?></td>
                    <? /*<td class="project-title"><?=Uslugatovar::$TypeExport_str[$usl->TypeExport]?></td>*/ ?>
                    <td class="project-actions">
                        <a href="/partner/partner/uslugi-edit/<?= $usl->ID ?>"
                           class="btn btn-sm btn-default" title="Изменить">
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                        </a>
                        <a data-id="<?= $usl->ID ?>" data-action="delUsluga"
                           class="btn btn-sm btn-default" title="Удалить">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr>
                <td colspan="9">Нет услуг</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $this->registerJs('lk.partnerlistuslugi()', yii\web\View::POS_READY);
