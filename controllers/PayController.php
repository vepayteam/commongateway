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
use app\models\Payschets;
use app\models\TU;
use kartik\mpdf\Pdf;
use Yii;
use yii\db\Exception;
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
                    $csp .= ' frame-src ' . $params['URLSite'].';';
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
     * @throws \yii\db\Exception
     */
    public function actionCreatepay()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $payform = new PayForm();

            $payform->load(Yii::$app->request->post(), 'PayForm');
            if (!$payform->validate()) {
                return ['status' => 0, 'message' => $payform->GetError()];
            }

            Yii::warning("PayForm create id=" . $payform->IdPay);
            //данные счета для оплаты
            $payschets = new Payschets();
            $params = $payschets->getSchetData($payform->IdPay, null);

            $partner = Partner::findOne(['ID' => $params['IDPartner']]);

            if ($params && $params['DateCreate'] + $params['TimeElapsed'] > time()) {

                Yii::$app->session->set('IdPay', $params['ID']);

                if ($params['Status'] != 0 || $params['UserClickPay'] != 0 || !TU::IsInPay($params['IsCustom'])) {
                    //нельзя оплачивать
                    return $this->redirect(\yii\helpers\Url::to('/pay/orderok?id='.$params['ID']));
                }

                if (!$this->antifraud_validated($payform, Yii::$app->request->post('user_hash'))) {
                    //действие если антифрод проверку не прошел.
                    //пока оставить пустым т.к. собирается статистика.
                }

                $params['card']['number'] = $payform->CardNumber;
                $params['card']['holder'] = $payform->CardHolder;
                $params['card']['year'] = $payform->CardYear;
                $params['card']['month'] = $payform->CardMonth;
                $params['card']['cvc'] = $payform->CardCVC;

                //занести данные карты
                $payschets->SetCardPay($params['ID'], $params['card']);

                if ($params['IsCustom'] == TU::$POGASHATF) {
                    if (Cards::GetTypeCard($payform->CardNumber) == 6) {
                        //карты маэстро только по еком надо
                        $params['IsCustom'] = TU::$POGASHECOM;
                        $payschets->ChangeUsluga($params['ID'], $params['IDPartner'], $params['IsCustom']);
                    }
                }

                $bankClass = Banks::getBankClassByPayment($partner);
                $payschets->ChangeBank($params['ID'], $bankClass::$bank);
                $params['Bank'] = $bankClass::$bank;
                $merchBank = BankMerchant::Create($params);
                $ret = $merchBank->PayXml($params);

                if ($ret['status'] == 1) {
                    $payschets->SetStartPay($params['ID'], $ret['transac'], $payform->Email);

                    if ($params['IdUsluga'] == 1) {
                        //регистрация карты
                        $tokenizer = new Tokenizer();
                        if (($IdPan = $tokenizer->CheckExistToken($payform->CardNumber,$payform->CardMonth.$payform->CardYear)) == 0) {
                            $IdPan = $tokenizer->CreateToken($payform->CardNumber, $payform->CardMonth.$payform->CardYear, $payform->CardHolder);
                        }

                        if ($IdPan) {
                            //сохранить карту
                            $payschets->SaveCardPan($params['IdUser'], $params['card'], $IdPan);
                        }
                    }

                    //отправить запрос адреса формы 3ds
                    return [
                        'status' => 1,
                        'url' => $ret['url'],
                        'pa' => $ret['pa'],
                        'md' => $ret['md'],
                        'termurl' => $payform->GetRetUrl($params['ID']),
                    ];
                } elseif ($ret['status'] == 2) {
                    //отменить счет
                    $payschets->confirmPay([
                        'idpay' => $params['ID'],
                        'result_code' => 2,
                        'trx_id' => 0,
                        'ApprovalCode' => '',
                        'RRN' => '',
                        'message' => $ret['message']
                    ]);
                    return $this->redirect(\yii\helpers\Url::to('/pay/orderok?id='.$params['ID']));

                } else {
                    return $ret;
                }
            } else {
                return ['status' => 0, 'message' => 'Время для оплаты истекло'];
            }

        } else {
            throw new NotFoundHttpException();
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
    public function actionOrderdone($id)
    {
        $payschets = new Payschets();
        //данные счета для оплаты
        $params = $payschets->getSchetData($id, null);

        Yii::warning("PayForm done id=".$id);

        if ($params) {
            $md = Yii::$app->request->post('MD', '');
            if ($params['Status'] == 0 && !empty($md)) {
                //завершить платеж
                $merchBank = BankMerchant::Create($params);
                $ret = $merchBank->ConfirmXml([
                    'ID' => $params['ID'],
                    'ExtBillNumber' => $params['ExtBillNumber'],
                    'MD' => $md,
                    'PaRes' => Yii::$app->request->post('PaRes')
                ]);
                //ret статус проверить?
            }
            return $this->redirect(\yii\helpers\Url::to('/pay/orderok?id='.$id));

        } else {
            throw new NotFoundHttpException();
        }
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
        // TODO: DRY
        Yii::warning("PayForm orderok id=".$id);
        $SesIdPay = Yii::$app->session->get('IdPay');
        if (
            (!Yii::$app->user->isGuest && UserLk::IsAdmin(Yii::$app->user)) || ($id && $id == $SesIdPay)
        ) {
            //завершение оплаты + в колбэк приходит + в планировщике проверяется статус
            sleep(5); //подождать завершения оплаты

            $payschets = new Payschets();
            $params = $payschets->getSchetData($id, null);
            if (!$params) {
                throw new NotFoundHttpException();
            }
            $merchBank = BankMerchant::Create($params);
            $res = $merchBank->confirmPay($id);

            // Если платеж не в ожидание, и у платежа имеется PostbackUrl, отправляем
            if(!empty($params['PostbackUrl']) && in_array($res['status'], [1, 2, 3])) {
                $data = [
                    'status' => $res['status'],
                    'message' => $res['message'],
                    'id' => $params['ID'],
                    'amount' => $params['SummPay'],
                    'extid' => $params['Extid'],
                    'card_num' => $params['CardNum'],
                    'card_holder' => $params['CardHolder'],
                ];

                // TODO: queue
                try {
                    $this->sendPostbackRequest($params['PostbackUrl'], $data);
                } catch (\Exception $e) {
                    Yii::warning("Error $id postbackurl: ".$e->getMessage());
                }
            }

            // TODO:
            if($params['IdUsluga'] == 1 && $params['IdOrg'] == '3') {
                return $this->redirect("https://cashtoyou.ru/registration/third/");
            } elseif ($params['IdUsluga'] == 1 && $params['IdOrg'] == '8') {
                return $this->redirect("https://oneclickmoney.ru/registration/third/");
            }

            if (in_array($res['status'], [1, 3])) {

                $this->layout = 'order_done';
                return $this->render('order-ok', ['params' => $params]);

                if (!empty($params['SuccessUrl'])) {
                    //перевод на ok
                    return $this->redirect(Payschets::RedirectUrl($params['SuccessUrl'], $id, $params['Extid']));
                } else {
                    return $this->render('paydone', [
                        'message' => 'Оплата прошла успешно.'
                    ]);
                }

            } elseif (in_array($res['status'], [2])) {
                if (!empty($params['FailedUrl']) && (mb_stripos($res['message'], 'Отказ от оплаты') === false || empty($params['CancelUrl']))) {
                    //перевод на fail
                    return $this->redirect(Payschets::RedirectUrl($params['FailedUrl'], $id, $params['Extid']));
                } elseif (!empty($params['CancelUrl'])) {
                    //перевод на cancel
                    return $this->redirect(Payschets::RedirectUrl($params['CancelUrl'], $id, $params['Extid']));
                } else {
                    return $this->render('paycancel', ['message' => $res['message']]);
                }
            } else {
                return $this->render('paywait');
            }
        } else {
            throw new NotFoundHttpException();
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

    private function sendPostbackRequest($url, $data)
    {
        // TODO: refact to service
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
    }



}
