<?php

namespace app\modules\suppliers\controllers;

use Yii;
use app\modules\suppliers\models\SupplierServiceType;
use yii\data\ActiveDataProvider;
use app\modules\suppliers\controllers\BaseController;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * @OA\Tag(
 *   name="SupplierServiceTypes",
 *   description="",
 * )
 */
class SupplierServiceTypesController extends BaseController
{
    public $modelClass = 'app\modules\suppliers\models\SupplierServiceType';

    /**
     * @OA\Get(
     *     path="/supplier-service-types",
     *     summary="SupplierServiceType",
     *     tags={"SupplierServiceTypes"},
     *     description="",
     *     operationId="findSupplierServiceType",
     *     @OA\Parameter(
     *         name="ids",
     *         in="query",
     *         description="逗号隔开的 id",
     *         required=false,
     *         @OA\Schema(
     *           type="string",
     *           @OA\Items(type="string"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="",
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/SupplierServiceType")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="",
     *     )
     * )
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => SupplierServiceType::find()->with('creator')->with('updater'),
        ]);
        return $dataProvider;
    }

    /**
     * @OA\Get(
     *     path="/supplier-service-types/{id}",
     *     summary="",
     *     description="",
     *     operationId="getSupplierServiceTypeById",
     *     tags={"SupplierServiceTypes"},
     *     @OA\Parameter(
     *         description="id",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *           format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/SupplierServiceType")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description=""
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description=""
     *     )
     * )
     */
    public function actionView($id)
    {
        return $this->findModel($id);
    }

    /**
     * @OA\Post(
     *     path="/supplier-service-types",
     *     tags={"SupplierServiceTypes"},
     *     operationId="addSupplierServiceType",
     *     summary="",
     *     description="",
     *   @OA\RequestBody(
     *       required=true,
     *       description="",
     *       @OA\JsonContent(ref="#/components/schemas/SupplierServiceType"),
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(ref="#/components/schemas/SupplierServiceType")
     *       )
     *   ),
     *     @OA\Response(
     *         response=201,
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/SupplierServiceType")
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="",
     *     )
     * )
     */
    public function actionCreate()
    {
        $model = new SupplierServiceType();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }
        return $model;
    }

    /**
     * @OA\Put(
     *     path="/supplier-service-types/{id}",
     *     tags={"SupplierServiceTypes"},
     *     operationId="updateSupplierServiceTypeById",
     *     summary="",
     *     description="",
     *     @OA\Parameter(
     *         description="id",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *           format="int64"
     *         )
     *     ),
     *   @OA\RequestBody(
     *       required=true,
     *       description="SupplierServiceType",
     *       @OA\JsonContent(ref="#/components/schemas/SupplierServiceType"),
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(ref="#/components/schemas/SupplierServiceType")
     *       )
     *   ),
     *     @OA\Response(
     *         response=200,
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/SupplierServiceType")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="",
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="",
     *     )
     * )
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->getBodyParams(), '') && $model->save()) {
            Yii::$app->response->setStatusCode(200);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }
        return $model;
    }

    /**
     * @OA\Delete(
     *     path="/supplier-service-types/{id}",
     *     summary="删除SupplierServiceType",
     *     description="",
     *     operationId="deleteSupplierServiceType",
     *     tags={"SupplierServiceTypes"},
     *     @OA\Parameter(
     *         description="需要删除数据的ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description=""
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description=""
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description=""
     *     )
     * )
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }
        Yii::$app->getResponse()->setStatusCode(204);
    }

    /**
     * Finds the SupplierServiceType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SupplierServiceType the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SupplierServiceType::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested SupplierServiceType does not exist.');
    }
}
