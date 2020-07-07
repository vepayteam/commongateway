<?php

namespace app\modules\suppliers\controllers;

use app\modules\suppliers\models\Supplier;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * @OA\Tag(
 *   name="Suppliers",
 *   description="Поставщики",
 *   @OA\ExternalDocumentation(
 *     description="",
 *     url="http://dakara.cn"
 *   )
 * )
 */
class SupplierController extends BaseController
{
    public $modelClass = 'app\services\suppliers\models\Supplier';

    /**
     * @OA\Get(
     *     path="/suppliers",
     *     summary="Supplier",
     *     tags={"Suppliers"},
     *     description="",
     *     operationId="findSupplier",
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
     *             @OA\Items(ref="#/components/schemas/Supplier")
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
            'query' => Supplier::find(),
        ]);
        return $dataProvider;
    }

    /**
     * @OA\Get(
     *     path="/suppliers/{id}",
     *     summary="",
     *     description="",
     *     operationId="getSupplierById",
     *     tags={"Suppliers"},
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
     *         @OA\JsonContent(ref="#/components/schemas/Supplier")
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
     *     path="/suppliers",
     *     tags={"Suppliers"},
     *     operationId="addSupplier",
     *     summary="",
     *     description="",
     *   @OA\RequestBody(
     *       required=true,
     *       description="",
     *       @OA\JsonContent(ref="#/components/schemas/Supplier"),
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(ref="#/components/schemas/Supplier")
     *       )
     *   ),
     *     @OA\Response(
     *         response=201,
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/Supplier")
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="",
     *     )
     * )
     */
    public function actionCreate()
    {
        $model = new Supplier();
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
     *     path="/suppliers/{id}",
     *     tags={"Suppliers"},
     *     operationId="updateSupplierById",
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
     *       description="",
     *       @OA\JsonContent(ref="#/components/schemas/Supplier"),
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(ref="#/components/schemas/Supplier")
     *       )
     *   ),
     *     @OA\Response(
     *         response=200,
     *         description="",
     *         @OA\JsonContent(ref="#/components/schemas/Supplier")
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
     *     path="/suppliers/{id}",
     *     summary="",
     *     description="",
     *     operationId="deleteSupplier",
     *     tags={"Suppliers"},
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
     * Finds the Supplier model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Supplier the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Supplier::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested Supplier does not exist.');
    }
}
