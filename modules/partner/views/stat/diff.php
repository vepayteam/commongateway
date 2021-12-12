<?php

/* @var yii\web\View $this */
/* @var array $uslugilist */
/* @var $partnerlist  */
/* @var $IsAdmin bool */

$this->title = "сверка операций с провайдером";

$this->params['breadtitle'] = "Сверка операций с провайдером";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];
?>

<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Сверка операций с провайдером</h5>
            </div>
            <div class="ibox-content">
                <form class="form-horizontal" id="diffdataform">
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="bank">Провайдер</label>
                        <div class="col-sm-10 col-md-6">
                            <select class="form-control" id="bank" name="bank">
                                <option value="TKB">ТКБ</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="registryFile">Файл списка операций</label>
                        <div class="col-sm-10 col-md-6">
                            <input class="form-control" id="registryFile" name="registryFile" type="file">
                        </div>
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

                <div class="table-responsive" id="diffdataresult"></div>
            </div>
        </div>
    </div>
</div>

<?php $this->registerJs('
lk.diffdata();
'); ?>
