<?php

namespace app\models\kkt;

use Yii;
use qfsx\yii2\curl\Curl;
use app\models\Payschets;

class OnlineKassa
{
    private $draftData = null;

    /**
     * Пробить чек
     * @param int $IdPayschet
     * @param string $tovar
     * @param string $tovarOFD
     * @param int $summ
     * @param string $email
     * @return array|null
     */
    public function createDraft($IdPayschet, $tovar, $tovarOFD, $summ, $email)
    {
        $ans = $this->curlReq('sum=' . intval($summ) .
            '&org=2&section=3&Tovar=' . urlencode($tovarOFD) .
            '&email=' . $email . '&token=');

        if (isset($ans['xml']) && !empty($ans['xml'])) {
            $xmlDraft = simplexml_load_string($ans['xml']);
            /*
                NumDocument;
                NumDraft;
                Smena;
                DateDraft;
                KPKNumber;  //номер фискального документа
                KPKCode;    //фискальный признак

                KassaLogicalNumber; //номер в налоговой
                KassaSerialNumber;
                EKLZSerialNumber; //ФН номер
             */

            $this->draftData = [
                'IdPaySchet' => $IdPayschet,
                'Urlico' => Yii::$app->params['kkt']['urlico'],
                'Inn' => Yii::$app->params['kkt']['inn'],
                'Sno' => Yii::$app->params['kkt']['sno'],
                'NumDocument' => $xmlDraft->NumDocument,
                'NumDraft' => $xmlDraft->NumDraft,
                'Smena' => $xmlDraft->Smena,
                'DateDraft' => $xmlDraft->DateDraft,
                'FDNumber' => $xmlDraft->KPKNumber,
                'FPCode' => $xmlDraft->KPKCode,
                'KassaRegNumber' => $xmlDraft->KassaLogicalNumber,
                'KassaSerialNumber' => $xmlDraft->KassaSerialNumber,
                'FNSerialNumber' => $xmlDraft->EKLZSerialNumber,
                'Tovar' => $tovar,
                'Summ' => $summ,
                'SummNoNds' => $summ,
                'Email' => $email
            ];

            $this->saveToDB();

            return $this->draftData;
        }

        return null;
    }

    /**
     * GET запрос
     * @param string $get
     * @return array xml|error
     */
    private function curlReq($get)
    {
        $url = Yii::$app->params['kkt']['host'] . "?" . $get .
            "&token=" . Yii::$app->params['kkt']['token'];

        Yii::warning("req: " . $url . "\r\n", 'qroplata');

        $curl = new Curl();
        $curl->reset()->get($url);

        $ans = [];
        Yii::warning("curlcode: " . $curl->errorCode, 'qroplata');
        Yii::warning("curlans: " . $curl->responseCode . ":" . $curl->response, 'qroplata');
        switch ($curl->responseCode) {
            case 200:
            case 202:
                $ans['xml'] = $curl->response;
                break;
            default:
                $ans['error'] = $curl->errorCode . ": " . $curl->responseCode;
                break;
        }

        return $ans;
    }

    private function saveToDB()
    {
        Yii::$app->db->createCommand()->insert('drafts', $this->draftData)->execute();
    }

    /**
     * Текст чека по номеру счета
     * @param int $IdPayschet
     * @param bool $need
     * @return false|string
     */
    public function printFromDB($IdPayschet, $need = false)
    {
        if ($IdPayschet) {
            $this->draftData = Yii::$app->db->createCommand(
                'SELECT * FROM `drafts` WHERE `IdPaySchet` = :IDPAYSCHT', [
                ':IDPAYSCHT' => $IdPayschet,
            ])->queryOne();

            if (!$this->draftData && $need) {

                $ps = new Payschets();
                $query = $ps->getPayInfoFoDraft($IdPayschet);

                $this->draftData = $this->createDraft($IdPayschet, $query['tovar'], $query['tovarOFD'], $query['summ'],
                    isset($query['Email']) ? $query['Email'] : '');
            }

            if (is_array($this->draftData)) {
                return $this->getDraftText();
            }
        }

        return false;

    }

    /**
     * Чек по формату
     * @return string
     */
    public function getDraftText()
    {
        $draft = '';
        if ($this->draftData) {
            $draftData = $this->draftData;
            $qrcode = "https://api.vepay.online/qr?data=".urlencode("t=".
                date("Ymd\\THi", strtotime($draftData['DateDraft'])) . "00&s=" .
                sprintf('%02.2f', $draftData['Summ']/100.0) . "&fn=" .
                $draftData['FNSerialNumber'] . "&i=" . $draftData['FDNumber'] . "&fp=" . $draftData['FPCode']."&n=1");
            $curl = new Curl();
            $qrcodeBase64 = $curl
                ->setOption(CURLOPT_SSL_VERIFYHOST, 0)
                ->setOption(CURLOPT_SSL_CIPHER_LIST, 'TLSv1')
                ->setOption(CURLOPT_SSL_VERIFYPEER, false)
                ->get($qrcode);
            if (!empty($qrcodeBase64)) {
                $qrcode = "data:image/png;base64," . base64_encode($qrcodeBase64);
            }
            $draft = Yii::$app->view->render('@app/views/communal/textdraft.php', [
                'draftData' => $this->draftData,
                'qrcode' => $qrcode
            ]);
        }
        return $draft;
    }
}