<?php

use yii\grid\GridView;
use yii\helpers\Html;

echo Html::tag('h3', Html::encode('Сообщения очереди(reserved)'), ['class' => '']);
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'key',
        'value'
    ],
]);
