<?php

namespace app\models\partner\stat;

use app\models\partner\UserLk;
use app\services\payment\banks\BRSAdapter;
use app\services\payment\models\PaySchet;
use Yii;

class BrsPaySchetStat extends PayShetStat
{
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
        $this->idBank = BRSAdapter::$bank;

        $ret = [];
        $cnt = $sumPay = $sumComis = $voznagps = $bankcomis = 0;
        $before = microtime(true);

        try {

            $CNTPAGE = 100;

            $IdPart = $IsAdmin ? $this->IdPart : UserLk::getPartnerId(Yii::$app->user);

            $query = PaySchet::find()
                ->alias('ps')
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
                    'ps.IdKard',
                    'pbg.Login as GateLogin',
                    'pbg.AdvParam_1 as GateAdvParam_1',
                ])
                ->leftJoin('`banks` AS b', 'ps.Bank = b.ID')
                ->leftJoin('`uslugatovar` AS qp', 'ps.IdUsluga = qp.ID')
                ->leftJoin('`partner_bank_gates` AS pbg',
                    'pbg.BankId = b.ID AND pbg.PartnerId = qp.IDPartner AND pbg.TU = qp.IsCustom')
                ->leftJoin('`user` AS u', 'u.`ID` = ps.`IdUser`')
                ->where('ps.Bank = :BANK', [':BANK' => BRSAdapter::$bank])
                ->andWhere('ps.DateCreate BETWEEN :DATEFROM AND :DATETO', [
                    ':DATEFROM' => strtotime($this->datefrom . ":00"),
                    ':DATETO' => strtotime($this->dateto . ":59")
                ]);

            if ($IdPart > 0) {
                $query->andWhere('qp.IDPartner = :IDPARTNER', [':IDPARTNER' => $IdPart]);
            }
            if (count($this->status) > 0) {
                $query->andWhere(['in', 'ps.Status', $this->status]);
            }
            if (count($this->TypeUslug) > 0) {
                $query->andWhere(['in', 'qp.IsCustom', $this->TypeUslug]);
            }

            $allres = $query->cache(10)->all();

            foreach ($allres as $row) {
                /** @var PaySchet $row */
                $sumPay += $row->SummPay;
                $sumComis += $row->ComissSumm;
                $voznagps += $row->ComissSumm - $row->BankComis + $row->MerchVozn;
                $bankcomis += $row->BankComis;
                $cnt++;
            }

            if (!$nolimit) {
                if ($page > 0) {
                    $query->offset($CNTPAGE * $page);
                }
                $query->orderBy('`ID` DESC')->limit($CNTPAGE);
            }

            $ret = $query->cache(3)->all();

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
}
