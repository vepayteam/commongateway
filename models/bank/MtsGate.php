<?php


namespace app\models\bank;

use app\models\TU;
use Yii;

class MtsGate implements IBankGate
{
    public $bank = 3;
    public $IdPartner = 0;
    public $typeGate;
    public $AutoPayIdGate = 0;
    public $gates;

    /**
     * @param int $IdPartner Мерчант
     * @param int $typeGate Тип Шлюз
     * @param int|null $IsCustom Или тип услуги (для выбора шлюза по типу услуги)
     */
    public function __construct($IdPartner, $typeGate, $IsCustom = null)
    {
        $this->AutoPayIdGate = 1;
        $this->typeGate = $typeGate;
        if ($IsCustom) {
            $this->SetTypeGate($IsCustom);
        }
        $this->IdPartner = $IdPartner;
    }

    /**
     * Соотношение типа услуги и шлюза банка
     * @return array
     */
    public static function GetIsCustomBankGates()
    {
        return [
            TU::$TOCARD => MTSBank::$OCTGATE,
            TU::$POGASHATF => MTSBank::$AFTGATE,
            TU::$AVTOPLATATF => MTSBank::$AFTGATE,
            TU::$TOSCHET => MTSBank::$SCHETGATE,
            TU::$POGASHECOM => MTSBank::$ECOMGATE,
            TU::$ECOM => MTSBank::$ECOMGATE,
            TU::$VYPLATVOZN => MTSBank::$VYVODGATE,
            TU::$VYVODPAYS => MTSBank::$VYVODGATE,
            TU::$JKH => MTSBank::$JKHGATE,
            TU::$REGCARD => MTSBank::$AUTOPAYGATE,
            TU::$AVTOPLATECOM => MTSBank::$AUTOPAYGATE,
            TU::$REVERSCOMIS => MTSBank::$PEREVODGATE,
            TU::$PEREVPAYS => MTSBank::$PEREVODGATE,
            TU::$REVERSCOMIS => MTSBank::$PEREVODGATE,
            TU::$PEREVPAYS => MTSBank::$PEREVODGATE,

            TU::$ECOMPARTS => MTSBank::$PARTSGATE,
            TU::$JKHPARTS => MTSBank::$PARTSGATE,
            TU::$POGASHECOMPARTS => MTSBank::$PARTSGATE,
            TU::$AVTOPLATATFPARTS => MTSBank::$PARTSGATE,
            TU::$POGASHATFPARTS => MTSBank::$PARTSGATE,
            TU::$AVTOPLATECOMPARTS => MTSBank::$PARTSGATE,
        ];
    }

    /**
     * Шлюз по услуге
     * @param $IsCustom
     * @return int
     */
    public function SetTypeGate($IsCustom)
    {
        $isCustomBankGates = MtsGate::GetIsCustomBankGates();
        if(array_key_exists($IsCustom, $isCustomBankGates)) {
            $this->typeGate = $isCustomBankGates[$IsCustom];
        }
    }

    /**
     * Шлюзы мерчанта
     * @return array|false|null
     * @throws \yii\db\Exception
     */
    public function GetGates()
    {
        $res = Yii::$app->db->createCommand('
            SELECT 
                `MtsLogin`, 
                `MtsPassword`,
                `MtsToken`,
                `MtsLoginAft`, 
                `MtsPasswordAft`,
                `MtsTokenAft`,
                `MtsLoginOct`, 
                `MtsPasswordOct`,
                `MtsTokenOct`,
                `MtsLoginJkh`, 
                `MtsPasswordJkh`,
                `MtsTokenJkh`,
                
                `MtsLoginParts`, 
                `MtsPasswordParts`,
                `MtsTokenParts`
            FROM 
                `partner` 
            WHERE 
                `IsDeleted` = 0 AND `IsBlocked` = 0 AND `ID` = :IDMFO 
            LIMIT 1
        ', [':IDMFO' => $this->IdPartner]
        )->queryOne();
        if ($res) {
            if ($this->AutoPayIdGate) {
                $res['AutoPayIdGate'] = $this->AutoPayIdGate;
            }
            $this->gates = $res;
            return $res;
        } else {
            return null;
        }
    }

    /**
     * Проверка настройки шлюза для мерчанта
     * @param $gate
     * @return bool
     * @throws \yii\db\Exception
     */
    public function IsGate()
    {
        $gates = $this->GetGates();

        if (in_array($this->typeGate, [MTSBank::$OCTGATE, MTSBank::$SCHETGATE]) && $gates && !empty($gates['MtsLoginOct'])) {
            return true;
        } elseif ($this->typeGate == MTSBank::$AFTGATE && $gates && !empty($gates['MtsLoginAft'])) {
            return true;
        } elseif ($this->typeGate == MTSBank::$ECOMGATE && $gates && !empty($gates['MtsLogin'])) {
            return true;
        } elseif ($this->typeGate == MTSBank::$JKHGATE && $gates && !empty($gates['MtsLoginJkh'])) {
            return true;
        } elseif ($this->typeGate == MTSBank::$AUTOPAYGATE && $gates && !empty($gates['MtsLogin'])) {
            return true;
        } elseif ($this->typeGate == MTSBank::$PEREVODGATE && $gates && !empty($gates['MtsLogin'])) {
            return true;
        } elseif ($this->typeGate == MTSBank::$VYVODGATE && $gates && !empty($gates['MtsLogin'])) {
            return true;
        } elseif ($this->typeGate == MTSBank::$PEREVODOCTGATE && $gates && !empty($gates['MtsLogin'])) {
            return true;
        } elseif ($this->typeGate == MTSBank::$VYVODOCTGATE && $gates && !empty($gates['MtsLogin'])) {
            return true;
        }
        return false;
    }

    /**
     * @return int
     */
    public function getTypeGate()
    {
        return $this->typeGate;
    }

    /**
     * @return int
     */
    public function getBank()
    {
        return $this->bank;
    }
}