<?php

namespace app\modules\suppliers\controllers;

use app\modules\suppliers\models\Supplier;
use app\services\suppliers\SuppliersService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
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
class SuppliersController extends BaseController
{
    public $modelClass = 'app\services\suppliers\models\Supplier';

    protected function verbs()
    {
        return [
            'create' => ['POST'],
        ];
    }

    /**
     * @OA\Get(
     *     path="/suppliers",
     *     summary="Supplier",
     *     tags={"Suppliers"},
     *     description="",
     *     operationId="findSupplier",
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
        $models = $this->getSuppliersService()->getSuppliersByPartnerId($this->partnerId);
        return $models;
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
        $models = $this->getSuppliersService()->getSupplierByPartnerId($id, $this->partnerId);
        return $models;
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
     *         response=400,
     *         description="",
     *     )
     * )
     */
    public function actionCreate()
    {
        $model = $this->getSuppliersService()->createSupplierByPartnerId($this->partnerId, $this->body);

        if($model === false) {
            throw new BadRequestHttpException();
        }

        $response = Yii::$app->getResponse();
        $response->setStatusCode(201);
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
        $model = $this->getSuppliersService()->updateSupplierByPartnerId($id, $this->partnerId, $this->body);
        if($model === false) {
            throw new BadRequestHttpException();
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
        $model = $this->getSuppliersService()->getSupplierByPartnerId($id, $this->partnerId);
        if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }
        Yii::$app->getResponse()->setStatusCode(204);
    }

    /**
     * @return SuppliersService
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function getSuppliersService()
    {
        return Yii::$container->get('SuppliersService');
    }
}
