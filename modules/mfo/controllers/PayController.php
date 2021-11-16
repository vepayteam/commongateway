<?php

namespace app\modules\mfo\controllers;

use app\models\api\CorsTrait;
use app\models\kfapi\KfFormPay;
use app\models\mfo\MfoReq;
use app\modules\mfo\jobs\recurrentPaymentParts\ExecutePaymentJob;
use app\modules\mfo\models\RecurrentPaymentPartsForm;
use app\services\compensationService\CompensationException;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\CreatePayPartsForm;
use app\services\payment\forms\MfoLkPayForm;
use app\services\payment\models\PaySchet;
use app\services\payment\payment_strategies\CreatePayPartsStrategy;
use app\services\payment\payment_strategies\mfo\MfoAutoPayStrategy;
use app\services\payment\payment_strategies\mfo\MfoPayLkCreateStrategy;
use app\services\PaySchetService;
use app\services\RecurrentPaymentPartsService;
use app\services\recurrentPaymentPartsService\PaymentException;
use Yii;
use yii\base\Exception;
use yii\queue\Queue;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;


class PayController extends Controller
{
    use CorsTrait;

    /**
     * @var PaySchetService
     */
    private $paySchetService;
    /**
     * @var RecurrentPaymentPartsService
     */
    private $recurrentPaymentService;
    /**
     * @var Queue
     */
    private $queue;

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();

        $this->paySchetService = \Yii::$app->get(PaySchetService::class);
        $this->recurrentPaymentService = \Yii::$app->get(RecurrentPaymentPartsService::class);
        $this->queue = \Yii::$app->queue;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $this->updateBehaviorsCors($behaviors);
        return $behaviors;
    }

    /**
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if ($this->checkBeforeAction()) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $this->enableCsrfValidation = false;
            return parent::beforeAction($action);
        }
        return false;
    }

    protected function verbs()
    {
        return [
            'lk' => ['POST'],
            'form-lk' => ['POST'],
            'lk-parts' => ['POST'],
            'auto' => ['POST'],
            'state' => ['POST'],
        ];
    }

    /**
     * Погашение займа из ЛК МФО
     * @return array|mixed
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \Exception
     */
    public function actionLk()
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        $form = new MfoLkPayForm();
        $form->partner = $mfo->getPartner();
        if (!$form->load($mfo->Req(), '') || !$form->validate()) {
            Yii::warning("pay/lk: " . $form->GetError());
            return ['status' => 0, 'message' => $form->getError()];
        }

        // рубли в копейки
        // TODO: in model validation
        // TODO: in other currency conversation
        $form->amount *= 100;
        $form->client = $mfo->getRequestData('client');

        $message = sprintf(
            '/pay/lk mfo=%d sum=%d currency=%s extid=%d',
            $mfo->mfo,
            $form->amount,
            $form->currency,
            $form->extid
        );
        Yii::warning($message, 'mfo');
        $paymentStrategy = new MfoPayLkCreateStrategy($form);

        try {

            $payschet = $paymentStrategy->exec();

        } catch (CreatePayException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        } catch (GateException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        } catch (CompensationException $e) {
            $message = 'Ошибка расчета комисси.';
            if ($e->getCode() === CompensationException::NO_EXCHANGE_RATE) {
                $message = 'Обменный курс для расчета комисси не найден.';
            }
            return ['status' => 0, 'message' => $message];
        }

        $urlForm = Yii::$app->params['domain'] . '/pay/form/' . $payschet->ID;
        return [
            'status' => 1,
            'id' => (int)$payschet->ID,
            'url' => $urlForm,
            'message' => ''
        ];
    }

    public function actionFormLk()
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        $kfFormPay = new KfFormPay();
        $kfFormPay->scenario = KfFormPay::SCENARIO_FORM;
        $kfFormPay->load($mfo->Req(), '');
        if (!$kfFormPay->validate()) {
            return ['status' => 0, 'message' => $kfFormPay->GetError()];
        }
        $result = $this->actionLk();

        if($result['status'] == 1) {
            $kfFormPay->createFormElements($result['id']);
            $result['url'] = $kfFormPay->GetPayForm($result['id']);
        }
        return $result;
    }

    public function actionLkParts()
    {
        $mfoReq = new MfoReq();
        $mfoReq->LoadData(Yii::$app->request->getRawBody());

        $createPayPartsForm = new CreatePayPartsForm();
        $createPayPartsForm->partner = $mfoReq->getPartner();
        $createPayPartsForm->load($mfoReq->Req(), '');
        if(!$createPayPartsForm->validate()) {
            Yii::error("pay/lk: " . $createPayPartsForm->GetError());
            return ['status' => 0, 'message' => $createPayPartsForm->GetError()];
        }
        Yii::warning('/pay/lk mfo=' . $mfoReq->mfo . " sum=" . $createPayPartsForm->amount . " extid=" . $createPayPartsForm->extid, 'mfo');

        $createPayPartsStrategy = new CreatePayPartsStrategy($createPayPartsForm);
        try {
            $paySchet = $createPayPartsStrategy->exec();
            $urlForm = Yii::$app->params['domain'] . '/pay/form/' . $paySchet->ID;
            return [
                'status' => 1,
                'message' => '',
                'id' => $paySchet->ID,
                'url' => $urlForm,
            ];
        } catch (CreatePayException |GateException $e) {
            return [
                'status' => 2,
                'message' => $e->getMessage(),
            ];
        }
    }

        /**
     * Автопогашение займа
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     * @throws \Exception
     */
    public function actionAuto()
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());
        Yii::warning("mfo/pay/auto Authorization mfo=$mfo->mfo", 'mfo');
        $autoPayForm = new AutoPayForm();
        $autoPayForm->partner = $mfo->getPartner();
        $autoPayForm->load($mfo->Req(), '');
        if(!$autoPayForm->validate()) {
            Yii::warning("mfo/pay/auto: ошибка валидации формы");
            return ['status' => 0, 'message' => $autoPayForm->getError()];
        }
    
        if ($autoPayForm->getCard() && (!$autoPayForm->getCard()->ExtCardIDP || $autoPayForm->getCard()->ExtCardIDP == '0')) {
            Yii::warning("mfo/pay/auto: у карты нет ExtCardIDP");
            $autoPayForm->addError('card', 'Карта не зарегистрирована');
            return ['status' => 0, 'message' => $autoPayForm->getError()];
        }
        
        Yii::warning("mfo/pay/auto AutoPayForm extid=$autoPayForm->extid amount=$autoPayForm->amount", 'mfo');
        // рубли в копейки
        $autoPayForm->amount *= 100;

        $mfoAutoPayStrategy = new MfoAutoPayStrategy($autoPayForm);
        try {
            $paySchet = $mfoAutoPayStrategy->exec();
        } catch (CreatePayException $e) {
            return ['status' => 2, 'message' => $e->getMessage()];
        } catch (GateException $e) {
            return ['status' => 2, 'message' => $e->getMessage()];
        }

        return ['status' => 1, 'message' => '', 'id' => $paySchet->ID];
    }

    /**
     * @throws \yii\web\UnauthorizedHttpException
     * @throws ForbiddenHttpException
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws Exception
     */
    public function actionAutoParts(): array
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());
        $partner = $mfo->getPartner();

        $form = new RecurrentPaymentPartsForm($partner);
        $form->load($mfo->Req(), '');

        try {
            if (!$form->validate()) {
                return ['status' => 0, 'message' => array_values($form->getFirstErrors())[0]];
            }
            $paySchet = $this->recurrentPaymentService->createPayment($partner, $form);
            $this->queue->push(new ExecutePaymentJob(['paySchetId' => $paySchet->ID]));

            return ['status' => 1, 'message' => '', 'id' => $paySchet->ID];
        } catch (PaymentException $e) {
            switch ($e->getCode()) {
                case PaymentException::CARD_EXPIRED:
                    return ['status' => 0, 'message' => 'Карта просрочена.'];
                case PaymentException::EMPTY_CARD:
                    return ['status' => 0, 'message' => 'Пустая карта.'];
                case PaymentException::NO_USLUGATOVAR:
                    return ['status' => 0, 'message' => 'Услуга не найдена.'];
                case PaymentException::NO_PAN_TOKEN:
                    return ['status' => 0, 'message' => 'Отсутствует Pan Token.'];
                case PaymentException::NO_GATE:
                    return ['status' => 0, 'message' => 'Шлюз не найден.'];
                case PaymentException::BANK_EXCEPTION:
                    return ['status' => 2, 'message' => 'Ошибка банка.'];
                default:
                    return ['status' => 2, 'message' => 'Ошибка оплаты.'];
            }
        }
    }

    /**
     * Статус платежа погашения
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function actionState()
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        $paySchetId = $mfo->GetReq('id');

        $paySchet = PaySchet::findOne([
            'ID' => $paySchetId,
            'IdOrg' => $mfo->mfo,
        ]);

        if(!$paySchet) {
            return ['status' => 0, 'message' => 'Счет не найден'];
        }

        if($paySchet->Status == PaySchet::STATUS_WAITING) {
            return [
                'status' => 0,
                'message' => 'В обработке',
                'rc' => '',
                'channel' => $paySchet->bank->ChannelName,
            ];
        } else {
            return [
                'status' => (int)$paySchet->Status,
                'message' => (string)$paySchet->ErrorInfo,
                'rc' => $paySchet->RCCode,
                'channel' => $paySchet->bank->ChannelName,
            ];
        }
    }

}
