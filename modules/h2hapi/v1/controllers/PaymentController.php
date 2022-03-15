<?php

namespace app\modules\h2hapi\v1\controllers;

use app\modules\h2hapi\v1\components\BaseApiController;
use app\modules\h2hapi\v1\jobs\ReversePaymentJob;
use app\modules\h2hapi\v1\objects\PaymentObject;
use app\modules\h2hapi\v1\services\PaymentApiService;
use app\modules\h2hapi\v1\services\paymentApiService\PaymentCreateException;
use app\services\payment\models\PaySchet;
use app\services\PaymentService;
use yii\base\InvalidConfigException;
use yii\queue\Queue;
use yii\web\ConflictHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class PaymentController extends BaseApiController
{
    /**
     * @var PaymentApiService
     */
    private $apiService;
    /**
     * @var Queue
     */
    private $queue;

    /**
     * {@inheritDoc}
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->apiService = \Yii::$app->get(PaymentApiService::class);
        $this->queue = \Yii::$app->queue;
    }

    /**
     * {@inheritDoc}
     */
    public function verbs(): array
    {
        return [
            'put' => ['PUT'],
            'put-reversed' => ['PUT'],
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

        if ($this->apiService->hasPayment($paySchet)) {
            throw new ConflictHttpException('Оплата по счету уже произведена.');
        }

        $paymentObject = new PaymentObject();
        $paymentObject->load(\Yii::$app->request->bodyParams, '');

        try {
            if ($paymentObject->validate()) {
                return $this->apiService->create($paySchet, $paymentObject);
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

    /**
     * Reverses the specified payment.
     *
     * @param mixed $paySchetId ID of payment to reverse.
     * @throws NotFoundHttpException
     * @throws ConflictHttpException
     */
    public function actionPutReversed($paySchetId)
    {
        $paySchet = $this->findPaySchet($paySchetId);

        if ($paySchet->Status !== PaySchet::STATUS_DONE) {
            throw new ConflictHttpException('Платеж не завершен.');
        }

        $this->queue->push(new ReversePaymentJob([
            'paySchetId' => $paySchet->ID,
        ]));

        \Yii::$app->response->content = '';
    }
}