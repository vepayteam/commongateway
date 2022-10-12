<?php

namespace app\controllers;

use app\models\kfapi\KfRequest;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\CreateP2pForm;
use app\services\payment\forms\SendP2pForm;
use app\services\payment\models\PaySchet;
use app\services\payment\models\UslugatovarType;
use app\services\payment\payment_strategies\CreateP2pFormStrategy;
use app\services\payment\payment_strategies\SendP2pStrategy;
use Yii;
use yii\helpers\Url;
use yii\redis\Mutex;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class P2pController extends Controller
{
    /**
     * {@inheritDoc}
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, [
            'create',
            'send',
        ])) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $kf = new KfRequest();
        $kf->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(), 0);

        $createP2pForm = new CreateP2pForm();
        $createP2pForm->load($kf->req, '');
        if (!$createP2pForm->validate()) {
            return ['status' => 0, 'message' => $createP2pForm->GetError()];
        }

        $createP2pForm->partner = $kf->partner;
        $createP2pFormStrategy = new CreateP2pFormStrategy($createP2pForm);
        try {
            $paySchet = $createP2pFormStrategy->exec();
            return [
                'status' => 1,
                'id' => (int)$paySchet->ID,
                'url' => $createP2pForm->getPayForm($paySchet->ID),
                'message' => '',
            ];

        } catch (CreatePayException|GateException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        }
    }

    /**
     * @param $id
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionForm($id)
    {
        Yii::info("P2P: form open (ID: {$id}).");
        $paySchet = $this->findPaySchet($id);

        if ($paySchet->Status != PaySchet::STATUS_WAITING) {
            return $this->redirect(Url::to('/pay/orderok?id=' . $id));
        }

        //разрешить открытие во фрейме на сайте мерчанта
        $csp = "default-src 'self' 'unsafe-inline' https://mc.yandex.ru https://pay.google.com; " .
            "img-src 'self' data: https://mc.yandex.ru https://google.com/pay https://google.com/pay https://www.gstatic.com; " .
            "connect-src *; frame-src *;";
        Yii::$app->response->headers->add('Content-Security-Policy', $csp);

        $this->layout = 'base_p2p_layout';
        try {
            return $this->render('formpay', [
                'paySchet' => $paySchet,
            ]);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionSend($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $paySchet = $this->findPaySchet($id);

        if ($paySchet->ExtBillNumber !== null) {
            \Yii::warning("P2P: payment has already been made (PaySchet ID:{$paySchet->ID}).");
            return [
                'status' => 0,
                'message' => 'Оплата уже произведена.',
            ];
        }

        $sendP2pForm = new SendP2pForm($paySchet);
        $sendP2pForm->load(Yii::$app->request->post(), '');
        if (!$sendP2pForm->validate()) {
            return [
                'status' => 0,
                'message' => $sendP2pForm->GetError(),
            ];
        }

        $sendP2pStrategy = new SendP2pStrategy($sendP2pForm);

        $mutex = new Mutex();
        $mutexKey = static::class . "::actionSend({$id})";

        if ($mutex->acquire($mutexKey)) {
            try {
                $sendP2pStrategy->exec();
                $response = $sendP2pStrategy->sendP2pResponse;
                $result = [
                    'status' => (int)$response->status === BaseResponse::STATUS_DONE,
                    'message' => $response->message,
                    'url' => $response->url,
                ];
            } catch (CreatePayException|GateException $e) {
                \Yii::warning($e);
                $result = [
                    'status' => 0,
                    'message' => $e->getMessage(),
                ];
            } catch (\Exception $e) {
                \Yii::error($e);
                $result = [
                    'status' => 0,
                    'message' => 'Ошибка запроса.',
                ];
            } finally {
                $mutex->release($mutexKey);
            }
        } else {
            \Yii::warning("P2P: mutex locked on send (PaySchet ID:{$paySchet->ID}).");
            $result = [
                'status' => 0,
                'message' => 'Запрос в процессе обработки.',
            ];
        }

        return $result;
    }

    /**
     * @param $id
     * @return PaySchet
     * @throws NotFoundHttpException
     */
    private function findPaySchet($id): PaySchet
    {
        $paySchet = PaySchet::findOne(['ID' => $id]);
        if (!$paySchet) {
            throw new NotFoundHttpException('Форма для перевода не найдена.');
        }
        if ($paySchet->uslugatovar->IsCustom !== UslugatovarType::P2P) {
            \Yii::warning("P2P: incorrect uslugatovar type (PaySchet ID: {$id}).");
            throw new NotFoundHttpException('Форма для перевода не найдена.');
        }

        return $paySchet;
    }
}
