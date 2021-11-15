<?php

namespace app\controllers;

use app\models\payonline\OrderPay;
use app\models\Payschets;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\forms\OkPayForm;
use app\services\payment\models\PaySchet;
use app\services\payment\payment_strategies\CreatePayStrategy;
use app\services\payment\payment_strategies\DonePayStrategy;
use app\services\payment\payment_strategies\OkPayStrategy;
use app\services\payment\WidgetService;
use app\services\PaySchetService;
use Exception;
use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class WidgetController extends Controller
{
    public $layout = 'widgetlayout';

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();
    }

    public function actions(): array
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
     */
    public function beforeAction($action): bool
    {
        if (in_array($action->id, [
            'form',
            'pay',
            'createpay',
            'orderdone',
            'orderok'
        ])) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * Виджет для оплаты
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionOrder($id): string
    {
        $orderPay = OrderPay::findOne(['ID' => $id, 'IdDeleted' => 0]);
        if (!$orderPay) {
            throw new NotFoundHttpException("Счет не найден");
        }
        if ($orderPay->StateOrder !== 0) { // Статус должен быть установлен в 0 (ожидает оплаты)
            Yii::warning('WidgetController id ' .
                $id .
                ' статус не установлен в 0 (ожидание оплаты) текущий статус ' .
                $orderPay->StateOrder
            );
            throw new NotFoundHttpException("Счет не может быть оплачен");
        }

        $widgetService = new WidgetService($orderPay->IdPartner);
        $partner = $widgetService->getPartner();
        if (!$partner) {
            throw new NotFoundHttpException('Магазин не найден');
        }

        $uslugatovar = $widgetService->getUslugatovar();
        if (!$uslugatovar) {
            return $this->render('serviceunavailable', [
                'partner' => $partner
            ]);
        }

        $payForm = new CreatePayForm();

        return $this->render('index', [
            'order' => $orderPay,
            'isorder' => 1,
            'payform' => $payForm,
        ]);
    }

    /**
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPay(): string
    {
        $prov = Yii::$app->request->get('prov', 0);
        $id = Yii::$app->request->get('id', 0);
        $sum = Yii::$app->request->get('sum', 0);
        $info = htmlspecialchars(Yii::$app->request->get('info', ''));

        $widgetService = new WidgetService($prov);
        $partner = $widgetService->getPartner();
        if (!$partner) {
            throw new NotFoundHttpException('Магазин не найден');
        }

        $uslugatovar = $widgetService->getUslugatovar();
        if (!$uslugatovar) {
            return $this->render('serviceunavailable', [
                'partner' => $partner
            ]);
        }

        if ($sum <= 0) {
            throw new NotFoundHttpException('Неверная сумма платежа');
        }

        $orderPay = new OrderPay();
        $orderPay->IdPartner = $partner->ID;
        $orderPay->SumOrder = $sum;
        $orderPay->Comment = 'Заказ ' . $id . '. ' . $info;

        $payForm = new CreatePayForm();

        return $this->render('index', [
            'order' => $orderPay,
            'isorder' => 0,
            'payform' => $payForm,
        ]);
    }

    /**
     * @return array|Response
     * @throws NotFoundHttpException
     */
    public function actionCreatepay()
    {
        if (!Yii::$app->request->isAjax) {
            throw new NotFoundHttpException();
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        if (intval(Yii::$app->request->post('isorder', 1)) === 1) {
            $orderPay = OrderPay::findOne(['ID' => Yii::$app->request->post('IdOrder')]);
            if (!$orderPay) {
                Yii::warning('WidgetController не найден orderPay idOrder: ' . Yii::$app->request->post('IdOrder'));
                return ['status' => 0, 'message' => 'Счет не найден'];
            }
        } else {
            //без счета
            $orderPay = new OrderPay();
            if (!$orderPay->load(Yii::$app->request->post(), 'Order') || !$orderPay->validate()) {
                Yii::warning('WidgetController не удалось загрузить orderPay: ' . $orderPay->GetError());
                return ['status' => 0, 'message' => 'Ошибка данных заказа'];
            }
        }

        $widgetService = new WidgetService($orderPay->IdPartner);
        $partner = $widgetService->getPartner();
        $uslugatovar = $widgetService->getUslugatovar();
        if (!$partner || !$uslugatovar) {
            throw new NotFoundHttpException('Магазин не найден');
        }

        if (!$orderPay->IdPaySchet) {
            $idPaySchet = $widgetService->createPaySchet($orderPay, $uslugatovar);
            if ($idPaySchet === null) {
                return ['status' => 0, 'message' => 'Ошибка создания заказа'];
            }

            $orderPay->IdPaySchet = $idPaySchet;
        }

        $createPayForm = new CreatePayForm();
        $createPayForm->load(Yii::$app->request->post(), 'CreatePayForm');
        $createPayForm->IdPay = $orderPay->IdPaySchet;
        if (!$createPayForm->validate()) {
            Yii::warning('WidgetController не удалось загрузить createPayForm: ' . $createPayForm->GetError());
            return ['status' => 0, 'message' => $createPayForm->GetError()];
        }

        $paySchet = PaySchet::findOne(['ID' => $orderPay->IdPaySchet]);
        if ($widgetService->isExpired($paySchet)) {
            return ['status' => 0, 'message' => 'Время для оплаты истекло'];
        }

        $createPayStrategy = new CreatePayStrategy($createPayForm);
        try {
            $createPayStrategy->exec();
        } catch (Exception $e) {
            Yii::$app->errorHandler->logException($e);
            return ['status' => 0, 'message' => $e->getMessage()];
        }

        $createPayResponse = $createPayStrategy->getCreatePayResponse();
        $createPayResponse->termurl = $createPayForm->GetWidgetRetUrl($orderPay->IdPaySchet);
        switch ($createPayResponse->status) {
            case BaseResponse::STATUS_DONE:
                //отправить запрос адреса формы 3ds
                Yii::warning('WidgetController createPayResponse data: ' . Json::encode($createPayResponse->getAttributes()));
                return $createPayResponse->getAttributes();
            case BaseResponse::STATUS_ERROR:
                //отменить счет
                return $this->redirect(Url::to('/pay/orderok?id=' . $orderPay->IdPaySchet));
            case BaseResponse::STATUS_CREATED:
                $createPayResponse->termurl = $createPayResponse->getStep2Url($orderPay->IdPaySchet);
                return $createPayStrategy->getCreatePayResponse()->getAttributes();
            default:
                return $createPayStrategy->getCreatePayResponse()->getAttributes();
        }
    }

    /**
     * Завершение оплаты после 3DS(PCI DSS)
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionOrderdone($id): Response
    {
        $paySchet = PaySchet::findOne(['ID' => $id]);
        if (!$paySchet) {
            throw new NotFoundHttpException();
        }

        Yii::warning('WidgetController orderDone id=' . $id);

        if ($paySchet->Status === PaySchet::STATUS_WAITING) {
            $donePayForm = new DonePayForm([
                'IdPay' => $paySchet->ID,
                'md' => Yii::$app->request->post('MD'),
                'paRes' => Yii::$app->request->post('PaRes'),

            ]);
            $donePayStrategy = new DonePayStrategy($donePayForm);
            $donePayStrategy->exec();
        }

        return $this->redirect(Url::to('/widget/orderok?id=' . $id));
    }

    /**
     * Статус оплаты (PCI DSS)
     * @param $id
     * @return string|Response
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws GateException
     */
    public function actionOrderok($id)
    {
        $paySchet = PaySchet::findOne(['ID' => $id]);
        if (!$paySchet) {
            throw new NotFoundHttpException();
        }

        Yii::warning('WidgetController orderOk id=' . $id);

        $okPayForm = new OkPayForm(['IdPay' => $paySchet->ID]);
        $okPayStrategy = new OkPayStrategy($okPayForm);
        $paySchet = $okPayStrategy->exec();

        if (in_array($paySchet->Status, [PaySchet::STATUS_DONE, PaySchet::STATUS_CANCEL])) {
            if (!empty($paySchet->SuccessUrl)) {
                //перевод на ok
                return $this->redirect(Payschets::RedirectUrl($paySchet->SuccessUrl, $paySchet->ID, $paySchet->Extid));
            } else {
                return $this->render('paydone', [
                    'message' => 'Оплата прошла успешно.',
                    'paySchet' => $paySchet,
                ]);
            }

        } elseif (in_array($paySchet->Status, [PaySchet::STATUS_ERROR])) {
            if (!empty($paySchet->FailedUrl)) {
                $redirectUrl = mb_stripos($paySchet->ErrorInfo, 'Отказ от оплаты') === false
                    ? $paySchet->FailedUrl
                    : $paySchet->CancelUrl;

                return $this->redirect(Payschets::RedirectUrl($redirectUrl, $paySchet->ID, $paySchet->Extid));
            } else {
                return $this->render('paycancel', ['message' => $paySchet->ErrorInfo]);
            }
        } else {
            return $this->render('paywait');
        }
    }
}
