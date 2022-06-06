<?php

namespace app\models\planner;

use app\models\mfo\DistributionReports;
use app\models\partner\stat\BrsPaySchetStat;
use app\models\partner\stat\export\csv\BrsReportToCSV;
use app\models\planner\exceptions\BankAdapterBuildException;
use app\models\SendEmail;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use Yii;

class BrsReportToEmail extends OtchToEmail
{
    protected $dateFrom;
    protected $dateTo;
    protected $emailList;

    /**
     * @param DistributionReports $partners - партнеры которым можно сделать рассылку
     * @param string|null $dateFrom - "дата от" в формате d.m.Y H:i
     * @param string|null $dateTo - "дата до" в формате d.m.Y H:i
     */
    public function __construct(DistributionReports $partners, ?string $dateFrom = null, ?string $dateTo = null, ?string $emailList = null)
    {
        parent::__construct($partners);

        $this->dateFrom = date('d.m.Y H:i', strtotime($dateFrom ?? 'yesterday'));
        $this->dateTo = date('d.m.Y H:i', strtotime($dateTo ?? 'today'));
        $this->emailList = $emailList ?? '';
    }

    public function run()
    {
        $dateFrom = $this->dateFrom;
        $dateTo = $this->dateTo;
        echo "from ".$dateFrom." to ".$dateTo."\r\n";
        Yii::warning("from ".$dateFrom." to ".$dateTo, "rsbcron");

        $emptyDataCount = 0;

        foreach ($this->partners as $partner) {

            Yii::warning("BrsReportToEmail: send to " . $partner->partner_id, "rsbcron");
            echo "BrsReportToEmail: send to " . $partner->partner_id . "\r\n";

            $payschet = new BrsPaySchetStat([
                'IdPart' => $partner->partner_id,
                'datefrom' => $dateFrom,
                'dateto' => $dateTo,
                'status' => [PaySchet::STATUS_DONE],
                'TypeUslug' => [UslugatovarType::TOCARD],
            ]);
            if ($partner->payment || $partner->repayment) {
                $list = $payschet->getList(1, 0, 1);
            } else {
                $list = ['data' => []];
            }

            if (empty($list['data'])) {
                echo "Нет данных для отображения | ". "\r\n";
                $emptyDataCount++;
                continue;
            }

            $firstItem = reset($list['data']);
            $partnerBankGateAdvParam1 = $firstItem['GateAdvParam_1'];

            $otch = new BrsReportToCSV($list, $partner->payment, $partner->repayment);
            $otch->export();

            $sender = new SendEmail();
            $subject = "Отчет по реестру БРС за период " . $dateFrom . ' - ' . $dateTo;
            $fileName = 'PRT' . $partnerBankGateAdvParam1 . date('Ymd') . '1.csv';
            $emailBody = 'Отчет предоставлен в виде прикрепленного файла csv.';

            $res = $sender->sendReestr($partner->email, $subject, $emailBody, [[
                'data' => file_get_contents($otch->fullpath()),
                'name' => $fileName]]);

            Yii::warning("BrsReportToEmail: send to " . $partner->email . " result = " . $res, "rsbcron");
            echo "BrsReportToEmail: send to " . $partner->email . " result = " . $res . "\r\n" . ' | ';

            if(!empty($this->emailList)) {

                $res = $sender->sendReestr($this->emailList, $subject, 'Отчет предоставлен в виде прикрепленного файла csv.', [[
                    'data' => file_get_contents($otch->fullpath()),
                    'name' => $fileName
                ]]);

                Yii::warning("BrsReportToEmail: send to " . $this->emailList . " result = " . $res, "rsbcron");
            }

            if (file_exists($otch->fullpath())) {
                unlink($otch->fullpath());
            }
        }

        if ($emptyDataCount === count($this->partners)) {
            echo 'Отсутствуют записи для отправки | '. "\r\n";
        }
    }
}
