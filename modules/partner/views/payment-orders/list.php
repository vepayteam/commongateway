<?php
/**
 * @var \app\models\partner\order\SpecialOrders $orders
 * @var \yii\web\View $this
 */
$this->title = 'Платежные поручения';
$this->params['breadtitle'] = "Платежные поручения";
$this->params['breadcrumbs'][] = $this->params['breadtitle'];

use app\models\payonline\Cards;
use yii\grid\GridView;
use yii\helpers\Html; ?>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Платежные поручения</h5>
            </div>
            <div class="ibox-content">
                <div class="table-responsive payment-orders-list">
                    <?=
                    GridView::widget([
                        'dataProvider' => $orders->TransactionProvider(),
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
                                'label' => '<button class="btn btn-white btn-md select-all ">Выделить все</button>',
                                'value' => function ($model) {
                                    return '<input type="checkbox" class="checkbox-list">';
                                },
                                'format'=>'raw',
                                'encodeLabel'=>false
                            ],
                            [
                                'header' => 'Услуга',
                                'value' => function ($model) {
                                    return $model['NameUsluga'];
                                }
                            ],
                            [
                                'header' => 'Реквизиты',
                                'value' => function ($model) {
                                    $params = $model['QrParams'];
                                    return str_replace("\n", '<br>', Html::encode(str_replace('|', "\n", $params)));
                                },
                                'format' => 'raw'

                            ],
                            [
                                'header' => 'Сумма',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return number_format($model['SummPay']/100, 2, '.', ' ');
                                }
                            ],
                            [
                                'header' => 'Комиссия',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return number_format($model['ComissSumm']/100, 2, '.', ' ');
                                }
                            ],
                            [
                                'header' => 'Действие',
                                'value' => function ($model) {
                                    return '<button class="btn btn-white btn-xs confirm-small" data-id="' . Html::encode($model['ID']) . '">Подтвердить</button>';
                                },
                                'format' => 'raw'
                            ]
                        ],
                        'layout' => "{sorter}\n{items}\n{pager}"
                    ]);
                    ?>
                    <div class="summary">
                        <button class="btn btn-white btn-md disabled confirm-all" disabled>Подтвердить все</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->render('_modal_confirm_form')?>