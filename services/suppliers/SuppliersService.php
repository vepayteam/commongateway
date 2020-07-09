<?php


namespace app\services\suppliers;


use app\modules\suppliers\models\Supplier;
use app\modules\suppliers\models\SupplierService;
use Yii;
use yii\web\ServerErrorHttpException;

class SuppliersService
{
    /**
     * @param $partnerId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getSuppliersByPartnerId($partnerId)
    {
        $models = Supplier::find()->where('PartnerId', $partnerId)->all();
        return $models;
    }

    /**
     * @param $partnerId
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getSupplierServicesByPartnerId($partnerId)
    {
        $models = SupplierService::find()->where('PartnerId', $partnerId)->all();
        return $models;
    }

    /**
     * @param $id
     * @param $partnerId
     * @return array|\yii\db\ActiveRecord|null
     */
    public function getSupplierByPartnerId($id, $partnerId)
    {
        $model = Supplier::findOneByPartnerId($id, $partnerId);
        return $model;
    }

    public function getSupplierServiceByPartnerId($id, $partnerId)
    {
        $model = SupplierService::findOneByPartnerId($id, $partnerId);
        return $model;
    }

    /**
     * @param $partnerId
     * @param $data
     * @return Supplier|bool
     */
    public function createSupplierByPartnerId($partnerId, $data)
    {
        $data['PartnerId'] = $partnerId;

        $model = new Supplier();
        if (!$model->load($data, '') || !$model->save()) {
            return false;
        }
        return $model;
    }

    /**
     * @param $partnerId
     * @param $data
     * @return SupplierService|bool
     */
    public function createSupplierServiceByPartnerId($partnerId, $data)
    {
        $data['PartnerId'] = $partnerId;

        $model = new SupplierService();
        if (
            !$model->load($data, '')
            || !$model->hasErrors()
            || $model->supplier->PartnerId != $partnerId
            || !$model->save()
        ) {
            return false;
        }
        return $model;
    }

    /**
     * @param $id
     * @param $partnerId
     * @param $data
     * @return array|bool|\yii\db\ActiveRecord
     */
    public function updateSupplierByPartnerId($id, $partnerId, $data)
    {
        $model = Supplier::findOneByPartnerId($id, $partnerId);

        if (!$model || !$model->load($data, '') || !$model->save()) {
            return false;
        }
        return $model;
    }

    public function updateSupplierServiceByPartnerId($id, $partnerId, $data)
    {
        $model = SupplierService::findOneByPartnerId($id, $partnerId);

        if (!$model || !$model->load($data, '') || !$model->save()) {
            return false;
        }
        return $model;
    }

}
