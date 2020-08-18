<?php

namespace app\modules\suppliers\models;

use Yii;
/**
* @OA\Schema(
*      schema="Supplier",
*      required={"Name"},
*     @OA\Property(
*        property="Name",
*        description="",
*        type="string",
*        maxLength=250,
*    ),
*     @OA\Property(
*        property="SchetTcb",
*        description="",
*        type="string",
*        maxLength=40,
*    ),
* )
*/

/**
 * This is the model class for table "suppliers".
 *
 * @property int $Id
 * @property int $PartnerId
 * @property string $Name
 * @property string $SchetTcb
 *
 * @property SupplierService[] $supplierServices
 */
class Supplier extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'suppliers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Name', 'PartnerId'], 'required'],
            [['Name'], 'string', 'max' => 250],
            [['SchetTcb'], 'string', 'max' => 40],
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
            'SchetTcb' => 'Schet Tcb',
        ];
    }

    /**
    * @return \yii\db\ActiveQuery
    */
    public function getSupplierServices()
    {
        return $this->hasMany(SupplierService::className(), ['SupplierId' => 'Id']);
    }

    public static function findOneByPartnerId($id, $partnerId)
    {
        return self::find()
            ->where(['PartnerId' => $partnerId, 'Id' => $id])
            ->one();
    }

}
