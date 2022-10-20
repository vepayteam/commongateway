<?php

namespace app\modules\mfo\controllers;

use app\models\api\CorsTrait;
use app\models\kfapi\KfFormPay;
use app\models\mfo\MfoReq;
use app\models\payonline\Cards;
use app\modules\mfo\jobs\recurrentPaymentParts\ExecutePaymentJob;
use app\modules\mfo\models\PayToCardForm;
use app\modules\mfo\models\RecurrentPaymentPartsForm;
use app\services\base\exceptions\InvalidInputParamException;
use app\services\compensationService\CompensationException;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\exceptions\NotUniquePayException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\CreatePayPartsForm;
use app\services\payment\forms\MfoCallbackForm;
use app\services\payment\forms\MfoLkPayForm;
use app\services\payment\models\Currency;
use app\services\payment\models\PaySchet;
use app\services\payment\payment_strategies\CreatePayPartsStrategy;
use app\services\payment\payment_strategies\mfo\MfoAutoPayStrategy;
use app\services\payment\payment_strategies\mfo\MfoPayLkCallbackStrategy;
use app\services\payment\payment_strategies\mfo\MfoPayLkCreateStrategy;
use app\services\PaySchetService;
use app\services\PayToCardService;
use app\services\payToCardService\CreatePaymentException;
use app\services\payToCardService\data\CreatePaymentData;
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
     * @var PayToCardService
     */
    private $payToCardService;
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
        $this->payToCardService = \Yii::$app->get(PayToCardService::class);

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

    /**
     * {@inheritDoc}
     */
    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        Yii::info([
            'endpoint' => $action->uniqueId,
            'header' => Yii::$app->request->headers->toArray(),
            'body' => Cards::maskCardUni(Yii::$app->request->post()),
            'return' => (array)$result,
        ], 'mfo_' . $action->controller->id . '_' . $action->id);

        return $result;
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

        } catch (NotUniquePayException $e) {
            return $this->asJson([
                'status' => 0,
                'message' => $e->getMessage(),
                'id' => $e->getPaySchetId(),
                'extid' => $e->getPaySchetExtId(),
            ])->setStatusCode(400);
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

        if ($result['status'] == 1) {
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
        if (!$createPayPartsForm->validate()) {
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
        } catch (NotUniquePayException $e) {
            return $this->asJson([
                'status' => 0,
                'message' => $e->getMessage(),
                'id' => $e->getPaySchetId(),
                'extid' => $e->getPaySchetExtId(),
            ])->setStatusCode(400);
        } catch (CreatePayException|GateException $e) {
            return [
                'status' => 2,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Автопогашение займа
     * @return array|Response
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
        if (!$autoPayForm->validate()) {
            Yii::warning("mfo/pay/auto: ошибка валидации формы");
            return ['status' => 0, 'message' => $autoPayForm->getError()];
        }

        if ($autoPayForm->getCard() && $autoPayForm->getCard()->Status === Cards::STATUS_BLOCKED) {
            Yii::warning('mfo/pay/auto: карта заблокирована.');
            return ['status' => 0, 'message' => 'Карта заблокирована.'];
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
        } catch (NotUniquePayException $e) {
            return $this->asJson([
                'status' => 0,
                'message' => $e->getMessage(),
                'id' => $e->getPaySchetId(),
                'extid' => $e->getPaySchetExtId(),
            ])->setStatusCode(400);
        } catch (CreatePayException $e) {
            return ['status' => 2, 'message' => $e->getMessage()];
        } catch (GateException $e) {
            return ['status' => 2, 'message' => $e->getMessage()];
        }

        return ['status' => 1, 'message' => '', 'id' => $paySchet->ID];
    }

    /**
     * @return array|Response
     * @throws ForbiddenHttpException
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function actionAutoParts()
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

            $card = Cards::findOne($form->card);
            if ($card->Status === Cards::STATUS_BLOCKED) {
                return ['status' => 0, 'message' => 'Карта заблокирована.'];
            }

            $paySchet = $this->recurrentPaymentService->createPayment($partner, $form);
            $this->queue->push(new ExecutePaymentJob(['paySchetId' => $paySchet->ID]));

            return ['status' => 1, 'message' => '', 'id' => $paySchet->ID];
        } catch (NotUniquePayException $e) {
            /**
             * Обработка NotUniquePayException {@see RecurrentPaymentPartsForm::validateExtId()}
             */

            return $this->asJson([
                'status' => 0,
                'message' => $e->getMessage(),
                'id' => $e->getPaySchetId(),
                'extid' => $e->getPaySchetExtId(),
            ])->setStatusCode(400);
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
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function actionPayToCard(): array
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());
        $partner = $mfo->getPartner();

        $form = new PayToCardForm($partner);
        $form->load($mfo->Req(), '');
        if (!$form->validate()) {
            return [
                'status' => 0,
                'message' => array_values($form->getFirstErrors())[0],
            ];
        }

        $currency = !empty($form->currency)
            ? Currency::findByCode($form->currency)
            : Currency::findDefaultCurrency();
        try {
            $paySchet = $this->payToCardService->createPayment($partner, new CreatePaymentData(
                round($form->amount * 100),
                $currency,
                $form->documentId,
                $form->fullName,
                $form->extId,
                $form->timeout,
                $form->successUrl,
                $form->failUrl,
                $form->cancelUrl,
                $form->language,
                $form->recipientCardNumber,
                $form->getPresetSenderCard(),
                $form->postbackUrl,
                $form->postbackUrlV2,
                (bool)$form->cardRegistration,
                $form->description
            ));
        } catch (CreatePaymentException $e) {
            switch ($e->getCode()) {
                case CreatePaymentException::NO_USLUGATOVAR:
                    return ['status' => 0, 'message' => 'Услуга не найдена.'];
                case CreatePaymentException::NO_GATE:
                    return ['status' => 0, 'message' => 'Шлюз не найден.'];
                case CreatePaymentException::TOKEN_ERROR:
                    return ['status' => 0, 'message' => 'Невозможно создать токен карты.'];
                default:
                    return ['status' => 0, 'message' => 'Ошибка оплаты.'];
            }
        }

        $urlForm = Yii::$app->params['domain'] . '/pay/form/' . $paySchet->ID;
        if ($paySchet->p2pRepayment->presetHash !== null) {
            $urlForm = $urlForm . '?' . http_build_query(['presetHash' => $paySchet->p2pRepayment->presetHash]);;
        }

        return [
            'status' => 1,
            'id' => $paySchet->ID,
            'url' => $urlForm,
            'message' => '',
        ];
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

        if (!$paySchet) {
            return ['status' => 0, 'message' => 'Счет не найден'];
        }

        if ($paySchet->Status == PaySchet::STATUS_WAITING) {
            return [
                'status' => 0,
                'serviceName' => $paySchet->uslugatovar->type->Name,
                'message' => 'В обработке',
                'rc' => '',
                'channel' => $paySchet->bank->ChannelName,
            ];
        } else {
            return [
                'status' => (int)$paySchet->Status,
                'serviceName' => $paySchet->uslugatovar->type->Name,
                'message' => (string)$paySchet->ErrorInfo,
                'rc' => $paySchet->RCCode,
                'channel' => $paySchet->bank->ChannelName,
            ];
        }
    }

    public function actionInfo()
    {
        $mfo = new MfoReq();
        $mfo->LoadData(Yii::$app->request->getRawBody());

        // TODO: DRY
        $paySchetId = $mfo->GetReq('id');
        $paySchet = PaySchet::findOne([
            'ID' => $paySchetId,
            'IdOrg' => $mfo->mfo,
        ]);

        if (!$paySchet) {
            return ['status' => 0, 'message' => 'Счет не найден'];
        }

        if ($paySchet->Status == PaySchet::STATUS_WAITING) {
            return [
                'status' => 0,
                'serviceName' => $paySchet->uslugatovar->type->Name,
                'message' => 'В обработке',
                'rc' => '',
                'channel' => $paySchet->bank->ChannelName,
                'gate_transaction_id' => $paySchet->ExtBillNumber,
            ];
        } else {
            return [
                'status' => (int)$paySchet->Status,
                'serviceName' => $paySchet->uslugatovar->type->Name,
                'message' => (string)$paySchet->ErrorInfo,
                'rc' => $paySchet->RCCode,
                'channel' => $paySchet->bank->ChannelName,
                'gate_transaction_id' => $paySchet->ExtBillNumber,
            ];
        }

    }

    /**
     * Callback-action после оплаты
     * @return array
     * @throws \Exception
     */
    public function actionCallback(): array
    {
        $data = Yii::$app->request->post();

        $form = new MfoCallbackForm();

        if (!$form->load($data, '') || !$form->validate()) {
            Yii::warning("pay/callback: " . $form->getError());
            return ['status' => 0, 'message' => $form->getError()];
        }

        $message = sprintf(
            '/pay/callback id=%s token=%s',
            $form->order_id,
            $form->cardToken
        );
        Yii::warning($message, 'mfo');
        $callbackStrategy = new MfoPayLkCallbackStrategy($form);

        try {
            $callbackStrategy->exec();
        } catch (InvalidInputParamException|\Exception $e) {
            Yii::warning("pay/callback: " . $e->getMessage());
            return ['status' => 0, 'message' => 'Ошибка запроса'];
        }

        return ['status' => 1, 'message' => ''];
    }
}
