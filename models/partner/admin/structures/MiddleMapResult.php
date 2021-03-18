<?php

namespace app\models\partner\admin\structures;

use app\models\payonline\Uslugatovar;
use app\services\payment\models\PaySchet;

class MiddleMapResult
{
    private $NamePartner   = '';
    private $IDPartner     = 0;
    private $SummVyveden   = null;
    private $SummPay       = 0;
    private $ComissSumm    = 0;
    private $MerchVozn     = 0;
    private $BankComis     = 0;
    private $CntPays       = 0;
    private $IdUsluga      = 0;
    private $IsCustom      = 0;
    private $ProvVoznagPC  = 0;
    private $ProvVoznagMin = 0;
    private $ProvComisPC   = 0;
    private $ProvComisMin  = 0;

    private $VoznagVyplatDirect = 0;
    private $VoznagSumm         = 0;
    private $DataVyveden        = 0;
    private $SummPerechisl      = null;
    private $DataPerechisl      = 0;

    private $UslugaTovarModel;

    /**
     * MiddleMapResult constructor.
     *
     * @param array $middleMapResult
     */
    public function __construct(array $middleMapResult)
    {
        foreach ($middleMapResult as $key => $value) {
            if ( property_exists($this, $key) ) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * @return string
     */
    public function getNamePartner(): string
    {
        return $this->NamePartner;
    }

    /**
     * @return int
     */
    public function getIDPartner(): int
    {
        return $this->IDPartner;
    }

    /**
     * @return int
     */
    public function getSummPay(): int
    {
        return $this->SummPay;
    }

    /**
     * @return int
     */
    public function getComissSumm(): int
    {
        return $this->ComissSumm;
    }

    /**
     * @return int
     */
    public function getMerchVozn(): int
    {
        return $this->MerchVozn;
    }

    /**
     * @return int
     */
    public function getBankComis(): int
    {
        return $this->BankComis;
    }

    /**
     * @return int
     */
    public function getCntPays(): int
    {
        return $this->CntPays;
    }

    /**
     * @return int
     */
    public function getIdUsluga(): int
    {
        return $this->IdUsluga;
    }

    /**
     * @return int
     */
    public function getIsCustom(): int
    {
        return $this->IsCustom;
    }

    /**
     * @return int
     */
    public function getProvVoznagPC(): int
    {
        return $this->ProvVoznagPC;
    }

    /**
     * @return int
     */
    public function getProvVoznagMin(): int
    {
        return $this->ProvVoznagMin;
    }

    /**
     * @return int
     */
    public function getProvComisPC(): int
    {
        return $this->ProvComisPC;
    }

    /**
     * @return int
     */
    public function getVoznagSumm(): int
    {
        return $this->VoznagSumm;
    }

    /**
     * @return int
     */
    public function getProvComisMin(): int
    {
        return $this->ProvComisMin;
    }

    /**
     * @return int
     */
    public function getVoznagVyplatDirect(): int
    {
        return $this->VoznagVyplatDirect;
    }

    /**
     * @return int
     */
    public function getSummVyveden(): int
    {
        return $this->SummVyveden;
    }

    /**
     * @return mixed
     */
    public function getUslugaTovarModel(): Uslugatovar
    {
        return $this->UslugaTovarModel;
    }

    /**
     * @param int|null $SummVyveden
     */
    public function setSummVyveden(?int $SummVyveden): void
    {
        $this->SummVyveden = $SummVyveden;
    }

    /**
     * @param int $VoznagSumm
     */
    public function setVoznagSumm(int $VoznagSumm): void
    {
        $this->VoznagSumm = $VoznagSumm;
    }

    /**
     * @param int $VoznagVyplatDirect
     */
    public function setVoznagVyplatDirect(int $VoznagVyplatDirect): void
    {
        $this->VoznagVyplatDirect = $VoznagVyplatDirect;
    }

    /**
     * @param int $DataVyveden
     */
    public function setDataVyveden(int $DataVyveden): void
    {
        $this->DataVyveden = $DataVyveden;
    }

    /**
     * @param int|null $SummPerechisl
     */
    public function setSummPerechisl(?int $SummPerechisl): void
    {
        $this->SummPerechisl = $SummPerechisl;
    }

    /**
     * @param int $DataPerechisl
     */
    public function setDataPerechisl(int $DataPerechisl): void
    {
        $this->DataPerechisl = $DataPerechisl;
    }

    /**
     * @param int $SummPay
     */
    public function setSummPay(int $SummPay): void
    {
        $this->SummPay = $SummPay;
    }

    /**
     * @param int $ComissSumm
     */
    public function setComissSumm(int $ComissSumm): void
    {
        $this->ComissSumm = $ComissSumm;
    }

    /**
     * @param int $MerchVozn
     */
    public function setMerchVozn(int $MerchVozn): void
    {
        $this->MerchVozn = $MerchVozn;
    }

    /**
     * @param int $BankComis
     */
    public function setBankComis(int $BankComis): void
    {
        $this->BankComis = $BankComis;
    }

    /**
     * @param int $CntPays
     */
    public function setCntPays(int $CntPays): void
    {
        $this->CntPays = $CntPays;
    }

    /**
     * @param Uslugatovar|null $UslugatovarModel
     */
    public function setUslugaTovarModel(?Uslugatovar $UslugatovarModel): void
    {
        $this->UslugaTovarModel = $UslugatovarModel;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
