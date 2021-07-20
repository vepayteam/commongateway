<?php


namespace app\models\partner\admin;


use app\models\partner\admin\structures\MiddleMapResult;
use app\models\partner\admin\structures\VyvodSystemFilterParams;
use app\models\partner\UserLk;
use app\models\payonline\Partner;
use app\models\payonline\Uslugatovar;
use app\models\TU;
use app\services\PartnerService;
use app\services\payment\models\PaySchet;
use Carbon\Carbon;
use Yii;
use yii\base\Model;
use yii\caching\TagDependency;
use yii\db\Query;

class VoznagStatNew extends Model
{
    const STAT_DAY_CACHE_PREFIX = 'VoznagStat_ForDay_';
    const STAT_DAY_TAG_PREFIX   = 'VoznagStat_ForDay_';

    //0 - все 1 - погашение 2 - выдача
    const TYPE_SERVICE_ALL     = 0;
    const TYPE_SERVICE_POGAS   = 1;
    const TYPE_SERVICE_VYDACHA = 2;

    const TYPE_VYVOD_POGASHENIE = 0;
    const TYPE_VYVOD_VYPLATY    = 1;

    const OPERATION_STATE_IN_PROGRESS = 0;
    const OPERATION_STATE_READY       = 1;
    const OPERATION_STATE_FAILED      = 2;

    public $datefrom;
    public $dateto;
    public $IdPart;
    public $TypeUslug = 0; //0 - все 1 - погашение 2 - выдача

    private $partner;

    public function rules()
    {
        return [
            [['IdPart', 'TypeUslug'], 'integer'],
            [['datefrom', 'dateto'], 'date', 'format' => 'php:d.m.Y H:i'],
            [['datefrom', 'dateto'], 'required'],
        ];
    }

    /**
     * Отчет по мерчантам
     *
     * @param $IsAdmin
     *
     * @return array
     * @throws \yii\db\Exception
     */
    public function GetOtchMerchant($IsAdmin)
    {
        $IdPart = $IsAdmin ? $this->IdPart : UserLk::getPartnerId(Yii::$app->user);

        $this->partner = Partner::findOne(['ID' => $IdPart]);

        $partner = $this->partner;

        // @TODO: Нужно решить момент со случаем, когда $partner === null. Узнать, каким путём исправить. Incarnator | 2021-03-09

        $tuList = [];
        if ($this->TypeUslug == self::TYPE_SERVICE_POGAS || $this->TypeUslug == self::TYPE_SERVICE_ALL) {
            $tuList = array_merge($tuList, TU::InAll());
        }
        if ($this->TypeUslug == self::TYPE_SERVICE_VYDACHA || $this->TypeUslug == self::TYPE_SERVICE_ALL) {
            $tuList = array_merge($tuList, TU::OutMfo());
        }

        $dateFrom = Carbon::createFromFormat('d.m.Y H:i:s', $this->datefrom . ":00")->getTimestamp();
        $dateTo = Carbon::createFromFormat('d.m.Y H:i:s', $this->dateto . ":59")->getTimestamp();

        $query = PaySchet::find()
                         ->with('uslugatovar')
                         ->from(['ps FORCE INDEX(DateCreate_idx)' => PaySchet::tableName()])
                         ->select([
                             'ps.IdUsluga',
                             'SUM(ps.SummPay) AS SummPay',
                             'SUM(ps.ComissSumm) AS ComissSumm',
                             'SUM(ps.MerchVozn) AS MerchVozn',
                             'SUM(ps.BankComis) AS BankComis',
                             'COUNT(*) AS CntPays',
                             'ut.*',
                         ])
                         ->innerJoin('`uslugatovar` AS ut', 'ps.IdUsluga = ut.ID')
                         ->andWhere(['BETWEEN', 'ps.DateCreate', $dateFrom, $dateTo])
                         ->andWhere(['=', 'ps.Status', '1'])
                         ->andWhere(['in', 'ut.IsCustom', $tuList])
                         ->andWhere(['ut.IDPartner' => $partner->ID])
                         ->groupBy('ps.IdUsluga')
                         ->indexBy('IdUsluga');

        $serviceSums = $query->all();

        return $this->mapReport($serviceSums, $partner);
    }

    /**
     * @param array|PaySchet[] $queryData
     * @param Partner          $partner
     *
     * @return array|MiddleMapResult[]
     */
    private function mapReport(array $queryData, Partner $partner): array
    {
        /** @var PartnerService $partnerService */
        $partnerService = \Yii::$app->get(PartnerService::class);

        $result = array_map(static function(PaySchet $serviceSum) use ($partner): MiddleMapResult {

            $uslugatovar = $serviceSum->uslugatovar;

            return new MiddleMapResult([
                'NamePartner' => $partner->Name,
                'IDPartner'   => $partner->ID,

                'SummPay'    => $serviceSum->SummPay,
                'ComissSumm' => $serviceSum->ComissSumm,
                'MerchVozn'  => $serviceSum->MerchVozn,
                'BankComis'  => $serviceSum->BankComis,
                'CntPays'    => $serviceSum->CntPays,

                'IdUsluga'           => $serviceSum->IdUsluga,
                'IsCustom'           => $uslugatovar->IsCustom,
                'ProvVoznagPC'       => $uslugatovar->ProvVoznagPC,
                'ProvVoznagMin'      => $uslugatovar->ProvVoznagMin,
                'ProvComisPC'        => $uslugatovar->ProvComisPC,
                'ProvComisMin'       => $uslugatovar->ProvComisMin,
                'VoznagVyplatDirect' => $partner->VoznagVyplatDirect,
                'UslugaTovarModel'   => $uslugatovar,
            ]);

        }, $queryData);

        $ret = [];

        $dateFrom = strtotime($this->datefrom . ":00");
        $dateTo = strtotime($this->dateto . ":59");

        /** @var MiddleMapResult $row */
        foreach ($result as $row) {

            $row->setVoznagSumm($row->getMerchVozn());

            $indx = $row->getIDPartner();

            if ( !isset($ret[$indx]) ) {
                $typeVyvyod = self::TYPE_VYVOD_POGASHENIE;
                if ( in_array($row->getIsCustom(), [TU::$TOSCHET, TU::$TOCARD], true) ) {
                    $typeVyvyod = self::TYPE_VYVOD_VYPLATY;
                }

                $filterParams = new VyvodSystemFilterParams(['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'filterByStateOp' => true, 'typeVyvyod' => $typeVyvyod]);

                $row->SetSummVyveden($partnerService->getSummVyveden($partner, $filterParams));
                $row->SetDataVyveden($partnerService->getDataVyveden($partner, $filterParams));

                if ( in_array($row->getIsCustom(), [TU::$TOSCHET, TU::$TOCARD], true) ) {
                    $row->setSummPerechisl(0);
                    $row->setDataPerechisl(0);
                } else {
                    $row->setSummPerechisl($partnerService->getSummPerechisl($partner, $filterParams));
                    $row->setDataPerechisl($partnerService->getDataPerechisl($partner, $filterParams));
                }

                $ret[$indx] = $row;

            } else {
                /** @var MiddleMapResult $retItem */
                $retItem = $ret[$indx];

                $retItem->setSummPay(($retItem->getSummPay() + $row->getSummPay()));
                $retItem->setComissSumm(($retItem->getComissSumm() + $retItem->getComissSumm()));
                $retItem->setVoznagSumm(($retItem->getVoznagSumm() + $row->getVoznagSumm()));
                $retItem->setMerchVozn(($retItem->getMerchVozn() + $row->getMerchVozn()));
                $retItem->setBankComis(($retItem->getBankComis() + $row->getBankComis()));
                $retItem->setCntPays(($retItem->getCntPays() + $row->getCntPays()));

                $ret[$indx] = $retItem;
            }
        }

        // @TODO: Пока здесь возвращается в виде многомерного массива в контроллер. Затем переделать на массив объектов. Incarnator | 2021-03-09
        return array_map(static function(MiddleMapResult $item): array {
            $item->setUslugaTovarModel(null);

            return $item->toArray();
        }, $ret);
    }

    /**
     * Сумма вознаграждения по партнёру
     *
     * @return int|mixed
     * @throws \yii\db\Exception
     */
    public function GetSummVoznag()
    {
        $sumVoznag = 0;
        $otch = $this->GetOtchMerchant(true);
        foreach ($otch as $row) {
            $sumVoznag += $row['VoznagSumm'];
        }

        return $sumVoznag;
    }
}
