<?php


namespace app\services\card;

use Yii;
use app\services\card\base\CardBase;
use app\models\bank\TCBank;
use app\models\bank\TcbGate;

class Get extends CardBase
{
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['card', 'id', 'type'], 'integer']
        ];
    }

    /**
     * On events
     */
    public function onEvents(): void
    {
        $this->on(self::EVENT_VALIDATE_ERRORS, function ($e) {
            Yii::warning("card/get: " . $this->GetError());
            $this->response =$this->responseErrors();
            $this->response = ['status' => 0, 'message' => $this->GetErrors()];
        });
    }

    /**
     * Init model
     */
    public function initModel(): void
    {
        $type = $this->mfo->GetReq('type', 0);
        if($this->checkStatus()) $this->confirmPay($type);
        if($this->checkStatus()) $this->CheckPayState();
        if($this->checkStatus()) $this->getCardInfo($type);

    }

    /**
     * @param string $type
     * @throws \yii\db\Exception
     */
    private function confirmPay(string $type): void
    {
        if ($type == 0) {
            $TcbGate = new TcbGate($this->mfo->mfo, TCBank::$ECOMGATE);
            $tcBank = new TCBank($TcbGate);
            $tcBank->confirmPay($this->id);
        }
    }

    /**
     * Check pay state
     */
    private function CheckPayState(): void
    {
        $statePay = $this->GetPayState();
        if ($statePay == 2) {
            $this->setStatus(false);
            $this->response = [
                'status' => 2,
                'message' => 'Платеж не успешный'
            ];
        }
    }

    /**
     * @param string $type
     * @throws \yii\db\Exception
     */
    private function getCardInfo(string $type)
    {
        $Card = $this->FindKardByPay($this->mfo->mfo, $type);
        //информация по карте
        if ($Card && $type == 0) {
            $this->response = [
                'status' => 1,
                'message' => '',
                'card' => [
                    'id' => (int)$Card->ID,
                    'num' => (string)$Card->CardNumber,
                    'exp' => $Card->getMonth() . "/" . $Card->getYear(),
                    'holder' => $Card->CardHolder
                ]
            ];

        } elseif ($Card && $type == 1) {
            $this->response = [
                'status' => 1,
                'message' => '',
                'card' => [
                    'id' => (int)$Card->ID,
                    'num' => $Card->CardNumber,
                    'exp' => '',
                    'holder' => ''
                ]
            ];
        } else {
            $this->response = ['status' => 0, 'message' => 'Ошибка запроса'];
        }
    }
}
