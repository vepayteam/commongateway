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
        $data = new DraftData();
        $data->customerContact = $email;
        $data->text = $tovar;
        $data->price = $summ;

        $OrangeData = new OrangeData();
        $ans = $OrangeData->CreateDraft($IdPayschet, $data);
        if ($ans['status'] == 1) {
            $ans = $OrangeData->StatusDraft($IdPayschet);
        }

        if (isset($ans['data']) && !empty($ans['data'])) {
            $FiskalData = new FiskalData();
            $FiskalData->Load($ans['data']);

            $this->draftData = $FiskalData->GetDraftData();

            $this->saveToDB();

            return $this->draftData;
        }

        return null;
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