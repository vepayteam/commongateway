<?php

namespace app\models\kkt;

use app\models\TU;
use Da\QrCode\QrCode;
use Yii;
use app\models\Payschets;

class OnlineKassa
{
    private $draftData = null;
    private $kktConfig;

    public function __construct()
    {
        $this->kktConfig = $this->readConfig();
    }

    /**
     * Пробить чек
     * @param int $IdPayschet
     * @param DraftData $draftData
     * @param bool $checkExist
     * @return array|null
     */
    public function createDraft($IdPayschet, DraftData $draftData, $checkExist = false)
    {
        $isNew = 0;
        $OrangeData = new OrangeData($this->kktConfig);
        if ($checkExist) {
            $ans = $OrangeData->StatusDraft($IdPayschet);
        } else {
            $ans = ['status' => 0];
        }
        if ($ans['status'] == 0) {
            $ans = $OrangeData->CreateDraft($IdPayschet, $draftData);
            $isNew = 1;
            if ($ans['status'] == 1) {
                $ans = $OrangeData->StatusDraft($IdPayschet);
            }
        }

        if ($ans['status'] == 1 && isset($ans['data'])) {
            $FiskalData = new FiskalData();
            $FiskalData->Load($ans['data']);

            $this->draftData = $FiskalData->GetDraftData();
            if ($isNew && is_array($this->draftData)) {
                $this->saveToDB();
            }

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

                if (!empty($query['Email']) && $query['Status'] == 1 && TU::IsInAll($query['IsCustom'])) {
                    $data = new DraftData();
                    $data->customerContact = $query['Email'];
                    $data->text = $query['tovar'];
                    $data->price = $query['summ'];

                    $this->draftData = $this->createDraft($IdPayschet, $data,true);
                }
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
        $draft = $qrcode = '';
        if ($this->draftData) {
            $qrcodeBase64 = $this->createQrImg();
            if (!empty($qrcodeBase64)) {
                $qrcode = "data:image/png;base64," . base64_encode($qrcodeBase64);
            }
            $draft = Yii::$app->view->render('@app/views/pay/textdraft.php', [
                'draftData' => $this->draftData,
                'qrcode' => $qrcode
            ]);
        }
        return $draft;
    }

    private function readConfig()
    {
        return [];
    }

    private function createQrImg()
    {
        $draftData = $this->draftData;

        $qrcode = "t=".
            date("Ymd\\THi", strtotime($draftData['DateDraft'])) . "00&s=" .
            sprintf('%02.2f', $draftData['Summ']/100.0) . "&fn=" .
            $draftData['FNSerialNumber'] . "&i=" . $draftData['FDNumber'] . "&fp=" . $draftData['FPCode']."&n=1";

        $qrCode = (new QrCode($qrcode))
//            ->setSize(70)
            ->setMargin(3)
            ->useForegroundColor(0, 0, 0);

        return $qrCode->writeString();
    }
}