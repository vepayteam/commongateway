<?php
/**
 * @var View $this
 * @var \yii\data\ActiveDataProvider $data_provider
 * @var \app\models\antifraud\tables\AFRuleInfo $model
 */

use yii\grid\GridView;
use yii\web\View;

$this->title = "Антифрод - общая статистика";
$this->params['breadtitle'] = "Общая статистика";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];
?>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <?= $this->render('_nav'); ?>
            </div>
            <div class="ibox-content">
                <?=
                GridView::widget([
                    'dataProvider' => $data_provider,
                    'pager' => [
                        'firstPageLabel' => '«',
                        'lastPageLabel' => '»',
                        'nextPageLabel' => '›',
                        'prevPageLabel' => '‹',
                        'maxButtonCount' => 4,
                    ],
                    'tableOptions' => [
                        'class' => 'table table-striped tabledata'
                    ],
                    'columns' => [
                        [
                            'header' => 'Правило',
                            'value' => function ($model) {
                                return $model->rule_title;
                            }
                        ],
                        [
                            'header' => 'Описание',
                            'value' => function ($model) {
                                return $model->description;
                            }
                        ],
                        [
                            'header' => 'Пороговое значение',
                            'value' => function ($model) {
                                return $model->critical_value;
                            }
                        ],
                        [
                            'header' => 'Кол-во успешных транзакций',
                            'value' => function ($model) {
                                return $model->success_count;
                            }
                        ],
                        [
                            'header' => 'Кол-во не успешных тразакций',
                            'value' => function ($model) {
                                return $model->fail_count;
                            }
                        ],
                        [
                            'header' => 'Всего транзакций',
                            'value' => function ($model) {
                                return $model->all_count;
                            }
                        ]
                    ],
                    'layout' => "{sorter}\n{items}\n{pager}"
                ]);
                ?>
            </div>
        </div>
    </div>
</div>

