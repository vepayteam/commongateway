<?php


namespace app\models\bank;


use app\models\TU;
use Yii;

class TcbGate
{
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
     * Шлюз ТКБ по услуге (!не все услуги к шлюзу 1 к 1)
     * @param $IsCustom
     * @return int
     */
    public function SetTypeGate($IsCustom)
    {
        if ($IsCustom == TU::$TOCARD) {
            //выдача на карту
            $this->typeGate = TCBank::$OCTGATE;
        } elseif (in_array($IsCustom, [TU::$POGASHATF, TU::$AVTOPLATATF])) {
            //aft
            $this->typeGate = TCBank::$AFTGATE;
        } elseif ($IsCustom == TU::$TOSCHET) {
            //выдача на счет
            $this->typeGate = TCBank::$SCHETGATE;
        } elseif (in_array($IsCustom, [TU::$POGASHECOM, TU::$ECOM])) {
            //ecom
            $this->typeGate = TCBank::$ECOMGATE;
        } elseif (in_array($IsCustom, [TU::$VYPLATVOZN, TU::$VYVODPAYS])) {
            //вывод платежей и вознаграждения
            $this->typeGate = TCBank::$VYVODGATE;
        } elseif ($IsCustom == TU::$JKH) {
            //жкх
            $this->typeGate = TCBank::$JKHGATE;
        } elseif (in_array($IsCustom, [TU::$REGCARD, TU::$AVTOPLATECOM])) {
            //автоплатеж
            $this->typeGate = TCBank::$AUTOPAYGATE;
        } elseif (in_array($IsCustom, [TU::$REVERSCOMIS, TU::$PEREVPAYS])) {
            //перевод с погашение на выдачу и возмещение комиссии банка
            $this->typeGate = TCBank::$PEREVODGATE;
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
                `LoginTkbOctVyvod`, `KeyTkbOctVyvod`, `LoginTkbOctPerevod`, `KeyTkbOctPerevod`
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
        }
        return false;
    }

}