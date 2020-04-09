<?php


namespace app\models\antifraud\support_objects;


use app\models\partner\stat\PayShetStat;
use app\models\payonline\Cards;
use app\models\Payschets;
use yii\helpers\VarDumper;

class RefundInfo
{
    private $record;
    private $finger_id;
    private $card_num;

    public function __construct(int $trans_id, $finger_print_id, $card_num)
    {
        $sheet = new Payschets();
        $this->record = $sheet->getSchetData($trans_id);
        $this->finger_id = $finger_print_id;
        $this->card_num = $card_num;
    }

    public function card_mask(): string
    {
        return $this->card_num;
    }

    public function ext_id(): string
    {
        return $this->record['Extid'];
        //if ($ext_id = $this->record['Extid']) {
            //return preg_replace('#\.(.*)#', '', $ext_id);
        //}
        //return '';
    }

    public function partner_id(): int
    {
        return $this->record['IDPartner'];
    }

    public function sum()
    {
        return $this->record['SummPay'];
    }

    public function transaction_id()
    {
        return $this->record['ID'];
    }

    public function finger_id()
    {
        return $this->finger_id;
    }
}