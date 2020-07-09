<?php

namespace app\modules\suppliers\models;

use Yii;
/**
* @OA\Schema(
*      schema="SupplierServiceType",
*      required={"Name"},
*     @OA\Property(
*        property="Id",
*        description="",
*        type="integer",
*        format="int64",
*    ),
*     @OA\Property(
*        property="Name",
*        description="",
*        type="string",
*        maxLength=250,
*    ),
* )
*/

/**
 * This is the model class for table "supplier_service_types".
 *
 * @property int $Id
 * @property string $Name
 *
 * @property SupplierService[] $supplierServices
 */
class SupplierServiceType extends \yii\db\ActiveRecord
{
    const SERVICE_TYPE_ID_JKH = 1;

    const SERVICE_TYPE_IDS = [
        self::SERVICE_TYPE_ID_JKH => 'ЖКХ',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'supplier_service_types';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Name'], 'required'],
            [['Name'], 'string', 'max' => 250],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'Id' => 'ID',
            'Name' => 'Name',
        ];
    }

    /**
    * @return \yii\db\ActiveQuery
    */
    public function getSupplierServices()
    {
        return $this->hasMany(SupplierService::className(), ['TypeId' => 'Id']);
    }

}
