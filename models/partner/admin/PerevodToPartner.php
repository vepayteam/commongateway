<?php


namespace app\models\partner\admin;

use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\payonline\CreatePay;
use app\models\payonline\Partner;
use app\models\payonline\Provparams;
use app\models\payonline\Uslugatovar;
use app\models\Payschets;
use app\models\TU;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\OutPayaccForm;
use app\services\payment\payment_strategies\mfo\MfoOutPayaccStrategy;
use Yii;
use yii\base\Model;

class PerevodToPartner extends Model
{
    public $IdPartner;
    public $Summ;
    public $TypeSchet;

    public function rules()
    {
        return [
            [['TypeSchet'], 'integer'],
            [['IdPartner'], 'integer', 'min' => 1],
            [['Summ'], 'number', 'min' => 1],
            [['IdPartner', 'Summ'], 'required']
        ];
    }

    public function attributeLabels()
    {
        return [
            'Summ' => 'Сумма'
        ];
    }

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

    /**
     * Перевод средств контрагенту через платеж ТКБ
     * @return array
     * @throws \yii\db\Exception
     */
    public function CreatePerevod()
    {
        $PBKPOrg = 1;

        $sumPays = round($this->Summ * 100.0);
        $dateFrom = strtotime('yesterday');
        $dateTo = strtotime('today') - 1;

        $Partner = Partner::findOne(['ID' => $this->IdPartner]);
        if (!$Partner || empty($Partner->INN) || empty($Partner->UrLico)) {
            return ['status' => 0, 'message' => 'Контрагент не найден'];
        }
        $recviz = $this->GetRecviz($Partner);
        if (!$recviz || empty($recviz['RS'])) {
            return ['status' => 0, 'message' => 'Счет не найден'];
        }

        $usl = $this->GetUslug($Partner->ID, $recviz['BIK'] == TCBank::BIC ? TU::$PEREVPAYS : TU::$VYVODPAYS);
        if (!$usl) {
            return ['status' => 0, 'message' => 'Услуга учёта не найдена'];
        }

        if ($Partner->IsCommonSchetVydacha) {
            //перевод со сета выплаты
            $TcbGate = new TcbGate($Partner->ID, $recviz['BIK'] == TCBank::BIC ? TCBank::$PEREVODOCTGATE : TCBank::$VYVODOCTGATE);
        } else {
            //перевод со счета погашения
            $TcbGate = new TcbGate($Partner->ID, $recviz['BIK'] == TCBank::BIC ? TCBank::$PEREVODGATE : TCBank::$VYVODGATE);
        }
        if (!$TcbGate->IsGate()) {
            return ['status' => 0, 'message' => 'Терминал не настроен'];
        }

        $tkb = new TCBank($TcbGate);
        $bal = $tkb->getBalance();

        if (!($bal['status'] == 1 && $bal['amount'] > $sumPays / 100.0)) {
            return ['status' => 0, 'message' => 'Недостаточно средств для перевода'];
        }

        $tr = Yii::$app->db->beginTransaction();
        Yii::$app->db->createCommand()->insert(
            'vyvod_reestr', [
                'DateOp' => time(),
                'IdPartner' => $Partner->ID,
                'DateFrom' => $dateFrom,
                'DateTo' => $dateTo,
                'SumOp' => $sumPays,
                'StateOp' => 0,
                'IdPay' => 0,
                'TypePerechisl' => $recviz['BIK'] == TCBank::BIC ? 0 : 1
            ]
        )->execute();

        $id = Yii::$app->db->getLastInsertID();

        $descript = str_ireplace(
            '%date%', (
            date('d.m', $dateFrom) == date('d.m', $dateTo) ?
                date('d.m', $dateFrom) :
                date('d.m', $dateFrom) . '-' . date('d.m', $dateTo)
            ) . '.' . date('Y', $dateTo),
            $recviz['NaznachenPlatez']
        );

        $pay = new CreatePay();
        $Provparams = new Provparams;
        $Provparams->prov = $usl;
        $Provparams->param = [$recviz['RS'], $recviz['BIK'], $recviz['NamePoluchat'], $recviz['INNPolushat'], $recviz['KPPPoluchat'], $descript];
        $Provparams->summ = $sumPays;
        $Provparams->Usluga = Uslugatovar::findOne(['ID' => $usl]);

        $idpay = $pay->createPay($Provparams, 0, 3, TCBank::$bank, $PBKPOrg, 'reestr' . $id, 0);

        if (!$idpay) {
            $tr->rollBack();
            return ['status' => 0, 'message' => 'Ошибка создания платежа'];
        }
        $idpay = $idpay['IdPay'];

        Yii::$app->db->createCommand()->update('vyvod_reestr', [
            'IdPay' => $idpay
        ], '`ID` = :ID', [':ID' => $id])->execute();

        $tr->commit();

        $outPayaccForm = new OutPayaccForm();
        $outPayaccForm->scenario = OutPayaccForm::SCENARIO_UL;
        $outPayaccForm->account = $recviz['RS'];
        $outPayaccForm->bic = $recviz['BIK'];
        $outPayaccForm->amount = $sumPays;
        $outPayaccForm->name = $recviz['NamePoluchat'];
        $outPayaccForm->inn = $recviz['INNPolushat'];
        $outPayaccForm->descript = $descript;

        if (!$outPayaccForm->validate()) {
            Yii::warning("out/payacc: " . $outPayaccForm->GetError(), 'mfo');
            return ['status' => 0, 'message' => $outPayaccForm->GetError()];
        }
        $outPayaccForm->partner = $Partner;

        $mfoOutPayaccStrategy = new MfoOutPayaccStrategy($outPayaccForm);
        try {
            $mfoOutPayaccStrategy->exec();
        } catch (CreatePayException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        } catch (GateException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        }

        return ['status' => 1, 'message' => 'Средства перечислены'];
    }

    /**
     * Услуга для вывода
     * @param $IdPartner
     * @param $TypeUsl
     * @return false|string|null
     * @throws \yii\db\Exception
     */
    public function GetUslug($IdPartner, $TypeUsl)
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
        ", [':IDMFO' => $IdPartner, ':TYPEUSL' => $TypeUsl])->queryScalar();
    }

    private function GetRecviz(Partner $Partner)
    {
        if ($this->TypeSchet == 0) {
            //на счет выплат в ткб
            return [
                'BIK' => TCBank::BIC,
                'RS' => $Partner->SchetTcb,
                'NamePoluchat' => $Partner->UrLico,
                'INNPolushat' => $Partner->INN,
                'KPPPoluchat' => $Partner->KPP,
                'NaznachenPlatez' => 'Перевод средств между своими счетами согласно условий договора '.$Partner->NumDogovor.
                    ' от '.$Partner->DateDogovor.' согласно реестру за %date% г.'
            ];
        } else {
            //на р/с
            $recv = $Partner->getPartner_bank_rekviz()->one();
            if ($recv) {
                return [
                    'BIK' => $recv->BIKPoluchat,
                    'RS' => $recv->RaschShetPolushat,
                    'NamePoluchat' => $recv->NamePoluchat,
                    'INNPolushat' => $recv->INNPolushat,
                    'KPPPoluchat' => $recv->KPPPoluchat,
                    'NaznachenPlatez' => 'Расчеты по договору '.$Partner->NumDogovor.
                        ' от '.$Partner->DateDogovor.' согласно реестру за %date% г.'
                ];
            }
        }
        return null;
    }

}
