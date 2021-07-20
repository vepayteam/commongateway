<?php

namespace app\models\partner\stat\export;

use app\models\Helper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Yii;
use yii\web\NotFoundHttpException;

class XlsActs
{
    private $datefrom;
    private $dateto;
    private $act;
    private $partner;

    /* @var Worksheet $sheet */
    private $sheet;

    public function __construct($params = [])
    {
        foreach ($params as $name => $val) {
            if (property_exists($this, $name)) {
                $this->$name = $val;
            }
        }
    }

    /**
     * Отчет XLSX
     * @return string|null
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Exception
     */
    public function content()
    {
        //Сохранение в файл
        $data = null;

        $tmpfile = Yii::$app->getBasePath() . "\\runtime\\tmp" . random_int(10000, 100000) . ".xlsx";
        $writer = IOFactory::createWriter($this->document(), 'Xlsx');
        $writer->save($tmpfile);
        $data = file_get_contents($tmpfile);
        @unlink($tmpfile);

        return $data;
    }

    /**
     * Отчет XLSX
     * @return string|null
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Exception
     */
    public function saveFile($filename)
    {
        //Сохранение в файл
        $data = null;

        if (!file_exists(Yii::$app->getBasePath() . "/runtime/acts/")) {
            mkdir(Yii::$app->getBasePath() . "/runtime/acts/");
        }
        $tmpfile = Yii::$app->getBasePath() . "/runtime/acts/" . $filename;
        $writer = IOFactory::createWriter($this->document(), 'Xlsx');
        $writer->save($tmpfile);
    }

    /**
     * @return Spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function document()
    {
        $objPHPExcel = new Spreadsheet();
        $objPHPExcel->setActiveSheetIndex(0);
        $this->sheet = $objPHPExcel->getActiveSheet();

        $this->sheet->getColumnDimension('A')->setWidth(2);
        $this->sheet->getColumnDimension('B')->setWidth(4);
        $this->sheet->getColumnDimension('C')->setWidth(14);
        $this->sheet->getColumnDimension('D')->setWidth(14);
        $this->sheet->getColumnDimension('E')->setWidth(14);
        $this->sheet->getColumnDimension('F')->setWidth(6);
        $this->sheet->getColumnDimension('G')->setWidth(6);

        $this->WriteTitle();
        $this->WriteGeo();
        $this->WriteHeader();

        $GapXLS = 5;
        //данные
        foreach ($this->strings() as $row) {
            foreach ($row as $i => $item) {
                $this->sheet->mergeCells("C" . $GapXLS . ":E" . $GapXLS);
                $this->sheet->mergeCells("F" . $GapXLS . ":G" . $GapXLS);
                $this->sheet->mergeCells("H" . $GapXLS . ":J" . $GapXLS);

                if ($i == 2) {
                    $this->sheet->setCellValue(self::xl(5) . $GapXLS, $item);
                } else if ($i == 3) {
                    $this->sheet->setCellValue(self::xl(7) . $GapXLS, $item);
                } else {
                    $this->sheet->setCellValue(self::xl($i + 1) . $GapXLS, $item);
                }
            }
            $this->sheet->getStyle(self::xl(5) . $GapXLS . ":" . self::xl(9) . $GapXLS)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $this->sheet->getStyle("B" . $GapXLS . ":J" . $GapXLS)->getAlignment()
                ->setWrapText(true);
            $this->sheet->getStyle("B" . $GapXLS . ":J" . $GapXLS)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
            $this->sheet->getStyle("B" . $GapXLS . ":J" . $GapXLS)->getFont()
                ->setSize(10);
            $this->sheet->getRowDimension($GapXLS)
                ->setRowHeight(40);

            $GapXLS++;
        }
        $GapXLS = 20;
        $this->WriteSecondTitle($GapXLS);
        $this->WriteSecondHeader($GapXLS);
        //вторая таблица.
        $this->PaySystData($GapXLS);

        //итого второй таблицы.
        $GapXLS++;

        $this->WriteEndDoc($GapXLS);
        return $objPHPExcel;
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function WriteTitle(): void
    {
        $this->sheet->getStyle("B1:J2")->getFont()
            ->setBold(true);
        $this->sheet->getStyle("B1:J2")->getAlignment()
            ->setWrapText(true);
        $this->sheet->getStyle('B1:B2')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $this->sheet->mergeCells("B1:J1");
        $this->sheet->mergeCells("B2:J2");
        $this->sheet->setCellValue("B1",
            'Акт об оказании услуг / Акт сверки № ' . $this->act->NumAct
        );
        $this->sheet->setCellValue("B2",
            'по Договору № '.$this->partner->NumDogovor.' от '.$this->partner->DateDogovor
        );
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function WriteGeo()
    {
        $this->sheet->setCellValue("B3", $this->city());
        $this->sheet->setCellValue("H3", Helper::DateRus($this->dateto));
        $this->sheet->getStyle("H3")->getFont()->setItalic(true);
        $this->sheet->getStyle('B3')->getFont()->setItalic(true);
        $this->sheet->getStyle('H3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $this->sheet->mergeCells("H3:J3");
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function WriteHeader()
    {
        $header = 'Общество с ограниченной ответственностью «Процессинговая компания быстрых платежей», именуемое «Оператор»,' .
            'в лице генерального директора Никонова Г.Б., действующего на основании Устава, составил, а '.$this->partner->UrLico.
            ', именуемое «Контрагент», в лице '.$this->partner->PodpDoljpostRod.' '.$this->partner->PodpisantShort.
            ', действующего на основании '.$this->partner->PodpOsnovanRod.
            ', утвердил настоящий Акт о том, что Оператор надлежащим образом исполнил обязательства по Договору в соответствии с нижеприведенными данными:';
        $this->sheet->setCellValue("B4", $header);
        $this->sheet->getStyle("B4")->getAlignment()
            ->setWrapText(true);
        $this->sheet->getRowDimension(4)->setRowHeight(110);

        $this->sheet->mergeCells("B4:J4");
    }

    private function city()
    {
        return 'г. Москва';
    }

    private function strings(): array
    {
        $data = [
            ['1', 'Дата, время начала отчетного периода',
                date("d.m.Y", $this->datefrom),
                date("H:i:s", $this->datefrom)
            ],
            ['2', 'Дата, время конца отчетного периода',
                date("d.m.Y", $this->dateto),
                date("H:i:s", $this->dateto)
            ],
            ['3', 'Задолженность Оператора перед Контрагентом на начало Отчетного периода, рубли',
                $this->act->BeginOstatokPerevod > 0 ? $this->act->BeginOstatokPerevod / 100.0 : 0,
                Helper::num2str($this->act->BeginOstatokPerevod > 0 ? $this->act->BeginOstatokPerevod / 100.0 : 0)
            ],
            ['4', 'Задолженность Контрагента перед Оператором на начало Отчетного периода, рубли',
                $this->act->BeginOstatokPerevod < 0 ? -$this->act->BeginOstatokPerevod / 100.0 : 0,
                Helper::num2str($this->act->BeginOstatokPerevod < 0 ? -$this->act->BeginOstatokPerevod / 100.0 : 0)
            ],
            ['5', 'Cумма  переводов, принятых Оператором в Отчетном периоде, рубли',
                $this->act->SumPerevod / 100.0,
                Helper::num2str($this->act->SumPerevod / 100.0)
            ],
            ['6', 'Сумма вознаграждения Оператора за Отчетный период, НДС не облагается в соответствии с пп. 4 п. 3 статьи 149 Налогового кодекса РФ, рубли',
                0/*$this->act->ComisPerevod / 100.0*/,
                Helper::num2str(/*$this->act->ComisPerevod*/0 / 100.0)
            ],
            ['7', 'Возвращено Оператором переводов в Отчетном периоде, рубли',
                $this->act->SumVozvrat / 100.0,
                Helper::num2str($this->act->SumVozvrat / 100.0)
            ],
            ['8', 'Подлежит удержанию Оператором по оспариваемым операциям в Отчетном периоде, рубли',
                $this->act->SumPodlejUderzOspariv / 100.0,
                Helper::num2str($this->act->SumPodlejUderzOspariv / 100.0)
            ],
            ['9', 'Подлежит возмещению Оператором по оспариваемым операциям в Отчетном периоде, рубли',
                $this->act->SumPodlejVozmeshOspariv / 100.0,
                Helper::num2str($this->act->SumPodlejVozmeshOspariv / 100.0)
            ],
            ['10', 'Перечислено Контрагентом на расчетный счет Оператора, рубли (в т.ч. суммы, перечисленные Оператором на счет Контрагента, отклоненные банком и подлежащие перечислению повторно)',
                $this->act->SumPerechKontrag / 100.0,
                Helper::num2str($this->act->SumPerechKontrag / 100.0)
            ],
            ['11', 'Перечислено Оператором на счет Контрагента в Отчетном периоде, рубли',
                $this->act->SumPerechislen / 100.0,
                Helper::num2str($this->act->SumPerechislen / 100.0)
            ],
            ['12', 'Перечислено Оператором на счет по учету обеспечения Контрагента в соответствии с Соглашением в Отчетном периоде, рубли',
                $this->act->SumPerechObespech / 100.0,
                Helper::num2str($this->act->SumPerechObespech / 100.0)
            ],
            ['13', 'Задолженность Оператора перед Контрагентом на конец Отчетного периода, рубли',
                $this->act->EndOstatokPerevod > 0 ? $this->act->EndOstatokPerevod / 100.0 : 0,
                Helper::num2str($this->act->EndOstatokPerevod > 0 ? $this->act->EndOstatokPerevod / 100.0 : 0)
            ],
            ['14', 'Задолженность Контрагента перед Оператором на конец Отчетного периода, рубли',
                $this->act->EndOstatokPerevod < 0 ? -$this->act->EndOstatokPerevod / 100.0 : 0,
                Helper::num2str($this->act->EndOstatokPerevod < 0 ? -$this->act->EndOstatokPerevod / 100.0 : 0)
            ]
        ];
        return $data;
    }

    private static function xl($inxd)
    {
        return Helper::xl($inxd);
    }

    /**
     * @param $GapXLS
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function WriteSecondTitle(&$GapXLS)
    {
        $this->sheet->getStyle("B" . $GapXLS)->getFont()
            ->setBold(true);
        $this->sheet->getStyle('B' . $GapXLS)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $this->sheet->mergeCells("B" . $GapXLS . ":J" . $GapXLS);
        $this->sheet->getRowDimension($GapXLS)
            ->setRowHeight(20);
        $this->sheet->setCellValue("B" . $GapXLS, "Таблица расшифровки платежей");

        $GapXLS++;
    }

    /**
     * @param $GapXLS
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function WriteSecondHeader(&$GapXLS)
    {
        $header = [
            '№ п/п',
            'Способ осуществления перевода плательщиком',
            'Сумма переводов, принятых Оператором в Отчетном периоде',
            'Возвращено Оператором переводов физическим лицам в Отчетном периоде',
            'Сумма вознаграждения Оператора, руб. (НДС не облагается)'
        ];

        $this->sheet->mergeCells("C" . $GapXLS . ":D". $GapXLS);
        $this->sheet->mergeCells("E" . $GapXLS . ":F". $GapXLS);
        $this->sheet->mergeCells("G" . $GapXLS . ":H". $GapXLS);
        $this->sheet->mergeCells("I" . $GapXLS . ":J". $GapXLS);

        $this->sheet->setCellValue("B" . $GapXLS, $header[0]);
        $this->sheet->setCellValue("C" . $GapXLS, $header[1]);
        $this->sheet->setCellValue("E" . $GapXLS, $header[2]);
        $this->sheet->setCellValue("G" . $GapXLS, $header[3]);
        $this->sheet->setCellValue("I" . $GapXLS, $header[4]);

        $this->sheet->getStyle("B" . $GapXLS . ":J" . $GapXLS)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);
        $this->sheet->getStyle("B" . $GapXLS . ":J" . $GapXLS)->getFont()
            ->setBold(true);
        $this->sheet->getRowDimension($GapXLS)
            ->setRowHeight(75);
        $this->sheet->getStyle("B" . $GapXLS . ":J" . $GapXLS)->getAlignment()
            ->setWrapText(true);

        $GapXLS++;
    }

    /**
     * @param $GapXLS
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function PaySystData(&$GapXLS)
    {
        $str = [
            [
                '1',
                'Банковские карты',
                $this->act->SumPerevod / 100.0,
                $this->act->SumVozvrat / 100.0,
                $this->act->ComisPerevod / 100.0
            ], [
                '',
                '',
                $this->act->SumPerevod / 100.0,
                $this->act->SumVozvrat / 100.0,
                $this->act->ComisPerevod / 100.0
            ]
        ];
        foreach ($str as $string) {
            $this->sheet->mergeCells("C" . $GapXLS . ":D" . $GapXLS);
            $this->sheet->mergeCells("E" . $GapXLS . ":F" . $GapXLS);
            $this->sheet->mergeCells("G" . $GapXLS . ":H" . $GapXLS);
            $this->sheet->mergeCells("I" . $GapXLS . ":J" . $GapXLS);

            $this->sheet->setCellValue("B" . $GapXLS, $string[0]);
            $this->sheet->setCellValue("C" . $GapXLS, $string[1]);
            $this->sheet->setCellValue("E" . $GapXLS, $string[2]);
            $this->sheet->setCellValue("G" . $GapXLS, $string[3]);
            $this->sheet->setCellValue("I" . $GapXLS, $string[4]);

            $this->sheet->getStyle("B" . $GapXLS . ":J" . $GapXLS)->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
            if (empty($string[0])) {
                $this->sheet->getStyle("B" . $GapXLS . ":J" . $GapXLS)->getFont()
                    ->setBold(true);
            }
            $this->sheet->getStyle("B" . $GapXLS . ":J" . $GapXLS)->getFont()
                ->setSize(10);
            $GapXLS++;
        }
    }

    /**
     * @param $GapXLS
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function WriteEndDoc(&$GapXLS)
    {
        $this->sheet->mergeCells("B" . $GapXLS . ":J" . $GapXLS);
        $this->sheet->setCellValue("B" . $GapXLS, "1. Стороны претензий друг к другу не имеют.");
        $GapXLS++;
        $this->sheet->mergeCells("B" . $GapXLS . ":J" . $GapXLS);
        $this->sheet->setCellValue("B" . $GapXLS, "2. Настоящий Акт составлен, утвержден и подписан в двух экземплярах, имеющих одинаковую юридическую силу, по одному для Оператора и Контрагента.");
        $this->sheet->getRowDimension($GapXLS)
            ->setRowHeight(35);
        $this->sheet->getStyle("B" . $GapXLS . ":J" . $GapXLS)->getAlignment()
            ->setWrapText(true);
        $GapXLS++;
        $GapXLS++;
        $this->sheet->mergeCells("B" . $GapXLS . ":J" . $GapXLS);
        $this->sheet->setCellValue("B" . $GapXLS, "ПОДПИСИ СТОРОН:");
        $this->sheet->getStyle("B" . $GapXLS)->getFont()->setBold(true);
        $this->sheet->getStyle("B" . $GapXLS)->getAlignment()->setHorizontal('center');
        $GapXLS++;
        $GapXLS++;
//        $sheet->mergeCells("B".$GapXLS.":С".$GapXLS);
        $this->sheet->mergeCells("I" . $GapXLS . ":J" . $GapXLS);
        $this->sheet->mergeCells("B" . $GapXLS . ":C" . $GapXLS);
        $this->sheet->setCellValue("B" . $GapXLS, "М.П.");
        $this->sheet->setCellValue("I" . $GapXLS, "М.П.");
        $this->sheet->getStyle("I" . $GapXLS)->getFont()->setBold(true);
        $this->sheet->getStyle("B" . $GapXLS)->getFont()->setBold(true);
        $GapXLS++;
        $GapXLS++;
        $this->sheet->setCellValue("B" . $GapXLS, $this->partner->PodpisantShort);
        $this->sheet->setCellValue("I" . $GapXLS, "Никонов Г.Б.");
        $this->sheet->getStyle("B" . $GapXLS . ":C" . $GapXLS)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
        $this->sheet->getStyle("I" . $GapXLS . ":J" . $GapXLS)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
    }
}