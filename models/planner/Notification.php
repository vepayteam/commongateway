<?php

namespace app\models\planner;

use app\models\bank\TCBank;
use app\models\SendHttp;
use app\models\TU;
use Yii;
use app\models\kkt\OnlineKassa;
use app\models\SendEmail;
use yii\helpers\VarDumper;
use yii\mutex\FileMutex;

/**
 * Class Notification
 *
 * @param bool   $withdraw
 * @param array[int] $paysId - array [1, 2, 3, 4]
 * @param TCBank $bank
 *
 * @package app\models\planner
 */
class Notification
{

    private $httpCode;
    private $httpAns;
    private $fullReq;
    private $withdraw;
    private $paysId = [];

    /**
     * Оповещение об оплате
     * @throws \yii\db\Exception
     */
    public function execute()
    {
        $mutex = new FileMutex();
        if ($mutex->acquire('Notification', 5)) {
            $this->reqSend();
            $mutex->release('Notification');
        } else {
            echo "Run Notification locked!\n";
            Yii::warning("Run Notification locked!", 'rsbcron');
        }
    }

    protected function reqSend()
    {

        $connection = Yii::$app->db;

        $query = $connection->createCommand('
            SELECT
		        p.ID,
                n.ID AS IdNotif,
                n.Email,
                n.TypeNotif,
                p.SummPay,
                p.QrParams,
                p.Status,
                us.EmailShablon,
                us.IsCustom,
                us.UrlInform,
                us.KeyInform,
                p.IdUsluga,
                p.UserUrlInform,
                p.UserKeyInform,
                p.Extid,
                n.SendCount,
                p.DateOplat
            FROM
                `notification_pay` AS n
                LEFT JOIN `pay_schet` AS p ON (p.ID = n.IdPay)
                LEFT JOIN uslugatovar AS us ON (us.ID = p.IdUsluga)
            WHERE
                n.DateSend = 0
        ')->query();
        while ($value = $query->read()) {

            echo "Run Notification ID=" . $value['IdNotif'] . " (" . $value['TypeNotif'] . ") count=" . $value['SendCount'] . "\n";
            Yii::warning("Run Notification ID=" . $value['IdNotif'] . " (" . $value['TypeNotif'] . ") count=" . $value['SendCount'], 'rsbcron');

            $this->fullReq = '';
            $this->httpCode = 0;
            $this->httpAns = '';

            try {
                switch ($value['TypeNotif']) {
                    default:
                    case 0:
                        $res = $this->sendToUser($value);
                        break;
                    case 1:
                        $res = $this->sendToShop($value);
                        break;
                    case 2:
                        $res = $this->sendReversHttp($value);
                        break;
                    case 3:
                        $res = $this->sendUserReversHttp($value);
                        break;
                }
                $connection->createCommand()
                    ->update('notification_pay', [
                        'SendCount' => $value['SendCount'] + 1,
                        'DateLastReq' => time(),
                        'HttpCode' => $this->httpCode,
                        'HttpAns' => $this->httpAns,
                        'FullReq' => $this->fullReq
                    ], '`ID` = :ID', [':ID' => $value['IdNotif']])
                    ->execute();
                if ($res || $value['SendCount'] > 10) {
                    //завершить обработку
                    if ($value['TypeNotif'] == 2 && $this->httpCode == 200 && $value['DateOplat'] > strtotime('today')) {
                        $this->addToRefundArray($this->httpAns, $value);
                    }
                    $connection->createCommand()
                        ->update('notification_pay', [
                            'DateSend' => time()
                        ], '`ID` = :ID', [':ID' => $value['IdNotif']])
                        ->execute();
                }
            } catch (\Exception $e) {
                Yii::error("Error Notification ID=" . $value['IdNotif'] . ": " . $e->getMessage(), 'rsbcron');
            }
        }

        echo "Send Notification end\n";
    }

    /**
     * Для плательщика письмо
     *
     * @param array $value [ID, Email]
     *
     * @return bool
     */
    private function sendToUser($value)
    {
        $IdSchet = $value['ID'];
        $Email = $value['Email'];
        $uslinfo = isset($value['EmailShablon']) ? $value['EmailShablon'] : '';

        $kkt = new OnlineKassa();
        $draft = $kkt->printFromDB($IdSchet, true);

        $content = Yii::$app->view->renderFile("@app/mail/notificate_template.php", [
            'IdSchet' => $IdSchet,
            'draft' => $draft,
            'uslinfo' => $uslinfo
        ]);

        $subject = 'Подтверждение оплаты заказа';
        $sendemail = new SendEmail();
        $res = $sendemail->send($Email, '', $subject, $content);

        Yii::warning("sendToUser: " . $Email . " - " . $IdSchet . "\r\n", 'rsbcron');

        return $res;
    }

    /**
     * Для магазина письмо
     *
     * @param array $value [ID, Email]
     *
     * @return bool
     */
    private function sendToShop($value)
    {
        if ($value['IsCustom'] > 0) {
            $IdSchet = $value['ID'];
            $Email = $value['Email'];

            if (!empty($Email)) {
                //оповещение по почте
                //Json::decode($row['CustomData']);
                $params = explode("|", $value['QrParams']);

                $content = Yii::$app->view->renderFile("@app/mail/shop_template.php", [
                    "IdSchet" => $IdSchet,
                    "params" => $params,
                    "sum" => $value['SummPay'],
                ]);

                $subject = 'Поступила оплата заказа';
                $sendemail = new SendEmail();
                $res = $sendemail->send($Email, '', $subject, $content);

                Yii::warning("sendToShop: " . $Email . " - " . $IdSchet . "\r\n", 'rsbcron');
                return $res;
            }
        }
        return true;
    }

    /**
     * Для магазина обратный http запрос
     *
     * @param array $value [ID, UrlInform, Status]
     *
     * @return bool
     * @throws \Exception
     */
    private function sendReversHttp($value)
    {
        if ($value['IsCustom'] > 0) {
            $IdSchet = $value['ID'];
            $Extid = $value['Extid'];
            $UrlInform = $value['UrlInform'];
            $Status = $value['Status'];
            $SummPay = sprintf("%02.2f", $value['SummPay'] / 100.0);
            $key = $value['KeyInform'];

            if (!empty($UrlInform)) {
                //оповещение по http
                $http = new SendHttp();
                $res = $http->sendReq($UrlInform, $SummPay, $Extid, $IdSchet, $Status, $key);

                $this->fullReq = $http->fullReq;
                $this->httpCode = $http->resultCode;
                $this->httpAns = $http->resultText;

                Yii::warning("sendReversHttp: " . $value['UrlInform'] . " - " . $IdSchet . "\r\n", 'rsbcron');
                return $res;
            }
        }
        return true;
    }

    /**
     * Для плательщика обратный http запрос
     *
     * @param array $value [ID, UrlInform, Status]
     *
     * @return bool
     * @throws \Exception
     */
    private function sendUserReversHttp($value)
    {
        $IdSchet = $value['ID'];
        $UrlInform = $value['UserUrlInform'];
        $Status = $value['Status'];
        $Prov = $value['IdUsluga'];
        $ls = $value['QrParams'];
        $SummPay = $value['SummPay'];
        $extid = $value['Extid'];
        $key = $value['UserKeyInform'];

        if (!empty($UrlInform)) {
            //оповещение по http
            $http = new SendHttp();
            $res = $http->sendReqUser($UrlInform, $SummPay, $IdSchet, $Status, $Prov, $ls, $extid, $key);

            $this->fullReq = $http->fullReq;
            $this->httpCode = $http->resultCode;
            $this->httpAns = $http->resultText;

            Yii::warning("sendUserReversHttp: " . $value['UserUrlInform'] . " - " . $IdSchet . "\r\n", 'rsbcron');
            return $res;
        }
        return true;
    }

    /**
     * Возвращает массив id ордеров по которым необходимо сделать возврат средств.
     * @return array - [1, 2, 3, 4, 5, 6, ...]
     */
    public function needReversOrderIds(): array
    {
        return $this->paysId;
    }

    private function reversFormatValidated(array $resp): bool
    {
        if (isset($resp['status'])) {
            return true;
        }
        return false;
    }

    /**
     * @param string $resp - ответ от МФО.
     * @param array $dataFromTable - результат выполнения sql запроса.
     */
    private function addToRefundArray($resp, array $dataFromTable): void
    {
        $resp = json_decode($resp, true);
        if (JSON_ERROR_NONE == 0 && is_array($resp) && $this->reversFormatValidated($resp) && $resp['status'] != 0) { //проверяем на необходимый формат
            $isCustom = $dataFromTable['IsCustom'];
            if (TU::IsInMfo($isCustom)) {
                $this->paysId[] = $dataFromTable['ID']; //id платежного поручения.
            }
        }
    }

    public function test(){
        $resp = "{\"status\":110,\"message\":\"Неверный формат номера договора\"}";
        $dataFormTable = [
            'ID'=>'9944',
            'IdNotif'=> '208',
            'Email'=>'http://192.168.0.22',
            'TypeNotif'=>'2',
            'SummPay'=>'50000',
            'QrParams'=>'116',
            'Status'=>'0',
            'EmailShablon'=> null,
            'IsCustom'=>14,
            'UrlInform'=>'http://192.168.0.22',
            'KeyInform'=>'test',
            'IdUsluga'=>185,
            'UserUrlInform'=>null,
            'UserKeyInform'=>null,
            'Extid'=>'',
            'SendCount'=>6
        ];
//        $this->addToRefundArray($resp, $dataFormTable);
    }
}