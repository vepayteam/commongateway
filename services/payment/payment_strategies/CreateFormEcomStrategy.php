<?php


namespace app\services\payment\payment_strategies;


use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\kfapi\KfPay;
use app\models\kfapi\KfRequest;
use app\models\TU;
use app\services\payment\payment_strategies\traits\PaymentFormTrait;
use Yii;
use yii\db\Exception;
use yii\mutex\FileMutex;

class CreateFormEcomStrategy implements IPaymentStrategy
{
    use PaymentFormTrait;

    private $gate;
    /** @var KfRequest */
    private $request;

    public function __construct(KfRequest $kfRequest)
    {
        $this->gate = TCBank::$ECOMGATE;
        $this->request = $kfRequest;
    }

    public function exec()
    {
        $kfPay = new KfPay();
        $kfPay->scenario = KfPay::SCENARIO_FORM;
        $kfPay->load($this->request->req, '');
        if (!$kfPay->validate()) {
            return ['status' => 0, 'message' => $kfPay->GetError()];
        }

        $usl = $this->getUsl();
        $tcbGate = $this->getTkbGate();

        if (!$usl || !$tcbGate->IsGate()) {
            return ['status' => 0, 'message' => 'Услуга не найдена'];
        }
        Yii::warning('/merchant/pay id='. $this->request->IdPartner . " sum=".$kfPay->amount . " extid=".$kfPay->extid, 'mfo');

        $pay = $this->createPay();

        $mutex = new FileMutex();
        if (!empty($kfPay->extid)) {
            //проверка на повторный запрос
            if (!$mutex->acquire('getPaySchetExt' . $kfPay->extid, 30)) {
                throw new Exception('getPaySchetExt: error lock!');
            }
            $paramsExist = $pay->getPaySchetExt($kfPay->extid, $usl, $kf->IdPartner);
            if ($paramsExist) {
                if ($kfPay->amount == $paramsExist['sumin']) {
                    return ['status' => 1, 'id' => (int)$paramsExist['IdPay'], 'url' => $kfPay->GetPayForm($paramsExist['IdPay']), 'message' => ''];
                } else {
                    return ['status' => 0, 'id' => 0, 'url' => '', 'message' => 'Нарушение уникальности запроса'];
                }
            }
        }

    }

    private function getUsl()
    {
        return Yii::$app->db->createCommand("
            SELECT `ID` 
            FROM `uslugatovar`
            WHERE `IDPartner` = :IDMFO AND `IsCustom` = :TYPEUSL AND `IsDeleted` = 0
        ", [':IDMFO' => $this->request->IdPartner, ':TYPEUSL' => TU::$ECOM]
        )->queryScalar();
    }


}
