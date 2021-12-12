<?php

use app\models\partner\stat\ActMfo;
use yii\helpers\Html;

/* @var $act ActMfo */

$this->title = "Отчетные документы: редактирование акта";
$this->params['breadtitle'] = "Отчетные документы: редактирование акта";
$this->params['breadcrumbs'][] = ['label' => 'Отчетные документы', 'url' => ['/partner/stat/acts']];
$this->params['breadcrumbs'][] = $this->params['breadtitle'];

?>

<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5><?=Html::encode($this->title)?></h5>
            </div>
            <div class="ibox-content">

                <div class="row">
                    <div class="col-sm-12">
                        <?php if ($act): ?>
                            <form method="POST" id="formEditAct">
                                <table class="table table-striped" style="width: 100%">
                                    <tr>
                                        <th style="width: 30px;">#</th>
                                        <th>Наименование</th>
                                        <th style="width: 250px;">Значение</th>
                                    </tr>
                                    <tr>
                                        <td>1</td>
                                        <td colspan="2"><?=Html::encode($act->getPartner()->Name)?></td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Период</td>
                                        <td><?=date("m.Y", $act->ActPeriod)?></td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>Задолженность Оператора перед Контрагентом на начало Отчетного периода, рубли</td>
                                        <td><input type="text" class="form-control" name="ActMfo[BeginOstatokPerevod]" value="<?=$act->BeginOstatokPerevod/100.0?>"></td>
                                    </tr>
                                    <tr>
                                        <td>5</td>
                                        <td>Cумма переводов, принятых Оператором в Отчетном периоде, рубли</td>
                                        <td><input type="text" class="form-control" name="ActMfo[SumPerevod]" value="<?=$act->SumPerevod/100.0?>"></td>
                                    </tr>
                                    <tr>
                                        <td>6</td>
                                        <td>Сумма вознаграждения Оператора за Отчетный период, НДС не облагается в соответствии с пп. 4 п. 3 статьи 149 Налогового кодекса РФ, рубли</td>
                                        <td><input type="text" class="form-control" name="ActMfo[ComisPerevod]" value="<?=$act->ComisPerevod/100.0?>"></td>
                                    </tr>
                                    <tr>
                                        <td>7</td>
                                        <td>Возвращено Оператором переводов в Отчетном периоде, рубли</td>
                                        <td><input type="text" class="form-control" name="ActMfo[SumVozvrat]" value="<?=$act->SumVozvrat/100.0?>"></td>
                                    </tr>
                                    <tr>
                                        <td>8</td>
                                        <td>Подлежит удержанию Оператором по оспариваемым операциям в Отчетном периоде, рубли</td>
                                        <td><input type="text" class="form-control" name="ActMfo[SumPodlejUderzOspariv]" value="<?=$act->SumPodlejUderzOspariv/100.0?>"></td>
                                    </tr>
                                    <tr>
                                        <td>9</td>
                                        <td>Подлежит возмещению Оператором по оспариваемым операциям в Отчетном периоде, рубли</td>
                                        <td><input type="text" class="form-control" name="ActMfo[SumPodlejVozmeshOspariv]" value="<?=$act->SumPodlejVozmeshOspariv/100.0?>"></td>
                                    </tr>
                                    <tr>
                                        <td>10</td>
                                        <td>Перечислено Контрагентом на расчетный счет Оператора, рубли (в т.ч. суммы, перечисленные Оператором на счет Контрагента, отклоненные банком и подлежащие перечислению повторно)</td>
                                        <td><input type="text" class="form-control" name="ActMfo[SumPerechKontrag]" value="<?=$act->SumPerechKontrag/100.0?>"></td>
                                    </tr>
                                    <tr>
                                        <td>11</td>
                                        <td>Перечислено Оператором на счет Контрагента в Отчетном периоде, рубли</td>
                                        <td><input type="text" class="form-control" name="ActMfo[SumPerechislen]" value="<?=$act->SumPerechislen/100.0?>"></td>
                                    </tr>
                                    <tr>
                                        <td>12</td>
                                        <td>Перечислено Оператором на счет по учету обеспечения Контрагента в соответствии с Соглашением в Отчетном периоде, рубли</td>
                                        <td><input type="text" class="form-control" name="ActMfo[SumPerechObespech]" value="<?=$act->SumPerechObespech/100.0?>"></td>
                                    </tr>
                                    <tr>
                                        <td>13</td>
                                        <td>Задолженность Оператора перед Контрагентом на конец Отчетного периода, рубли</td>
                                        <td><input type="text" class="form-control" name="ActMfo[EndOstatokPerevod]" value="<?=$act->EndOstatokPerevod/100.0?>"></td>
                                    </tr>
                                </table>
                                <input type="hidden" name="ID" value="<?=Html::encode($act->ID)?>">
                                <?= Html::hiddenInput('_csrf', Yii::$app->request->csrfToken, ['id' => '_csrf']) ?>
                            </form>
                        <?php else: ?>
                            <div class="row">
                                <div class="col-sm-12">
                                    <p class="text-center">Нет акта, необходимо сформировать</p>
                                </div>
                            </div>
                        <?php endif;?>
                    </div>
                </div>

                <div class="row">
                    <?php if ($act): ?>
                        <div class="col-sm-4">
                            <input class="btn btn-sm btn-primary" type="button" value="Сохранить" id="btnSaveAct">
                        </div>
                    <?php endif;?>
                </div>

            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs("act.edit();");
?>