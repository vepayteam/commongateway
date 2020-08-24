<?php


namespace app\models\bank;


use app\models\TU;
use Yii;

class TcbGate implements IBankGate
{
    public $bank = 2;
    public $IdPartner = 0;
    public $typeGate;
    public $AutoPayIdGate = 0;

    /**
     * @param int $IdPartner Мерчант
     * @param int $typeGate Шлюз ТКБ
     * @param int|null $IsCustom Тип услуги (для выбора шлюза по типу услуги)
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
            TU::$TOCARD => TCBank::$OCTGATE,
            TU::$POGASHATF => TCBank::$AFTGATE,
            TU::$AVTOPLATATF => TCBank::$AFTGATE,
            TU::$TOSCHET => TCBank::$SCHETGATE,
            TU::$POGASHECOM => TCBank::$ECOMGATE,
            TU::$ECOM => TCBank::$ECOMGATE,
            TU::$VYPLATVOZN => TCBank::$VYVODGATE,
            TU::$VYVODPAYS => TCBank::$VYVODGATE,
            TU::$JKH => TCBank::$JKHGATE,
            TU::$REGCARD => TCBank::$AUTOPAYGATE,
            TU::$AVTOPLATECOM => TCBank::$AUTOPAYGATE,
            TU::$REVERSCOMIS => TCBank::$PEREVODGATE,
            TU::$PEREVPAYS => TCBank::$PEREVODGATE,
            TU::$REVERSCOMIS => TCBank::$PEREVODGATE,
            TU::$PEREVPAYS => TCBank::$PEREVODGATE,

            TU::$ECOMPARTS => TCBank::$PARTSGATE,
            TU::$JKHPARTS => TCBank::$PARTSGATE,
            TU::$POGASHATFPARTS => TCBank::$PARTSGATE,
            TU::$AVTOPLATATFPARTS => TCBank::$PARTSGATE,
            TU::$POGASHATFPARTS => TCBank::$PARTSGATE,
            TU::$AVTOPLATECOMPARTS => TCBank::$PARTSGATE,
        ];
    }

    /**
     * Шлюз ТКБ по услуге (!не все услуги к шлюзу 1 к 1)
     * @param $IsCustom
     * @return int
     */
    public function SetTypeGate($IsCustom)
    {
        $isCustomBankGates = TcbGate::GetIsCustomBankGates();
        if(array_key_exists($IsCustom, $isCustomBankGates)) {
            return $isCustomBankGates[$IsCustom];
        }

        return $this->typeGate;
    }

    /**
     * Шлюзы ТКБ для МФО
     * @return array|false|null
     * @throws \yii\db\Exception
     */
    public function GetGates()
    {
        $res = Yii::$app->db->createCommand('
            SELECT 
                `LoginTkbAft`, `KeyTkbAft`,
                `LoginTkbEcom`, `KeyTkbEcom`,                                  
                `LoginTkbJkh`, `KeyTkbJkh`,
                `LoginTkbOct`, `KeyTkbOct`,
                `LoginTkbAuto1`, `LoginTkbAuto2`, `LoginTkbAuto3`, `LoginTkbAuto4`, `LoginTkbAuto5`, `LoginTkbAuto6`, `LoginTkbAuto7`,
                `KeyTkbAuto1`, `KeyTkbAuto2`, `KeyTkbAuto3`, `KeyTkbAuto4`, `KeyTkbAuto5`, `KeyTkbAuto6`, `KeyTkbAuto7`, 
                `LoginTkbVyvod`, `KeyTkbVyvod`, `LoginTkbPerevod`, `KeyTkbPerevod`,
                `LoginTkbOctVyvod`, `KeyTkbOctVyvod`, `LoginTkbOctPerevod`, `KeyTkbOctPerevod`,
                `LoginTkbParts`
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
     * Проверка настройки шлюза ТКБ для МФО
     * @param $gate
     * @return bool
     * @throws \yii\db\Exception
     */
    public function IsGate()
    {
        $gates = $this->GetGates();

        // TODO: переделать под универсальный для банков
        if (in_array($this->typeGate, [TCBank::$OCTGATE, TCBank::$SCHETGATE]) && $gates && !empty($gates['LoginTkbOct'])) {
            return true;
        } elseif ($this->typeGate == TCBank::$AFTGATE && $gates && !empty($gates['LoginTkbAft'])) {
            return true;
        } elseif ($this->typeGate == TCBank::$ECOMGATE && $gates && !empty($gates['LoginTkbEcom'])) {
            return true;
        } elseif ($this->typeGate == TCBank::$JKHGATE && $gates && !empty($gates['LoginTkbJkh'])) {
            return true;
        } elseif ($this->typeGate == TCBank::$AUTOPAYGATE && $gates && !empty($gates['LoginTkbAuto1'])) {
            return true;
        } elseif ($this->typeGate == TCBank::$PEREVODGATE && $gates && !empty($gates['LoginTkbPerevod'])) {
            return true;
        } elseif ($this->typeGate == TCBank::$VYVODGATE && $gates && !empty($gates['LoginTkbVyvod'])) {
            return true;
        } elseif ($this->typeGate == TCBank::$PEREVODOCTGATE && $gates && !empty($gates['LoginTkbOctPerevod'])) {
            return true;
        } elseif ($this->typeGate == TCBank::$VYVODOCTGATE && $gates && !empty($gates['LoginTkbOctVyvod'])) {
            return true;
        } elseif ($this->typeGate == TCBank::$PARTSGATE && $gates && !empty($gates['LoginTkbParts'])) {
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
