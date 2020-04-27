<?php


namespace app\models\kkt;


class FiskalData
{
    public $id;
    public $deviceSN;
    public $deviceRN;
    public $fsNumber;
    public $ofdName;
    public $odfWebsite;
    public $odfINN;
    public $fnsWebsite;
    public $companyINN;
    public $companyName;
    public $documentNumber;
    public $shiftNumber;
    public $documentIndex;
    public $processedAt;
    public $content;
    public $change;
    public $fp;

    public function Load($data)
    {
        $this->id = $data['id'];
        $this->deviceSN = $data['deviceSN'];
        $this->deviceRN = $data['deviceRN'];
        $this->fsNumber = $data['fsNumber'];
        $this->ofdName = $data['ofdName'];
        $this->odfWebsite = $data['odfWebsite'];
        $this->odfINN = $data['odfINN'];
        $this->fnsWebsite = $data['fnsWebsite'];
        $this->companyINN = $data['companyINN'];
        $this->companyName = $data['companyName'];
        $this->documentNumber = $data['documentNumber'];
        $this->shiftNumber = $data['shiftNumber'];
        $this->documentIndex = $data['documentIndex'];
        $this->processedAt = $data['processedAt'];
        $this->content = $data['content'];
        $this->change = $data['change'];
        $this->fp = $data['fp'];
    }

    public function GetDraftData()
    {
        return [
            'IdPaySchet' => $this->id,
            'Urlico' => $this->companyName,
            'Inn' => $this->companyINN,
            'Sno' => '',
            'NumDocument' => $this->documentNumber,
            'NumDraft' => $this->documentIndex,
            'Smena' => $this->shiftNumber,
            'DateDraft' => $this->processedAt,
            'FDNumber' => $this->documentNumber,
            'FPCode' => $this->fp,
            'KassaRegNumber' => $this->deviceRN,
            'KassaSerialNumber' => $this->deviceSN,
            'FNSerialNumber' => $this->fsNumber,
            'Tovar' => $this->content['text'],
            'Summ' => $this->content['checkClose']['payments'][0]['amount'],
            'SummNoNds' => $this->content['checkClose']['payments'][0]['amount'],
            'Email' => $this->content['customerContact']
        ];
    }
}