<?php

namespace app\controllers;

use app\models\antifraud\AntiFraud;
use app\models\antifraud\tables\AFFingerPrit;
use app\models\bank\BankMerchant;
use app\models\bank\ApplePay;
use app\models\bank\Banks;
use app\models\bank\GooglePay;
use app\models\bank\IBank;
use app\models\bank\MTSBank;
use app\models\bank\SamsungPay;
use app\models\bank\TCBank;
use app\models\crypt\Tokenizer;
use app\models\partner\UserLk;
use app\models\payonline\Cards;
use app\models\payonline\Partner;
use app\models\payonline\PayForm;
use app\models\payonline\Uslugatovar;
use app\models\Payschets;
use app\models\TU;
use app\services\payment\banks\bank_adapter_responses\BaseResponse;
use app\services\payment\banks\bank_adapter_responses\CreatePayResponse;
use app\services\payment\banks\BankAdapterBuilder;
use app\services\payment\banks\TKBankAdapter;
use app\services\payment\exceptions\BankAdapterResponseException;
use app\services\payment\exceptions\Check3DSv2DuplicatedException;
use app\services\payment\exceptions\reRequestingStatusOkException;
use app\services\payment\exceptions\reRequestingStatusException;
use app\services\payment\exceptions\Check3DSv2Exception;
use app\services\payment\exceptions\CreatePayException;
use app\services\payment\exceptions\GateException;
use app\services\payment\forms\CreatePayForm;
use app\services\payment\forms\CreatePaySecondStepForm;
use app\services\payment\forms\DonePayForm;
use app\services\payment\forms\OkPayForm;
use app\services\payment\interfaces\Cache3DSv2Interface;
use app\services\payment\models\PaySchet;
use app\services\payment\payment_strategies\CreatePayStrategy;
use app\services\payment\payment_strategies\DonePayStrategy;
use app\services\payment\payment_strategies\OkPayStrategy;
use kartik\mpdf\Pdf;
use Yii;
use yii\db\Exception;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class PayController extends Controller
{
    public $layout = 'paylayout';

    public function actions()
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
    public function beforeAction($action)
    {
        if (in_array($action->id, [
            'form-data',
            'save-data',
            'form',
            'orderdone',
            'orderok',
            'createpay-second-step',
            'createpay',
        ])) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    public function actionFormData($id)
    {
        Yii::warning("SetData open id=".$id);
        $payschets = new Payschets();
        //данные счета для оплаты
        $params = $payschets->getSchetData($id, null);
        $formData = $payschets->getSchetFormData($id);

        if (!$params || !TU::IsInPay($params['IsCustom']) || !$formData) {
            throw new NotFoundHttpException("Счет для оплаты не найден");
        }

        //разрешить открытие во фрейме на сайте мерчанта
        $csp = "default-src 'self' 'unsafe-inline' https://mc.yandex.ru; img-src 'self' data: https://mc.yandex.ru; connect-src 'self' https://mc.yandex.ru;";
        if (!empty($params['URLSite'])) {
            $csp .= ' frame-src ' . $params['URLSite'].';';
        }
        Yii::$app->response->headers->add('Content-Security-Policy', $csp);
        return $this->render('formdata', ['params' => $params, 'formData' => $formData]);
    }

    public function actionSaveData($id)
    {
        Yii::warning("SaveData open id=".$id);
        $payschets = new Payschets();
        if (!$payschets->validateAndSaveSchetFormData($id, Yii::$app->request->post())) {
            throw new BadRequestHttpException();
        }
        return $this->redirect(\yii\helpers\Url::to('/pay/form/'.$id));
    }

    /**
     * Форма оплаты своя (PCI DSS)
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionForm($id)
    {
        Yii::warning("PayForm open id=".$id);
        $payschets = new Payschets();
        //данные счета для оплаты
        $params = $payschets->getSchetData($id, null);
        $payform = new PayForm();
        if ($params && TU::IsInPay($params['IsCustom'])) {
            if ($params['Status'] == 0 && $params['UserClickPay'] == 0 &&
                $params['DateCreate'] + $params['TimeElapsed'] > time()
            ) {
                $payschets->SetIpAddress($params['ID']);

                //разрешить открытие во фрейме на сайте мерчанта
                $csp = "default-src 'self' 'unsafe-inline' https://mc.yandex.ru https://pay.google.com; ".
                    "img-src 'self' data: https://mc.yandex.ru https://google.com/pay https://google.com/pay https://www.gstatic.com; ".
                    "connect-src 'self' https://mc.yandex.ru https://play.google.com;";
                if (!empty($params['URLSite'])) {
                    $csp .= ' frame-src *;';
                }
                Yii::$app->response->headers->add('Content-Security-Policy', $csp);

                $ApplePay = new ApplePay();
                $apple = $ApplePay->GetConf($params['IDPartner']);
                $GooglePay = new GooglePay();
                $google = $GooglePay->GetConf($params['IDPartner']);
                $SamsungPay = new SamsungPay();
                $samsung = $SamsungPay->GetConf($params['IDPartner']);

                return $this->render('formpay', ['params' => $params, 'apple' => $apple, 'google' => $google, 'samsung' => $samsung, 'payform' => $payform]);

            } else {
                return $this->redirect(\yii\helpers\Url::to('/pay/orderok?id='.$id));
            }
        } else {
            throw new NotFoundHttpException("Счет для оплаты не найден");
        }
    }

    /**
     * Форма оплаты своя (PCI DSS)
     * @return array|Response
     * @throws NotFoundHttpException
     * @throws \app\services\payment\exceptions\MerchantRequestAlreadyExistsException
     * @throws reRequestingStatusException
     * @throws reRequestingStatusOkException
     */
    public function actionCreatepay()
    {
        if(!Yii::$app->request->isAjax) {
            throw new NotFoundHttpException();
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        $form = new CreatePayForm();

        if(!$form->load(Yii::$app->request->post(), 'PayForm') || !$form->validate()) {
            return ['status' => 0, 'message' => $form->GetError()];
        }
        Yii::warning("PayForm create id=" . $form->IdPay);
        Yii::$app->session->set('IdPay', $form->IdPay);

        $createPayStrategy = new CreatePayStrategy($form);

        try {
            $paySchet = $createPayStrategy->exec();
        } catch (CreatePayException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        } catch (GateException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        } catch (reRequestingStatusException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        } catch (reRequestingStatusOkException $e) {
            return [
                'status' => 2,
                'message' => $e->getMessage(),
                'url' => Yii::$app->params['domain'] . '/pay/orderok?id=' . $form->IdPay,
            ];
        } catch (Check3DSv2DuplicatedException $e) {
            //отменить счет
            return [
                'status' => 2,
                'message' => $e->getMessage(),
                'url' => Yii::$app->params['domain'] . '/pay/orderok?id=' . $form->IdPay,
            ];
        }  catch (BankAdapterResponseException $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        } catch (Check3DSv2Exception $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        } catch (Exception $e) {
            return ['status' => 0, 'message' => $e->getMessage()];
        }

        $createPayResponse = $createPayStrategy->getCreatePayResponse();
        $createPayResponse->termurl = $createPayResponse->GetRetUrl($paySchet->ID);
        switch ($createPayResponse->status) {
            case BaseResponse::STATUS_DONE:
                //отправить запрос адреса формы 3ds
                Yii::warning('PayController createPayResponse data: ' . Json::encode($createPayResponse->getAttributes()));
                return $createPayResponse->getAttributes();
            case BaseResponse::STATUS_ERROR:
                //отменить счет
                return $this->redirect(\yii\helpers\Url::to('/pay/orderok?id=' . $form->IdPay));
            case BaseResponse::STATUS_CREATED:
                $createPayResponse->termurl = $createPayResponse->getStep2Url($paySchet->ID);
                return $createPayStrategy->getCreatePayResponse()->getAttributes();
            default:
                return $createPayStrategy->getCreatePayResponse()->getAttributes();
        }
    }

    public function actionCreatepaySecondStep($id)
    {
        // TODO: DRY
        if(Yii::$app->request->isPost) {
            return $this->redirect(\yii\helpers\Url::to('/pay/orderdone/' . $id));
        }

        // TODO: refact
        $createPaySecondStepForm = new CreatePaySecondStepForm();
        $createPaySecondStepForm->IdPay = $id;

        $paySchet = $createPaySecondStepForm->getPaySchet();
        $bankAdapterBuilder = new BankAdapterBuilder();
        $bankAdapterBuilder->buildByBank($paySchet->partner, $paySchet->uslugatovar, $paySchet->bank);

        /** @var TKBankAdapter $tkbAdapter */
        $tkbAdapter = $bankAdapterBuilder->getBankAdapter();
        $createPayResponse = $tkbAdapter->createPayStep2($createPaySecondStepForm);
        $paySchet->IsNeed3DSVerif = $createPayResponse->isNeed3DSVerif;
        $paySchet->save(false);

        if($createPayResponse->isNeed3DSVerif) {
            return $this->render('createpay-second-step', ['createPayResponse' => $createPayResponse]);
        } else {
            return $this->redirect(\yii\helpers\Url::to('/pay/orderdone/' . $paySchet->ID));
        }
    }

    /**
     * Отказаться от оплтаты
     * @return array
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionDeclinepay()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $payschets = new Payschets();
            $params = $payschets->getSchetData(Yii::$app->request->post('ID'),null);
            if ($params && $params['Status'] == 0 && $params['UserClickPay'] == 0 && TU::IsInPay($params['IsCustom'])) {
                Yii::$app->session->set('IdPay', $params['ID']);
                //отменить счет
                $payschets->confirmPay([
                    'idpay' => $params['ID'],
                    'result_code' => 2,
                    'trx_id' => 0,
                    'ApprovalCode' => '',
                    'RRN' => '',
                    'message' => 'Отказ от оплаты'
                ]);
                return ['status' => 1];
            }
            return ['status' => 0];
        } else {
            throw new NotFoundHttpException("Счет для оплаты не найден");
        }
    }

    /**
     * Завершение оплаты после 3DS(PCI DSS)
     * @param $id
     * @return string
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionOrderdone($id = null)
    {
        $donePayForm = new DonePayForm();
        $donePayForm->IdPay = $id;
        $donePayForm->trans = Yii::$app->request->post('trans_id', null);

        // Для тестирования, добавляем возможность передать ид транзакции GET параметром
        if(!empty($trans = Yii::$app->request->get('trans_id', null))) {
            $donePayForm->trans = $trans;
        }

        $donePayForm->md = Yii::$app->request->post('MD', null);
        $donePayForm->paRes = Yii::$app->request->post('PaRes', null);
        $donePayForm->cres = Yii::$app->request->post('cres', null);

        if(!empty($donePayForm->cres)) {
            Yii::$app->cache->set(Cache3DSv2Interface::CACHE_PREFIX_CRES, $donePayForm->cres, 60 * 60);
        }

        Yii::warning('Orderdone ' . $id . 'POST: ' . json_encode(Yii::$app->request->post()));

        if(!$donePayForm->validate()) {
            throw new BadRequestHttpException();
        }

        Yii::warning("PayForm done id=".$id);
        $donePayStrategy = new DonePayStrategy($donePayForm);
        $paySchet = $donePayStrategy->exec();

        return $this->redirect(\yii\helpers\Url::to('/pay/orderok?id=' . $paySchet->ID));
    }

    /**
     * Статус оплаты (PCI DSS)
     * @param $id
     * @return string
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionOrderok($id)
    {
        Yii::warning("PayForm orderok id=" . $id);

        // Дадим время, чтобы банк закрыл платеж
        sleep(5);

        $okPayForm = new OkPayForm();
        $okPayForm->IdPay = $id;

        if(!$okPayForm->existPaySchet()) {
            throw new NotFoundHttpException();
        }

        $okPayStrategy = new OkPayStrategy($okPayForm);
        $paySchet = $okPayStrategy->exec();

        // TODO:
        if($paySchet->IdUsluga == Uslugatovar::TYPE_REG_CARD && $paySchet->IdOrg == '3') {
            return $this->redirect("https://cashtoyou.ru/registration/third/");
        } elseif ($paySchet->IdUsluga == Uslugatovar::TYPE_REG_CARD && $paySchet->IdOrg == '8') {
            return $this->redirect("https://oneclickmoney.ru/registration/third/");
        }

        if (in_array($paySchet->Status, [PaySchet::STATUS_DONE, PaySchet::STATUS_CANCEL])) {

            $this->layout = 'order_done';
            return $this->render('order-ok', ['paySchet' => $paySchet]);

        } elseif ($paySchet->Status == PaySchet::STATUS_ERROR) {
            if (!empty($paySchet->FailedUrl) && (mb_stripos($paySchet->ErrorInfo, 'Отказ от оплаты') === false || empty($paySchet->CancelUrl))) {
                //перевод на fail
                return $this->redirect(Payschets::RedirectUrl($paySchet->FailedUrl, $id, $paySchet->Extid));
            } elseif (!empty($params['CancelUrl'])) {
                //перевод на cancel
                return $this->redirect(Payschets::RedirectUrl($paySchet->CancelUrl, $id, $paySchet->Extid));
            } else {
                return $this->render('paycancel', ['message' => $paySchet->ErrorInfo]);
            }
        } else {
            return $this->render('paywait');
        }
    }

    public function actionOrderPrint($id)
    {
        // TODO: DRY
        Yii::warning("PayForm orderprint id=".$id);
        $SesIdPay = Yii::$app->session->get('IdPay');
        if (!$id || $id != $SesIdPay) {
            throw new NotFoundHttpException();
        }

        $payschets = new Payschets();
        $params = $payschets->getSchetData($id, null);
        if (!$params || $params['Status'] != 1) {
            throw new NotFoundHttpException();
        }

        $this->layout = null;
        return $this->render('order-print', [
            'params' => $params,
            'isPage' => true,
        ]);
    }

    public function actionOrderInvoice($id)
    {
        // TODO: DRY
        Yii::warning("PayForm orderinvoice id=".$id);
        $SesIdPay = Yii::$app->session->get('IdPay');
        if (!$id || $id != $SesIdPay) {
            throw new NotFoundHttpException();
        }

        $payschets = new Payschets();
        $params = $payschets->getSchetData($id, null);
        if (!$params || $params['Status'] != 1) {
            throw new NotFoundHttpException();
        }

        $content = $this->renderPartial('order-print', [
            'params' => $params,
            'isPage' => false,
        ]);

        $pdf = new Pdf([
            // 'mode' => Pdf::MODE_CORE,
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'destination' => Pdf::DEST_BROWSER,
            'content' => $content,
            'cssFile' => '@webroot/aassets/css/order-print.css',
            // 'cssInline' => '.kv-heading-1{font-size:18px}',
        ]);

        // return the pdf output as per the destination setting
        return $pdf->render();
    }

    public function actionApplepayvalidate()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $payschets = new Payschets();
            $params = $payschets->getSchetData((int)Yii::$app->request->post('IdPay'),null);
            if (!$params) {
                throw new NotFoundHttpException();
            }

            $bank = BankMerchant::GetApplePayBank();
            $payschets->ChangeBank($params['ID'], $bank);

            $validationURL = Yii::$app->request->post('validationURL');
            if (!empty($validationURL)) {
                $ApplePay = new ApplePay();
                return [
                    'status' => $ApplePay->ValidateSession($params['IDPartner'], $validationURL)
                ];
            }
            return ['status' => 0];
        }
        return '';
    }

    public function actionApplepaycreate()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $payschets = new Payschets();
            $params = $payschets->getSchetData((int)Yii::$app->request->post('IdPay'),null);
            if (!$params) {
                throw new NotFoundHttpException();
            }

            $paymentToken = Yii::$app->request->post('paymentToken');
            $merchBank = BankMerchant::Create($params);
            $ApplePay = new ApplePay();
            $res = $merchBank->PayApple($params + ['Apple_MerchantID' => $ApplePay->GetConf($params['IDPartner'])['Apple_MerchantID'], 'PaymentToken' => $paymentToken]);

            if ($res) {
                return ['status' => 1];
            }
            return ['status' => 0, 'message' => 'Ошибка запроса'];
            //return $this->redirect(\yii\helpers\Url::to('/pay/orderok?id='.$params['ID']));
        }
        return '';
    }

    public function actionGooglepaycreate()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $payschets = new Payschets();
            $params = $payschets->getSchetData((int)Yii::$app->request->post('IdPay'),null);
            if (!$params) {
                throw new NotFoundHttpException();
            }

            $paymentToken = Yii::$app->request->post('paymentToken');
            $merchBank = BankMerchant::Create($params);
            $res = $merchBank->PayGoogle($params + ['PaymentToken' => $paymentToken]);

            if ($res) {
                return ['status' => 1];
            }
            return ['status' => 0, 'message' => 'Ошибка запроса'];
            //return $this->redirect(\yii\helpers\Url::to('/pay/orderok?id='.$params['ID']));
        }
        return '';
    }

    public function actionSamsungpaycreate()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $payschets = new Payschets();
            $params = $payschets->getSchetData((int)Yii::$app->request->post('IdPay'),null);
            if (!$params) {
                throw new NotFoundHttpException();
            }

            $paymentToken = Yii::$app->request->post('paymentToken');
            $merchBank = BankMerchant::Create($params);
            $res = $merchBank->PaySamsung($params + ['PaymentToken' => $paymentToken]);

            if ($res) {
                return ['status' => 1];
            }
            return ['status' => 0, 'message' => 'Ошибка запроса'];
            //return $this->redirect(\yii\helpers\Url::to('/pay/orderok?id='.$params['ID']));
        }
        return '';
    }

//    public function actionRegisterTracking(){
//        Yii::$app->response->format = Response::FORMAT_JSON;
//        if ()
//        new FingerPrint();
//    }

    private function antifraud_validated(PayForm $form, $user_hash): bool
    {
        /**@var AFFingerPrit $finger_print */
        try{
            $antifraud = new AntiFraud($form->IdPay);
            $validated = $antifraud->validate($user_hash, $form->CardNumber);
            return $validated;
        } catch (\Throwable $e) {
            return true;
        }
    }
}
