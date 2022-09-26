<?php

use app\modules\partner\models\data\PartListItem;
use app\modules\partner\models\search\PartListFilter;
use yii\data\ActiveDataProvider;
use yii\grid\DataColumn;
use yii\grid\GridView;
use yii\web\View;

/**
 * @var View $this
 * @var PartListFilter $searchModel
 * @var ActiveDataProvider $dataProvider
 * @var array $columns
 */

$instance = PartListItem::instance();
$numberFormat = function (PartListItem $item, $key, $index, DataColumn $column) {
    $value = $item->{$column->attribute} ?? null;
    if ($value === null) {
        return null;
    }
    return number_format($value, 2);
};

$grid = GridView::begin([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'options' => ['class' => 'grid-view table-responsive'],
    'layout' => "{pager}\n{summary}\n<div class=\"table-responsive text-nowrap\">{items}</div>\n{pager}",
    'columns' => [
        [
            'attribute' => 'paySchetId',
            'label' => $instance->getAttributeLabel('paySchetId'),
            'filterInputOptions' => [
                'class' => 'form-control',
                'style' => 'width: 90px',
            ],
        ],
        [
            'attribute' => 'partnerName',
            'label' => $instance->getAttributeLabel('partnerName'),
        ],
        [
            'attribute' => 'partAmount',
            'label' => $instance->getAttributeLabel('partAmount'),
            'value' => $numberFormat,
        ],
        [
            'attribute' => 'createdAt',
            'label' => $instance->getAttributeLabel('createdAt'),
            'format' => ['date', 'php:d.m.Y H:i:s'],
        ],
        [
            'attribute' => 'extId',
            'label' => $instance->getAttributeLabel('extId'),
        ],
        [
            'attribute' => 'paySchetAmount',
            'label' => $instance->getAttributeLabel('paySchetAmount'),
            'value' => $numberFormat,
        ],
        [
            'attribute' => 'clientCompensation',
            'label' => $instance->getAttributeLabel('clientCompensation'),
            'value' => $numberFormat,
        ],
        [
            'attribute' => 'partnerCompensation',
            'label' => $instance->getAttributeLabel('partnerCompensation'),
            'value' => $numberFormat,
        ],
        [
            'attribute' => 'bankCompensation',
            'label' => $instance->getAttributeLabel('bankCompensation'),
            'value' => $numberFormat,
        ],
        [
            'attribute' => 'message',
            'label' => $instance->getAttributeLabel('message'),
        ],
        [
            'attribute' => 'cardNumber',
            'label' => $instance->getAttributeLabel('cardNumber'),
        ],
        [
            'attribute' => 'cardHolder',
            'label' => $instance->getAttributeLabel('cardHolder'),
        ],
        [
            'attribute' => 'contract',
            'label' => $instance->getAttributeLabel('contract'),
        ],
        [
            'attribute' => 'fio',
            'label' => $instance->getAttributeLabel('fio'),
        ],
        [
            'attribute' => 'withdrawalPayschetId',
            'label' => $instance->getAttributeLabel('withdrawalPayschetId'),
        ],
        [
            'attribute' => 'withdrawalAmount',
            'label' => $instance->getAttributeLabel('withdrawalAmount'),
            'value' => $numberFormat,
        ],
        [
            'attribute' => 'withdrawalCreatedAt',
            'label' => $instance->getAttributeLabel('withdrawalCreatedAt'),
            'format' => ['date', 'php:d.m.Y H:i:s'],
        ],
    ],
]);

foreach ($grid->columns as $i => $column) {
    if ($column instanceof DataColumn && !in_array($column->attribute, $columns)) {
        unset($columns[$i]);
    }
}
$grid->columns = array_values($grid->columns);

GridView::end();
