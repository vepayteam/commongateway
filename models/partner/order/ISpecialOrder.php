<?php


namespace app\models\partner\order;


use yii\data\SqlDataProvider;

interface ISpecialOrder
{
    /**
     * @param int $idOrg - id организации для которой ищутся транзакции.
     *
     * @return SqlDataProvider - Провайдер данных, для их же отображения.
     */
    public function TransactionProvider(): SqlDataProvider;
}