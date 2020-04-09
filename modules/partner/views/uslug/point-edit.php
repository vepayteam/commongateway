<?php

/* @var $this \yii\web\View */
/* @var $isAdmin boolean */
/* @var $partner \app\models\payonline\Partner */
/* @var $usl \app\models\payonline\Uslugatovar */
/* @var $mags array */

$this->title = "изменить точку";

$this->params['breadtitle'] = "Изменить точку";
$this->params['breadcrumbs'][] = ['label' => 'Точки продаж', 'url' => ['/partner/uslug/index']];
$this->params['breadcrumbs'][] = $this->params['breadtitle'];
?>

<div class="row">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Точка №<?=$usl->ID?></h5>
                <?php if (intval($usl->ID) > 0) : ?>
                <div class="ibox-tools">
                    <a class="btn btn-xs btn-primary" data-toggle="modal" data-target="#showKodModal"
                       data-id="<?=$usl->ID?>"><i class="fa fa-cog" aria-hidden="true"></i> Код на сайт</a>
                    <a class="btn btn-xs btn-default" href="/partner/uslug/index"><i class="fa fa-close" aria-hidden="true"></i> Назад</a>
                </div>
                <?php endif; ?>
            </div>
            <div class="ibox-content">
                <div class="sk-spinner sk-spinner-wave">
                    <div class="sk-rect1"></div>
                    <div class="sk-rect2"></div>
                    <div class="sk-rect3"></div>
                    <div class="sk-rect4"></div>
                    <div class="sk-rect5"></div>
                </div>
                <form class="form-horizontal" id="pointeditform">
                    <p><?=$partner->Name?></p>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Наименование точки</label>
                        <div class="col-sm-8">
                            <input type="text" name="NameUsluga" class="form-control" value="<?=$usl->NameUsluga?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Статус</label>
                        <div class="col-sm-8">
                            <?php if ($isAdmin) : ?>
                                <select class="form-control" name="EnabledStatus">
                                    <option value="0" <?=$usl->EnabledStatus == 0 ? 'selected' : ''?>>Новая</option>
                                    <option value="1" <?=$usl->EnabledStatus == 1 ? 'selected' : ''?>>Активна</option>
                                    <option value="2" <?=$usl->EnabledStatus == 2 ? 'selected' : ''?>>Заблокирована</option>
                                </select>
                            <?php elseif ($usl->isNewRecord || $usl->EnabledStatus == 0): ?>
                                <div style="margin-top: 7px;"><span class="label label-warning-light">Новая</span></div>
                            <?php else: ?>
                                <select class="form-control" name="EnabledStatus">
                                    <option value="1" <?=$usl->EnabledStatus == 1 ? 'selected' : ''?>>Активна</option>
                                    <option value="2" <?=$usl->EnabledStatus == 2 ? 'selected' : ''?>>Заблокирована</option>
                                </select>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Тип точки</label>
                        <div class="col-sm-8">
                            <select class="form-control">
                                <option value="0" selected>Интернет-эквайринг</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Магазин</label>
                        <div class="col-sm-8">
                            <select class="form-control" name="IdMagazin">
                                <option value="0" <?=$usl->IdMagazin == 0 ? 'selected' : ''?>>Нет</option>
                                <?php if ($mags) : ?>
                                    <?php foreach ($mags as $m) : ?>
                                    <option value="<?=$m->ID?>" <?=$usl->IdMagazin == $m->ID ? 'selected' : ''?>><?=$m->NameMagazin?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">URL-адрес точки</label>
                        <div class="col-sm-8">
                            <input type="url" name="SitePoint" class="form-control" value="<?=$usl->SitePoint?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Цветовая схема</label>
                        <div class="col-sm-8">
                            <span class="col-sm-6" style="padding-left: 0">
                            Основной цвет: <input type="text" class="form-control" id="colorpickerField1" name="ColorWdtMain" value="<?=$usl->ColorWdtMain?>">
                            </span>
                            <span class="col-sm-6" style="padding-right: 0">
                            Цвет выбранного: <input type="text" class="form-control ColorWdt" id="colorpickerField2" name="ColorWdtActive" value="<?=$usl->ColorWdtActive?>">
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Логотип магазина</label>
                        <div class="col-sm-8">
                            <div class="fileinput fileinput-new" data-provides="fileinput">
                            <span class="btn btn-default btn-file"><span class="fileinput-new">Выберите логотип</span>
                            <span class="fileinput-exists">Изменить</span>
                                <input type="file" name="fileLogoProv" accept=".png,.jpg"/></span>
                                <span class="fileinput-filename"></span>
                                <a href="#" class="close fileinput-exists" data-dismiss="fileinput" style="float: none">×</a>
                            </div>
                            <?php if (!empty($usl->LogoProv)) : ?>
                                <div class="row"><img src="/shopdata/<?=$usl->IDPartner?>/<?=$usl->LogoProv?>" class="m-l" style="width: 100px; height: auto;" /></div>
                            <? endif; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Инструменты платежа</label>
                        <div class="col-sm-8">
                            <select class="form-control">
                                <option value="0" selected>Банковская карта</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Email для оповещения о платеже и отправки реестра</label>
                        <div class="col-sm-8">
                            <input type="email" class="form-control" name="EmailReestr" value="<?=$usl->EmailReestr?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">URL-адрес для оповещения о платеже</label>
                        <div class="col-sm-8">
                            <input type="url" class="form-control" name="UrlInform" value="<?=$usl->UrlInform?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">URL-адрес для возврата клиента</label>
                        <div class="col-sm-8">
                            <input type="url" class="form-control" name="UrlReturn" value="<?=$usl->UrlReturn?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Шаблон email-уведомления клиента об успешном платеже</label>
                        <div class="col-sm-8">
                            <input type="button" class="btn btn-default" value="Форма чека" disabled>
                            <input type="button" class="btn btn-default" value="Шаблон уведомления"
                                   data-target="#modalMailInfo" data-toggle="modal">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-8">
                            <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                            <input name="ID" type="hidden" value="<?=intval($usl->ID)?>">
                            <input name="idpart" type="hidden" value="<?=intval($usl->IDPartner)?>">
                            <button class="btn btn-primary" type="submit">Сохранить</button>
                        </div>
                    </div>
                    <input type="hidden" name="EmailShablon" value="<?=$usl->EmailShablon?>">
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade in" id="showKodModal" tabindex="-1" role="dialog" aria-labelledby="showKodModalLabel"
     aria-hidden="true" style="display: none; padding-right: 17px;">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header"><!-- modal header -->
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="showKodModalLabel">Код на сайт</h4>
            </div><!-- /modal header -->

            <!-- modal body -->
            <div class="modal-body" id="showKodModalBody"></div>
            <!-- /modal body -->

            <div class="modal-footer"><!-- modal footer -->
                <button class="btn btn-primary" data-dismiss="modal">OK</button>
            </div><!-- /modal footer -->

        </div>
    </div>
</div>

<div class="modal fade in" id="modalMailInfo" tabindex="-1" role="dialog" aria-labelledby="modalMailInfoLabel"
     aria-hidden="true" style="display: none; padding-right: 17px;">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header"><!-- modal header -->
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="modalMailInfoLabel">Шаблон уведомления</h4>
            </div><!-- /modal header -->

            <!-- modal body -->
            <div class="modal-body no-padding" id="modalMailInfoBody">
                <div id="summernote">
                </div>
            </div>

            <div class="modal-footer"><!-- modal footer -->
                <button class="btn btn-primary" id="modalMailInfoSave">Сохранить</button>
                <button class="btn btn-default" data-dismiss="modal">Отмена</button>
            </div><!-- /modal footer -->

        </div>
    </div>
</div>
<?php $this->registerJs('points.init()', yii\web\View::POS_READY);