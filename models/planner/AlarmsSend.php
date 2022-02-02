<?php


namespace app\models\planner;


use app\models\extservice\HttpProxy;
use app\models\partner\admin\AlarmsSettings;
use app\models\SendEmail;
use qfsx\yii2\curl\Curl;
use Yii;
use yii\db\Exception;

class AlarmsSend
{
    use HttpProxy;

    public function execute()
    {
        try {
            //тип 0
            $this->runAlertCheckNewPay();
        } catch (\Throwable $e) {
            Yii::warning('runAlertCheckNewPay: error=' . $e->getMessage(), 'rsbcron');
        }
        try {
            //тип 2
            $this->runAlertCheckLastUpdateStatePay();
        } catch (\Throwable $e) {
            Yii::warning('runAlertCheckLastUpdateStatePay: error=' . $e->getMessage(), 'rsbcron');
        }
        try {
            //тип 1
            $this->runCheckSmsGate();
        } catch (\Throwable $e) {
            Yii::warning('runCheckSmsGate: error=' . $e->getMessage(), 'rsbcron');
        }
    }

    /**
     * Отсутствие изменений статуса операции и/или отклика на стороне эквайера в интервале между запросом обработки и моментом проверки в течение ___ минут
     * @throws \yii\db\Exception
     */
    private function runAlertCheckNewPay()
    {
        $type = 0;
        $config = AlarmsSettings::findOne(['TypeAlarm' => $type]);
        if ($config && $config->TimeAlarm > 10 && !empty($config->EmailAlarm)) {
            $res = Yii::$app->db->createCommand('
                SELECT 
                    ps.ID,
                    ps.DateCreate,
                    p.Name                
                FROM 
                    `pay_schet` AS ps
                    LEFT JOIN `alarms_send` AS a ON a.IdPay = ps.ID AND a.IdSetting = :IDSETTINGS
                    LEFT JOIN `uslugatovar` AS u ON u.ID = ps.IdUsluga
                    LEFT JOIN `partner` AS p ON p.ID = u.IDPartner
                WHERE 
                    ps.`Status` = 0
                    AND ps.sms_accept = 1 
                    AND a.ID IS NULL
            ', [':IDSETTINGS' => $config->ID])->query();

            //Yii::warning('runAlertCheckNewPay: count=' . $res->count(), 'rsbcron');

            $errPays = [];
            while ($row = $res->read()) {
                if ($row['DateCreate'] < time() - $config->TimeAlarm * 60) {

                    Yii::$app->db->createCommand()->insert('alarms_send', [
                        'IdSetting' => $config->ID,
                        'IdPay' => $row['ID'],
                        'TypeSend' => 0,
                        'DateSend' => time()
                    ])->execute();

                    $errPays[] = ['ID' => $row['ID'], 'DateCreate' => $row['DateCreate'], 'Name' => $row['Name']];
                }
            }

            //Yii::warning('runAlertCheckNewPay: errPays=' . count($errPays), 'rsbcron');

            if (count($errPays)) {
                $this->SendMail($type, $errPays, $config);
            }

        }
    }

    /**
     * Отсутствие изменений статуса операции в течение ___ минут.
     * @throws \yii\db\Exception
     */
    private function runAlertCheckLastUpdateStatePay()
    {
        $type = 2;
        $config = AlarmsSettings::findOne(['TypeAlarm' => $type]);
        if ($config && $config->TimeAlarm > 10 && !empty($config->EmailAlarm)) {
            $res = Yii::$app->db->createCommand('
                SELECT 
                    ps.ID,
                    ps.DateCreate,
                    p.Name
                FROM 
                    `pay_schet` AS ps 
                    LEFT JOIN `uslugatovar` AS u ON u.ID = ps.IdUsluga
                    LEFT JOIN `partner` AS p ON p.ID = u.IDPartner
                WHERE 
                    ps.`Status` = 0
                    AND ps.sms_accept = 1
                    AND ps.DateCreate < UNIX_TIMESTAMP() - :TIMEOUT * 60
                    AND (
                        SELECT `ID` 
                        FROM `alarms_send` AS a 
                        WHERE a.IdPay = ps.ID AND a.IdSetting = :IDSETTINGS AND a.DateSend > UNIX_TIMESTAMP() - :TIMEOUT * 60
                        LIMIT 1
                    ) IS NULL
            ', [':IDSETTINGS' => $config->ID, ':TIMEOUT' => $config->TimeAlarm])->query();

            //Yii::warning('runAlertCheckLastUpdateStatePay: count=' . $res->count(), 'rsbcron');

            $errPays = [];
            while ($row = $res->read()) {
                if ($row['DateCreate'] < time() - $config->TimeAlarm * 60) {

                    Yii::$app->db->createCommand()->insert('alarms_send', [
                        'IdSetting' => $config->ID,
                        'IdPay' => $row['ID'],
                        'TypeSend' => 0,
                        'DateSend' => time()
                    ])->execute();

                    $errPays[] = ['ID' => $row['ID'], 'DateCreate' => $row['DateCreate'], 'Name' => $row['Name']];
                }
            }

            //Yii::warning('runAlertCheckLastUpdateStatePay: errPays=' . count($errPays), 'rsbcron');

            if (count($errPays)) {
                $this->SendMail($type, $errPays, $config);
            }
        }
    }

    /**
     * Отсутствие отклика со стороны SMS шлюза в течение ___ минут.
     * @throws \yii\db\Exception
     */
    private function runCheckSmsGate()
    {
        $type = 1;
        $config = AlarmsSettings::findOne(['TypeAlarm' => $type]);

        if ($config && $config->TimeAlarm > 0 && !empty($config->EmailAlarm)) {

            $row = Yii::$app->db->createCommand('
                SELECT 
                    a.`DateSend`
                FROM
                    `alarms_send` AS a 
                WHERE 
                    a.`IdSetting` = :IDSETTINGS
                ORDER BY a.`DateSend` DESC
                LIMIT 1
            ', [':IDSETTINGS' => $config->ID])->queryOne();

            if (!$row || ($row && $row['DateSend'] < time() - $config->TimeAlarm * 60)) {
                $check = $this->sendCheckMainSms();

                //Yii::warning('runCheckSmsGate: ' . $check, 'rsbcron');

                if (!$check) {
                    //Error
                    if ($this->SendMail($type, ['ID' => 0, 'DateCreate' => $row ? $row['DateSend'] : time()], $config)) {

                        Yii::$app->db->createCommand()->insert('alarms_send', [
                            'IdSetting' => $config->ID,
                            'IdPay' => 0,
                            'TypeSend' => 0,
                            'DateSend' => time()
                        ])->execute();

                    }
                } else {
                    //проверка смс успешна
                    Yii::$app->db->createCommand()->insert('alarms_send', [
                        'IdSetting' => $config->ID,
                        'IdPay' => 0,
                        'TypeSend' => 1,
                        'DateSend' => time()
                    ])->execute();

                }
            }

        }
    }

    private function sendCheckMainSms()
    {
        $ret = 0;
        $curl = new Curl();
        $curl->setOptions([
            CURLOPT_VERBOSE => Yii::$app->params['VERBOSE'] === 'Y',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_CIPHER_LIST => 'TLSv1'
        ]);
        if (Yii::$app->params['DEVMODE'] != 'Y' && Yii::$app->params['TESTMODE'] != 'Y' && !empty($this->proxyHost)) {
            $curl->setOption(CURLOPT_PROXY, $this->proxyHost);
            $curl->setOption(CURLOPT_PROXYUSERPWD, $this->proxyUser);
        }
        try {
            $curl->head('https://mainsms.ru/');
            if ($curl->responseCode == 200) {
                $ret = 1;
            }
            if ($curl->errorCode) {
                Yii::warning('CheckMainSms error: ' . $curl->errorCode . ":" . $curl->errorText, 'rsbcron');
            }
        } catch (\Exception $e) {
            $this->response = '';
            Yii::warning('CheckMainSms Exception:' . $e->getMessage(), 'rsbcron');
        }
        return $ret;
    }

    private function SendMail($Type, $errPays, AlarmsSettings $config)
    {
        $mail = new SendEmail();
        if ($Type == 0) {
            $content = 'Отсутствие изменений статуса операции и/или отклика на стороне эквайера в интервале между запросом ' .
                'обработки и моментом проверки в течение ' . $config->TimeAlarm . ' минут<br>'."\r\n";
            foreach ($errPays as $row) {
                $content .= 'ID Vepay: ' . $row['ID'] . ' от ' . date('d.m.Y H:i:s', $row['DateCreate'])." по проекту ".$row['Name']."<br>\r\n";
            }
            return $mail->sendReestr($config->EmailAlarm, 'Отсутствие изменений статуса', $content);
        }

        if ($Type == 1) {
            $content = 'Отсутствие отклика со стороны SMS шлюза в течение ' . $config->TimeAlarm . ' минут<br>\r\n';
            foreach ($errPays as $row) {
                $content .= 'Проверка от ' . date('d.m.Y H:i:s', $row['DateCreate'])."<br>\r\n";
            }
            return $mail->sendReestr($config->EmailAlarm, 'Отсутствие отклика SMS шлюза', $content);
        }

        if ($Type == 2) {
            $content = 'Отсутствие изменений статуса операции в течение ' . $config->TimeAlarm . ' минут<br>'."\r\n";
            foreach ($errPays as $row) {
                $content .= 'ID Vepay: ' . $row['ID'] . ' от ' . date('d.m.Y H:i:s', $row['DateCreate'])." по проекту ".$row['Name']."<br>\r\n";
            }
            return $mail->sendReestr($config->EmailAlarm, 'Отсутствие изменений статуса', $content);
        }

        return 1;
    }

}