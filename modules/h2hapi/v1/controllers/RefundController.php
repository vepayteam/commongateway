<?php

namespace app\modules\h2hapi\v1\controllers;

use app\modules\h2hapi\v1\components\BaseApiController;
use app\modules\h2hapi\v1\jobs\ExecuteRefundPaymentJob;
use app\modules\h2hapi\v1\objects\RefundObject;
use app\services\payment\models\PaySchet;
use app\services\PaymentService;
use app\services\paymentService\CreateRefundException;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\queue\Queue;
use yii\web\ConflictHttpException;
use yii\web\NotFoundHttpException;

class RefundController extends BaseApiController
{
    /**
     * @var PaymentService
     */
    private $paymentService;
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

        $this->paymentService = \Yii::$app->get(PaymentService::class);
        $this->queue = \Yii::$app->queue;
    }

    /**
     * {@inheritDoc}
     */
    public function verbs(): array
    {
        return [
            'post' => ['POST'],
            'get' => ['GET'],
        ];
    }

    /**
     * Creates an additional refund payment {@see PaySchet} for the specified source payment.
     *
     * @param mixed $paySchetId ID of the source payment to refund.
     * @return RefundObject
     * @throws ConflictHttpException
     * @throws CreateRefundException
     * @throws NotFoundHttpException
     * @throws \ReflectionException
     */
    public function actionPost($paySchetId): RefundObject
    {
        $paySchet = $this->findPaySchet($paySchetId);

        if ($paySchet->Status !== PaySchet::STATUS_DONE) {
            throw new ConflictHttpException('Платеж не завершен.');
        }

        $refundObject = new RefundObject();
        $refundObject->load(\Yii::$app->request->bodyParams, '');

        if ($refundObject->validate()) {
            try {
                $refundPayschet = $this->paymentService->createRefundPayment($paySchet, $refundObject->amountFractional);
            } catch (CreateRefundException $e) {
                switch ($e->getCode()) {
                    case CreateRefundException::AMOUNT_EXCEEDED:
                        throw new ConflictHttpException('Сумма возврата превышена.');
                    case CreateRefundException::GATE_NOT_FOUND:
                        throw new ConflictHttpException('Не найден шлюз при расчете комиссии.');
                    case CreateRefundException::COMPENSATION_ERROR:
                        throw new ConflictHttpException('Ошибка расчета комиссии.');
                    default:
                        throw $e;
                }
            }

            $this->queue->push(new ExecuteRefundPaymentJob(['refundPaySchetId' => $refundPayschet->ID]));

            \Yii::$app->response
                ->setStatusCode(201)
                ->getHeaders()->set('Location', Url::toRoute(['get', 'id' => $refundPayschet->ID], true));

            $refundObject->mapRefundPayschet($refundPayschet);
        }

        return $refundObject;
    }

    /**
     * @param $refundPayschetId
     * @return RefundObject
     * @throws NotFoundHttpException
     */
    public function actionGet($refundPayschetId): RefundObject
    {
        $refund = $this->findRefundPayschet($refundPayschetId);

        return (new RefundObject())->mapRefundPayschet($refund);
    }

    /**
     * @param $refundPayschetId
     * @return PaySchet
     * @throws NotFoundHttpException
     */
    private function findRefundPayschet($refundPayschetId): PaySchet
    {
        /** @var PaySchet $paySchet */
        $paySchet = PaySchet::find()
            ->andWhere([
                'ID' => $refundPayschetId,
                'IdOrg' => $this->partner->ID,
            ])
            ->andWhere(['is not', 'RefundSourceId', null])
            ->one();
        if ($paySchet === null) {
            throw new NotFoundHttpException('Возврат не найден.');
        }

        return $paySchet;
    }
}