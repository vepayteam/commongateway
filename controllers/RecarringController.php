<?php

namespace app\controllers;

use app\models\api\CorsTrait;
use app\models\api\Reguser;
use app\models\kfapi\KfCard;
use app\models\kfapi\KfPay;
use app\models\kfapi\KfRequest;
use app\models\payonline\CreatePay;
use app\models\payonline\Uslugatovar;
use app\models\TU;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\AutoPayForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\helpers\PaymentHelper;
use app\services\payment\models\PaySchet;
use app\services\payment\payment_strategies\mfo\MfoAutoPayStrategy;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

class RecarringController extends Controller
{
    use CorsTrait;

    /**
     * @return array
     */
    public function behaviors(): array
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
    public function beforeAction($action): bool
    {
        if ($this->checkBeforeAction()) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $this->enableCsrfValidation = false;
            return parent::beforeAction($action);
        }

        return false;
    }

    /**
     * @return array
     */
    protected function verbs(): array
    {
        return [
            'info' => ['POST'],
            'reg' => ['POST'],
            'get' => ['POST'],
            'del' => ['POST'],
            'pay' => ['POST'],
            'state' => ['POST']
        ];
    }

    /**
     * Получить данные карты
     * @return array
     * @throws BadRequestHttpException
     * @throws \yii\db\Exception
     * @throws UnauthorizedHttpException
     * @throws ForbiddenHttpException
     */
    public function actionInfo(): array
    {
        $kfRequest = new KfRequest();
        $kfRequest->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(), 0);

        $kfCard = new KfCard();
        $kfCard->scenario = KfCard::SCENARIO_INFO;
        $kfCard->load($kfRequest->req, '');
        if (!$kfCard->validate()) {
            $err = $kfCard->GetError();
            Yii::warning('recarring/info: ошибка валидации формы: ' . $err);
            return ['status' => 0, 'message' => $err];
        }

        $card = $kfCard->FindKard($kfRequest->IdPartner, 0);
        if (!$card) {
            Yii::warning('recarring/info: карта не найдена idPartner=' . $kfRequest->IdPartner);
            return ['status' => 0, 'message' => 'Карта не найдена'];
        }

        return [
            'status' => 1,
            'card' => [
                'id' => intval($card->ID),
                'num' => $card->CardNumber,
                'exp' => $card->getMonth() . "/" . $card->getYear()
            ]
        ];
    }

    /**
     * Зарегистрировать карту
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws UnauthorizedHttpException
     */
    public function actionReg(): array
    {
        $kfRequest = new KfRequest();
        $kfRequest->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(), 0);

        $kfCard = new KfCard();
        $kfCard->scenario = KfCard::SCENARIO_REG;

        $regUser = new Reguser();
        $extUser = $kfRequest->IdPartner . '-' . time();

        $user = $regUser->findUser('0', $extUser, md5($extUser), $kfRequest->IdPartner, false);
        if (!$user) {
            Yii::warning('recarring/reg: пользователь не найден idPartner=' . $kfRequest->IdPartner);
            return ['status' => 0, 'message' => 'Пользователь не найден'];
        }

        $uslugatovar = Uslugatovar::findOne(['IDPartner' => $kfRequest->IdPartner, 'IsCustom' => TU::$REGCARD]);
        if (!$uslugatovar) {
            Yii::warning('recarring/reg: услуга ' . TU::$REGCARD . ' не найдена idPartner=' . $kfRequest->IdPartner);
            return ['status' => 0, 'message' => 'Услуга не найдена'];
        }

        try {
            $bankAdapterBuilder = new BankAdapterBuilder();
            $bankAdapter = $bankAdapterBuilder->build($kfRequest->partner, $uslugatovar)->getBankAdapter();
        } catch (GateException $e) {
            Yii::warning('recarring/reg: ' . $e->getMessage());
            return ['status' => 0, 'message' => $e->getMessage()];
        }

        $pay = new CreatePay($user);
        $data = $pay->payActivateCard(0, $kfCard, 3, $bankAdapter->getBankId(), $kfRequest->IdPartner);

        //PCI DSS form
        return [
            'status' => 1,
            'id' => intval($data['IdPay']),
            'url' => $kfCard->GetRegForm($data['IdPay'])
        ];
    }

    /**
     * Получить данные карты после платежа регистранции карты
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws UnauthorizedHttpException
     */
    public function actionGet(): array
    {
        $kfRequest = new KfRequest();
        $kfRequest->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(), 0);

        $kfCard = new KfCard();
        $kfCard->scenario = KfCard::SCENARIO_GET;
        $kfCard->load($kfRequest->req, '');
        if (!$kfCard->validate()) {
            $err = $kfCard->GetError();
            Yii::warning('recarring/get: ошибка валидации формы: ' . $err);
            return ['status' => 0, 'message' => $err];
        }

        $paySchet = PaySchet::findOne(['ID' => $kfCard->id]);
        if (!$paySchet) {
            Yii::warning('recarring/get: paySchet не найден id=' . $kfCard->id);
            return ['status' => 0, 'message' => 'Счет не найден'];
        }

        $uslugatovar = Uslugatovar::findOne(['ID' => $paySchet->IdUsluga]);
        $partner = $kfRequest->partner;
        if (!$uslugatovar || !$partner) {
            Yii::warning('recarring/get: partner или uslugatovar не найдена paySchet id=' . $kfCard->id);
            return ['status' => 0, 'message' => 'Счет не найден'];
        }

        $donePayForm = new DonePayForm(['IdPay' => $paySchet->ID]);

        $bankAdapterBuilder = new BankAdapterBuilder();
        try {
            $bankAdapter = $bankAdapterBuilder->build($partner, $uslugatovar)->getBankAdapter();
            $bankAdapter->confirm($donePayForm);
        } catch (GateException $e) {
            Yii::warning('recarring/get: ' . $e->getMessage());
            return ['status' => 0, 'message' => $e->getMessage()];
        }

        $card = $kfCard->FindKardByPay($kfRequest->IdPartner, 0);
        if (!$card) {
            Yii::warning('recarring/get: карта не найдена idPartner=' . $kfRequest->IdPartner);
            return ['status' => 0, 'message' => 'Карта не найдена'];
        }

        return [
            'status' => 1,
            'card' => [
                'id' => intval($card->ID),
                'num' => $card->CardNumber,
                'exp' => $card->getMonth() . "/" . $card->getYear()
            ]
        ];
    }

    /**
     * Удалить карту (у нас)
     * @return array
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     * @throws \yii\db\Exception
     * @throws ForbiddenHttpException
     */
    public function actionDel(): array
    {
        $kfRequest = new KfRequest();
        $kfRequest->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(), 0);

        $kfCard = new KfCard();
        $kfCard->scenario = KfCard::SCENARIO_INFO;
        $kfCard->load($kfRequest->req, '');
        if (!$kfCard->validate()) {
            $err = $kfCard->GetError();
            Yii::warning('recarring/del: ошибка валидации формы: ' . $err);
            return ['status' => 0, 'message' => $err];
        }

        $card = $kfCard->FindKard($kfRequest->IdPartner, 0);
        if (!$card) {
            Yii::warning('recarring/del: карта не найдена idPartner=' . $kfRequest->IdPartner);
            return ['status' => 0, 'message' => ''];
        }

        //удалить карту
        $card->IsDeleted = 1;
        $card->save(false);

        return ['status' => 1];
    }

    /**
     * Автоплатеж
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws UnauthorizedHttpException
     */
    public function actionPay(): array
    {
        $kfRequest = new KfRequest();
        $kfRequest->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(), 0);

        $autoPayForm = new AutoPayForm();
        $autoPayForm->partner = $kfRequest->partner;
        $autoPayForm->load($kfRequest->req, '');

        if (!$autoPayForm->validate()) {
            $err = $autoPayForm->getError();
            Yii::warning('recarring/pay: ошибка валидации формы: ' . $err);
            return ['status' => 0, 'message' => $err];
        }

        Yii::warning("recarring/pay: autoPayForm extid=$autoPayForm->extid amount=$autoPayForm->amount");
        $autoPayForm->amount = PaymentHelper::convertToPenny($autoPayForm->amount); // рубли в копейки

        $mfoAutoPayStrategy = new MfoAutoPayStrategy($autoPayForm);
        try {
            $paySchet = $mfoAutoPayStrategy->exec();
        } catch (CreatePayException | GateException $e) {
            Yii::warning('recarring/pay: mfoAutoPayStrategy exec exception: ' . $e->getMessage());
            return ['status' => 2, 'message' => $e->getMessage()];
        }

        return ['status' => 1, 'message' => '', 'id' => $paySchet->ID];
    }

    /**
     * Статус платежа
     * @return array
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws \yii\db\Exception
     * @throws UnauthorizedHttpException
     */
    public function actionState(): array
    {
        $kfRequest = new KfRequest();
        $kfRequest->CheckAuth(Yii::$app->request->headers, Yii::$app->request->getRawBody(), 0);

        $kfPay = new KfPay();
        $kfPay->scenario = KfPay::SCENARIO_STATE;
        $kfPay->load($kfRequest->req, '');
        if (!$kfPay->validate()) {
            $err = $kfPay->GetError();
            Yii::warning('recarring/state: ошибка валидации формы: ' . $err);
            return ['status' => 0, 'message' => $err];
        }

        $paySchet = PaySchet::findOne(['ID' => $kfPay->id]);
        if (!$paySchet) {
            Yii::warning('recarring/state: paySchet не найден id=' . $kfPay->id);
            return ['status' => 0, 'message' => 'Счет не найден'];
        }

        $uslugatovar = Uslugatovar::findOne(['ID' => $paySchet->IdUsluga]);
        $partner = $kfRequest->partner;
        if (!$uslugatovar || !$partner) {
            Yii::warning('recarring/state: partner или uslugatovar не найдена paySchet id=' . $kfPay->id);
            return ['status' => 0, 'message' => 'Счет не найден'];
        }

        $donePayForm = new DonePayForm(['IdPay' => $paySchet->ID]);

        $bankAdapterBuilder = new BankAdapterBuilder();
        try {
            $bankAdapter = $bankAdapterBuilder->build($partner, $uslugatovar)->getBankAdapter();
            $payResponse = $bankAdapter->confirm($donePayForm);
        } catch (GateException $e) {
            Yii::warning('recarring/state: ');
            return ['status' => 0, 'message' => $e->getMessage()];
        }

        return ['status' => intval($payResponse->status), 'message' => $payResponse->message];
    }
}
