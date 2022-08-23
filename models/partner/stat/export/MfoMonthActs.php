<?php


namespace app\models\partner\stat\export;

use app\models\partner\stat\ActMfo;
use app\models\partner\stat\PayShetStat;
use app\models\partner\stat\VyvodInfo;
use app\models\partner\UserLk;
use app\models\payonline\Partner;
use app\models\PpExport1s;
use app\models\TU;
use app\services\paymentReport\PaymentReportService;
use app\services\PaymentTransferService;
use Yii;
use yii\base\Model;

class MfoMonthActs extends Model
{
    private $period;

    public $datefrom;
    public $IdPart;

    public function rules()
    {
        return [
            [['datefrom'], 'date', 'format' => 'php:m.Y'],
            [['IdPart'], 'integer']
        ];
    }

    public function afterValidate()
    {
        $this->period = strtotime("01.".$this->datefrom);
        parent::afterValidate();
    }

    public function GetError()
    {
        $err = $this->firstErrors;
        $err = array_pop($err);
        return $err;
    }

    /**
     * Создать акты по агентам
     * @return int
     * @throws \yii\db\Exception
     */
    public function CreateActs()
    {
        if ($this->IdPart) {
            $partners = Partner::findAll(['ID' => $this->IdPart, 'IsDeleted' => 0]);
        } else {
            $partners = Partner::findAll(['IsDeleted' => 0]);
        }

        foreach ($partners as $partner) {
            $this->CreateAct($partner);
        }
        return 1;
    }

    /**
     * Сохранить данные акта
     * @param $partner
     * @return int
     * @throws \yii\db\Exception
     */
    private function CreateAct(Partner $partner)
    {
        $periodPrev = strtotime('-1 month', $this->period);

        $prevAct = ActMfo::findOne(['ActPeriod' => $periodPrev, 'IdPartner' => $partner->ID]);

        $act = ActMfo::findOne(['ActPeriod' => $this->period, 'IdPartner' => $partner->ID]);
        if (!$act) {
            $act = new ActMfo();
        }

        $paymentReportService = new PaymentReportService();

        $pays = new PayShetStat();
        $pays->setAttributes([
            'IdPart' => $partner->ID,
            'datefrom' => date("01.m.Y H:i", $this->period),
            'dateto' => date("t.m.Y 23:59", $this->period),
            'TypeUslug' => TU::InAll()
        ]);
        $dataIn = $paymentReportService->getLegacyReportEntities(true, $pays);
        $pays->setAttributes([
            'IdPart' => $partner->ID,
            'datefrom' => date("01.m.Y H:i", $this->period),
            'dateto' => date("t.m.Y 23:59", $this->period),
            'TypeUslug' => TU::OutMfo()
        ]);
        $dataOut = $paymentReportService->getLegacyReportEntities(true, $pays);

        $act->IdPartner = $partner->ID;
        $act->NumAct = $prevAct ? $prevAct->NumAct + 1 : 1;
        $act->ActPeriod = $this->period;
        $act->BeginOstatokPerevod = $prevAct ? $prevAct->EndOstatokPerevod : 0;
        $act->BeginOstatokVyplata = $prevAct ? $prevAct->EndOstatokVyplata : 0;
        $act->CntPerevod = 0;
        $act->SumPerevod = 0;
        $act->ComisPerevod = 0;
        $act->SumSchetComisPerevod = 0;
        $MerchVozn = 0;
        foreach ($dataIn as $row) {
            $act->SumPerevod += $row['SummPay'];
            $act->ComisPerevod += 0;
            $act->SumSchetComisPerevod += $row['VoznagSumm'];
            $MerchVozn += $row['MerchVozn'];
            $act->CntPerevod += $row['CntPays'];
        }
        $act->SumVozvrat = $pays->GetSummVozvrat();
        $act->CntVyplata = 0;
        $act->SumVyplata = 0;
        $act->ComisVyplata = 0;
        $act->SumSchetComisVyplata = 0;
        foreach ($dataOut as $row) {
            $act->SumVyplata += $row['SummPay'];
            $act->ComisVyplata += $row['VoznagSumm'];
            if (!$partner->VoznagVyplatDirect && ($partner->IsCommonSchetVydacha || $partner->IsUnreserveComis)) {
                $act->SumSchetComisVyplata += $row['MerchVozn'];  //c комиссией банка
            } else {
                $act->SumSchetComisVyplata += $row['VoznagSumm']; //без комиссии банка
            }
            $act->CntVyplata += $row['CntPays'];
        }
        //$act->SumPerechislen = $pays->GetSummPepechislen(1);
        //$act->SumPerechObespech = $pays->GetSummPepechislen(0);
        $VyvodInfo = new VyvodInfo([
            'Partner' => $partner,
            'DateFrom' => strtotime(date("01.m.Y H:i:s", $this->period)),
            'DateTo' => strtotime(date("t.m.Y 23:59:59", $this->period))
        ]);
        $act->SumPerechislen = $VyvodInfo->GetSummPepechislen(1);
        $act->SumPerechObespech = $VyvodInfo->GetSummPepechislen(0);
        $act->SumPostuplen = $VyvodInfo->SumPostuplen();
        $act->EndOstatokPerevod = $act->SumPerevod - $act->SumPerechislen - $act->SumPerechObespech - $MerchVozn;
        $act->EndOstatokVyplata = $act->SumPostuplen - $act->SumVyplata - $act->ComisVyplata;
        $act->DateCreate = time();

        $act->BeginOstatokVoznag = $prevAct ? $prevAct->EndOstatokVoznag : 0;
        $act->EndOstatokVoznag = $act->BeginOstatokVoznag;

        $act->FileName = "act_".$partner->ID."_".$act->ActPeriod.".xlsx";

        $act->save(false);

        $this->SaveXlsDocument($act);

        return 1;
    }

    public function PubActs()
    {
        if ($this->IdPart) {
            $partners = Partner::findAll(['ID' => $this->IdPart, 'IsDeleted' => 0]);
        } else {
            $partners = Partner::findAll(['IsDeleted' => 0]);
        }

        foreach ($partners as $partner) {
            $act = ActMfo::findOne(['ActPeriod' => $this->period, 'IdPartner' => $partner->ID]);
            if ($act) {
                $act->IsPublic = 1;
                $act->save(false);
            }
        }
        return 1;
    }

    /**
     * Список актов за период
     * @param $IsAdmin
     * @return ActMfo[]
     */
    public function GetList($IsAdmin)
    {
        $req = ['ActPeriod' => $this->period];
        if ($IsAdmin) {
           if ($this->IdPart) {
               $req['IdPartner'] = $this->IdPart;
           }
        } else {
            $req['IdPartner'] = UserLk::getPartnerId(Yii::$app->user);
            $req['IsPublic'] = 1;
        }
        $acts = ActMfo::findAll($req);
        return $acts;
    }

    /**
     * Сформировать файл акта
     * @param ActMfo $act
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function SaveXlsDocument(ActMfo $act)
    {
        $datefrom = date("01.m.Y H:i", $act->ActPeriod);
        $dateto = date("t.m.Y 23:59", $act->ActPeriod);

        $partner = $act->getPartner();
        if (!$partner) {
            return;
        }

        $XlsActs = new XlsActs([
            'partner' => $partner,
            'act' => $act,
            'datefrom' => strtotime($datefrom),
            'dateto' => strtotime($dateto)
        ]);
        $XlsActs->saveFile($act->FileName);
    }

    /**
     * Скачать файл акта
     * @param ActMfo $act
     * @return array|null
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function GetXlsDocument(ActMfo $act)
    {
        $datefrom = date("01.m.Y H:i", $act->ActPeriod);
        $dateto = date("t.m.Y 23:59", $act->ActPeriod);

        $partner = $act->getPartner();
        if (!$partner) {
            return null;
        }

        $tmpfile = Yii::$app->getBasePath() . "/runtime/acts/" . $act->FileName;
        if (!file_exists($tmpfile)) {
            $XlsActs = new XlsActs([
                'partner' => $partner,
                'act' => $act,
                'datefrom' => strtotime($datefrom),
                'dateto' => strtotime($dateto)
            ]);
            $XlsActs->saveFile($act->FileName);
            return [
                'data' => $XlsActs->content(),
                'name' => 'отчет_' . $partner->ID . '_' . date("m_Y", $act->ActPeriod) . ".xlsx"
            ];
        } else {
            return [
                'data' => $data = file_get_contents($tmpfile),
                'name' => 'отчет_' . $partner->ID . '_' . date("m_Y", $act->ActPeriod) . ".xlsx"
            ];
        }
    }

    public function GetPPDocument(ActMfo $act)
    {
        $partner = $act->getPartner();
        $recviz = null;
        if ($partner) {
            $recviz = $partner->getPartner_bank_rekviz()->one();
        }
        if (!$partner || !$recviz) {
            return null;
        }

        /** @var PaymentTransferService $paymentTransferService */
        $paymentTransferService = Yii::$app->get(PaymentTransferService::class);
        $r = $paymentTransferService->getLegacyRewardRequisites();
        $poluchat = new \stdClass();
        $poluchat->Name = $r['name'];
        $poluchat->INN = $r['inn'];
        $poluchat->KPP = $r['kpp'];
        $poluchat->RaschShet = $r['account'];
        $poluchat->KorShet = $r['ks'];
        $poluchat->NameBank = $r['bankname'];
        $poluchat->SityBank = $r['bankcity'];
        $poluchat->BIK = $r['bic'];
        $PpExport1s = new PpExport1s($poluchat);


        $pp = new \stdClass();
        $pp->Number = $act->ID + 1000;
        $pp->fSumm = round($act->SumSchetComisVyplata / 100.0,2);
        $pp->RaschShet = $recviz->RaschShetPolushat;
        $pp->INN = $recviz->INNPolushat;
        $pp->Name = $recviz->NamePoluchat;
        $pp->KPP = $recviz->KPPPoluchat;
        $pp->NameBank = $recviz->NameBankPoluchat;
        $pp->SityBank = $partner->SityBankPoluchat;
        $pp->BIK = $partner->BIKPoluchat;
        $pp->KorShet = $partner->KorShetPolushat;
        $pp->PokazKBK = '0';
        $pp->OKATO = '0';
        $pp->NaznaChenPlatez = "Вознаграждение за оказанные услуги по договору " . $partner->NumDogovor . ' от ' . $partner->DateDogovor . " за " . date('m.Y', $act->ActPeriod);
        $PpExport1s->AddPp($pp);

        return [
            'data'=> $PpExport1s->Export(),
            'name' => 'отчет_'.$partner->ID.'_'.date("m_Y", $act->ActPeriod).".xlsx"
        ];
    }
}