<?php


namespace app\services\card;

use app\models\api\Reguser;
use app\models\bank\TCBank;
use Yii;
use app\services\card\base\CardBase;
use yii\base\Exception;
use yii\mutex\FileMutex;
use app\models\payonline\CreatePay;

class Reg extends CardBase
{
    public function rules()
    {
        return [
            [['card', 'id', 'type'], 'integer'],
            [['extid'], 'string', 'max' => 40],
            [['successurl', 'failurl', 'cancelurl'], 'url'],
            [['successurl', 'failurl', 'cancelurl'], 'string', 'max' => 300],
            [['timeout'], 'integer', 'min' => 10, 'max' => 59],
            [['type'], 'integer', 'min' => 0]
        ];
    }

    /**
     * On events
     */
    public function onEvents(): void
    {
        $this->on(self::EVENT_VALIDATE_ERRORS, function ($e) {
            Yii::warning("card/reg: " . $this->GetError());
        });
    }

    /**
     * @throws Exception
     */
    public function initModel(): void
    {
        if($this->checkStatus()) $this->fileMutex();
        if($this->checkStatus()) $this->getUser();
        if($this->checkStatus()) $this->cardRegistration();
    }

    /**
     * @throws Exception
     * @throws \yii\db\Exception
     */
    private function fileMutex(): void
    {
        $this->result['mutex'] = new FileMutex();
        if (!empty($this->extid)) {
            //проверка на повторный запрос
            if (!$this->result['mutex']->acquire('getPaySchetExt' . $this->extid, 30)) {
                throw new Exception('getPaySchetExt: error lock!');
            }
            $pay = new CreatePay();
            $paramsExist = $pay->getPaySchetExt($this->extid, 1, $this->mfo->mfo);
            if ($paramsExist) {
                $this->setStatus(false);
                $this->response = ['status' => 1, 'message' => '', 'id' => (int)$paramsExist['IdPay'], 'url' => $this->GetRegForm($paramsExist['IdPay'])];
            }
        }
    }

    /**
     * @throws \Exception
     */
    private function getUser(): void
    {
        $reguser = new Reguser();
        $this->result['user']  = $reguser->findUser('0', $this->mfo->mfo.'-'.time().random_int(100,999), md5($this->mfo->mfo.'-'.time()), $this->mfo->mfo, false);
    }

    /**
     * Card registration
     */
    private function cardRegistration(): void
    {
        $type = $this->mfo->GetReq('type');
        Yii::warning('/card/reg mfo='. $this->mfo->mfo . " type=". $type, 'mfo');
        if ($type == 0) {
            //карта для автоплатежа
            $this->forAutopayment();
        } elseif ($type == 1) {
            //карта для выплат
            $this->forPayments();
        }
    }

    /**
     * @throws \yii\db\Exception
     */
    public function forAutopayment(): void
    {
        $pay = new CreatePay($this->result['user']);
        $data = $pay->payActivateCard(0, $this,3, TCBank::$bank, $this->mfo->mfo);
        if (!empty($this->extid)) {
            $this->result['mutex']->release('getPaySchetExt' . $this->extid);
        }
        //PCI DSS
        $this->response = [
            'status' => 1,
            'message' => '',
            'id' => $data['IdPay'],
            'url' => $this->GetRegForm($data['IdPay'])
        ];
    }

    /**
     * @throws \yii\db\Exception
     */
    private function forPayments(): void
    {
        $pay = new CreatePay($this->result['user']);
        $data = $pay->payActivateCard(0, $this,3,0, $this->mfo->mfo); //Provparams
        if (!empty($this->extid)) {
            $this->result['mutex']->release('getPaySchetExt' . $this->extid);
        }

        if (isset($data['IdPay'])) {
            $this->response = [
                'status' => 1,
                'message' => '',
                'id' => $data['IdPay'],
                'url' => $this->mfo->getLinkOutCard($data['IdPay'])
            ];
        }
    }

}