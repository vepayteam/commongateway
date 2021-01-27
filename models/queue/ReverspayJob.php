<?php


namespace app\models\queue;


use app\models\bank\BankMerchant;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\mfo\statements\ReceiveStatemets;
use app\models\payonline\Partner;
use app\models\Payschets;
use app\models\TU;
use Yii;
use yii\base\BaseObject;
use yii\db\Exception;

class ReverspayJob extends BaseObject implements \yii\queue\JobInterface
{
    public $idpay;

    /**
     * @param \yii\queue\Queue $queue
     */
    public function execute($queue)
    {
        try {
            $payschets = new Payschets();
            $ps = $payschets->getSchetData($this->idpay, '');
            if ($ps && $ps['Status'] == 1) {

                if ($ps['Bank'] == 2) {
                    $gate = TCBank::$ECOMGATE;
                    if ($ps['IsCustom'] == TU::$JKH) {
                        $gate = TCBank::$JKHGATE;
                    } elseif ($ps['IsCustom'] == TU::$POGASHATF || $ps['IsCustom'] == TU::$AVTOPLATATF) {
                        $gate = TCBank::$AFTGATE;
                    }
                    if ($ps['IdUsluga'] == 1) {
                        //регистрация карты
                        $TcbGate = new TcbGate($ps['IdOrg'], TCBank::$AUTOPAYGATE);
                    } else {
                        $TcbGate = new TcbGate($ps['IDPartner'], $gate);
                    }

                    Yii::warning('ReverspayJob execute $gate: ' . $gate, 'merchant');
                    $Merchant = new TCBank($TcbGate);
                    $res = $Merchant->reversOrder($this->idpay);

                } elseif ($ps['Bank'] == 0 && empty($ps['UrlFormPay'])) {
                    Yii::warning('ReverspayJob execute isReversCardRegType1', 'merchant');
                    //отмена для карт на выдачу (без платежа)
                    $res['state'] = 0;//1;

                } else {
                    $res['state'] = 0;
                }

                if ($res['state'] == 1) {
                    $payschets->SetReversPay($ps['ID']);
                    Yii::warning("ReverspayJob " . $this->idpay . " ok: ".'Операция отменена', "rsbcron");
                } else {
                    Yii::warning("ReverspayJob " . $this->idpay . " error: ".($res['message']??''), "rsbcron");
                }
            }
        } catch (Exception $e) {
            Yii::warning("ReverspayJob " . $this->idpay . " error: ".$e->getMessage(), "rsbcron");
        }
    }
}
