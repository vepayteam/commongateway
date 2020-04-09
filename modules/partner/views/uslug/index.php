<?php
/* @var yii\web\View $this */
/* @var array $uslug */
/* @var $IsAdmin bool */
/* @var \app\models\payonline\Partner $partner */
/* @var array $partnerlist */
/* @var array $magazlist */

use app\models\payonline\Uslugatovar;

$this->title = "точки продаж";

$this->params['breadtitle'] = "Точки продаж";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];

$st = [0 => 'Новая', 1 => 'Активна', 2 => 'Заблокирована'];
$stc = [0 => 'label-info', 1 => 'label-success', 2 => 'label-danger'];
?>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5>Точки продаж</h5>
                <div class="ibox-tools">
                    <input type="hidden" value="<?= $partner ? $partner->ID : 0 ?>" id="IdPartner"/>
                </div>
            </div>
            <div class="ibox-content">

                <div class="row m-b-sm m-t-none">
                    <div class="col-sm-12">
                        <a href="point-add/<?= $partner ? $partner->ID : 0 ?>" id="PointAddBtn"
                           class="btn btn-primary">Добавить</a>
                    </div>
                </div>

                <div class="row m-b-xl m-t-sm">
                    <?php if ($IsAdmin) : ?>
                    <div class="col-sm-4">
                    <select class="form-control" name="partnersel">
                        <option value="-1">Все мерчанты</option>
                        <? foreach ($partnerlist as $partn) : ?>
                            <option value="<?=$partn->ID?>"><?=$partn->Name?></option>
                        <? endforeach; ?>
                    </select>
                    </div>
                    <?php endif; ?>
                    <div class="col-sm-4">
                        <select class="form-control" name="magazsel">
                            <option value="-1">Все магазины</option>
                            <? foreach ($magazlist as $mag) : ?>
                                <option value="<?=$mag->ID?>"><?=$mag->NameMagazin?></option>
                            <? endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="project-list">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>Номер</th>
                            <th>Название точки</th>
                            <th>Статус</th>
                            <th>Тип</th>
                            <th>Адрес</th>
                            <th class="text-right">Действия</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (is_array($uslug) && count($uslug) > 0) : ?>
                            <?php foreach ($uslug as $usl) : ?>
                                <tr data-partner="<?=$usl['IDPartner']?>" data-magaz="<?=$usl['IdMagazin']?>">
                                    <td class="project-title">
                                        <?= $usl['ID'] ?>
                                    </td>
                                    <td class="project-title"><?= $usl['NameUsluga'] ?></td>
                                    <td class="project-title">
                                        <span class="label <?=$stc[$usl['EnabledStatus']]?>"><?=$st[$usl['EnabledStatus']]?></span>
                                    </td>
                                    <td class="project-title">Интернет-эквайринг</td>
                                    <td class="project-title"><?= $usl['SitePoint'] ?></td>
                                    <td class="project-actions">
                                        <a class="btn btn-white btn-sm" href="point-edit/<?= $usl['ID'] ?>" title="Изменить">
                                            <i class="fa fa-pencil" aria-hidden="true"></i></a>
                                        <a class="btn btn-white btn-sm"
                                           data-action="delPoint"
                                           data-id="<?=$usl['ID']?>" title="Удалить">
                                            <i class="fa fa-trash" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5">Нет услуг</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->registerJs('points.listinit()', yii\web\View::POS_READY);