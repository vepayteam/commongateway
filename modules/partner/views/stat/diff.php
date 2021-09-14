<?php

use app\services\payment\models\Bank;
use app\services\payment\models\PaySchet;

/* @var yii\web\View $this */
/* @var Bank[] $banks */

$this->title = "сверка операций с банком";

$this->params['breadtitle'] = "Сверка операций с Банком";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];
?>

<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Сверка операций с Банком</h5>
            </div>
            <div class="ibox-content">
                <form class="form-horizontal" id="diffForm">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="bank">Банк-эквайер</label>
                        <div class="col-sm-10 col-md-6">
                            <select class="form-control" id="bank" name="bank">
                                <?php /** @var Bank $bank */ ?>
                                <?php foreach ($banks as $bank): ?>
                                    <option value="<?= $bank->ID ?>"><?= $bank->Name ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="registryFile">Файл списка операций</label>
                        <div class="col-sm-10 col-md-6">
                            <input class="form-control" id="registryFile" name="registryFile" type="file">
                        </div>
                    </div>
                    <div class="form-group" id="registrySelectColumnGroup" style="display: none;">
                        <label class="col-sm-2 control-label" for="registrySelectColumn">Столбик выборки из файла реестра</label>
                        <div class="col-sm-10 col-md-6">
                            <select class="form-control" id="registrySelectColumn" name="registrySelectColumn"></select>
                        </div>
                    </div>
                    <div class="form-group" id="dbColumnGroup" style="display: none;">
                        <label class="col-sm-2 control-label" for="dbColumn">Столбик из БД</label>
                        <div class="col-sm-10 col-md-6">
                            <select class="form-control" id="dbColumn" name="dbColumn">
                            </select>
                        </div>
                    </div>
                    <div id="registryStatusColumnGroup" style="display: none;">
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="registryStatusColumn">Столбик статуса из файла реестра</label>
                            <div class="col-sm-10 col-md-6">
                                <select class="form-control" id="registryStatusColumn" name="registryStatusColumn"></select>
                            </div>
                        </div>
                        <div class="form-group row" id="registryStatusColumnGroup">
                            <div class="col-sm-10 col-sm-offset-2">
                                <div class="checkbox">
                                    <input type="checkbox" id="allRegistryStatusSuccess" name="allRegistryStatusSuccess" value="1">
                                    <label for="allRegistryStatusSuccess">Все статусы в реестре успешны</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr/>
                    <div id="registryStatuses" style="display: none;">
                        <?php foreach ([PaySchet::STATUS_DONE, PaySchet::STATUS_ERROR, PaySchet::STATUS_CANCEL] as $status): ?>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="status<?= $status ?>">Статус в реестре для "<?= PaySchet::STATUSES[$status] ?>"</label>
                                <div class="col-sm-10 col-md-6">
                                    <input class="form-control" id="status<?= $status ?>" name="statuses[<?= $status ?>]" type="text">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-10 col-sm-offset-2 col-md-6 col-md-offset-2">
                            <input type="submit" value="Загрузить и сверить" class="btn btn-primary">
                        </div>
                    </div>
                </form>

                <div class="sk-spinner sk-spinner-wave">
                    <div class="sk-rect1"></div>
                    <div class="sk-rect2"></div>
                    <div class="sk-rect3"></div>
                    <div class="sk-rect4"></div>
                    <div class="sk-rect5"></div>
                </div>

                <div class="table-responsive" id="diffDataResult"></div>
            </div>
        </div>
    </div>
</div>

<?php $this->registerJs('lk.diffFunc();'); ?>
