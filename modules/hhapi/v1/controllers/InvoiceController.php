<?php

namespace app\modules\hhapi\v1\controllers;

use app\modules\hhapi\v1\components\BaseApiController;
use app\modules\hhapi\v1\objects\InvoiceObject;
use app\modules\hhapi\v1\services\InvoiceApiService;
use app\modules\hhapi\v1\services\invoiceApiService\InvoiceCreateException;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\web\ConflictHttpException;
use yii\web\NotFoundHttpException;

/**
 * REST API Счета.
 */
class InvoiceController extends BaseApiController
{
    /**
     * @var InvoiceApiService
     */
    private $service;

    /**
     * {@inheritDoc}
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->service = \Yii::$app->get(InvoiceApiService::class);
    }

    /**
     * {@inheritDoc}
     */
    public function verbs(): array
    {
        return [
            'get' => ['GET'],
            'post' => ['POST'],
        ];
    }

    /**
     * @param $id
     * @throws NotFoundHttpException
     */
    public function actionGet($id): InvoiceObject
    {
        $paySchet = $this->findPaySchet($id);

        return $this->service->get($this->partner, $paySchet);
    }

    /**
     * @return InvoiceObject
     * @throws ConflictHttpException
     * @throws \ReflectionException
     */
    public function actionPost(): InvoiceObject
    {
        $invoiceObject = new InvoiceObject($this->partner);
        $invoiceObject->load(\Yii::$app->request->bodyParams, '');

        try {
            if ($invoiceObject->validate()) {
                $paySchet = $this->service->create($this->partner, $invoiceObject);

                \Yii::$app->response
                    ->setStatusCode(201)
                    ->getHeaders()
                    ->set('Location', Url::toRoute(['get', 'id' => $paySchet->ID], true));

                $paySchet->refresh();
                return $this->service->get($this->partner, $paySchet);
            }
        } catch (InvoiceCreateException $e) {
            if ($e->getCode() === InvoiceCreateException::NO_USLUGATOVAR) {
                throw new ConflictHttpException('Услуга не найдена.');
            } elseif ($e->getCode() === InvoiceCreateException::NO_GATE) {
                throw new ConflictHttpException('Шлюз не найден.');
            }
        }

        return $invoiceObject;
    }
}