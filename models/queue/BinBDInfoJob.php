<?php

namespace app\models\queue;

use app\models\bank\TCBank;
use app\models\extservice\BindbNet;
use Yii;
use yii\base\BaseObject;
use yii\db\Exception;

class BinBDInfoJob extends BaseObject implements \yii\queue\JobInterface
{
    public $idpay;
    public $card;

    public function execute($queue)
    {
        $this->card = str_ireplace(' ', '', $this->card);
        $this->card = substr($this->card, 7, 1) == "*" ? substr($this->card, 0, 6) : substr($this->card, 0, 8);

        $nameBank = '';
        /*$bank = new TCBank(); - не работает у них
        try {
            $ret = $bank->GetBinDBInfo($this->card);
            if ($ret['status'] == 1) {
                $nameBank = isset($ret['info']['bank_name']) ? $ret['info']['bank_name'] : '';
            }
        } catch (\Exception $e) {
        }*/

        if (empty($nameBank)) {
            $binNet = new BindbNet();
            $ret = $binNet->GetBankInfo($this->card);
            if ($ret['status'] == 1) {
                $nameBank = isset($ret['info']['bank']['name']) ? $ret['info']['bank']['name'] : '';
            }
        }

        if (!empty($nameBank)) {
            try {
                Yii::$app->db->createCommand()
                    ->update('pay_schet', ['BankName' => $nameBank], '`ID` = :ID', [':ID' => $this->idpay])
                    ->execute();
            } catch (Exception $e) {
            }
        }
    }

}