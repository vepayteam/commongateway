<?php


namespace app\services\card;

use Yii;
use app\services\card\base\CardBase;

class Del extends CardBase
{
    public function rules()
    {
        return [
            [['card', 'type'], 'required'],
            [['card', 'id', 'type'], 'integer'],
        ];
    }

    /**
     * On events
     */
    public function onEvents(): void
    {
        $this->on(self::EVENT_VALIDATE_ERRORS, function ($e) {
            Yii::warning("card/del: " . $this->GetError());
        });
    }

    /**
     * @throws \yii\db\Exception
     */
    public function initModel(): void
    {
        $Card = $this->FindKard($this->mfo->mfo, 0);
        if (!$Card) {
            $Card = $this->FindKard($this->mfo->mfo, 1);
        }
        if ($Card) {
            $Card->IsDeleted = 1;
            $Card->save(false);
            $this->response = ['status' => 1, 'message' => ''];
        } else {
            $this->response =  ['status' => 0, 'message' => 'Ошибка запроса'];
        }
    }

}