<?php


namespace app\services\card;

use app\models\payonline\Cards;
use Yii;
use app\services\card\base\CardBase;

class Info extends CardBase
{
    public function rules()
    {
        return [
            [['card', 'id', 'type'], 'integer'],
            [['card'], 'required'],
        ];
    }

    /**
     * On events
     */
    public function onEvents(): void
    {
        $this->on(self::EVENT_VALIDATE_ERRORS, function ($e) {
            Yii::warning("card/info: " . $this->GetError());
        });
    }

    /**
     * @throws \yii\db\Exception
     */
    public function initModel(): void
    {
        $type = $this->mfo->GetReq('type', 0);
        $Card = $this->FindKard($this->mfo->mfo, $type);
        if (!$Card) {
            Yii::warning("card/info: нет такой карты", 'mfo');
            $this->response = ['status' => 0, 'message' => 'Нет такой карты'];
        } else {
            $this->getCardInfo($Card, $type);
        }
    }

    /**
     * @param Cards $Card
     * @param string $type
     */
    private function getCardInfo(Cards $Card, string $type): void
    {
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
                    'num' => (string)$Card->CardNumber,
                    'exp' => '',
                    'holder' => ''
                ]
            ];
        } else {
            $this->response =  ['status' => 0, 'message' => 'Ошибка запроса'];
        }
    }
}

