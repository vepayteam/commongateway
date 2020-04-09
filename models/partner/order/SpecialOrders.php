<?php


namespace app\models\partner\order;


use Yii;
use yii\data\SqlDataProvider;
use yii\db\Command;
use yii\db\Query;
use yii\helpers\VarDumper;

class SpecialOrders implements ISpecialOrder
{

    private $idOrg;

    public function __construct(int $idOrg)
    {
        $this->idOrg = $idOrg;
    }

    /**
     * @param int $idOrg - id организации для которой ищутся транзакции.
     *
     * @return SqlDataProvider - Провайдер данных, для их же отображения.
     */
    public function TransactionProvider(): SqlDataProvider
    {
        $command = $this->statusCreatedCommand($this->idOrg);
        $provider = new SqlDataProvider(
            [
                'sql' => $command->sql,
                'params' => $command->params,
                'pagination' => [
                    'pageSize' => 10
                ],
                'totalCount' => $this->count($command)
            ]
        );
        return $provider;
    }

    private function statusCreatedCommand(int $idOrg): Command
    {
        return Yii::$app->db->createCommand(
            "SELECT
                    p.ID, 
                    p.ComissSumm,
                    p.SummPay,
                    p.Schetcheks,
                    p.QrParams,
                    p.IdShablon,
                    p.IdUsluga,
                    us.NameUsluga
              FROM
                `pay_schet` AS p
                LEFT JOIN `uslugatovar` AS us ON us.ID = p.IdUsluga
              WHERE
                p.`IdOrg` = :idOrg AND
                p.Status = 0 AND 
                p.sms_accept = 0
              order by p.ID
              DESC 
                ",
            [
                ":idOrg" => $idOrg
            ]
        );
    }

    private function count(Command $command): int
    {
        return count($command->queryAll()); //query scalar не подходит.
    }

    private function isAdmin()
    {
        if (Yii::$app->user->identity->getIsAdmin()){
            return true;
        }
        return false;
    }
}