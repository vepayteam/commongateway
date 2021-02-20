<?php

namespace app\services\cards\models;

use Yii;

/**
 * This is the model class for table "pan_token".
 *
 * @property int $ID
 * @property string|null $FirstSixDigits
 * @property string|null $LastFourDigits
 * @property string|null $EncryptedPAN
 * @property string|null $ExpDateMonth
 * @property string|null $ExpDateYear
 * @property int $CreatedDate
 * @property int $UpdatedDate
 * @property int $CryptoKeyId
 * @property string|null $CardHolder
 */
class PanToken extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pan_token';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['CreatedDate', 'UpdatedDate', 'CryptoKeyId'], 'required'],
            [['CreatedDate', 'UpdatedDate', 'CryptoKeyId'], 'integer'],
            [['FirstSixDigits', 'LastFourDigits', 'ExpDateMonth', 'ExpDateYear'], 'string', 'max' => 10],
            [['EncryptedPAN'], 'string', 'max' => 250],
            [['CardHolder'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'FirstSixDigits' => 'First Six Digits',
            'LastFourDigits' => 'Last Four Digits',
            'EncryptedPAN' => 'Encrypted Pan',
            'ExpDateMonth' => 'Exp Date Month',
            'ExpDateYear' => 'Exp Date Year',
            'CreatedDate' => 'Created Date',
            'UpdatedDate' => 'Updated Date',
            'CryptoKeyId' => 'Crypto Key ID',
            'CardHolder' => 'Card Holder',
        ];
    }
}
