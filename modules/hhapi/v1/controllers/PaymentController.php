<?php

namespace app\modules\hhapi\v1\controllers;

use app\modules\hhapi\v1\components\BaseApiController;
use app\modules\hhapi\v1\objects\PaymentObject;
use app\modules\hhapi\v1\services\PaymentApiService;
use app\modules\hhapi\v1\services\paymentApiService\PaymentCreateException;
use yii\base\InvalidConfigException;
use yii\web\ConflictHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * REST API оплаты.
 */
class PaymentController extends BaseApiController
{
    /**
     * @var PaymentApiService
     */
    private $service;

    /**
     * {@inheritDoc}
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->service = \Yii::$app->get(PaymentApiService::class);
    }

    /**
     * {@inheritDoc}
     */
    public function verbs(): array
    {
        return [
            'put' => ['PUT'],
        ];
    }

    /**
     * @param $paySchetId
     * @return PaymentObject
     * @throws ConflictHttpException
     * @throws ServerErrorHttpException
     * @throws \ReflectionException
     * @throws NotFoundHttpException
     */
    public function actionPut($paySchetId): PaymentObject
    {
        $paySchet = $this->findPaySchet($paySchetId);

        if ($this->service->hasPayment($paySchet)) {
            throw new ConflictHttpException('Оплата по счету уже произведена.');
        }

        $paymentObject = new PaymentObject();
        $paymentObject->load(\Yii::$app->request->bodyParams, '');

        try {
            if ($paymentObject->validate()) {
                return $this->service->create($paySchet, $paymentObject);
            }
        } catch (PaymentCreateException $e) {
            if ($e->getCode() === PaymentCreateException::INVOICE_EXPIRED) {
                throw new ConflictHttpException('Время ожидания оплаты вышло.');
            } elseif ($e->getCode() === PaymentCreateException::NO_GATE) {
                throw new ConflictHttpException('Шлюз не найден.');
            } elseif ($e->getCode() === PaymentCreateException::CREATE_PAY_ERROR) {
                throw new ConflictHttpException('Ошибка проведения платежа: ' . $e->getLegacyMessage());
            } elseif ($e->getCode() === PaymentCreateException::BANK_ADAPTER_ERROR) {
                throw new ServerErrorHttpException('Ошибка подключения к банку: ' . $e->getLegacyMessage());
            } else {
                throw new ServerErrorHttpException($e->getMessage());
            }
        }

        return $paymentObject;
    }
}