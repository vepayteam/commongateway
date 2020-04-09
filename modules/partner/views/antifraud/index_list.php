<?php

use app\models\payonline\Cards;
use yii\grid\GridView;

/**@var \yii\data\ArrayDataProvider $list_provider */
?>
<div class="row">
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>Платежные поручения (антифрод)</h5>
            </div>
            <div class="ibox-content">
                <div class="table-responsive payment-orders-list">
                    <?=
                    GridView::widget([
                        'dataProvider' => $list_provider,
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
                                'header' => 'ID Vepay',
                                'value' => function ($model) {
                                    return $model['ID'];
                                }

                            ],
                            [
                                'header' => 'Хэш пользователя',
                                'value' => function ($model) {
                                    return $model['user_hash'];
                                }
                            ],
                            [
                                'header' => 'Итоговый "вес"',
                                'value' => function ($model) {
                                    return $model['weight'];
                                }
                            ],
                            [
                                'header' => 'Ext ID',
                                'value' => function ($model) {
                                    return $model['Extid'];
                                }
                            ],
                            [
                                'header' => 'Услуга',
                                'value' => function ($model) {
                                    return $model['NameUsluga'];
                                }
                            ],
                            [
                                'header' => 'Сумма',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return number_format($model['SummPay'] / 100, 2, '.', '&nbsp;');
                                }
                            ],
                            [
                                'header' => 'Комиссия',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return number_format($model['ComissSumm'] / 100, 2, '.', '&nbsp;');
                                }
                            ],
                            [
                                'header' => 'Дата Создания / Оплаты',
                                'value' => function ($model) {
                                    return
                                        date('d.m.Y H:i', $model['DateCreate'])
                                        . ' / ' .
                                        ($model['DateOplat'] > 0 ? date('d.m.Y H:i', $model['DateOplat']) : '-');
                                },
                                'format' => 'raw',
                            ],
                            [
                                'header' => '',
                                'value' => function ($model) {
                                    return '<button class="btn btn-white btn-xs confirm-small send-pjax" data-user-hash="' . $model['user_hash'] . '" data-transaction-id="'.$model['transaction_id'].'">Иформация</button>';
                                },
                                'format' => 'raw'
                            ]
                        ],
                        'layout' => "{sorter}\n{items}\n{pager}"
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>