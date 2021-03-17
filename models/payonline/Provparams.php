<?php

namespace app\models\payonline;

use app\services\payment\models\PaySchet;
use yii\base\Model;

/**
 * Атрибуты для оплаты услуги
 * Class Provparams
 * @package app\models\payonline
 */

class Provparams extends Model
{
    const P_LS = "LS";
    const P_PERIOD = "PERIOD";
    const P_FIO = "FIO";
    const P_ADDRESS = "ADDRESS";

    /* @var int $prov */
    public $prov;
    /* @var array $param */
    public $param;
    /* @var int $summ */
    public $summ;

    /* @var Uslugatovar $Usluga */
    public $Usluga;

    /* @var array $dynHelpParam */
    public $dynHelpParam;

    /* @var array $dynHelpParam */
    public $schNeedReq;

    /* @var string $info */
    public $info;

    /**
     * Атрибуты для load
     * @return array
     */
    public function scenarios()
    {
        return [
            Model::SCENARIO_DEFAULT => ['prov', 'param', 'summ']
        ];
    }

    /**
     * Проверка суммы
     * @param int $min
     * @param int $max
     * @return bool
     */
    public function validateSumm($min = 100, $max = 1500000)
    {
        if ($this->summ < $min || $this->summ > $max) {
            return false;
        }
        return true;
    }

    /**
     * Проверка реквизитов по маске
     * @param string $errVal
     * @return bool
     */
    public function validateByRegex(&$errVal)
    {
        $ret = true;
        $regexArr = explode("|||", $this->Usluga->Regex);
        $masks = explode("|", $this->Usluga->Mask);
        $labels = explode("|", $this->Usluga->Labels);
        foreach ($masks as $i => $mask) {
            if (!isset($this->param[$i])) {
                $ret = $this->validateByMaskOne('', $regexArr[$i]);
                //\Yii::warning($ret."==".$regexArr[$i]);
            } elseif (isset($this->param[$i]) && isset($regexArr[$i])) {
                $ret = $this->validateByMaskOne($this->param[$i], $regexArr[$i]);
                //\Yii::warning($ret."=".$this->param[$i]."=".$regexArr[$i]);
            } else {
                $ret = true;
            }
            if (!$ret) {
                if (isset($this->param[$i]) && !empty($this->param[$i])) {
                    $errVal = $this->param[$i];
                } else {
                    $errVal = $labels[$i];
                }
                break;
            }
        }

        return $ret;
    }

    /**
     * @param $param
     * @param $regex
     * @return bool
     */
    private function validateByMaskOne($param, $regex)
    {
        if (!empty($regex)) {
            return preg_match('/^' . $regex . '$/uis', $param);
        }
        return true;
    }

    /**
     * Комиссия (в коп)
     * @return int
     */
    public function calcComiss()
    {
        return PaySchet::calcClientFeeStatic($this->summ, $this->Usluga->PcComission, $this->Usluga->MinsumComiss);
    }

    public function getParamByType($type)
    {
        $arExpFormat = explode("|", $this->Usluga->ProfitExportFormat);
        $Param = '';
        if (is_array($arExpFormat)) {
            foreach ($this->param as $k => $ef) {
                if ($type == @$arExpFormat[$k]) {
                    $Param = $ef;
                    break;
                }
            }
        }
        return $Param;
    }

    /**
     * @param string $params
     * @param Uslugatovar $usl
     */
    public function SetParamFromStr($params, Uslugatovar $usl)
    {
        $masks = explode('|', $usl->Mask);
        $ls = explode('|', $params);
        foreach ($masks as $i => $mask) {
            $this->param[] = isset($ls[$i]) ? $ls[$i] : '';
        }
    }

}
