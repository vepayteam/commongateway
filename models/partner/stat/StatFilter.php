<?php

namespace app\models\partner\stat;


use app\services\payment\models\Bank;
use app\models\payonline\Partner;
use app\models\payonline\PartnerDogovor;
use app\models\payonline\Uslugatovar;
use app\models\TU;
use app\services\payment\models\UslugatovarType;
use yii\helpers\StringHelper;

class StatFilter
{
    /**
     * @param int $IdPartner
     * @param array|int $IsCustom
     * @return Uslugatovar[]|array|\yii\db\ActiveRecord[]
     */
    public function getUslugList($IdPartner, $IsCustom)
    {
        $sqlfltr = '';
        $par = [];
        if ($IdPartner > 0) {
            $sqlfltr .= ' AND IDPartner = :IDPART';
            $par[":IDPART"] = $IdPartner;
        }
        if (is_array($IsCustom)) {
            $custom = [];
            foreach ($IsCustom as $c) {
                $custom[] = (int)$c;
            }
            $sqlfltr .= ' AND `IsCustom` IN ('.implode(',', $custom).')';

        } elseif ($IsCustom > 0) {
            $sqlfltr .= ' AND `IsCustom` = :CUSTOM';
            $par[":CUSTOM"] = $IsCustom;
        }
        $uslug = Uslugatovar::find()->where(
            '`IsDeleted` = 0' . $sqlfltr, $par);

        return $uslug->all();
    }

    /**
     * Список контрагентов
     * @param $onlymfo boolean
     * @param bool $notehpartner
     * @return Partner[]|array|\yii\db\ActiveRecord[]
     */
    public function getPartnersList($onlymfo = false, $notehpartner = false)
    {
        $partners = Partner::find()
            ->where(['IsDeleted' => '0'])
            ->andFilterWhere(['IsMfo' => $onlymfo ?: null]);
        if ($notehpartner) {
            $partners = $partners->andWhere('ID <> 1');
        }

        return $partners->all();
    }

    /**
     * @param int $IdPartner
     * @return Uslugatovar[]|array|\yii\db\ActiveRecord[]
     */
    public function getMagazList($IdPartner)
    {
        $sqlfltr = '';
        $par = [];
        if ($IdPartner > 0) {
            $sqlfltr .= ' AND IdPartner = :IDPART';
            $par[":IDPART"] = $IdPartner;
        }

        $uslug = PartnerDogovor::find()->where(
            '`IsDeleted` = 0' . $sqlfltr, $par);

        return $uslug->all();
    }

    public function getTypeUslugLiust($filter = -1)
    {
        $ret = [];
        foreach (UslugatovarType::typeList() as $k => $val) {
            $need = 1;
            if (is_array($filter) && !in_array($k, $filter)) {
                $need = 0;
            }
            if ($need) {
                $tp = new \stdClass();
                $tp->ID = $k;
                if (in_array($k, TU::NoPart())) {
                    $tp->IsMfo = 2;
                } elseif ($k == 2) {
                    $tp->IsMfo = '-1';
                } else {
                    $tp->IsMfo = $k > 9 ? '1' : '0';
                }
                $tp->Name = $val;
                $ret[] = $tp;
            }
        }
        return $ret;
    }

    /**
     * @return Bank[]
     */
    public function getBankList(): array
    {
        return Bank::find()->all();
    }

}