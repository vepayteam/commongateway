<?php

use app\modules\partner\models\data\IdentificationListItem;
use app\modules\partner\models\search\IdentificationListFilter;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\web\View;

/**
 * @var View $this
 * @var IdentificationListFilter $searchModel
 * @var ActiveDataProvider $dataProvider
 * @var array $columns
 */

$instance = IdentificationListItem::instance();

$grid = GridView::begin([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'options' => ['class' => 'grid-view table-responsive'],
    'layout' => "{pager}\n{summary}\n<div class=\"table-responsive text-nowrap\">{items}</div>\n{pager}",
    'columns' => [
        [
            'attribute' => 'id',
            'label' => $instance->getAttributeLabel('id'),
            'filterInputOptions' => [
                'class' => 'form-control',
                'style' => 'width: 90px',
            ],
        ],
        [
            'attribute' => 'createdAt',
            'label' => $instance->getAttributeLabel('createdAt'),
            'format' => ['date', 'php:d.m.Y H:i:s'],
        ],
        [
            'attribute' => 'firstName',
            'label' => $instance->getAttributeLabel('firstName'),
        ],
        [
            'attribute' => 'transactionNumber',
            'label' => $instance->getAttributeLabel('transactionNumber'),
        ],
        [
            'attribute' => 'lastName',
            'label' => $instance->getAttributeLabel('lastName'),
        ],
        [
            'attribute' => 'middleName',
            'label' => $instance->getAttributeLabel('middleName'),
        ],
        [
            'attribute' => 'inn',
            'label' => $instance->getAttributeLabel('inn'),
        ],
        [
            'attribute' => 'snils',
            'label' => $instance->getAttributeLabel('snils'),
        ],
        [
            'attribute' => 'passportSeries',
            'label' => $instance->getAttributeLabel('passportSeries'),
        ],
        [
            'attribute' => 'passportNumber',
            'label' => $instance->getAttributeLabel('passportNumber'),
        ],
        [
            'attribute' => 'passportDepartmentCode',
            'label' => $instance->getAttributeLabel('passportDepartmentCode'),
        ],
        [
            'attribute' => 'passportIssueDate',
            'label' => $instance->getAttributeLabel('passportIssueDate'),
            'format' => ['date', 'php:d.m.Y'],
        ],
        [
            'attribute' => 'passportIssuedBy',
            'label' => $instance->getAttributeLabel('passportIssuedBy'),
        ],
    ],
]);

GridView::end();
