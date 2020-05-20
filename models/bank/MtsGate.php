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
     * Шлюз по услуге
     * @param $IsCustom
     * @return int
     */
    public function SetTypeGate($IsCustom)
    {
        if ($IsCustom == TU::$TOCARD) {
            //выдача на карту
            $this->typeGate = MTSBank::$OCTGATE;
        } elseif (in_array($IsCustom, [TU::$POGASHATF, TU::$AVTOPLATATF])) {
            //aft
            $this->typeGate = MTSBank::$AFTGATE;
        } elseif ($IsCustom == TU::$TOSCHET) {
            //выдача на счет
            $this->typeGate = MTSBank::$SCHETGATE;
        } elseif (in_array($IsCustom, [TU::$POGASHECOM, TU::$ECOM])) {
            //ecom
            $this->typeGate = MTSBank::$ECOMGATE;
        } elseif (in_array($IsCustom, [TU::$VYPLATVOZN, TU::$VYVODPAYS])) {
            //вывод платежей и вознаграждения
            $this->typeGate = MTSBank::$VYVODGATE;
        } elseif ($IsCustom == TU::$JKH) {
            //жкх
            $this->typeGate = MTSBank::$JKHGATE;
        } elseif (in_array($IsCustom, [TU::$REGCARD, TU::$AVTOPLATECOM])) {
            //автоплатеж
            $this->typeGate = MTSBank::$AUTOPAYGATE;
        } elseif (in_array($IsCustom, [TU::$REVERSCOMIS, TU::$PEREVPAYS])) {
            //перевод с погашение на выдачу и возмещение комиссии банка
            $this->typeGate = MTSBank::$PEREVODGATE;
        }

        return $this->typeGate;
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
                `MtsToken`
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

        if (in_array($this->typeGate, [MTSBank::$OCTGATE, MTSBank::$SCHETGATE]) && $gates && !empty($gates['MtsLogin'])) {
            return true;
        } elseif ($this->typeGate == MTSBank::$AFTGATE && $gates && !empty($gates['MtsLogin'])) {
            return true;
        } elseif ($this->typeGate == MTSBank::$ECOMGATE && $gates && !empty($gates['MtsLogin'])) {
            return true;
        } elseif ($this->typeGate == MTSBank::$JKHGATE && $gates && !empty($gates['MtsLogin'])) {
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