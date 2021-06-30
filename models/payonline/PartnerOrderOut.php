<?php

namespace app\models\payonline;

use yii\db\ActiveRecord;

/**
 * Class PartnerOrderOut
 *
 * @property int ID
 * @property int IdPartner
 * @property string Comment
 * @property int Summ
 * @property int DateOp
 * @property int TypeOrder
 * @property int SummAfter
 * @property int IdPay
 * @property int IdStatm
 */
class PartnerOrderOut extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return 'partner_orderout';
    }
}
