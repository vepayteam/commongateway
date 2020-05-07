<?php

namespace app\models\queue;

use app\models\kkt\DraftData;
use app\models\kkt\OnlineKassa;
use yii\base\BaseObject;

class DraftPrintJob extends BaseObject implements \yii\queue\JobInterface
{
    public $idpay;
    public $tovar;
    public $tovarOFD;
    public $summDraft;
    public $summComis;
    public $email = '';

    public function execute($queue)
    {
        //чек пробить
        $data = new DraftData();
        $data->customerContact = $this->email;
        $data->text = $this->tovar;
        $data->price = $this->summDraft;
        $data->comis = $this->summComis;

        if (!empty($this->email)) {
            $kassa = new OnlineKassa();
            $kassa->createDraft($this->idpay, $data, false);
        }
    }
}