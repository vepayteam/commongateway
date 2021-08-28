<?php


namespace app\services\statements;


use app\models\kfapi\KfStatement;
use app\services\payment\models\PartnerBankGate;
use app\services\statements\jobs\GetStatementsJob;
use app\services\statements\models\StatementsAccount;
use app\services\statements\models\StatementsPlanner;
use Yii;
use yii\helpers\Json;

class StatementsService
{
    /**
     * @param KfStatement $kfStatement
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getBanksStatements(KfStatement $kfStatement)
    {
        $gates = $kfStatement->partner
            ->getBankGates()
            ->where([
                'Enable' => 1,
            ])
            ->all();
        $createdJobHashes = [];
        /** @var PartnerBankGate $gate */
        foreach ($gates as $gate) {
            $isExistStatements = StatementsPlanner::find()
                ->where([
                    'IdPartner' => $kfStatement->partner->ID,
                    'BankId' => $gate->BankId,
                    'IdTypeAcc' => $gate->SchetType,
                ])
                ->andWhere(['<', 'DateUpdateFrom', $kfStatement->datefrom])
                ->andWhere(['>', 'DateUpdateTo', $kfStatement->dateto])
                ->andWhere(['<', 'DateUpdateTo', time() - 60 * 15])
                ->exists();


            if(!$isExistStatements) {
                $jobParams = [
                    'IdPartner' => $kfStatement->partner->ID,
                    'bankId' => $gate->BankId,
                    'TypeAcc' => $gate->SchetType,
                    'datefrom' => $kfStatement->datefrom,
                    'dateto' => $kfStatement->dateto,
                ];
                $hash = md5(Json::encode($jobParams));

                if(!in_array($hash, $createdJobHashes)) {
                    $createdJobHashes[] = $hash;
                    Yii::$app->queue->push(new GetStatementsJob($jobParams));
                }
            }
        }

        $statements = StatementsAccount::find()
            ->where([
                'IdPartner' => $kfStatement->partner->ID,
                'TypeAccount' => $kfStatement->typeAcc,
            ])
            ->andWhere(['>', 'DatePP', $kfStatement->datefrom])
            ->andWhere(['<', 'DatePP', $kfStatement->dateto])
            ->orderBy('DatePP ' . ($kfStatement->sort ? 'DESC' : 'ASC'))
            ->all();

        return $statements;
    }
}
