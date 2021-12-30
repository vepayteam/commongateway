<?php

/* @var $acts ActMfo[] */
/* @var $IsAdmin */

use app\models\partner\stat\ActMfo;
use yii\helpers\Html;
use yii\helpers\Url;

$pubdone = 0;
$puball = 1;
foreach ($acts as $act) {
    if ($act->IsPublic) {
        $pubdone = 1;
    }
    if (!$act->IsPublic) {
        $puball = 0;
    }
}

?>
<?php if ($IsAdmin) : ?>
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2" style="padding-left: 0; padding-bottom: 20px;">
            <input class="btn btn-sm btn-default" type="button" value="Сформировать" id="btnFormirAct" <?=$pubdone ? 'disabled="disabled"' : ''?> >
            <input class="btn btn-sm btn-default" type="button" value="Опубликовать" id="btnPubAct" <?=$puball ? 'disabled="disabled"' : ''?> >
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-sm-12">
        <table class="table table-striped">
            <thead>
            <tr>
                <th>№</th>
                <th>Наименование проекта/контрагента</th>
                <th>Сумма<br>комиссии / выплаты</th>
                <th>Период</th>
                <th>Акт</th>
                <?php if ($IsAdmin) : ?>
                    <th>Сумма счета</th>
                    <th>Счет</th>
                    <th>п/п</th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
                <?php foreach ($acts as $act) : ?>
                <tr>
                    <?php if ($IsAdmin) : ?>
                        <td><a href="/partner/stat/act-edit/<?=Html::encode($act->ID)?>"><?=Html::encode($act->ID)?></a></td>
                    <?php else: ?>
                        <td><?=Html::encode($act->ID)?></td>
                    <?php endif; ?>
                    <td><?=$act->getPartner()->Name?></td>
                    <td class="text-right"><span style="color: darkgreen"><?=number_format($act->ComisPerevod/100.0, 2, '.', '&nbsp;')?></span></td>
                    <td><?=date("m.Y", $act->ActPeriod)?></td>
                    <td><a href="<?= Url::to(['stat/acts-xls'])?>/<?=$act->ID?>" target=_blank> Скачать XSL</a></td>
                    <?php if ($IsAdmin) : ?>
                        <td class="text-right">
                            <?=number_format($act->SumSchetComisVyplata/100.0, 2, '.', '&nbsp;')?>
                        </td>
                        <td>
                            <?php if (($schet = $act->getActSchet()) != null) : ?>
                                <a href="<?=Url::to(['stat/acts-schetget'])?>/<?=Html::encode($schet->ID)?>" target=_blank> Скачать счет</a>
                            <?php elseif ($act->getPartner()->IsMfo && !$act->getPartner()->VoznagVyplatDirect) : ?>
                                <a href="javascript::void()" class="btn btn-white btn-xs" data-click="schetsend" data-id="<?=Html::encode($act->ID)?>"> Выставить счет</a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($act->ComisPerevod < 0) : ?>
                                <a href="<?= Url::to(['stat/acts-pp'])?>/<?=Html::encode($act->ID)?>" target=_blank> Скачать п/п</a>
                            <?php else: ?>
                                n/a
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>