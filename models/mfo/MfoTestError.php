<?php


namespace app\models\mfo;


use app\models\payonline\Cards;
use app\models\Payschets;
use yii\helpers\Json;

class MfoTestError
{
    public $message = '';

    public function TestCancelCards($card, $IdPay)
    {
        //Некорректно введены данные карты
        $err1 = ['5336690161523611', '5336690108108782'];
        //Карта заблокирована
        $err2 = ['5469380055167372', '676280079036763771'];
        //Недостаточно средств на счёте
        $err3 = ['4377723743121349', '5586200069668959'];

        $cancel = false;
        if (in_array($card, $err1)) {
            $cancel = true;
            $this->message = "Неверный номер карты";
        }
        if (in_array($card, $err2)) {
            $cancel = true;
            $this->message = "Карта заблокирована";
        }
        if (in_array($card, $err3)) {
            $cancel = true;
            $this->message = "Недостаточно средств на счете";
        }
        if ($cancel) {
            $this->CancelPay($IdPay);
            return true;
        }
        return false;
    }

    public function CancelPay($IdPay)
    {
        $pay = new Payschets();
        $pay->confirmPay([
            'idpay' => $IdPay,
            'idgroup' => 0,
            'result_code' => 2,
            'trx_id' => 0,
            'ApprovalCode' => '',
            'RRN' => '',
            'message' => $this->message
        ]);
    }

    public function ConfirmOut($IdPay)
    {
        $pay = new Payschets();
        $pay->confirmPay([
            'idpay' => $IdPay,
            'idgroup' => 0,
            'result_code' => 1,
            'trx_id' => $IdPay,
            'ApprovalCode' => '',
            'RRN' => '',
            'message' => 'Успех'
        ]);
    }

    public function TestStatements()
    {
        $fl = file_get_contents(\Yii::$app->basePath.'/tests/_data/statements.json');
        $statements = Json::decode($fl)['statements'];
        $ret = [];
        foreach ($statements as $statement) {

            $ret[] = [
                "id" => $statement['id'],
                "number" => $statement['docnumber'],
                "date" => $statement['datedoc'],
                "summ" => $statement['docsumm']['sum'],
                "description" => $statement['description'],
                "iscredit" => $statement['iscredit'] ? false : true,
                "name" => $statement['iscredit'] ? $statement['recipientname'] : $statement['clientname'] ,
                "inn" => $statement['iscredit'] ? $statement['recipientinn'] : $statement['clientinn'],
                "kpp" => "",
                "bic" => $statement['iscredit'] ? $statement['recipientbik'] : $statement['clientbik'],
                "bank" => $statement['iscredit'] ? $statement['recipientbank'] : $statement['clientbank'],
                "bankaccount" => $statement['iscredit'] ? $statement['recipientbankaccount'] : $statement['clientbankaccount'],
                "account" => $statement['iscredit'] ? $statement['recipientaccount'] : $statement['clientaccount']
            ];
        }

        $id = \Yii::$app->cache->get('statementid');
        if (!$id) {
            $id = 10;
        } else {
            $id += 1;
        }
        \Yii::$app->cache->set('statementid', $id);
        $ret[] = [
            "id" => $id,
            "number" => random_int(1000, 9999),
            "date" => date("d.m.Y\TH:i:00"),
            "summ" => random_int(10000, 100000),
            "description" => "Пополнение виртуального счёта инвестора ".$this->generate_uuid().", ИНН 925165135503",
            "iscredit" => false,
            "name" => "ТКБ БАНК ПАО",
            "inn" => "925165135503",
            "kpp" => "770901001",
            "bic" => "044525388",
            "bank" => "ТКБ БАНК ПАО",
            "bankaccount" => "30102810900000000388",
            "account" => "30102810900000000388"
        ];

        return $ret;
    }

    public function generate_uuid()
    {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int( 0, 0xffff ), random_int( 0, 0xffff ),
            random_int( 0, 0xffff ),
            random_int( 0, 0x0fff ) | 0x4000,
            random_int( 0, 0x3fff ) | 0x8000,
            random_int( 0, 0xffff ), random_int( 0, 0xffff ), random_int( 0, 0xffff )
        );
    }

    public function TestCancelSchet($account, $IdPay)
    {
        return 0;
    }

    public function ConfirmPayTest($IdPay, $cardnum)
    {
        $pay = new Payschets();
        $pay->confirmPay([
            'idpay' => $IdPay,
            'idgroup' => 0,
            'result_code' => 1,
            'trx_id' => $IdPay,
            'ApprovalCode' => '',
            'CardNum' => !empty($cardnum) ? Cards::MaskCard($cardnum) : '',
            'CardBrand' => '',
            'CardIssuingBank' => '',
            'RRN' => '',
            'message' => 'Успех'
        ]);
    }

    public function TestNominalStatements()
    {
        $fl = file_get_contents(\Yii::$app->basePath.'/tests/_data/nomilal.json');
        $st = Json::decode($fl);
        $st = self::array_change_key_case_recursive($st,CASE_LOWER);
        return ['status' => 1, 'statements' => $st['documents']];
    }

    public function TestTransitStatements()
    {
        $fl = file_get_contents(\Yii::$app->basePath.'/tests/_data/transit.json');
        $st = Json::decode($fl);
        $st = self::array_change_key_case_recursive($st,CASE_LOWER);
        return ['status' => 1, 'statements' => $st['statement']];
    }

    private static function array_change_key_case_recursive($array, $case)
    {
        $array = array_change_key_case($array, $case);
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::array_change_key_case_recursive($value, $case);
            }
        }
        return $array;
    }

}