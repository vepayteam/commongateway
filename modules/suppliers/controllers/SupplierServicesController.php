<?php

namespace app\modules\suppliers\controllers;

use Yii;
use app\modules\suppliers\models\SupplierService;
use yii\data\ActiveDataProvider;
use app\modules\suppliers\controllers\BaseController;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * @OA\Tag(
 *   name="SupplierServices",
 *   description="",
 * )
 */
class SupplierServicesController extends BaseController
{
    public $modelClass = 'app\modules\suppliers\models\SupplierService';

    /**
     * @OA\Get(
     *     path="/supplier-services",
     *     summary="SupplierService",
     *     tags={"SupplierServices"},
     *     description="",
     *     operationId="findSupplierService",
     *     @OA\Parameter(
     *         name="ids",
     *         in="query",
     *         description="id",
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
     *             @OA\Items(ref="#/components/schemas/SupplierService")
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
            'query' => SupplierService::find()->with('creator')->with('updater'),
        ]);
        return $dataProvider;
    }

    /**
     * @OA\Get(
     *     path="/supplier-services/{id}",
     *     summary="",
     *     description="",
     *     operationId="getSupplierServiceById",
     *     tags={"SupplierServices"},
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
     *         @OA\JsonContent(ref="#/components/schemas/SupplierService")
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
     *     path="/supplier-services/create",
     *     tags={"SupplierServices"},
     *     operationId="addSupplierService",
     *     summary="",
     *     description="",
     *   @OA\RequestBody(
     *       required=true,
     *       description="SupplierService",
     *       @OA\JsonContent(ref="#/components/schemas/SupplierService"),
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(ref="#/components/schemas/SupplierService")
     *       )
     *   ),
     *     @OA\Response(
     *         response=201,
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/SupplierService")
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="",
     *     )
     * )
     */
    public function actionCreate()
    {
        $model = new SupplierService();
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
     *     path="/supplier-services/update/{id}",
     *     tags={"SupplierServices"},
     *     operationId="updateSupplierServiceById",
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
     *       description="SupplierService",
     *       @OA\JsonContent(ref="#/components/schemas/SupplierService"),
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(ref="#/components/schemas/SupplierService")
     *       )
     *   ),
     *     @OA\Response(
     *         response=200,
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/SupplierService")
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
     *     path="/supplier-services/{id}",
     *     summary="",
     *     description="",
     *     operationId="deleteSupplierService",
     *     tags={"SupplierServices"},
     *     @OA\Parameter(
     *         description="",
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
     * Finds the SupplierService model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SupplierService the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SupplierService::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested SupplierService does not exist.');
    }
}
