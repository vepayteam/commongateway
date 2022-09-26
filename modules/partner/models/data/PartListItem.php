<?php

namespace app\modules\partner\models\data;

use app\models\PayschetPart;
use app\modules\partner\models\PartListFields;
use yii\base\Model;

class PartListItem extends Model
{
    use PartListFields;

    public function mapPayschetPart(PayschetPart $part): PartListItem
    {
        $this->id = $part->Id;
        $this->partnerName = $part->partner->Name;
        $this->partAmount = round($part->Amount / 100, 2);

        $paySchet = $part->paySchet;
        $this->paySchetId = $paySchet->ID;
        $this->createdAt = $paySchet->DateCreate;
        $this->extId = $paySchet->Extid;
        $this->paySchetAmount = round($paySchet->SummPay / 100, 2);
        $this->clientCompensation = round($paySchet->ComissSumm / 100, 2);
        $this->partnerCompensation = round($paySchet->MerchVozn / 100, 2);
        $this->bankCompensation = round($paySchet->BankComis / 100, 2);
        $this->message = $paySchet->ErrorInfo;
        $this->cardNumber = $paySchet->CardNum;
        $this->cardHolder = $paySchet->CardHolder;
        $this->contract = $paySchet->Dogovor;
        $this->fio = $paySchet->FIO;

        $this->withdrawalPayschetId = $part->vyvod->PayschetId ?? null;
        $this->withdrawalAmount = isset($part->vyvod->Amount) ? round($part->vyvod->Amount / 100, 2) : null;
        $this->withdrawalCreatedAt = $part->vyvod->DateCreate ?? null;

        return $this;
    }
}