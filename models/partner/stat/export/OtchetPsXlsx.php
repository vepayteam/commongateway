<?php


namespace app\models\partner\stat\export;

use app\models\Helper;
use app\models\partner\stat\PayShetStat;
use app\models\payonline\Partner;
use app\models\TU;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Yii;
use yii\db\Query;

class OtchetPsXlsx
{
    public $datefrom;
    public $dateto;
    public $partner = 0;

    public function __construct($datefrom, $dateto, $partner)
    {
        $this->datefrom = strtotime($datefrom.":00");
        $this->dateto = strtotime($dateto.":59");
        $this->partner = (int)$partner;
    }

    public function RenderContent()
    {
        $objPHPExcel = new Spreadsheet();
        $objPHPExcel->setActiveSheetIndex(0);
        $this->sheet = $objPHPExcel->getActiveSheet();

        $this->sheet->getColumnDimension('A')->setWidth(26);
        $this->sheet->getColumnDimension('B')->setWidth(21);
        $this->sheet->getColumnDimension('C')->setWidth(18);
        $this->sheet->getColumnDimension('D')->setWidth(18);
        $this->sheet->getColumnDimension('E')->setWidth(18);
        $this->sheet->getColumnDimension('F')->setWidth(18);
        $this->sheet->getColumnDimension('G')->setWidth(18);
        $this->sheet->getColumnDimension('H')->setWidth(18);
        $this->sheet->getColumnDimension('I')->setWidth(21);

        $head = [
            "Платежная система",
            "Остатки на начало периода ". date("d.m.Y", $this->datefrom),
            "Выручка, руб (погашения)",
            "Выдача займа, руб",
            "Пополнение плат системы, руб",
            "Перечисление на р/сч, руб",
            "Прочие списания (погашения), руб",
            "Прочие списания (выдачи), руб",
            "Остаток на конец периода ". date("d.m.Y", $this->dateto)
        ];

        $this->sheet->getStyle("A1:I1")->getFont()
            ->setBold(true);
        $this->sheet->getStyle("A1:I1")->getAlignment()
            ->setWrapText(true);
        $this->sheet->getStyle('A1:I1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        foreach ($head as $k => $h) {
            $this->sheet->setCellValue(self::xl($k)."1", $h);
            $this->sheet->getStyle(self::xl($k)."1")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $data = $this->GetData();
        foreach ($data as $i => $row) {
            foreach ($row as $k => $v) {
                $this->sheet->setCellValue(self::xl($k) . ($i + 2), $k > 0 ? $v / 100 : $v);
                $this->sheet->getStyle(self::xl($k) . ($i + 2))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }
        }

        $this->sheet->setCellValue(self::xl($k+20)."1", 'Остаток на конец = Остаток на начало периода + Выручка - Выдача займа + Пополнение плат системы + Перечисление на р/сч');

        $content['name'] = 'otchet.xlsx';
        $content['data'] = $this->content($objPHPExcel);

        return $content;
    }

    /**
     * Контент XLSX
     * @return string|null
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Exception
     */
    private function content($document)
    {
        //Сохранение в файл
        $data = null;

        $tmpfile = Yii::$app->getBasePath() . "/runtime/tmp" . random_int(10000, 100000) . ".xlsx";
        $writer = IOFactory::createWriter($document, 'Xlsx');
        $writer->save($tmpfile);
        $data = file_get_contents($tmpfile);
        @unlink($tmpfile);

        return $data;
    }

    private static function xl($inxd)
    {
        return Helper::xl($inxd);
    }

    private function GetData()
    {
        $ret = [];

        if (!$this->datefrom || !$this->dateto) {
            return $ret;
        }

        $partners = Partner::find()->where(['IsDeleted' => 0]);
        if ($this->partner > 0) {
            $partners = $partners->andWhere(['ID' => $this->partner]);
        } else {
            $partners = $partners->andWhere(['not in', 'ID', [1,5,7,9]]);
        }
        $partners = $partners->all();
        /** @var Partner $partner */
        foreach ($partners as $partner) {
            //$begOstVozn = $this->VoznVepayPrevPeriod($partner);
            $row = [
                $partner->Name,
                $this->OstBeg($partner),
                $this->PogashSum($partner),
                $this->VydachSum($partner),
                $this->PopolnenSum($partner),
                $this->VyvodSum($partner),
                $this->ProchSpisanPaySum($partner, TU::InAll()),
                $this->ProchSpisanPaySum($partner, TU::OutMfo()),
                0//$this->OstEnd($partner)
            ];
            $row[8] = $row[1] + $row[2] - $row[3] + $row[4] - $row[5];
            $ret[] = $row;

            Yii::$app->db->createCommand()
                ->delete('otchetps', ['IdPartner' => $partner->ID, 'DateFrom' => $this->datefrom, 'DateTo' => $this->dateto])
                ->execute();
            Yii::$app->db->createCommand()
                ->insert('otchetps', [
                    'IdPartner' => $partner->ID,
                    'DateFrom' => $this->datefrom,
                    'DateTo' => $this->dateto,
                    'OstBeg' => $row[1],
                    'OstEnd' => $row[8],
                    'Pogashen' => $row[2],
                    'Vedacha' => $row[3],
                    'Popolnen' => $row[4],
                    'Perechislen' => $row[5],
                    'ProchspisanPogas' => $row[6],
                    'ProchspisanVydach' => $row[7]
                ])->execute();
        }
        return $ret;
    }

    private function VyvodSum($partner)
    {

        /*$query = (new Query())
            ->select('SUM(SumOp)')
            ->from('vyvod_reestr')
            ->where(['IdPartner' => $partner->ID, 'StateOp' => 1, 'TypePerechisl' => 1])
            ->andWhere('DateOp BETWEEN :DATEFROM AND  :DATETO', [':DATEFROM' => $this->datefrom, ':DATETO' => $this->dateto]);

        $sumout = $query->scalar()*/

        $query = (new Query())
            ->select('SUM(SummPP)')
            ->from('statements_account')
            ->where(['IdPartner' => $partner->ID, 'IsCredit' => 0])
            ->andWhere(['or',
                ['like', 'Description', 'Расчеты по договору'],
                ['like', 'Description', 'Перевод денежных средств по заявлению Клиента']
            ])
            ->andWhere(['<>', 'Bic', '044525388'])
            ->andWhere('DatePP BETWEEN :DATEFROM AND  :DATETO', [':DATEFROM' => $this->datefrom, ':DATETO' => $this->dateto]);

        $sumout = $query->scalar();

        return round($sumout);
    }

    private function OstBeg(Partner $partner)
    {
        /*//на счете - сумма погашений + вознаграждение Vepay (за минусом банка) за предыдущий период
        $query = (new Query())
            ->select('SummAfter')
            ->from('partner_orderout')
            ->where(['IdPartner' => $partner->ID])
            ->andWhere('DateOp < :DATEFROM', [':DATEFROM' => $this->datefrom])
            ->orderBy(['ID' => SORT_DESC])
            ->limit(1);

        $sumout = $query->scalar();

        //на счете - остаток по выплатам + вознаграждение Vepay (с учётом банка) за предыдущий период
        //если один счет то плюсом сумма погашений с вознагражденим Vepay за предыдущий период
        $query = (new Query())
            ->select('SummAfter')
            ->from('partner_orderin')
            ->where(['IdPartner' => $partner->ID])
            ->andWhere('DateOp < :DATEFROM', [':DATEFROM' => $this->datefrom])
            ->orderBy(['ID' => SORT_DESC])
            ->limit(1);

        $sumin = $query->scalar();

        return $sumout + $sumin;*/

        $prevMonth = strtotime('-1 month', $this->datefrom);

        $query = (new Query())
            ->select('OstEnd')
            ->from('otchetps')
            ->where(['IdPartner' => $partner->ID, 'DateFrom' => $prevMonth, 'DateTo' => $this->datefrom - 1])
            ->limit(1);

        $sumost = $query->scalar();

        return round($sumost);

    }

    private function OstEnd(Partner $partner)
    {
        $query = (new Query())
            ->select('SummAfter')
            ->from('partner_orderout')
            ->where(['IdPartner' => $partner->ID])
            ->andWhere('DateOp <= :DATETO', [':DATETO' => $this->dateto])
            ->orderBy(['ID' => SORT_DESC])
            ->limit(1);

        $sumout = $query->scalar();

        $query = (new Query())
            ->select('SummAfter')
            ->from('partner_orderin')
            ->where(['IdPartner' => $partner->ID])
            ->andWhere('DateOp <= :DATETO', [':DATETO' => $this->dateto])
            ->orderBy(['ID' => SORT_DESC])
            ->limit(1);

        $sumin = $query->scalar();

        return round($sumout + $sumin);
    }

    private function PogashSum(Partner $partner)
    {
        $pays = new PayShetStat();
        $pays->setAttributes([
            'IdPart' => $partner->ID,
            'datefrom' => date("d.m.Y H:i", $this->datefrom),
            'dateto' => date("d.m.Y H:i", $this->dateto),
            'TypeUslug' => TU::InAll()
        ]);
        $dataIn = $pays->getOtch(true);
        $sum = 0;
        foreach ($dataIn as $data) {
            $sum += $data['SummPay'];
        }
        return round($sum);
    }

    private function VydachSum(Partner $partner)
    {
        $pays = new PayShetStat();
        $pays->setAttributes([
            'IdPart' => $partner->ID,
            'datefrom' => date("d.m.Y H:i", $this->datefrom),
            'dateto' => date("d.m.Y H:i", $this->dateto),
            'TypeUslug' => TU::OutMfo()
        ]);
        $dataOut = $pays->getOtch(true);
        $sum = 0;
        foreach ($dataOut as $data) {
            $sum += $data['SummPay'];
        }
        return round($sum);
    }

    private function PopolnenSum(Partner $partner)
    {
        /*$query = (new Query())
            ->select('SUM(Summ)')
            ->from('partner_orderout')
            ->where(['IdPartner' => $partner->ID, 'TypeOrder' => 0])
            ->andWhere(['>', 'Summ', 0])
            ->andWhere(['or',
                ['like', 'Comment', 'пополнение транзитного счета'],
                ['like', 'Comment', 'Перенос денежных средств']
            ])
            ->andWhere('DateOp BETWEEN :DATEFROM AND  :DATETO', [':DATEFROM' => $this->datefrom, ':DATETO' => $this->dateto]);*/

        $query = (new Query())
            ->select('SUM(SummPP)')
            ->from('statements_account')
            ->where(['IdPartner' => $partner->ID, 'IsCredit' => 1])
            ->andWhere(['or',
                ['like', 'Description', 'пополнение транзитного счета'],
                ['like', 'Description', 'Перенос денежных средств'],
                ['like', 'Description', 'Перенос денежных средств']
            ])
            ->andWhere('DatePP BETWEEN :DATEFROM AND  :DATETO', [':DATEFROM' => $this->datefrom, ':DATETO' => $this->dateto]);

        return round($query->scalar());
    }

    private function ProchSpisanSum(Partner $partner)
    {
        $query = (new Query())
            ->select('SUM(Summ)')
            ->from('partner_orderout')
            ->where(['IdPartner' => $partner->ID, 'TypeOrder' => 0])
            ->andWhere(['like', 'Comment', 'Перенос денежных средств'])
            ->andWhere(['<', 'Summ', 0])
            ->andWhere('DateOp BETWEEN :DATEFROM AND  :DATETO', [':DATEFROM' => $this->datefrom, ':DATETO' => $this->dateto]);

        $sumout = -$query->scalar();

        $query = (new Query())
            ->select('SUM(Summ)')
            ->from('partner_orderin')
            ->where(['IdPartner' => $partner->ID, 'TypeOrder' => 0])
            ->andWhere(['like', 'Comment', 'Перенос денежных средств'])
            ->andWhere(['<', 'Summ', 0])
            ->andWhere('DateOp BETWEEN :DATEFROM AND  :DATETO', [':DATEFROM' => $this->datefrom, ':DATETO' => $this->dateto]);

        $sumin = -$query->scalar();

        return round($sumout+$sumin);
    }

    private function ProchSpisanPaySum(Partner $partner, array $typesUsl)
    {
        $pays = new PayShetStat();
        $pays->setAttributes([
            'IdPart' => $partner->ID,
            'datefrom' => date("d.m.Y H:i", $this->datefrom),
            'dateto' => date("d.m.Y H:i", $this->dateto),
            'TypeUslug' => $typesUsl
        ]);
        $dataIn = $pays->getOtch(true);
        $sum = 0;
        foreach ($dataIn as $data) {
            $sum += $data['ComissSumm'] + $data['MerchVozn'];
        }
        return round($sum);
    }

    private function VoznVepayPrevPeriod(Partner $partner)
    {
        $pays = new PayShetStat();
        $pays->setAttributes([
            'IdPart' => $partner->ID,
            'datefrom' => date("d.m.Y H:i", strtotime('-1 month', $this->datefrom)),
            'dateto' => date("d.m.Y H:i", $this->datefrom - 1),
            'TypeUslug' => array_merge(TU::InAll(), TU::OutMfo())
        ]);
        $dataIn = $pays->getOtch(true);
        $sum = 0;
        foreach ($dataIn as $data) {
            $sum += $data['VoznagSumm'];
        }
        return round($sum);
    }

}