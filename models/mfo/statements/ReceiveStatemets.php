<?php


namespace app\models\mfo\statements;


use app\models\bank\TCBank;
use app\models\bank\TcbGate;
use app\models\kfapi\KfStatement;
use app\models\mfo\MfoReq;
use app\models\mfo\MfoTestError;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\models\StatementsAccount;
use app\models\TU;
use app\models\payonline\BalancePartner;
use Yii;
use yii\helpers\ArrayHelper;

class ReceiveStatemets
{
    /** @var Partner $Partner */
    public $Partner;

    private $list = null;

    public function __construct(Partner $Partner)
    {
        $this->Partner = $Partner;
    }

    /**
     * @param $dateFrom
     * @param $dateTo
     * @return array
     * @throws \yii\db\Exception
     */
    public function getAll($dateFrom, $dateTo)
    {
        $list = [];
        $mfo = new MfoReq();
        $mfo->mfo = $this->Partner->ID;
        $TcbGate = new TcbGate($this->Partner->ID, TCBank::$AFTGATE);
        $tcBank = new TCBank($TcbGate);

        //TODO вынеси в константы имена полей счетов
        foreach (['SchetTcbNominal', 'SchetTcbTransit', 'SchetTcb'] as $accountType) {
            $isNominal = $accountType == 'SchetTcbNominal';
            $account = $this->Partner->$accountType;
            if (empty($account)) {
                continue;
            }

            $request = [
                'account' => $account,
                'datefrom' => date('Y-m-d\TH:i:s', $dateFrom),
                'dateto' => date('Y-m-d\TH:i:s', $dateTo)
            ];

            if ($isNominal) {
                $res = $tcBank->getStatementNominal($request);
            } else {
                $res = $tcBank->getStatement($request);
            }

            if (isset($res['statements'])) {
                if ($isNominal) {
                    $appendingList = $this->ParseSatementsNominal($res['statements']);
                } else {
                    $appendingList = $this->ParseSatements($res['statements']);
                }
                if (!empty($appendingList)) {
                    foreach ($appendingList as &$row) {
                        $row['our_schet_type'] = $accountType;
                        $row['our_schet'] = $this->Partner->$accountType;
                    }
                }
                $list = ArrayHelper::merge($list, $appendingList);
            }
        }
        return $list;
    }

    /**
     * @param int $TypeSchet 0 - счет на выдачу 1 - счет на погашение 2 - номинальный счет
     * @param int $dateFrom
     * @param int $dateTo
     * @throws \yii\db\Exception
     */
    public function UpdateStatemets($TypeSchet, $dateFrom, $dateTo)
    {
        $mfo = new MfoReq();
        $mfo->mfo = $this->Partner->ID;
        $TcbGate = new TcbGate($this->Partner->ID, TCBank::$AFTGATE);
        $tcBank = new TCBank($TcbGate);

        $bal = null;
        if (!empty($this->Partner->SchetTcbNominal) && $TypeSchet == 2) {
            $bal = $tcBank->getStatementNominal([
                'account' => $this->Partner->SchetTcbNominal,
                'datefrom' => date('Y-m-d\TH:i:s', $dateFrom),
                'dateto' => date('Y-m-d\TH:i:s', $dateTo)
            ]);
            if ($bal && isset($bal['statements'])) {
                $this->list = $this->ParseSatementsNominal($bal['statements']);
            }
        } elseif (!empty($this->Partner->SchetTcbTransit) && $TypeSchet == 1) {
            $bal = $tcBank->getStatement([
                'account' => $this->Partner->SchetTcbTransit,
                'datefrom' => date('Y-m-d\TH:i:s', $dateFrom),
                'dateto' => date('Y-m-d\TH:i:s', $dateTo)
            ]);
            if ($bal && isset($bal['statements'])) {
                $this->list = $this->ParseSatements($bal['statements']);
            }
        } elseif (!empty($this->Partner->SchetTcb) && $TypeSchet == 0) {
            if (Yii::$app->params['TESTMODE'] == 'Y') {
                $MfoTestError = new MfoTestError();
                $bal = $MfoTestError->TestTransitStatements();
            } else {
                $bal = $tcBank->getStatement([
                    'account' => $this->Partner->SchetTcb,
                    'datefrom' => date('Y-m-d\TH:i:s', $dateFrom),
                    'dateto' => date('Y-m-d\TH:i:s', $dateTo)
                ]);
            }
            if ($bal && isset($bal['statements'])) {
                $this->list = $this->ParseSatements($bal['statements']);
            }
        }

        if ($this->list && count($this->list)) {
            $this->SaveStatemens($TypeSchet, $dateFrom, $dateTo);
        }

    }

    /**
     * Выписки
     * @param array $statements
     * @return array
     */
    public function ParseSatements($statements)
    {
        $ret = [];
        foreach ($statements as $statement) {
            $ret[] = [
                "id" =>  $statement['id'],
                "number" => $statement['docnumber'],
                "date" => $statement['datedoc'],
                "datedoc" => $statement['datedoc'],
                "summ" => round($statement['docsumm']['sum'],2),
                "description" => $statement['description'],
                "iscredit" => $statement['iscredit'] ? true : false, //true - пополнение счета
                "name" => $statement['iscredit'] ? $statement['payername'] : $statement['payeename'],
                "inn" => $statement['iscredit'] ? $statement['payerinn'] : $statement['payeeinn'],
                "kpp" => '',
                "bic" => $statement['iscredit'] ? $statement['payerbik'] : $statement['payeebik'],
                "bank" => $statement['iscredit'] ? $statement['payerbank'] : $statement['payeebank'],
                "bankaccount" => $statement['iscredit'] ? $statement['payerbankaccount'] : $statement['payeebankaccount'],
                "account" => $statement['iscredit'] ? $statement['payeraccount'] : $statement['payeeaccount']
            ];
        }
        return $ret;
    }

    /**
     * Выписки ABS
     * @param array $statements
     * @return array
     */
    public function ParseSatementsAbs($statements)
    {
        $ret = [];
        foreach ($statements as $statement) {
            $ret[] = [
                "id" =>  $statement['id'],
                "number" => $statement['noper'],
                "date" => $statement['operdate'],
                "datedoc" => $statement['operdate'],
                "summ" => round($statement['totalamount'], 2),
                "description" => $statement['comment'],
                "iscredit" => $statement['priras'] == "C" ? true : false, //true - пополнение счета
                "name" => '',
                "inn" => '',
                "kpp" => '',
                "bic" => '',
                "bank" => '',
                "bankaccount" => '',
                "account" => $statement['priras'] == "C" ? $statement['accountdebet'] : $statement['accountcredit']
            ];
        }
        return $ret;
    }

    public function ParseSatementsNominal($statements)
    {
        $ret = [];
        foreach ($statements as $statement) {
            $ret[] = [
                "id" =>  $statement['id'],
                "number" => $statement['number'],
                "date" => $statement['datecr'] ?? $statement['date'],
                "datedoc" => $statement['datedoc'] ?? $statement['date'],
                "summ" => round($statement['summ'],2),
                "description" => $statement['description'] ?? '',
                "iscredit" => $statement['iscredit'] == "true" ? true : false, //true - пополнение счета
                "name" => $statement['name'] ?? '',
                "inn" => $statement['inn'] ?? '',
                "kpp" => $statement['kpp'] ?? '',
                "bic" => $statement['bic'] ?? '',
                "bank" => $statement['bank'] ?? '',
                "bankaccount" => $statement['bankaccount'] ?? '',
                "account" => $statement['account'] ?? ''
            ];
        }
        return $ret;
    }

    /**
     * Сохранить выписки в БД
     * @param $TypeSchet
     * @param $dateFrom
     * @param $dateTo
     * @throws \yii\db\Exception
     */
    private function SaveStatemens($TypeSchet, $dateFrom, $dateTo)
    {
        $tr = Yii::$app->db->beginTransaction();

        $UslPsr = Uslugatovar::findOne(['IDPartner' => $this->Partner->ID, 'IsCustom' => TU::$TOSCHET, 'IsDeleted' => 0]);
        $UslCard = Uslugatovar::findOne(['IDPartner' => $this->Partner->ID, 'IsCustom' => TU::$TOCARD, 'IsDeleted' => 0]);

        $BalanceIn = new BalancePartner(BalancePartner::IN, $this->Partner->ID);
        $BalanceOut = new BalancePartner(BalancePartner::OUT, $this->Partner->ID);

        foreach ($this->list as $statement) {

            $existId = Yii::$app->db->createCommand("
                SELECT
                    `ID`
                FROM
                    `statements_account`
                WHERE
                    `IdPartner` = :IDPARTNER
                    AND `BnkId` = :BNKID
                    AND `TypeAccount` = :ACCTYPE
            ", [':IDPARTNER' => $this->Partner->ID, ':BNKID' => $statement['id'], ':ACCTYPE' => $TypeSchet])->queryScalar();

                $sumVyp = round($statement['summ'] * 100.0);
                $comisSum = 0;
                $name = $statement['name'];
                $inn = $statement['inn'];
                $description = $statement['description'];
                if ($inn == '7709129705') {
                    //заменить ТКБ на Vepay и прибвать комиссию
                    $name = 'ООО "ПКБП"';
                    $inn = '7728487400';
                    /*if (!empty($this->Partner->SchetTcbNominal)) {
                        $comisSum = $this->CalcComiss($sumVyp, $statement['description'], $UslPsr, $UslCard);
                    }*/
                    $description = $this->ChangeDescript($description);
                } elseif ($inn == '7707083893' || $inn == '7744001497') {
                    //сбербанк,гпб - подставить реквизиты из name и назначения
                    $n = explode('//', $name);
                    if (count($n) > 2) {
                        $name = $n[1].(isset($n[3]) ? '//'.$n[3].'//' : '');
                    }
                    if (preg_match('/ИНН\s+(\d+)/ius', $description, $d)) {
                        $inn = $d[1];
                    }
                } elseif (empty($inn) || $inn == 0) {
                    //нет инн в пп, взять из назначения
                    if (preg_match('/ИНН\s+(\d+)/ius', $description, $d)) {
                        $inn = $d[1];
                    }
                }

            if (!$existId) {
                $statementsAccaunt = new StatementsAccount([
                    'IdPartner' => $this->Partner->ID,
                    'TypeAccount' => $TypeSchet,
                    'BnkId' => $statement['id'],
                    'NumberPP' => $statement['number'],
                    'DatePP' => strtotime($statement['date']),
                    'DateDoc' => strtotime($statement['datedoc']),
                    'DateRead' => time(),
                    'SummPP' => $sumVyp,
                    'SummComis' => $comisSum,
                    'Description' => $description,
                    'IsCredit' => $statement['iscredit'], //true - пополнение счета
                    'Name' => $name,
                    'Inn' => $inn,
                    'Kpp' => $statement['kpp'],
                    'Account' => $statement['account'],
                    'Bic' => $statement['bic'],
                    'Bank' => $statement['bank'],
                    'BankAccount' => $statement['bankaccount']
                ]);
                $statementsAccaunt->save(false);

                $IdStatm = Yii::$app->db->lastInsertID;

                if ($statement['iscredit']/* && $this->IsPopolnen($statement['description'])*/) {
                    //пополнение счета
                    //0 - счет на выдачу 1 - счет на погашение 2 - номинальный счет
                    if ($TypeSchet == 2 && mb_stripos($description, 'Переводы по TCBPay') === false) {
                        //баланс номинального счета
                        $BalanceIn->Inc($sumVyp, $description, 0, 0, $IdStatm);
                    } elseif ($TypeSchet == 0) {
                        //баланс выдачи
                        $BalanceOut->Inc($sumVyp, $description, 0, 0, $IdStatm);
                    }
                } else {
                    //списание со счета
                    //0 - счет на выдачу 1 - счет на погашение 2 - номинальный счет
                    if ($TypeSchet == 0 /*&& mb_stripos($description, 'Переводы по TCBPay') === false*/) {
                        //баланс выдачи
                        $BalanceOut->Dec($sumVyp, $description, 0, 0, $IdStatm);
                    } elseif ($TypeSchet == 2) {
                        //баланс номинального счета
                        $BalanceIn->Dec($sumVyp, $description, 0, 0, $IdStatm);
                    }
                }

            } else {
                Yii::$app->db->createCommand()->update('statements_account', [/*
                    'NumberPP' => $statement['number'],
                    'DatePP' => strtotime($statement['date']),
                    'SummPP' => $sumVyp,*/
                    'SummComis' => $comisSum,
                    /*'Description' => $description,
                    'IsCredit' => $statement['iscredit'], //true - пополнение счета*/
                    'DateDoc' => strtotime($statement['datedoc']),
                    'Name' => $name,
                    'Inn' => $inn,
                    'Kpp' => $statement['kpp'],/*
                    'Account' => $statement['account'],
                    'Bic' => $statement['bic'],
                    'Bank' => $statement['bank'],
                    'BankAccount' => $statement['bankaccount']*/
                ],'`ID` = :ID', [':ID' => $existId]
                )->execute();
            }
        }

        $IdPlanner = Yii::$app->db->createCommand("
            SELECT
                `ID`
            FROM
                `statements_planner`
            WHERE
                `IdPartner` = :IDPARTNER
                AND `IdTypeAcc` = :ACCTYPE
        ", [':IDPARTNER' => $this->Partner->ID, ':ACCTYPE' => $TypeSchet])->queryScalar();

        if ($IdPlanner) {
            Yii::$app->db->createCommand()->update('statements_planner', [
                'DateUpdateFrom' => $dateFrom,
                'DateUpdateTo' => min($dateTo, time() - 60)
            ],'`ID` = :ID', [':ID' => $IdPlanner])->execute();
        } else {
            Yii::$app->db->createCommand()->insert('statements_planner', [
                'IdPartner' => $this->Partner->ID,
                'IdTypeAcc' => $TypeSchet,
                'DateUpdateFrom' => $dateFrom,
                'DateUpdateTo' => min($dateTo, time() - 60)
            ])->execute();
        }

        $tr->commit();
    }


    private function ChangeDescript($description)
    {
        $ret = $description;
        if (mb_strripos($description, 'Комиссия Банка') !== false) {
            $ret = str_ireplace('Комиссия Банка', 'Комиссия Vepay', $description);
        } elseif (mb_strripos($description, 'Комиссия по операциям') !== false) {
            $ret = preg_replace('/за вычетом комиссии [\d+\.]+/ius', '', $description);
        }

        return $ret;
    }

    /**
     * @param $sumVyp
     * @param $description
     * @param Uslugatovar $UslPsr
     * @param Uslugatovar $UslCard
     * @return float|int
     */
    private function CalcComiss($sumVyp, $description, $UslPsr, $UslCard)
    {
        $comis = $sumVyp;
        if (mb_strripos($description, 'Комиссия Банка') !== false ||
            mb_strripos($description, 'Комиссия по документу') !== false ||
            mb_strripos($description, 'Досписание комиссии по документу') !== false
        ) {
            //ПСР
            $comis = round($sumVyp / $UslPsr->ProvComisPC * $UslPsr->ProvVoznagPC);
            if ($comis < $UslPsr->ProvVoznagMin * 100.0) {
                $comis = $UslPsr->ProvVoznagMin * 100.0;
            }
        } elseif (mb_strripos($description, 'Комиссия по операциям') !== false) {
            //на карту
            $comis = round($sumVyp / $UslCard->ProvComisPC * $UslCard->ProvVoznagPC);
            if ($comis < $UslCard->ProvVoznagMin * 100.0) {
                $comis = $UslCard->ProvVoznagMin * 100.0;
            }
        }
        return $comis - $sumVyp;
    }

    private function IsPopolnen($description)
    {
        $nazn = [
            'Перевод средств между своими счетами',
            'пополнение транзитного счета',
            'Пополнение лицевого счета',
            'Оплата по договору займа'
        ];
        foreach ($nazn as $n) {
            if (mb_stripos($n, $description) !== false) {
                return true;
            }
        }
        return false;
    }

}