<?php

namespace app\models\partner\stat;

use app\models\partner\UserLk;
use app\models\TU;
use app\services\payment\models\PaySchet;
use app\services\payment\models\repositories\CurrencyRepository;
use Yii;
use yii\base\Model;
use yii\db\Expression;
use yii\data\Pagination;
use yii\db\Query;
use yii\helpers\VarDumper;

class PayShetStat extends Model
{
    public $IdPart = 0;
    public $idParts = [];
    public $usluga = [];
    public $TypeUslug = [];
    public $Extid = '';
    public $id = 0;
    public $summpayFrom = 0;
    public $summpayTo = 0;
    public $status = [];
    public $params = [];
    public $datefrom = '';
    public $dateto = '';

    public function rules()
    {
        return [
            [['IdPart'], 'integer'],
            [['id'], 'string'],
            [['summpayFrom','summpayTo'], 'number'],
            [['Extid'], 'string'],
            [['datefrom', 'dateto'], 'date', 'format' => 'php:d.m.Y H:i'],
            [['datefrom', 'dateto'], 'required'],
            [['usluga', 'status', 'TypeUslug', 'idParts'], 'each', 'rule' => ['integer']],
            [['params'], 'each', 'rule' => ['string']],
        ];
    }

    public function validateParams()
    {
        foreach ($this->params as $key => $value) {
            if (in_array($key, [0, 'bankName', 'operationNumber','cardMask',], true) === true) {
                if(is_string($value) === false) {
                    $this->addError('params', $key.' value is incorrect.');
                    return;
                }
            } elseif (in_array($key, ['fullSummpayFrom','fullSummpayTo'], true) === true) {
                if(is_numeric($value) === false) {
                    $this->addError('params', $key.' value is incorrect.');
                    return;
                }
            }

            $this->addError('params', $key.'value is incorrect');
            return;
        }
    }

    //после валидации - преобразуем данные в int - для запроса в бд.
    public function afterValidate()
    {
        foreach ($this->usluga as $key=>$val){
            $this->usluga[$key] = (int)$val;
        }
        foreach ($this->status as $key=>$val) {
            $this->status[$key] = (int)$val;
        }
        foreach ($this->TypeUslug as $key=>$val) {
            $this->TypeUslug[$key] = (int)$val;
        }
        parent::afterValidate(); // TODO: Change the autogenerated stub
    }

    public function attributeLabels()
    {
        return [
            'datefrom' => 'Период',
            'dateto' => 'Период',
            'summpayFrom' => 'Сумма платежа (от)',
            'summpayTo' => 'Сумма платежа (до)',
            'id' => 'Идентификатор',
            'usluga' => 'Услуга',
        ];
    }

    /**
     * Список платежей
     *
     * @param int $IsAdmin
     * @param int $page
     * @param int $nolimit
     *
     * @return array
     */
    public function getList($IsAdmin, $page = 0, $nolimit = 0)
    {
        $before = microtime(true);
        try {

        $CNTPAGE = 100;

        $IdPart = $IsAdmin ? $this->IdPart : UserLk::getPartnerId(Yii::$app->user);

        $query = new Query();
        $query
            ->select([
                'ps.ID',
                'ps.IdOrg',
                'ps.Extid',
                'ps.RRN',
                'ps.CardNum',
                'ps.CardHolder',
                'ps.BankName',
                'ps.IdKard',//
                'qp.NameUsluga',
                'ps.SummPay',
                'ps.CurrencyId',
                'ps.ComissSumm',
                'ps.MerchVozn',
                'ps.BankComis',
                'ps.DateCreate',
                'ps.DateOplat',
                'ps.PayType',
                'ps.ExtBillNumber',
                'ps.Status',
                'ps.Period',
                'u.`UserDeviceType`',
                'ps.IdKard',
                'ps.CardType',
                'ps.QrParams',
                'ps.IdShablon',
                'ps.IdQrProv',
                'ps.IdAgent',
                'qp.IsCustom',
                'ps.ErrorInfo',
                'ps.BankName',
                'ps.CountryUser',
                'ps.CityUser',
                'qp.ProvVoznagPC',
                'qp.ProvVoznagMin',
                'qp.ProvComisPC',
                'qp.ProvComisMin',
                'ps.sms_accept',
                'ps.Dogovor',
                'ps.FIO',
                'ps.RCCode',
                'ps.IdOrg',
                'ps.RRN',
                'ps.CardNum',
                'ps.CardHolder',
                'ps.BankName',
                'ps.IdKard',//IdCard->cards->IdPan->pan_token->encryptedPan
            ])
            ->from('`pay_schet` AS ps')
            ->leftJoin('`banks` AS b', 'ps.Bank = b.ID')
            ->leftJoin('`uslugatovar` AS qp', 'ps.IdUsluga = qp.ID')
            ->leftJoin('`user` AS u', 'u.`ID` = ps.`IdUser`')
            ->where('ps.DateCreate BETWEEN :DATEFROM AND :DATETO', [
                ':DATEFROM' => strtotime($this->datefrom . ":00"),
                ':DATETO' => strtotime($this->dateto . ":59")
            ]);

        if ($IdPart > 0) {
            $query->andWhere('qp.IDPartner = :IDPARTNER', [':IDPARTNER' => $IdPart]);
        }
        if (count($this->status) > 0) {
            $query->andWhere(['in', 'ps.Status', $this->status]);
        }
        if (count($this->usluga) > 0) {
            $query->andWhere(['in', 'ps.IdUsluga', $this->usluga]);
        }
        if (count($this->TypeUslug) > 0) {
            $query->andWhere(['in', 'qp.IsCustom', $this->TypeUslug]);
        }
        if ($this->id > 0) {
            $query->andWhere('ps.ID = :ID', [':ID' => $this->id]);
        }
        if (!empty($this->Extid)) {
            $query->andWhere('ps.Extid = :EXTID', [':EXTID' => $this->Extid]);
        }
        if ($this->summpay > 0) {
            $query->andWhere('ps.SummPay = :SUMPAY', [':SUMPAY' => round($this->summpay * 100.0)]);
        }
        if (count($this->params) > 0) {
            if (!empty($this->params[0])) $query->andWhere(['like', 'ps.Dogovor', $this->params[0]]);
        }

        $cnt = $sumPay = $sumComis = $voznagps = $bankcomis = 0;
        $allres = $query->cache(10)->all();

        foreach ($allres as $row) {
            $sumPay += $row['SummPay'];
            $sumComis += $row['ComissSumm'];
            $voznagps += $row['ComissSumm'] - $row['BankComis'] + $row['MerchVozn'];
            $bankcomis += $row['BankComis'];
            $cnt++;
        }

        if (!$nolimit) {
            if ($page > 0) {
                $query->offset($CNTPAGE * $page);
            }
            $query->orderBy('`ID` DESC')->limit($CNTPAGE);
        }
        $res = $query->cache(3)->all();

        $ret = [];
        foreach ($res as $row) {
            $row['VoznagSumm'] = $row['ComissSumm'] - $row['BankComis'] + $row['MerchVozn'];

            $ret[] = $row;
        }
        $after = microtime(true);
        $delta = $after - $before;
        Yii::warning('Profiling delta ' . self::class . __METHOD__ . ': ' . $delta);
        } catch (\Exception $e) {
            Yii::warning("getList Error: " . $e->getMessage() . ' file: ' . $e->getFile(). ' line: ' . $e->getLine());
        } catch (\Throwable $e) {
            Yii::warning("getList Error: " . $e->getMessage() . ' file: ' . $e->getFile(). ' line: ' . $e->getLine());
        } finally {
            Yii::warning("getList Error FINALLY ");
        }
        return ['data' => $ret, 'cnt' => $cnt, 'cntpage' => $CNTPAGE, 'sumpay' => $sumPay, 'sumcomis' => $sumComis, 'bankcomis' => $bankcomis, 'voznagps' => $voznagps];
    }

    /**
     * Список платежей
     *
     * @param int $IsAdmin
     * @param int $page
     * @param int $nolimit
     *
     * @return array
     */
    public function getList2($IsAdmin, $page = 0, $nolimit = 0)
    {
        $CNTPAGE = 100;

        $IdPart = $IsAdmin ? $this->IdPart : UserLk::getPartnerId(Yii::$app->user);
        $select = [
            'COUNT(ps.ID) as c',
            'SUM(ps.SummPay) AS SummPay',
            'SUM(ps.ComissSumm) AS ComissSumm',
            //'SUM(ps.ComissSumm - ps.BankComis + ps.MerchVozn) AS VoznagPS',
            'SUM(ps.BankComis) AS BankComis',
            'SUM(ps.MerchVozn) AS MerchVozn',
        ];
        $query = $this->buildQuery($select, $IdPart);

        $cnt = $sumPay = $sumComis = $voznagps = $bankcomis = 0;

        // @TODO: костыль, без него ругается на invalid parameter number, но запрос в консоли БД выполняется нормально
        $res = Yii::$app->db->createCommand($query->createCommand()->getRawSql())->cache(10)->queryOne();

        $sumPay = $res['SummPay'];
        $sumComis = $res['ComissSumm'];
        $summBankComis = $res['BankComis'];
        $summMerchVozn = $res['MerchVozn'];
        $voznagps = $sumComis - $summBankComis + $summMerchVozn;

        $bankcomis = $res['BankComis'];
        $cnt = $res['c'];

        $select = [
            'ps.ID',
            'ps.IdOrg',
            'ps.Extid',
            'ps.RRN',
            'ps.CardNum',
            'ps.CardHolder',
            'ps.IdKard',//
            'qp.NameUsluga',
            'ps.SummPay',
            'ps.CurrencyId',
            'ps.ComissSumm',
            'ps.MerchVozn',
            'ps.BankComis',
            'ps.DateCreate',
            'ps.DateOplat',
            'ps.PayType',
            'ps.ExtBillNumber',
            'ps.Status',
            'ps.Period',
            'u.`UserDeviceType`',
            'ps.CardType',
            'ps.QrParams',
            'ps.IdShablon',
            'ps.IdQrProv',
            'ps.IdAgent',
            'qp.IsCustom',
            'ps.ErrorInfo',
            'ps.CountryUser',
            'ps.CityUser',
            'qp.ProvVoznagPC',
            'qp.ProvVoznagMin',
            'qp.ProvComisPC',
            'qp.ProvComisMin',
            'ps.sms_accept',
            'ps.Dogovor',
            'ps.FIO',
            'ps.RCCode',
            'b.Name as BankName',
        ];
        $query = $this->buildQuery($select, $IdPart);

        if (!$nolimit) {
            if ($page > 0) {
                $query->offset($CNTPAGE * ($page-1));
            }
            $query->orderBy('ID DESC')->limit($CNTPAGE);
        }

        // @TODO: костыль, без него ругается на invalid parameter number, но запрос в консоли БД выполняется нормально
        $res = Yii::$app->db->createCommand($query->createCommand()->getRawSql())->cache(10)->queryAll();

        if($nolimit) {

            $data = self::mapQueryPaymentResult($res);

        } else {
            $data = [];

            foreach ($res as $row) {
                $row['VoznagSumm'] = $row['ComissSumm'] - $row['BankComis'] + $row['MerchVozn'];
                $row['Currency'] = CurrencyRepository::getCurrencyCodeById($row['CurrencyId'])->Code;
                $data[] = $row;
            }
        }

        $pagination = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => $CNTPAGE,
        ]);

        return ['data' => $data, 'pagination' => $pagination, 'cnt' => $cnt, 'cntpage' => $CNTPAGE, 'sumpay' => $sumPay, 'sumcomis' => $sumComis, 'bankcomis' => $bankcomis, 'voznagps' => $voznagps];
    }

    /**
     * @param Query $query
     *
     * @return \Generator
     */
    private static function mapQueryPaymentResult(array $res): \Generator
    {
        foreach ($res as $row) {

            $row['VoznagSumm'] = $row['ComissSumm'] - $row['BankComis'] + $row['MerchVozn'];
            $row['Currency'] = CurrencyRepository::getCurrencyCodeById($row['CurrencyId'])->Code;
            yield $row;
        }
    }

    /**
     * @param $select
     * @param $IdPart
     * @return Query
     */
    private function buildQuery($select, $IdPart)
    {
        $query = new Query();
        $query
            ->select($select)
            ->from('pay_schet AS ps FORCE INDEX(DateCreate_idx)')
            ->leftJoin('banks AS b', 'ps.Bank = b.ID')
            ->leftJoin('cards AS c', 'ps.IdKard = c.ID')
            ->leftJoin('uslugatovar AS qp', 'ps.IdUsluga = qp.ID')
            ->leftJoin('user AS u', 'u.ID = ps.IdUser')
            ->where('ps.DateCreate BETWEEN :DATEFROM AND :DATETO', [
                ':DATEFROM' => strtotime($this->datefrom . ":00"),
                ':DATETO' => strtotime($this->dateto . ":59")
            ]);

        $query->andFilterWhere(['qp.IDPartner' => $this->idParts]);
        $query->andFilterWhere(['ps.ID' => $this->id ? explode(';', $this->id) : null]);
        $query->andFilterWhere(['ps.Extid' => $this->Extid ? explode(';', $this->Extid) : null]);

        if ($IdPart > 0) {
            $query->andWhere('qp.IDPartner = :IDPARTNER', [':IDPARTNER' => $IdPart]);
        }
        if (count($this->status) > 0) {
            if (in_array(PaySchet::STATUS_WAITING, $this->status, true)) {
                $this->status = array_unique(
                    array_merge($this->status, [PaySchet::STATUS_NOT_EXEC, PaySchet::STATUS_WAITING_CHECK_STATUS])
                );
            }
            $query->andWhere(['in', 'ps.Status', $this->status]);
        }
        if (count($this->usluga) > 0) {
            $query->andWhere(['in', 'ps.IdUsluga', $this->usluga]);
        }
        if (count($this->TypeUslug) > 0) {
            $query->andWhere(['in', 'qp.IsCustom', $this->TypeUslug]);
        }
        if (is_numeric($this->summpayFrom) && is_numeric($this->summpayTo)) {
            $query->andWhere(['between', 'ps.SummPay', round($this->summpayFrom * 100.0), round($this->summpayTo * 100.0)]);
        } elseif (is_numeric($this->summpayFrom)) {
            $query->andWhere(['>=', 'ps.SummPay', round($this->summpayFrom * 100.0)]);
        } elseif (is_numeric($this->summpayTo)) {
            $query->andWhere(['<=', 'ps.SummPay', round($this->summpayTo * 100.0)]);
        }
        if (count($this->params) > 0) {
            if (!empty($this->params[0])) $query->andWhere(['like', 'ps.Dogovor', $this->params[0]]);
            if (isset($this->params['fullSummpayFrom']) && isset($this->params['fullSummpayTo'])
                && is_numeric($this->params['fullSummpayFrom']) && is_numeric($this->params['fullSummpayTo'])) {
                $query->andWhere([
                    'between', new Expression('(`ps`.`SummPay` + `ps`.`ComissSumm`)'),
                    round($this->params['fullSummpayFrom'] * 100.0), round($this->params['fullSummpayTo'] * 100.0)
                ]);
            } elseif (isset($this->params['fullSummpayFrom'])&& is_numeric($this->params['fullSummpayFrom'])) {
                $query->andWhere(['>=', new Expression('(`ps`.`SummPay` + `ps`.`ComissSumm`)'),
                                  round($this->params['fullSummpayFrom'] * 100.0)]);
            } elseif (isset($this->params['fullSummpayTo'])&& is_numeric($this->params['fullSummpayTo'])) {
                $query->andWhere(['<=', new Expression('(`ps`.`SummPay` + `ps`.`ComissSumm`)'),
                                  round($this->params['fullSummpayTo'] * 100.0)]);
            }
            if (array_key_exists('cardMask', $this->params) && $this->params['cardMask'] !== '') {
                if (strpos($this->params['cardMask'], '*') !== false) {
                    $regexp = str_replace(['*', ';'], ['(\d|\*)', '|'], $this->params['cardMask']);
                    $query->andWhere(['REGEXP','c.CardNumber', $regexp]);
                } else {
                    $query->andWhere(['like', 'c.CardNumber', $this->params['cardMask'].'%', false]);
                }
            }
            if (array_key_exists('cardMask', $this->params) && $this->params['bankName'] !== '') {
                $query->andWhere(['like', 'b.Name',  $this->params['bankName']]);
            }
            if (array_key_exists('operationNumber', $this->params) && $this->params['operationNumber'] !== '') {
                $query->andWhere(['ps.ExtBillNumber' => explode(';', $this->params['operationNumber'])]);
            }
        }
        return $query;
    }

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

    /**
     * Отчет по платежам
     *
     * @param $IsAdmin
     *
     * @return array
     */
    public function getOtch($IsAdmin)
    {
        $IdPart = $IsAdmin ? $this->IdPart : UserLk::getPartnerId(Yii::$app->user);

        $query = new Query();
        $query
            ->select([
                'ut.NameUsluga',
                'ut.ProvVoznagPC',
                'ut.ProvVoznagMin',
                'ps.SummPay',
                'ps.ComissSumm',
                'ps.MerchVozn',
                'ps.BankComis',
                'ps.IdUsluga',
                'ut.IsCustom',
                'ut.ProvVoznagPC',
                'ut.ProvVoznagMin',
                'ut.ProvComisPC',
                'ut.ProvComisMin'
            ])
            ->from('`pay_schet` AS ps')
            ->leftJoin('`uslugatovar` AS ut', 'ps.IdUsluga = ut.ID')
            ->leftJoin('`banks` AS b', 'ps.Bank = b.ID')
            ->where('ps.DateCreate BETWEEN :DATEFROM AND :DATETO', [
                ':DATEFROM' => strtotime($this->datefrom . ":00"),
                ':DATETO' => strtotime($this->dateto . ":59")
            ])
            ->andWhere('ps.Status = 1');

        if ($IdPart > 0) {
            $query->andWhere('ut.IDPartner = :IDPARTNER', [':IDPARTNER' => $IdPart]);
        }

        if (count($this->usluga) > 0) {
            $query->andWhere(['in', 'ut.ID', $this->usluga]);
        }
        //if ($data['paytype'] >= 0) {
        //$query->andWhere('ps.PayType = :IDPAYTYPE', [':IDPAYTYPE' => $data['paytype']]);
        //}
        if (count($this->TypeUslug) > 0) {
            $query->andWhere(['in', 'ut.IsCustom', $this->TypeUslug]);
        }

        $res = $query->cache(10)->all();

        $ret = [];
        foreach ($res as $row) {
            $indx = $row['IdUsluga'];
            $row['VoznagSumm'] = $row['ComissSumm'] - $row['BankComis'] + $row['MerchVozn'];

            if (!isset($ret[$indx])) {
                $row['CntPays'] = 1;
                $ret[$indx] = $row;
            } else {
                $ret[$indx]['SummPay'] += $row['SummPay'];
                $ret[$indx]['ComissSumm'] += $row['ComissSumm'];
                $ret[$indx]['BankComis'] += $row['BankComis'];
                $ret[$indx]['VoznagSumm'] += $row['VoznagSumm'];
                $ret[$indx]['MerchVozn'] += $row['MerchVozn'];
                $ret[$indx]['CntPays']++;
            }
        }

        return $ret;
    }

    /**
     * Сумма перечислений в МФО
     * @param int $TypePerech
     * @return integer
     * @throws \yii\db\Exception
     */
    public function GetSummPepechislen($TypePerech)
    {
        $summPerechisl = Yii::$app->db->createCommand("
            SELECT
                SUM(`SumOp`)
            FROM
                `vyvod_reestr`
            WHERE
                `IdPartner` = :IDMFO
                AND `DateFrom` >= :DATEFROM
                AND `DateTo` <= :DATETO
                AND `StateOp` = 1
                AND `TypePerechisl` = :TYPEPERECH
            ORDER BY `DateTo` DESC
            LIMIT 1
        ", [
            ':IDMFO' => $this->IdPart,
            ':DATEFROM' => strtotime($this->datefrom . ":00"),
            ':DATETO' => strtotime($this->dateto . ":59"),
            ':TYPEPERECH' => $TypePerech
        ])->queryScalar();

        return (double)$summPerechisl;
    }

    /**
     * Сумма возвращенных платежей
     * @return integer
     * @throws \yii\db\Exception
     */
    public function GetSummVozvrat()
    {
        $summVozvr = Yii::$app->db->createCommand("
            SELECT
                SUM(ps.`SummPay`)
            FROM
                `pay_schet` AS ps
                LEFT JOIN `uslugatovar` AS ut ON ps.IdUsluga = ut.ID
            WHERE
                ut.IDPartner = :IDMFO
                AND ps.`DateCreate` BETWEEN :DATEFROM AND :DATETO
                AND `Status` = 3
                AND ut.`IsCustom` IN (".implode(',', TU::InAll()).")
        ", [
            ':IDMFO' => $this->IdPart,
            ':DATEFROM' => strtotime($this->datefrom . ":00"),
            ':DATETO' => strtotime($this->dateto . ":59")])->queryScalar();

        return (double)$summVozvr;
    }

}