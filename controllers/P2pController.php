<?php


namespace app\controllers;


use app\models\bank\ApplePay;
use app\models\bank\GooglePay;
use app\models\bank\SamsungPay;
use app\models\kfapi\KfRequest;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\CreateP2pForm;
use app\services\payment\forms\SendP2pForm;
use app\services\payment\models\PaySchet;
use app\services\payment\payment_strategies\CreateP2pFormStrategy;
use app\services\payment\payment_strategies\SendP2pStrategy;
use Yii;
use yii\helpers\Url;
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

        } catch (CreatePayException | GateException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        }
    }

    public function actionForm($id)
    {
        Yii::warning("P2P open id={$id}");
        $paySchet = PaySchet::findOne(['ID' => $id]);
        if(!$paySchet) {
            throw new NotFoundHttpException("Форма дял перевода не найдена");
        }

        if($paySchet->Status != PaySchet::STATUS_WAITING) {
            return $this->redirect(Url::to('/pay/orderok?id=' . $id));
        }

        //разрешить открытие во фрейме на сайте мерчанта
        $csp = "default-src 'self' 'unsafe-inline' https://mc.yandex.ru https://pay.google.com; " .
            "img-src 'self' data: https://mc.yandex.ru https://google.com/pay https://google.com/pay https://www.gstatic.com; " .
            "connect-src *; frame-src *;";
        Yii::$app->response->headers->add('Content-Security-Policy', $csp);

        $this->layout = false;
        return $this->render('formpay', [
            'paySchet' => $paySchet,
        ]);
    }

    public function actionSend($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post = Yii::$app->request->post();

        $paySchet = PaySchet::findOne(['ID' => $id]);
        if(!$paySchet) {
            throw new NotFoundHttpException("Форма для перевода не найдена");
        }

        $sendP2pForm = new SendP2pForm();
        $sendP2pForm->load($post, '');
        if(!$sendP2pForm->validate()) {
            return [
                'status' => 0,
                'message' => $sendP2pForm->GetError(),
            ];
        }
        $sendP2pForm->paySchet = $paySchet;

        $sendP2pStrategy = new SendP2pStrategy($sendP2pForm);
        try {
            $sendP2pStrategy->exec();
            $sendP2pResponse = $sendP2pStrategy->sendP2pResponse;
            return $sendP2pResponse->getAttributes();
        } catch (\Exception $e) {
            return [
                'status' => 2,
                'message' => 'Ошибка запроса',
            ];
        }
    }
}
