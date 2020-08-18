<?php


namespace app\services\payment\payment_strategies;


use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\kfapi\KfPayParts;
use app\models\mfo\MfoReq;
use app\models\payonline\CreatePay;
use Yii;
use yii\base\Exception;
use yii\mutex\FileMutex;

class CreateFormMfoAftPartsStrategy implements IMfoStrategy
{
    private $gate;
    /** @var MfoReq $mfoReq */
    private $mfoReq;

    public function __construct(MfoReq $mfoReq)
    {
        $this->gate = TCBank::$AFTGATE;
        $this->mfoReq = $mfoReq;
    }

    public function exec()
    {
        $kfPay = new KfPayParts();
        $kfPay->scenario = KfPayParts::SCENARIO_FORM;
        $kfPay->load($this->mfoReq->Req(), '');
        if (!$kfPay->validate()) {
            Yii::warning("pay/lk: ".$kfPay->GetError());
            return ['status' => 0, 'message' => $kfPay->GetError()];
        }

        $TcbGate = new TcbGate($this->mfoReq->mfo, TCBank::$AFTGATE);
        $usl = $kfPay->GetUslug($this->mfoReq->mfo, TCBank::$AFTGATE);

        if (!$usl || !$TcbGate->IsGate()) {
            return ['status' => 0, 'message' => 'Нет шлюза'];
        }

        $pay = new CreatePay();
        $mutex = new FileMutex();
        if (!empty($kfPay->extid)) {
            //проверка на повторный запрос
            if (!$mutex->acquire('getPaySchetExt' . $kfPay->extid, 30)) {
                throw new Exception('getPaySchetExt: error lock!');
            }
            $paramsExist = $pay->getPaySchetExt($kfPay->extid, $usl, $this->mfoReq->mfo);
            if ($paramsExist) {
                if ($kfPay->amount == $paramsExist['sumin']) {
                    return ['status' => 1, 'message' => '', 'id' => (int)$paramsExist['IdPay'], 'url' => $kfPay->GetPayForm($paramsExist['IdPay'])];
                } else {
                    Yii::warning("pay/lk: Нарушение уникальности запроса");
                    return ['status' => 0, 'message' => 'Нарушение уникальности запроса'];
                }
            }
        }
        $params = $pay->payToMfo(null, [$kfPay->document_id, $kfPay->fullname], $kfPay, $usl, TCBank::$bank, $this->mfoReq->mfo,0);

        foreach ($kfPay->parts as $part) {
            $tcbGate = $this->getTkbGate($part['merchant_id']);

            if(!$tcbGate->IsGate()) {
                return [
                    'status' => 0,
                    'message' => 'Услуга не найдена'];
            }
        }

        if (!empty($kfPay->extid)) {
            $mutex->release('getPaySchetExt' . $kfPay->extid);
        }
        //PCI DSS
        return [
            'status' => 1,
            'message' => '',
            'id' => (int)$params['IdPay'],
            'url' => $kfPay->GetPayForm($params['IdPay'])
        ];
    }
}
