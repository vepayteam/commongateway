<?php


namespace app\models\kkt;


class FiskalData
{
    public $id;
    public $deviceSN;
    public $deviceRN;
    public $fsNumber;
    public $ofdName;
    public $ofdWebsite;
    public $ofdinn;
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
        $this->ofdWebsite = $data['ofdWebsite'];
        $this->ofdinn = $data['ofdinn'];
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
            'Sno' => 'УСН доход - расход',
            'NumDocument' => $this->documentNumber,
            'NumDraft' => $this->documentIndex,
            'Smena' => $this->shiftNumber,
            'DateDraft' => date('d.m.Y H:i:s', strtotime($this->processedAt)),
            'FDNumber' => $this->documentNumber,
            'FPCode' => $this->fp,
            'KassaRegNumber' => $this->deviceRN,
            'KassaSerialNumber' => $this->deviceSN,
            'FNSerialNumber' => $this->fsNumber,
            'Tovar' => $this->content['positions'][0]['text'],
            'Summ' => $this->content['checkClose']['payments'][0]['amount'],
            'SummNoNds' => $this->content['checkClose']['payments'][0]['amount'],
            'Email' => $this->content['customerContact']
        ];
    }
}