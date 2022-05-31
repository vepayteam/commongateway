<?php


namespace app\models\planner;


use app\models\mfo\DistributionReports;
use app\models\partner\stat\export\csv\OtchToCSV;
use app\models\partner\stat\PayShetStat;
use app\models\SendEmail;
use app\models\TU;
use DateTime;
use DateTimeZone;
use Yii;
use yii\helpers\VarDumper;

class OtchToEmail
{
    protected $now;
    protected $partners;
    private $dateFrom;
    private $dateTo;
    private $emailList;

    /**
     * @param DistributionReports $partners - партнеры которым можно сделать рассылку
     */
    public function __construct(DistributionReports $partners, ?string $dateFrom = null, ?string $dateTo = null, ?string $emailList = null)
    {
        $this->partners = $partners->validPartners();
        $this->dateFrom = $dateFrom ?? 'yesterday';
        $this->dateTo = $dateTo ?? 'today';
        $this->emailList = $emailList ?? '';
    }

    public function run()
    {
        $dateFrom = date('d.m.Y H:i', strtotime($this->dateFrom));
        $dateTo = date('d.m.Y H:i', strtotime($this->dateTo));
        echo "from ".$dateFrom." to ".$dateTo."\r\n";
        Yii::warning("from ".$dateFrom." to ".$dateTo, "rsbcron");

        $i = 0;
        foreach ($this->partners as $partner) {

            Yii::warning("OtchToEmail: send to " . $partner->partner_id, "rsbcron");
            echo "OtchToEmail: send to " . $partner->partner_id . "\r\n";

            $i++;
            $payschet = new PayShetStat([
                'IdPart' => $partner->partner_id,
                'datefrom' => $dateFrom,
                'dateto' => $dateTo,
            ]);
            if ($partner->payment || $partner->repayment) {
                $list = $payschet->getList(true, 0, null);
            } else {
                $list = ['data' => []];
            }
            if ($list['data']) {
                $otch = new OtchToCSV($list, $partner->payment, $partner->repayment);
                $otch->export();
                $sender = new SendEmail();//['mailer' => $this->mailer(), 'fromEmail'=>'payments@vepay.online']
                $subject = "Отчет за период " . $dateFrom . ' - ' . $dateTo;
                $res = $sender->sendReestr($partner->email, $subject, 'Отчет предоставлен в виде прикрепленного файла csv.', [[
                        'data' => file_get_contents($otch->fullpath()),
                        'name' => time().$i. '.csv'
                ]]);
                Yii::warning("OtchToEmail: send to " . $partner->email . " result = " . $res, "rsbcron");
                echo "OtchToEmail: send to " . $partner->email . " result = " . $res . "\r\n";

                if(!empty($this->emailList)) {

                    $res = $sender->sendReestr($this->emailList, $subject, 'Отчет предоставлен в виде прикрепленного файла csv.', [[
                        'data' => file_get_contents($otch->fullpath()),
                        'name' => time().$i. '.csv'
                    ]]);

                    Yii::warning("OtchToEmail: send to " . $this->emailList . " result = " . $res, "rsbcron");
                    echo "OtchToEmail: send to " . $this->emailList . " result = " . $res . "\r\n";
                }

                if (file_exists($otch->fullpath())){
                    unlink($otch->fullpath());
                }
            }
        }
    }

    protected function mailer()
    {
        return Yii::createObject([
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'viewPath' => '@app/mail/', // Путь до папки с шаблоном
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'mail.dengisrazy.ru',//'localhost',
                'username' => 'payments@vepay.online',
                'password' => 'wH3zfpspfCvn',
                'port' => '25',
                'encryption' => 'tls',
            ],
        ]);
    }

}
