<?php


namespace app\models\partner\admin;

use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\payonline\CreatePay;
use app\models\payonline\Partner;
use app\models\payonline\Provparams;
use app\models\payonline\Uslugatovar;
use app\models\Payschets;
use app\models\SendEmail;
use app\models\TU;
use yii\base\Model;
use Yii;

class VyvodVoznag extends Model
{
    private $recviz = [
        'account' => '40702810401500092051',
        'bic' => '044525999',
        'name' => 'ООО "ПРОЦЕССИНГОВАЯ КОМПАНИЯ БЫСТРЫХ ПЛАТЕЖЕЙ"',
        'inn' => '7728487400',
        'kpp' => '772801001',
        'bankname' => 'ТОЧКА ПАО БАНКА "ФК ОТКРЫТИЕ"',
        'bankcity' => 'г Москва',
        'ks' => '30101810845250000999',
    ];

    public $partner;
    public $summ;
    public $datefrom;
    public $dateto;
    public $isCron = false;
    public $balance = 0;
    public $type = 0; // 0 - погашения 1 - выплаты

    public function rules()
    {
        return [
            [['isCron', 'type'], 'integer'],
            [['balance'], 'number'],
            [['partner', 'summ'], 'integer', 'min' => 1],
            [['datefrom', 'dateto'], 'date', 'format' => 'php:d.m.Y H:i'],
            [['partner', 'summ', 'datefrom', 'dateto'], 'required']
        ];
    }

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

    /**
     * Вывод средств
     * @throws \yii\db\Exception
     */
    public function CreatePayVyvod()
    {
        $this->datefrom = strtotime($this->datefrom . ':00');
        $this->dateto = strtotime($this->dateto . ':59');

        $mfo = Partner::findOne(['ID' => $this->partner]);
        if (!$mfo ||
            ($this->type == 0 && empty($mfo->LoginTkbVyvod)) ||
            ($this->type == 1 && $mfo->VoznagVyplatDirect && empty($mfo->LoginTkbOctVyvod))
        ) {
            Yii::warning("VyvodVoznag: error mfo=" . $this->partner, "rsbcron");
            if ($this->isCron) {
                echo "VyvodVoznag: error mfo=" . $this->partner . "\r\n";
            }
            return 0;
        }

        if ($this->type == 0 || ($this->type == 1 && $mfo->VoznagVyplatDirect)) {
            //вывод вознаграждения со счета через платеж
            return $this->VyplataDirect($mfo);

        } elseif ($this->type == 1 && !$mfo->VoznagVyplatDirect) {
            //вывод вознаграждения (по выдачам) через выставление счета - только отметить что выплачено
            return $this->VyplataSetOk();
        }
        return 0;
    }

    /**
     * @param Partner $mfo
     * @return int
     * @throws \yii\db\Exception
     */
    private function VyplataDirect($mfo)
    {
        $PBKPOrg = 1;

        $tr = Yii::$app->db->beginTransaction();
        Yii::$app->db->createCommand()->insert(
            'vyvod_system', [
                'DateOp' => time(),
                'IdPartner' => $this->partner,
                'DateFrom' => $this->datefrom,
                'DateTo' => $this->dateto,
                'Summ' => $this->summ,
                'SatateOp' => 0,
                'IdPay' => 0,
                'TypeVyvod' => $this->type
            ]
        )->execute();

        $id = Yii::$app->db->getLastInsertID();

        $descript = "Вознаграждение за оказанные услуги по договору " . $mfo->NumDogovor . ' от ' . $mfo->DateDogovor . ", за " . date("d.m.Y", $this->datefrom)."-".date("d.m.Y", $this->dateto);

        $usl = $this->GetUslug();
        if (!$usl) {
            $tr->rollBack();
            Yii::warning("VyvodVoznag: error mfo=" . $this->partner . " usl=" . $usl, "rsbcron");
            if ($this->isCron) {
                echo "VyvodVoznag: error mfo=" . $this->partner . " usl=" . $usl . "\r\n";
            }
            return 0;
        }

        $pay = new CreatePay();
        $Provparams = new Provparams;
        $Provparams->prov = $usl;
        $Provparams->param = [$this->recviz['account'], $this->recviz['bic'], $this->recviz['name'], $this->recviz['inn'], $this->recviz['kpp'], $descript];
        $Provparams->summ = $this->summ;
        $Provparams->Usluga = Uslugatovar::findOne(['ID' => $usl]);

        $idpay = $pay->createPay($Provparams,0, 3, TCBank::$bank, $PBKPOrg, 'voznout'.$id, 0);

        if (!$idpay) {
            Yii::warning("VyvodSumPay: error mfo=" . $this->partner . " idpay=" . $idpay, "rsbcron");
            if ($this->isCron) {
                echo "VyvodVoznag: error mfo=" . $this->partner . " idpay=" . $idpay . "\r\n";
            }
            $tr->rollBack();
            return 0;
        }
        $idpay = $idpay['IdPay'];

        Yii::$app->db->createCommand()->update('vyvod_system', [
            'IdPay' => $idpay
        ],'`ID` = :ID', [':ID' => $id])->execute();

        $tr->commit();

        Yii::warning("VyvodVoznag: mfo=" . $this->partner . " idpay=" . $idpay, "rsbcron");
        if ($this->isCron) {
            echo "VyvodVoznag: mfo=" . $this->partner . " idpay=" . $idpay . "\r\n";
        }

        $TcbGate = new TcbGate($mfo->ID,($this->type == 1 || $mfo->IsCommonSchetVydacha) ? TCBank::$VYVODOCTGATE : TCBank::$VYVODGATE);
        $bank = new TCBank($TcbGate);
        $ret = $bank->transferToAccount([
            'IdPay' => $idpay,
            'account' => $this->recviz['account'],
            'bic' => $this->recviz['bic'],
            'summ' => $this->summ,
            'name' => $this->recviz['name'],
            'inn' => $this->recviz['inn'],
            'descript' => $descript
        ]);

        if ($ret && $ret['status'] == 1) {
            //сохранение номера транзакции
            $payschets = new Payschets();
            $payschets->SetBankTransact([
                'idpay' => $idpay,
                'trx_id' => $ret['transac'],
                'url' => ''
            ]);

            Yii::warning("VyvodVoznag: mfo=" . $this->partner . ", transac=" . $ret['transac'], "rsbcron");
            if ($this->isCron) {
                echo "VyvodVoznag: mfo=" . $this->partner . ", transac=" . $ret['transac'] . "\r\n";
            }

            Yii::$app->db->createCommand()->update('vyvod_system', [
                'SatateOp' => 1
            ],'`ID` = :ID', [':ID' => $id])->execute();

            if ($this->isCron) {
                $this->SendMail($this->balance, $this->summ / 100.0,
                    $mfo->Name, $this->recviz['account'],
                    $this->datefrom, $this->dateto, $idpay, $ret['transac']);
            }

        } else {
            //не вывелось
            Yii::$app->db->createCommand()->update('vyvod_system', [
                'SatateOp' => 2
            ],'`ID` = :ID', [':ID' => $id])->execute();

        }

        return 1;
    }

    /**
     * @return int
     * @throws \yii\db\Exception
     */
    private function VyplataSetOk()
    {
        if (!Yii::$app->db->createCommand("
            SELECT
                `ID`
            FROM
                `vyvod_system`
            WHERE
                `TypeVyvod` = :TypeVyvod
                AND `DateFrom` = :DateFrom
                AND `DateTo` = :DateTo  
                AND `IdPartner` = :IdPartner
                AND `SatateOp` = 1
        ", [':TypeVyvod' => $this->type, ':DateFrom' => $this->datefrom, ':DateTo' => $this->dateto, ':IdPartner' => $this->partner])->queryScalar()) {
            Yii::$app->db->createCommand()->insert(
                'vyvod_system', [
                    'DateOp' => time(),
                    'IdPartner' => $this->partner,
                    'DateFrom' => $this->datefrom,
                    'DateTo' => $this->dateto,
                    'Summ' => $this->summ,
                    'SatateOp' => 1,
                    'IdPay' => 0,
                    'TypeVyvod' => $this->type
                ]
            )->execute();
        }

        return 1;
    }

    public function GetUslug()
    {
        return Yii::$app->db->createCommand("
            SELECT 
                `ID`
            FROM 
                `uslugatovar`
            WHERE
                `IDPartner` = 1 
                AND `ExtReestrIDUsluga` = :IDMFO 
                AND `IsCustom` = :TYPEUSL 
                AND `IsDeleted` = 0
        ", [':IDMFO' => $this->partner, ':TYPEUSL' => TU::$VYPLATVOZN])->queryScalar();
    }

    /**
     * Оповещение о выводе
     */
    private function SendMail($bal, $sumPays, $NamePoluchat, $RaschShetPolushat, $datefrom, $dateto,  $idpay, $transac)
    {
        $balAfter = (float)$bal - (float)$sumPays;
        $emailTo = [Yii::$app->params['support_email']];

        $mail = new SendEmail();
        $mail->send($emailTo, 'robot@vepay.online', 'Вывод комиссии с МФО',
            'Перечисление средств ' . $NamePoluchat . ' за ' . date('d.m.Y', $datefrom) . ' - ' . date('d.m.Y', $dateto) .
            ' на счет ' . $RaschShetPolushat . '<br>' .
            'Сумма: ' . sprintf("%02.2f", $sumPays) . ' руб., баланс после операции: ' . sprintf("%02.2f", $balAfter) . ' руб.<br>' .
            '№ операции: ' . $idpay . ', № танзакции: ' . $transac
        );
    }

    public static function GetRecviz()
    {
        return (new self())->recviz;

    }
}
