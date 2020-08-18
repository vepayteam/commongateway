<?php

namespace app\modules\suppliers\models;

use Yii;
/**
* @OA\Schema(
*      schema="SupplierService",
*      required={"SupplierId","TypeId","Title"},
*     @OA\Property(
*        property="Id",
*        description="",
*        type="integer",
*        format="int64",
*    ),
*     @OA\Property(
*        property="SupplierId",
*        description="",
*        type="integer",
*        format="int64",
*    ),
*     @OA\Property(
*        property="TypeId",
*        description="",
*        type="integer",
*        format="int64",
*    ),
*     @OA\Property(
*        property="Title",
*        description="",
*        type="string",
*        maxLength=250,
*    ),
* )
*/

/**
 * This is the model class for table "supplier_services".
 *
 * @property int $Id
 * @property int $SupplierId
 * @property int $TypeId
 * @property string $Title
 *
 * @property Supplier $supplier
 * @property SupplierServiceType $type
 */
class SupplierService extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'supplier_services';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['SupplierId', 'TypeId', 'Title'], 'required'],
            [['SupplierId', 'TypeId'], 'integer'],
            [['Title'], 'string', 'max' => 250],
            [['SupplierId'], 'exist', 'skipOnError' => true, 'targetClass' => Supplier::className(), 'targetAttribute' => ['SupplierId' => 'Id']],
            [['TypeId'], 'exist', 'skipOnError' => true, 'targetClass' => SupplierServiceType::className(), 'targetAttribute' => ['TypeId' => 'Id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'Id' => 'ID',
            'SupplierId' => 'Supplier ID',
            'TypeId' => 'Type ID',
            'Title' => 'Title',
        ];
    }

    /**
    * @return \yii\db\ActiveQuery
    */
    public function getSupplier()
    {
        return $this->hasOne(Supplier::className(), ['Id' => 'SupplierId']);
    }

    /**
    * @return \yii\db\ActiveQuery
    */
    public function getType()
    {
        return $this->hasOne(SupplierServiceType::className(), ['Id' => 'TypeId']);
    }


}
