<?php


namespace app\models\partner\stat\export;


use app\models\partner\stat\ExportExcel;
use app\models\partner\stat\PayShetStat;
use app\models\partner\UserLk;
use app\models\Payschets;
use app\services\paymentReport\PaymentReportService;
use Yii;
use yii\helpers\VarDumper;
use yii\web\Response;

/**
 * @property ExportExcel $excel
 * @property PayShetStat $stat
 */
class ExportOtch
{
    private $data;
    private $excel;
    private $stat;
    private $otch;

    public function __construct(ExportExcel $excel, PayShetStat $stat)
    {
        $this->excel = $excel;
        $this->stat = $stat;
    }

    public function content()
    {
        return $this->excel->CreateXls('Export', $this->headXLSX(), $this->dataXLSX(), $this->sizesXLSX(), $this->itogsXLSX());
    }

    public function test()
    {
        $all = [
            'IdPart' => '-1',
            'summary' => '',
            'id' => '',
            'datefrom' => '25.02.2019 00:00',
            'dateto' => '28.12.2019 23:59',
            'usluga[]' => '-1',
            'status[]' => '-1'
        ];

        //партнер
        $withPartner = $all;
        $withPartner['IdPart'] = '1';

        $_GET = array_merge($_GET, $withPartner);

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->setDownloadHeaders(
            "export.xlsx",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        );
        return $this->content();
    }

    public function successful():bool
    {
        if ($this->dataXLSX()){
            return true;
        }
        return false;
    }

    private function isAdmin(): bool
    {
        return UserLk::IsAdmin(Yii::$app->user);
    }

    private function data(): array
    {
        if (!$this->otch) {
            $this->stat->load(Yii::$app->request->get(), '');

            $paymentReportService = new PaymentReportService();

            $this->otch = $paymentReportService->getLegacyReportEntities($this->isAdmin(), $this->stat);
        }
        return $this->otch;
    }

    private function dataXLSX(): array
    {
        $data = [];
        $i = 0;
        foreach ($this->data() as $id => $values) {
            $i++;
            $data[] = [
                $i,
                $values['NameUsluga'],
                sprintf('%02.2f', $values['SummPay'] / 100.0),
                sprintf('%02.2f', $values['ComissSumm'] / 100.0),
                sprintf('%02.2f', ($values['SummPay'] + $values['ComissSumm']) / 100.0),
                $this->isAdmin() ? sprintf("%02.2f", $values['BankComis'] / 100.0) : null,
                $this->isAdmin() ? sprintf("%02.2f", $values['MerchVozn'] / 100.0) : null,
                $this->isAdmin() ? sprintf("%02.2f", $values['VoznagSumm'] / 100.0) : null,
                $values['CntPays']
            ];
        }
        return $data;
    }

    private function headXLSX(): array
    {
        return [
            '#',
            'Услуга',
            'К зачислению',
            'Комиссия',
            'К оплате',
            $this->isAdmin() ? 'Комис. банка' : null,
            $this->isAdmin() ? 'Возн. с мерч.' : null,
            $this->isAdmin() ? 'Возн. Vepay' : null,
            'Число операций',
        ];
    }

    private function sizesXLSX(): array
    {
        //7
        return [10, 50, 20, 15, 15, $this->isAdmin() ? 15 : null, $this->isAdmin() ? 15 : null, $this->isAdmin() ? 15 : null, 15];
    }

    private function itogsXLSX(): array
    {
        return [2 => 1, 3 => 1, 4 => 1, 5 => $this->isAdmin() ? 1 : null, 6 => $this->isAdmin() ? 1 : null, 7 => $this->isAdmin() ? 1 : null, 8 => 1];
    }

}