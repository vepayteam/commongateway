<?php
/* @var yii\web\View $this */
/* @var array $list */
/* @var $IsAdmin bool */
/* @var $roleUser int */
/* @var $partnerlist array */

use app\models\payonline\Partner;

$this->title = "контрагенты";

$this->params['breadtitle'] = "Контрагенты";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];

?>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Контрагенты</h5>
                </div>
                <div class="ibox-content">

                    <?php if ($IsAdmin) : ?>

                        <div class="row m-b-sm m-t-none">
                            <div class="col-sm-12">
                                <a href="#modal-regpartner" data-toggle="modal" class="btn btn-primary">Регистрация</a>
                            </div>
                            <?=$this->render('_registermodal')?>
                        </div>


                    <?php endif; ?>

                    <div class="project-list">

                        <?php if ($IsAdmin) : ?>
                            <div class="row m-b-xl m-t-sm">
                                <div class="col-sm-4">
                                    <div class="btn-group" data-toggle="buttons">
                                        <label class="btn btn-default active">
                                            <input type="radio" name="partnertypesel" id="partnertypesel0" autocomplete="off" value="0" checked> Мерчанты
                                        </label>
                                        <label class="btn btn-default">
                                            <input type="radio" name="partnertypesel" id="partnertypesel1" autocomplete="off" value="1"> Партнеры
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <table class="table table-hover" id="listpartners">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Наименование</th>
                                <th>Юрлицо</th>
                                <th>Контактное лицо</th>
                                <th>Тип контрагента</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (is_array($list) && count($list) > 0) : ?>
                                <?php /** @var Partner $usl */
                                foreach ($list as $usl) : ?>
                                    <tr data-parttype="<?=$usl->TypeMerchant?>" data-click="/partner/partner/partner-edit/<?=$usl->ID?>" style="cursor: pointer;">
                                        <td class="project-title"><?=$usl->ID?></td>
                                        <td class="project-title"><?=$usl->Name?></td>
                                        <td class="project-title"><?=$usl->UrLico?></td>
                                        <td class="project-title">
                                            <div><?=$usl->KontTehFio?></div>
                                            <div><?=$usl->KontTehPhone?></div>
                                            <div><?=$usl->KontTehEmail?></div>
                                        </td>
                                        <td class="project-title"><?=Partner::$TypeContrag[$usl->TypeMerchant]?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr><td colspan="4">Нет услуг</td></tr>
                            <?php endif; ?>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php $this->registerJs('lk.listpartners()', yii\web\View::POS_READY);