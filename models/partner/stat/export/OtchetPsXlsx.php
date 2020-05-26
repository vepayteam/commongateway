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
        $this->datefrom = strtotime($datefrom);
        $this->dateto = strtotime($dateto);
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
        $this->sheet->getColumnDimension('H')->setWidth(21);

        $head = [
            "Платежная система",
            "Остатки на начало периода",
            "Выручка, руб (погашения)",
            "Выдача займа, руб",
            "Пополнение плат системы, руб",
            "Перечисление на р/сч, руб",
            "Прочие списания, руб",
            "Остаток на конец периода"
        ];

        $this->sheet->getStyle("A1:H1")->getFont()
            ->setBold(true);
        $this->sheet->getStyle("A1:H1")->getAlignment()
            ->setWrapText(true);
        $this->sheet->getStyle('A1:H1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        foreach ($head as $k => $h) {
            $this->sheet->setCellValue(self::xl($k)."1", $h);
            $this->sheet->getStyle(self::xl($k)."1")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        $data = $this->GetData();
        foreach ($data as $i => $row) {
            foreach ($row as $k => $v) {
                $this->sheet->setCellValue(self::xl($k) . ($i + 2), $v);
                $this->sheet->getStyle(self::xl($k) . ($i + 2))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }
        }

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

        $tmpfile = Yii::$app->getBasePath() . "\\runtime\\tmp" . random_int(10000, 100000) . ".xlsx";
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
        foreach ($partners as $partner) {
            $row = [
                $partner->Name,
                $this->OstBeg($partner),
                $this->PogashSum($partner),
                $this->VydachSum($partner),
                $this->PopolnenSum($partner),
                $this->VyvodSum($partner),
                $this->ProchSpisanSum($partner),
                $this->OstEnd($partner)
            ];
            $ret[] = $row;
        }
        return $ret;
    }

    private function VyvodSum($partner)
    {

        $query = (new Query())
            ->select('SUM(SumOp)')
            ->from('vyvod_reestr')
            ->where(['IdPartner' => $partner->ID, 'StateOp' => 1, 'TypePerechisl' => 1])
            ->andWhere('DateOp BETWEEN :DATEFROM AND  :DATETO', [':DATEFROM' => $this->datefrom, ':DATETO' => $this->dateto]);

        $sumout = round($query->scalar()/100.0, 2);

        return $sumout;

    }

    private function OstBeg(Partner $partner)
    {
        $query = (new Query())
            ->select('SummAfter')
            ->from('partner_orderout')
            ->where(['IdPartner' => $partner->ID])
            ->andWhere('DateOp < :DATEFROM', [':DATEFROM' => $this->datefrom])
            ->orderBy(['ID' => SORT_DESC])
            ->limit(1);

        $sumout = round($query->scalar()/100.0, 2);

        $query = (new Query())
            ->select('SummAfter')
            ->from('partner_orderin')
            ->where(['IdPartner' => $partner->ID])
            ->andWhere('DateOp < :DATEFROM', [':DATEFROM' => $this->datefrom])
            ->orderBy(['ID' => SORT_DESC])
            ->limit(1);

        $sumin = round($query->scalar()/100.0, 2);

        return $sumout + $sumin;
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

        $sumout = round($query->scalar()/100.0, 2);

        $query = (new Query())
            ->select('SummAfter')
            ->from('partner_orderin')
            ->where(['IdPartner' => $partner->ID])
            ->andWhere('DateOp <= :DATETO', [':DATETO' => $this->dateto])
            ->orderBy(['ID' => SORT_DESC])
            ->limit(1);

        $sumin = round($query->scalar()/100.0, 2);

        return $sumout + $sumin;
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
        return round($sum/100.0, 2);
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
        return round($sum/100.0, 2);
    }

    private function PopolnenSum(Partner $partner)
    {
        $query = (new Query())
            ->select('SUM(Summ)')
            ->from('partner_orderout')
            ->where(['IdPartner' => $partner->ID, 'TypeOrder' => 0])
            ->andWhere(['>', 'Summ', 0])
            ->andWhere(['or',
                ['like', 'Comment', 'пополнение транзитного счета'],
                ['like', 'Comment', 'Перенос денежных средств']
            ])
            ->andWhere('DateOp BETWEEN :DATEFROM AND  :DATETO', [':DATEFROM' => $this->datefrom, ':DATETO' => $this->dateto]);

        return round($query->scalar()/100.0, 2);
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

        $sumout = round($query->scalar()/100.0, 2);

        $query = (new Query())
            ->select('SUM(Summ)')
            ->from('partner_orderin')
            ->where(['IdPartner' => $partner->ID, 'TypeOrder' => 0])
            ->andWhere(['like', 'Comment', 'Перенос денежных средств'])
            ->andWhere(['<', 'Summ', 0])
            ->andWhere('DateOp BETWEEN :DATEFROM AND  :DATETO', [':DATEFROM' => $this->datefrom, ':DATETO' => $this->dateto]);

        $sumin = round($query->scalar()/100.0, 2);

        $query = (new Query())
            ->select('SUM(Summ)')
            ->from('vyvod_system')
            ->where(['IdPartner' => $partner->ID, 'SatateOp' => 1])
            ->andWhere('DateOp BETWEEN :DATEFROM AND  :DATETO', [':DATEFROM' => $this->datefrom, ':DATETO' => $this->dateto]);

        $sumvozn = round($query->scalar()/100.0, 2);

        return $sumout+$sumin+$sumvozn;
    }

}