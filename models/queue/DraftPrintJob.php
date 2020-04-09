<?php

namespace app\models\queue;

use app\models\kkt\OnlineKassa;
use yii\base\BaseObject;

class DraftPrintJob extends BaseObject implements \yii\queue\JobInterface
{
    public $idpay;
    public $tovar;
    public $tovarOFD;
    public $summDraft;
    public $email = '';

    public function execute($queue)
    {
        //чек пробить
        $kassa = new OnlineKassa();
        $kassa->createDraft($this->idpay, $this->tovar, $this->tovarOFD, $this->summDraft, $this->email);
    }
}