<?php

use yii\grid\GridView;
use yii\helpers\Html;

echo Html::tag('h3', Html::encode('Состояние очереди'), ['class' => '']);
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'status',
        'count'
    ],
]);
