<?php

namespace app\models\site;

use Yii;

/**
 * This is the model class for table "partner_reg".
 *
 * @property int $ID
 * @property int $UrState urid.status - 0 - ul 1 - ip 2 - fl
 * @property string|null $Email email dlia activacii
 * @property string|null $EmailCode kod dlia activacii email
 * @property int $DateReg data registracii
 * @property int $State status - 0 - novyii 1 - zaregistrirovan
 * @property int $IdPay id pay_schet - proverochnaya registracia karty fl
 */
class PartnerReg extends \yii\db\ActiveRecord
{

    /**
     * Статус: новый.
     */
    const STATE_NEW = 0;

    /**
     * Статус: зарегистрирован.
     */
    const STATE_REGISTERED = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'partner_reg';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['UrState', 'DateReg', 'State', 'IdPay'], 'required'],
            [['UrState', 'DateReg', 'State', 'IdPay'], 'integer'],
            [['Email'], 'email'],
            [['Email', 'EmailCode'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'UrState' => 'urid.status - 0 - ul 1 - ip 2 - fl',
            'Email' => 'email dlia activacii',
            'EmailCode' => 'kod dlia activacii email',
            'DateReg' => 'data registracii',
            'State' => 'status - 0 - novyii 1 - zaregistrirovan',
            'IdPay' => 'id pay_schet - proverochnaya registracia karty fl',
        ];
    }
}
