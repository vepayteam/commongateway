<?php

namespace app\models\payonline;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "partner_orderout".
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
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'partner_orderout';
    }
}
